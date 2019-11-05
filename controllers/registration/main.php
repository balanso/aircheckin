<?
header('Access-Control-Allow-Origin: ' . CLIENT_SITE_URL);
require_once ROOT . '/lib/shorty/shorty.php';

$footer              = [];
$content             = [];
$tpl_data            = [];
$call_admin_new_link = load_tpl('/views/parts/call_admin_new_link.tpl', [], true);

// Action = короткая ссылка регистрации /cdDWcFhMYg
if (isset($_GET['link']) && !empty($_GET['link'])) {
  $link        = $_GET['link'];
  $shorty      = new Shorty();
  $shorty_link = $shorty->get($link);

  if ($shorty_link) {
    preg_match('/pre_order#(\d+)#/ui', $shorty_link, $link_data);
    if (!empty($link_data[1])) {
      $pre_order_id = $link_data[1];
    }
  }

  if (!isset($pre_order_id) || $pre_order_id <= 0) {
    exit(json_html_page('Некорректная ссылка на регистрацию', $call_admin_new_link));
  }

  $pre_order = $db->getRow("SELECT * FROM pre_orders WHERE id=?i", $pre_order_id);
  // Если не найден предзаказ
  if (!$pre_order) {
    exit(json_html_page('Данная ссылка не действительна', $call_admin_new_link));
  }

  $apart = $db->getRow("SELECT address, name, id, door_key_id FROM aparts WHERE id=?i", $pre_order['apart_id']);

  // Если заказ по предзаказу ещё не создан
  if (!$pre_order['order_id']) {
    $reg_form_data = [
      'date_in'     => date('d.m.y H:i', $pre_order['date_in']),
      'date_out'    => date('d.m.y H:i', $pre_order['date_out']),
      'link'        => $link,
      'deposit'     => $pre_order['deposit'],
      'tarif_total' => $pre_order['tarif_total'],
    ];

    if (!empty($apart)) {
      $reg_form_data['apart_name']    = $apart['name'];
      $reg_form_data['apart_address'] = $apart['address'];
    } else {
      exit(json_html_page('Не найден апартамент с ID ' . $pre_order['apart_id'], $call_admin_new_link));
    }

    // 1 = создана ссылка на регистрацию, 2 = временная бронь
    if ($pre_order['status'] == ORDER_STATUS['registration_link'] || $pre_order['status'] == ORDER_STATUS['temporary_registration_link']) {
      if ($pre_order['status'] == ORDER_STATUS['temporary_registration_link']) {
        if (time() > $pre_order['timeout']) {
          exit(json_html_page('Время бронирования истекло', $call_admin_new_link));
        } else {
          $reg_form_data['order_timeout'] = $pre_order['timeout'];
        }
      }

    } else {
      $status_text = $db->getOne("SELECT user_description FROM order_statuses WHERE id=?i", $pre_order['status']);

      if (empty($status_text)) {
        $status_text = 'Неизвестный статус предзаказа';
      }

      exit(json_html_page($status_text, $call_admin_new_link));
    }

    $apart_name = $db->getOne("SELECT name FROM aparts WHERE id=?i", $pre_order['apart_id']);

    if (!$apart_name) {
      exit(json_html_page('Не найден апартамент ID ' . $pre_order['apart_id'] . ' в БД', $call_admin_new_link));
    }

    $apart_images = glob(ROOT . "/public/img/aparts/{$apart['id']}/*.{jpg,png}", GLOB_BRACE);

    // exit(json_html_page(print_r($apart_images, true), $call_admin_new_link));

    if (!empty($apart_images)) {
      $apart_images_path = [];

      foreach ($apart_images as $key => $image) {
        if (!empty(basename($image))) {
          $apart_images_path[] = basename($image);
        }

      }

      if (!empty($apart_images_path)) {
        $reg_form_data['apart_images'] = $apart_images_path;
        $reg_form_data['apart_id']     = $apart['id'];
      }
    }

    if (!empty($pre_order['phone'])) {
      exit(json_html([
        '/views/header.tpl',
        '/views/registration/greetings_form.tpl',
        '/views/registration/form.tpl',
        '/views/footer.tpl'],
        $reg_form_data));
    } else {
      exit(json_html([
        '/views/header.tpl',
        '/views/registration/greetings_sms.tpl',
        '/views/registration/sms_check.tpl',
        '/views/registration/form.tpl',
        '/views/footer.tpl'],
        $reg_form_data));
    }

  } elseif ($pre_order['order_id'] > 0) {
    $templates[] = '/views/header.tpl';

    $order = $db->getRow("SELECT * FROM orders WHERE id=?i", $pre_order['order_id']);

    // Если оплата сбером
    if ($order['payment_method_id'] == 1) {
      $sber_orders = $db->getAll("SELECT id, type, status, sberbank_order_id, amount, order_id FROM sber_orders WHERE order_id=?i", $order['id']);

      foreach ($sber_orders as $key => $ord) {
        $sber_orders[$key]['pay_url'] = get_config('sberbank')['pay_url'] . $ord['sberbank_order_id'];

        if (in_array($ord['status'], [2, 4, 6])) {

          $receipt = $db->getRow("SELECT * FROM ofd_receipts WHERE sber_order_id=?i ORDER BY id DESC", $ord['id']);

          if (!empty($receipt['doc_num'])) {
            $sber_orders[$key]['receipt'] = $receipt;

            $sber_orders[$key]['receipt']['type_description'] = $db->getOne("SELECT user_description FROM ofd_receipt_types WHERE id=?i", $sber_orders[$key]['receipt']['type']);

            $sber_orders[$key]['receipt']['item_description'] = "Аренда апартамента по доровору №{$order['id']}";
            $ofd_conf                                         = get_config('ofd');
            $sber_orders[$key]['receipt']['inn']              = $ofd_conf['inn'];
          }
        }
      }

      $templates_data['sber_orders'] = $sber_orders;
      $templates[]                   = '/views/registration/order_cabinet_sber.tpl';
    } else {
      $templates[] = '/views/registration/order_cabinet.tpl';
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

    $templates_data['order'] = $order;
    $templates_data['short_link'] = $link;
/*
    $templates[] = '/views/debug.tpl';
    $templates_data['debug_data'] = $apart;*/

    $templates[] = '/views/footer.tpl';

    if (!empty($order)) {
      // Отдаём страницу окончания регистрации если договор уже сформирован
      exit(json_html($templates,
        $templates_data));
    } else {
      // Не нашли договор записанный в предзаказе
      exit(json_html_page('Не найден договор №' . $pre_order['order_id']));
    }
  }
}

exit(json_html_page('Ссылка на регистрацию содержит ошибку #1', $call_admin_new_link));
