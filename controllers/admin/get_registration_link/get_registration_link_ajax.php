<?
check_user_access('admin');

if (!empty($_POST)) {
  global $db;
  require_once ROOT . '/lib/shorty/shorty.php';
  require_once ROOT . '/lib/exa_pms.php';

  $dates    = isset($_POST['dates']) ? explode(' - ', $_POST['dates']) : '';
  $date_in  = !empty($dates[0]) ? $dates[0] : '';
  $date_out = !empty($dates[1]) ? $dates[1] : '';

  if (!empty($date_in) && !empty($date_out)) {
    $date_in_pms  = date('Y-m-d', strtotime($date_in));
    $date_out_pms = date('Y-m-d', strtotime($date_out));

    $date_in_order  = strtotime($date_in);
    $date_out_order = strtotime($date_out);
  }

  $tarif         = preg_replace('/[^\d]/', '', $_POST['tarif']) ?? null;
  $tarif_total   = preg_replace('/[^\d]/', '', $_POST['tarif_total']) ?? null;
  $deposit       = preg_replace('/[^\d]/', '', $_POST['deposit']) ?? null;
  $guests        = preg_replace('/[^\d]/', '', $_POST['guests']) ?? null;
  $cleanings     = preg_replace('/[^\d]/', '', $_POST['cleanings']) ?? null;
  $cleaning_cost = preg_replace('/[^\d]/', '', $_POST['cleanings']) ?? null;
  $apart_id      = preg_replace('/[^\d]/', '', $_POST['apart_id']) ?? null;
  // $cleaner_id   = preg_replace('/[^\d]/', '', $_POST['cleaner_id']) ?? null;
  $apart_pms_id = $db->getOne("SELECT pms_id FROM aparts WHERE id=?i", $apart_id);

  if (isset($_POST['phone'])) {
    $phone = preg_replace('/[^\d+]/ui', '', $_POST['phone']);
  } else {
    $phone = '';
  }

  if (!$apart_pms_id) {
    $answer = json_encode(['status' => 'warning', 'message' => 'Не найден pms_id апартамента в aparts']);
    exit($answer);
  }

  $service_agent_id = preg_replace('/[^\d]/', '', $_POST['service_agent_id']) ?? null;

  $tmp                     = str_replace(',', '.', $_POST['marketing_agent_percent']);
  $marketing_agent_percent = preg_replace('/[^\d.]/', '', $tmp) ?? null;
  $marketing_agent_id      = preg_replace('/[^\d]/', '', $_POST['marketing_agent_id']) ?? null;

  $payment_method_id = preg_replace('/[^\d]/', '', $_POST['payment_method_id']) ?? null;
  $pms_order_id      = preg_replace('/[^\d]/', '', $_POST['pms_order_id']) ?? null;
  $custom_conditions = preg_replace('/[^А-яA-z\s]/u', '', $_POST['custom_conditions']) ?? null;

  // сумма тарифов по договорам за последние 30 дней
  // Динамический процент сервисного агента если сумма договоров > 80.000 и не задан процент в ручную
  if (!isset($_POST['service_agent_percent']) || empty($_POST['service_agent_percent'])) {
    $month        = time() - 60 * 60 * 24 * 30;
    $orders_tarif = $db->getAll("SELECT tarif_total FROM orders WHERE date_created > ?i AND apart_id=?i", $month, $apart_id);

    $total_tarif_sum       = 0;
    $service_agent_percent = 13.2;

    if (!empty($orders_tarif)) {
      foreach ($orders_tarif as $key => $order) {
        $total_tarif_sum += $order['tarif_total'];
      }

      $orders_num = count($orders_tarif);

      if ($total_tarif_sum > 80000) {
        $service_agent_percent += $orders_num * 0.3;
      }
    }
  } else {
    $tmp                   = str_replace(',', '.', $_POST['service_agent_percent']);
    $service_agent_percent = preg_replace('/[^\d.]/', '', $tmp) ?? null;
  }

  $order = [
    'date_created'            => time(),
    'date_in'                 => $date_in_order,
    'date_out'                => $date_out_order,
    'tarif'                   => $tarif,
    'tarif_total'             => $tarif_total,
    'deposit'                 => $deposit,
    'guests'                  => $guests,
    // 'cleaner_id'              => $cleaner_id,
    'cleanings'               => $cleanings,
    'cleaning_cost'           => $cleaning_cost,
    'apart_id'                => $apart_id,
    'service_agent_id'        => $service_agent_id,
    'service_agent_percent'   => $service_agent_percent,
    'marketing_agent_id'      => $marketing_agent_id,
    'marketing_agent_percent' => $marketing_agent_percent,
    'payment_method_id'       => $payment_method_id,
    'pms_id'                  => $pms_order_id,
    'status'                  => ORDER_STATUS['registration_link'],
    'custom_conditions'       => $custom_conditions,
    'phone'                   => $phone,
  ];

// Создаём временную бронь заказ в Exa на ORDER_TIMEOUT_SEC
  if ($marketing_agent_id == 0) {
    $order['timeout'] = time() + ORDER_TIMEOUT_SEC;
    $order['status']  = ORDER_STATUS['temporary_registration_link']; //Создана временная бронь и ссылка на регистрацию

    $pms_order = exa_create_order($date_in_pms, $date_out_pms, $apart_pms_id, 1, '');
    if (isset($pms_order['CreatedOrderKey'])) {
      $order['pms_id'] = $pms_order['CreatedOrderKey'];
    } else {
      $answer = json_encode(['status' => 'warning', 'message' => 'Ошибка ' . $pms_order['message']]);
      exit($answer);
    }
  } else {
    // Обновляем заказ если был создан заранее
    if ($order['pms_id'] > 0) {
      $need_update_pms_order = true;
    }
  }

  if ($order['pms_id'] == 0) {
    $answer = json_encode(['status' => 'warning', 'message' => 'Не получен ID заказа PMS']);
    exit($answer);
  }

  $db->query("INSERT INTO pre_orders SET ?u", $order);
  $pre_order_id = $db->insertId();
  $shorty       = new Shorty();
  $short_code   = $shorty->add('pre_order#' . $pre_order_id . '#');
  $short_url    = REGISTRATION_LINK_ROOT_URL . '?u=' . $short_code;

  $exa_data = exa_get_order_by_key($order['pms_id']);
  if (isset($exa_data['order']['ID'])) {
    $pms_order_note = '';

    if (!empty($short_url)) {
      $pms_order_note .= date('d.m H:i') . ' Создана ссылка на регистрацию ' . "\n" . $short_url . "\n";
    }

    if (isset($order['timeout']) && $order['timeout'] > 0) {
      $pms_order_note .= 'Бронирование по телефону, ожидание регистрации до ' . date('H:i', $order['timeout']) . "\n";
    }

    if (isset($exa_data['order']['Notes']) && !empty($exa_data['order']['Notes'])) {
      $pms_order_note .= '-----------------------------------------------------------' . "\n" . $exa_data['order']['Notes'];
    }

    $exa_edit_order_params = [
      'id'            => $exa_data['order']['ID'],
      'include_marks' => 'YhESo3v4v7r1F8BJS1qMbT',
      'notes'         => $pms_order_note,
    ];

    $exa_edit_order = exa_edit_order($exa_edit_order_params);
    if (isset($exa_edit_order['error'])) {
      $answer = json_encode(['status' => 'warning', 'message' => "Ошибка " . $exa_edit_order['message']]);
      exit($answer);
    }

  } else {
    $answer = json_encode(['status' => 'warning', 'message' => 'Ошибка ' . $exa_data['message'] . '. Заказ PMS №' . $order['pms_id'] . ' не обновлён.']);
    exit($answer);
  }

  $html = load_tpl('/views/admin/get_registration_link/success.tpl', ['url' => $short_url], true);

  if (!empty($phone)) {
    $html .= load_tpl('/views/admin/get_registration_link/success_send_sms.tpl', ['phone' => $phone, 'url' => $short_url], true);
  }

  $answer = json_encode(['status' => 'ok', 'html' => $html]);
  exit($answer);
} else {
  json_answer('error', 'Access denied, data is empty');
}
