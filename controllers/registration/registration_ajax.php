<?
header('Access-Control-Allow-Origin: *');

require_once ROOT . '/lib/sberbank.php';
require_once ROOT . '/lib/messaging.php';
require_once ROOT . '/lib/shorty/shorty.php';
require_once ROOT . '/lib/exa_pms.php';

$call_admin_new_link = load_tpl('/views/parts/call_admin_new_link.tpl', [], true);
$tg_error_message    = '';

if (!empty($_POST) && isset($_POST['link'])) {
  $link        = $_POST['link'];
  $shorty      = new Shorty();
  $shorty_link = $shorty->get($link);

  if ($shorty_link) {
    preg_match('/pre_order#(\d+)#/ui', $shorty_link, $link_data);
    if (!empty($link_data[1])) {
      $pre_order_id = $link_data[1];
    }
  }

  if (!isset($pre_order_id) || $pre_order_id <= 0) {
    exit(json_html('/views/page.tpl',
      ['title' => 'Некорректная ссылка на регистрацию' . $_POST['link'], 'text' => $call_admin_new_link]));
  }

  // Если не найден предзаказ
  $pre_order = $db->getRow("
    SELECT
      pre_orders.*,
      marketing_agents.name AS marketing_agent_name,
      service_agents.name AS service_agent_name
    FROM pre_orders
      RIGHT JOIN marketing_agents ON marketing_agents.id = marketing_agent_id
      RIGHT JOIN service_agents ON service_agents.id = service_agent_id
    WHERE pre_orders.id=?i", $pre_order_id
  );

  if (!$pre_order) {
    exit(json_html('/views/page.tpl',
      ['title' => 'Данная ссылка не действительна', 'text' => $call_admin_new_link]));
  }

  // 1 = создана ссылка на регистрацию, 2 = временная бронь
  // Если статус не распознан то выходим
  if ($pre_order['status'] != ORDER_STATUS['registration_link'] && $pre_order['status'] != ORDER_STATUS['temporary_registration_link']) {
    $status_text = $db->getOne("SELECT user_description FROM order_statuses WHERE id=?i", $pre_order['status']);

    if (empty($status_text)) {
      $status_text = 'Неизвестный статус предзаказа';
    }

    exit(json_html('/views/page.tpl',
      ['title' => $status_text, 'text' => $call_admin_new_link]));
  }

  $name = [];
  foreach ([$_POST['last_name'], $_POST['first_name'], $_POST['second_name']] as $key => $value) {
    $value = preg_replace('/[^\w\s-]/ui', '', $value) ?? null;
    $value = mb_strtolower($value);
    $value = mb_ucfirst($value);

    if (!empty($value)) {
      $name[] = $value;
    }
  }

  $name            = implode(' ', $name);
  $phone           = preg_replace('/[^\d+]/ui', '', $pre_order['phone']);
  $email           = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ?? null;
  $birthdate       = preg_match('/[\d]{2}\.[\d]{2}\.[\d]{4}/', $_POST['birthdate'], $matches);
  $birthdate       = $matches[0] ?? null;
  $nationality     = preg_replace('/[^\w\s]/ui', '', $_POST['nationality']) ?? null;
  $passport_type   = preg_replace('/[^\w]/ui', '', $_POST['passport_type']) ?? null;
  $passport_number = preg_replace('/[^\w0-9-\s]/ui', '', $_POST['passport_number']) ?? null;
  $comment         = preg_replace('/[^\w0-9-\s]/ui', '', $_POST['comment']) ?? '';

  $order = [
    'date_in'                 => $pre_order['date_in'],
    'date_out'                => $pre_order['date_out'],
    'date_created'            => time(),
    'deposit'                 => $pre_order['deposit'],
    'tarif'                   => $pre_order['tarif'],
    'tarif_total'             => $pre_order['tarif_total'],
    'guests'                  => $pre_order['guests'],
    'apart_id'                => $pre_order['apart_id'],
    'service_agent_id'        => $pre_order['service_agent_id'],
    'service_agent_percent'   => $pre_order['service_agent_percent'],
    'marketing_agent_id'      => $pre_order['marketing_agent_id'],
    'marketing_agent_percent' => $pre_order['marketing_agent_percent'],
    'payment_method_id'       => $pre_order['payment_method_id'],
    'cleanings'               => $pre_order['cleanings'],
    'cleaning_cost'           => $pre_order['cleaning_cost'],
    'status'                  => ORDER_STATUS['registered'],
    'status_updated_at'       => time(),
    'name'                    => $name,
    'phone'                   => $phone,
    'email'                   => $email,
    'birthdate'               => $birthdate,
    'passport_type'           => $passport_type,
    'passport_number'         => $passport_number,
    'pms_id'                  => $pre_order['pms_id'],
    'file_name'               => "Aeroapart.{$link}.pdf",
  ];

  $order_update_fields = [];

// Проверяем что все поля формы регистрации заполнены
  foreach ($order as $key => $value) {
    if ($value == '') {
      exit(json_html('/views/page.tpl',
        ['title' => 'При регистрации возникла ошибка #2', 'text' => $call_admin_new_link]));
    }
  }

  $tenant = [
    'name'            => $order['name'],
    'email'           => $order['email'],
    'birthdate'       => strtotime($birthdate),
    'passport_type'   => $order['passport_type'],
    'passport_number' => $order['passport_number'],
    'citizenship'     => '',
  ];

  $search_phone = '%' . format_phone_search($pre_order['phone'], true) . '%';
/*
exit(json_html('/views/page.tpl',
['title' => 'При регистрации возникла ошибка #3', 'text' => $search_phone]));
 */
  $exist_tenant_id = $db->getOne("SELECT id FROM tenants WHERE phone LIKE ?s", $search_phone);
  if ($exist_tenant_id) {
    $db->query("UPDATE tenants SET ?u WHERE id=?i", $tenant, $exist_tenant_id);
  } else {
    $tenant['phone'] = $pre_order['phone'];
    $db->query("INSERT INTO tenants SET ?u", $tenant);
  }

  $order['comment'] = $comment;
  $db->query("INSERT INTO orders SET ?u", $order);
  $order['id'] = $db->insertId();

  add_order_status_history($order['id'], $order['status']);
  $db->query("UPDATE pre_orders SET order_id=?i, status=?i WHERE id=?i", $order['id'], ORDER_STATUS['registered'], $pre_order_id);

  //Генерируем файл договора из шаблона /ajax/dogovor.html, подменяем в нём текст $название_поля на данные из договора
  $order_text = file_get_contents(ROOT . '/views/registration/order_template.tpl');
  $apart      = $db->getRow("SELECT * FROM aparts where id=?i", $order['apart_id']);
  $owner      = $db->getRow("SELECT * FROM owners WHERE id=?i", $apart['owner_id']);

  $order_text = str_replace('$[\'apart\']', $apart['name'], $order_text);

  if (!empty($apart['wifi_pass']) && !empty($apart['wifi_name'])) {
    $order_text = str_replace('$[\'wifi\']', 'Wi-Fi сеть: ' . $apart['wifi_name'] . ' пароль: ' . $apart['wifi_pass'], $order_text);
  } else {
    $order_text = str_replace('$[\'wifi\']', '', $order_text);
  }

  if (!empty($order['date_in']) && !empty($order['date_out'])) {
    $date_in    = date('d.m.Y H:i', $order['date_in']);
    $date_out   = date('d.m.Y H:i', $order['date_out']);
    $order_text = str_replace('$[\'date_in\']', $date_in, $order_text);
    $order_text = str_replace('$[\'date_out\']', $date_out, $order_text);
  } else {
    exit(json_html('/views/page.tpl',
      ['title' => 'При регистрации возникла ошибка #3', 'text' => $call_admin_new_link]));
  }

  foreach ($order as $key => $value) {
    $order_text = str_replace('$[\'' . $key . '\']', $order[$key], $order_text);
  }

  $order_text = str_replace('$[\'web_root\']', WEB_ROOT, $order_text);

  $filepath_pdf = ROOT . '/public/orders/' . $order['file_name'];
  $mpdf         = new \Mpdf\Mpdf(['tempDir' => ROOT . '/public/orders/']);
  $mpdf->WriteHTML($order_text);
  $mpdf->Output($filepath_pdf, 'F');

  /*
  Создаём ссылку на оплату в Сбере и записываем её в БД orders в наш договор в поле sber.
  При создании ссылки на оплату в sber_order_id записывается ID заказа сбер и ID договора Aeroapart
   */

  $payment_method = $db->getOne("SELECT name FROM payment_methods WHERE id=?i", $pre_order['payment_method_id']);
  $payment_description = '';

  if ($pre_order['payment_method_id'] == 1) {
    if (!empty($order['deposit'])) {
      $deposit_pay_url = create_sber_link($order['id'], $order['deposit'], 'deposit', REGISTRATION_LINK_ROOT_URL . '?u=' . $link);

      if ($deposit_pay_url) {
        $order['deposit_pay_url'] = $deposit_pay_url;
        $payment_description      = "Оплата депозита\n{$deposit_pay_url}\n";
      }
    }

    $tarif_pay_url = create_sber_link($order['id'], $order['tarif_total'], 'tarif', REGISTRATION_LINK_ROOT_URL . '?u=' . $link);

    if ($tarif_pay_url) {
      $order['tarif_pay_url'] = $tarif_pay_url;
      $payment_description .= "Оплата тарифа\n{$tarif_pay_url}";
    }

  } else {
    $payment_description = 'Оплата ' . $payment_method;
  }

// Интеграция с Exa PMS
  if (!empty($pre_order['pms_id'])) {
    $pms_order_id = $pre_order['pms_id'];
  } else {
    $tg_error_message .= 'Ошибка при регистрации: в предзаказе отсутствует номер заказа PMS. Заказ в PMS не обновлён.';
  }

  if (!empty($pms_order_id)) {
    $exa_data = exa_get_order_by_key($pms_order_id);

    if (isset($exa_data['order']['ID'])) {
      $order_update_fields['pms_id'] = $pms_order_id;

      $exa_edit_order_params = [
        'summ'          => $order['tarif_total'],
        'id'            => $exa_data['order']['ID'],
        'include_marks' => 'wE2lN2gh2Qj1jDKbh3juS8',
        'state'         => 5,
      ];

      $pms_order_note = date('d.m H:i') . ' Гость зарегистрировался' .
        "\n" . 'Договор №' . $order['id'] .
        "\n" . WEB_ROOT . '/public/orders/' . $order['file_name'] .
        "\n" . $payment_description;

      if (!empty($comment)) {
        $pms_order_note .= "\nОсобые пожаления: $comment";
      }

      if (isset($exa_data['order']['Notes']) && !empty($exa_data['order']['Notes'])) {
        $pms_order_note .= "\n" . '-----------------------------------------------------------' . "\n" . $exa_data['order']['Notes'] . "\n";
      }

      $exa_edit_order_params['notes'] = $pms_order_note;

      $exa_user = [
        'name'            => $name ?? '',
        'email'           => $email ?? '',
        'phone'           => $phone ?? '',
        'passport_number' => $passport_number ?? '',
        'birthday'        => $birthdate ?? '',
        'nationality'     => $nationality,
      ];

      $found_exa_user = exa_find_client_by_email($exa_user['email']);

      if ($found_exa_user) {
        if (isset($found_exa_user['ID'])) {
          $exa_user['id'] = $found_exa_user['ID'];
        } else {
          $tg_error_message .= 'Ошибка при регистрации: не удалось получить ID клиента PMS (см. /exa_pms_errors.log).';
        }
      } else {
        $new_exa_user = exa_register_client($exa_user);

        if (isset($new_exa_user['ID'])) {
          $exa_user['id'] = $new_exa_user['ID'];
        } else {
          $tg_error_message .= 'Ошибка при регистрации: не удалось создать клиента в PMS (см. /exa_pms_errors.log).';
        }
      }

      if (!empty($exa_user['id'])) {
        $exa_edit_order_params['customer'] = $exa_user['id'];
        $exa_edit_client                   = exa_edit_client($exa_user);

        if (isset($exa_edit_client['error'])) {
          if (!empty($exa_edit['message'])) {
            $tg_error_message .= "Ошибка при редактировании клиента PMS: " . $exa_edit_client['message'] . ', клиент в заказе PMS №' . $pms_order_id . ' не отредактирован.';
          } else {
            $tg_error_message .= "Ошибка при редактировании клиента PMS: Неизвестная ошибка, клиент в заказе PMS №{$pms_order_id} не отредактирован.";
          }
        }
      }

      $exa_edit_order = exa_edit_order($exa_edit_order_params);
      if (isset($exa_edit_order['error'])) {
        $tg_error_message .= 'Ошибка ' . $exa_edit_order['message'];
      }

    } else {
      $tg_error_message .= 'Ошибка ' . $exa_data['message'] . '. Заказ в PMS не обновлён.';
    }

    if (!empty($tg_error_message)) {
      $tg_error_message .= "\n" . ' ID предзаказа: ' . $pre_order['id'] . ', клиент: ' . $name . ' ' . $phone;
      file_put_contents(ROOT . '/registration_exa_errors.log', date('d.m.Y H:i') . ' ' . print_r($tg_error_message, true) . "\n", FILE_APPEND);
      send_tg($tg_error_message, $owner['tg_chat_id']);
    }
  }

// Записываем в заказ новые данные
  if (!empty($order_update_fields)) {
    $db->query("UPDATE orders SET ?u WHERE id=?i", $order_update_fields, $order['id']);
  }

  //Отправляем письма админу и пользователю с шаблоном /ajax/mail_template.html
  $attachment       = ['path' => $filepath_pdf, 'name' => 'Договор №' . $order['id'] . ' ' . $name . ' ' . date('d-m-Y') . '.pdf'];
  $message_template = load_tpl('/views/registration/mail_template.tpl', ['order_file_url' => WEB_ROOT . '/public/orders/' . $order['file_name']], true);

  $pay_buttons = '';
  if (!empty($deposit_pay_url)) {
    $pay_buttons .= load_tpl('/views/registration/mail_deposit_pay_button.tpl', ['deposit_pay_url' => $deposit_pay_url], true);
  }

  if (!empty($tarif_pay_url)) {
    $pay_buttons .= load_tpl('/views/registration/mail_tarif_pay_button.tpl', ['tarif_pay_url' => $tarif_pay_url], true);
  } else {
    $pay_buttons .= load_tpl('/views/registration/mail_pay_description.tpl', ['payment_description' => 'Оплата ' . $payment_method], true);
  }

  $message_template = str_replace('${pay_button}', $pay_buttons, $message_template);

  if (empty($tarif_pay_url)) {
    $admin_message_text = 'Создан договор №' . $order['id'] . ' в апартамент ' . $apart['name'] . ', <b>Оплата ' . $payment_method . '</b>';
  } else {
    $admin_message_text = 'Создан договор №' . $order['id'] . ' в апартамент ' . $apart['name'] . ', гостю выслана <a href="' . $tarif_pay_url . '"><b>ссылка</b></a> на оплату. <a href="' . WEB_ROOT . '/get_pay_link?order_id=' . $order['id'] . '&price=' . $order['tarif_total'] . '"><br>
    <b>Создать новую ссылку на оплату</b></a>';
  }

  send_mail(ADMIN_EMAIL,
    'Договор ' . $name,
    $admin_message_text, $attachment);

  send_mail($order['email'], 'AEROAPART.RU Регистрация завершена', $message_template);

  $tg_message = '<b>Регистрация On-line в ' . $apart['name'] . "</b>\n" . $name . ' ' . $phone . "\n\n";

  $service_agent_name   = $pre_order['service_agent_name'] ?? 'Не указан';
  $marketing_agent_name = $pre_order['marketing_agent_name'] ?? 'Не указан';

  $cleanings_cost = $order['cleanings'] * $order['cleaning_cost'];
  $marketing_cost = $order['tarif_total'] / 100 * $order['marketing_agent_percent'];
  $service_cost   = $order['tarif_total'] / 100 * $order['service_agent_percent'];
  $profit         = $order['tarif_total'] - $service_cost - $marketing_cost - $cleanings_cost;

  $tg_message .= "Сервис: {$service_agent_name} {$order['service_agent_percent']}%\n";
  $tg_message .= "Маркетинг: {$marketing_agent_name} {$order['marketing_agent_percent']}%\n";
  $tg_message .= "Уборок: {$order['cleanings']}\n";
  $tg_message .= "Партнёр: {$owner['name']}\n";
  $tg_message .= "Прибыль: {$profit}₽\n\n";

  $tg_message .= '<a href="' . WEB_ROOT . '/public/orders/' . $order['file_name'] . '">Скачать договор №' . $order['id'] . '</a>';

  if (!empty($order['comment'])) {
    $tg_message .= "\n" . 'Комментарий: ' . $order['comment'];
  }
  //Отправляем уведомление в Telegram
  send_tg($tg_message, $owner['tg_chat_id']);

  // Выводим в AJAX ответ кнопку со ссылкой на договор и кнопку со ссылкой на оплату

  // Если оплата сбером
  if ($order['payment_method_id'] == 1) {
    $sber_orders = $db->getAll("SELECT id, type, status, sberbank_order_id, amount, order_id FROM sber_orders WHERE order_id=?i", $order['id']);

    foreach ($sber_orders as $key => $ord) {
      $sber_orders[$key]['pay_url'] = get_config('sberbank')['pay_url'] . $ord['sberbank_order_id'];

      if (in_array($ord['status'], [2, 4, 6])) {

        $sber_orders[$key]['receipt'] = $db->getRow("SELECT * FROM ofd_receipts WHERE sber_order_id=?i ORDER BY id DESC",$ord['id']);

        $sber_orders[$key]['receipt']['type_description'] = $db->getOne("SELECT user_description FROM ofd_receipt_types WHERE id=?i", $sber_orders[$key]['receipt']['type']);

        $sber_orders[$key]['receipt']['item_description'] = "Аренда апартамента по доровору №{$order['id']}";
        $ofd_conf = get_config('ofd');
        $sber_orders[$key]['receipt']['inn'] = $ofd_conf['inn'];
      }
    }

    $template_data['sber_orders'] = $sber_orders;
    $template                     = '/views/registration/order_cabinet_sber.tpl';
  } else {
    $template = '/views/registration/order_cabinet.tpl';
  }

  $cur_time = time();
  if (!empty($apart['door_key_id'])) {

    $door_key = $db->getRow("SELECT * FROM door_keys WHERE id=?i", $apart['door_key_id']);

    if (!empty($door_key)) {
      if ($order['date_in'] <= $cur_time) {
        if ($order['date_out'] >= $cur_time) {
          $templates_data['door_key'] = 'enabled';
        }
      } else {
        $templates_data['door_key'] = 'disabled';
      }
    }
  }

  $template_data['order'] = $order;

  exit(json_html($template,
    $template_data));
}

exit(json_html('/views/page.tpl',
  ['title' => 'При регистрации возникла ошибка #3', 'text' => $call_admin_new_link]));
