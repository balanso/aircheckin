<?
/**
 * @param $args
 * @return mixed
 */
function CallExe($args)
{
  foreach ($args as $key => $val) {
    $args[$key] = $key . '=' . str_replace('"', '{QUOTE}', str_replace("\n", '{BR}', $val));
  }

  $url    = 'https://exadb.ru/cgi-bin/exaapartmentspublic.php';
  $result = file_get_contents($url, false, stream_context_create(array(
    'http' => array(
      'method'  => 'POST',
      'header'  => 'Content-type: application/x-www-form-urlencoded',
      'content' => http_build_query($args),
    ),
  )));

  if ($result) {
    $result = iconv('windows-1251', 'utf-8', $result);
    $arr    = explode('|', $result);
    return $arr;
  } else {
    return false;
  }
}
/**
 * @param $message
 * @param $backbutton
 */
function WriteExeErrorStr($message, $backbutton = true)
{
  echo '<p style="color: red;">' . $message . '</p>';
  if ($backbutton) {
    echo '<p align="center"><a href="#" onclick="history.back()"
class="major_button">Вернуться</a></p>';
  }

}
/**
 * @param $exe_result
 * @return mixed
 */
function GetExeResultValue($exe_result)
{
  return $exe_result[0];
}
/**
 * @param $exe_result
 */
function CheckExeResultValue($exe_result)
{
  return (GetExeResultValue($exe_result) == 'OK');
}
/**
 * @param $exe_result
 * @param $backbutton
 */
function WriteExeError($exe_result, $backbutton = true)
{
  switch (GetExeResultValue($exe_result)) {
    case 'INVALIDSESSION':
      header('Location: login.php');
      exit;
    case 'UNKNOWNCOMMAND':WriteExeError('Команда в запросе не задана или задана
некорректно.', $backbutton);
      break;
    case 'INVALIDLOGIN':WriteExeError('Неверный пароль или пользователь с таким логином
не существует.', $backbutton);
      break;
    case 'OBJECTNOTFOUND':WriteExeError('Объект не указан или не существует в базе
данных.', $backbutton);
      break;
    default:{
        if ($exe_result[0] == '') {
          WriteExeErrorStr('Ошибка при выполнении запроса.', $backbutton);
        } else {
          WriteExeErrorStr($exe_result[0], $backbutton);
        }

        break;
      }
  }
}
/**
 * @param $exe_result
 * @param $backbutton
 */
function CheckExeResult($exe_result, $backbutton = true)
{
  if (CheckExeResultValue($exe_result)) {
    return true;
  }

  // WriteExeError($exe_result, $backbutton);
  return false;
}
/**
 * @param $exe_result
 * @param $index
 * @return mixed
 */
function GetResultValue($exe_result, $index)
{
  return $exe_result[$index];
}
/**
 * @param $exe_result
 */
function GetXmlResult($exe_result)
{
  $string = GetResultValue($exe_result, 1);
  return simplexml_load_string($string);
}

/**
 * @param array $args
 * @return mixed
 */
function exa_request(array $args)
{
                             // получение позиций и вывод их простым списком
  $args['db'] = 'apbooking'; // идентификатор базы данных
  $exe_result = CallExe($args);

  if (CheckExeResult($exe_result)) {
    $xml = GetXmlResult($exe_result);
    return $xml;
  }

  if (empty($exe_result[0])) {
    $exe_result[0] = 'Не получен ответ от сервера Exa';
  }
  return ['error' => true, 'message' => $exe_result[0]];
}

// echo '<pre>';

/**
 * @param $date_from
 * @param $date_to
 * @return mixed
 */
function exa_get_accessible_items($date_from, $date_to, $include_occupied = false)
{
  // Получаем свободные апарты на даты
  $get_items_params = [
    'command'   => 'GetAccessibleItems2',
    'date_from' => $date_from,
    'date_to'   => $date_to,
  ];

  if ($include_occupied) {
    $get_items_params['include_occupied'] = 1;
  }

  $result = exa_request($get_items_params);

  $accessible_items = [];

  if (isset($result->Items)) {
    foreach ($result->Items->Item as $key => $value) {
      $value              = (array) $value;
      $accessible_items[] = $value['@attributes'];
    }
  } else {
    return [];
  }

  return $accessible_items;
}

/**
 * @return mixed
 */
function exa_find_client_by_email($email)
{
  $client = [
    'email' => $email,
  ];

  // Ищем клиента по почте
  $result = exa_request([
    'command' => 'FindClientByEmail',
    'email'   => $client['email'],
  ]);

  if (isset($result->Item)) {
    $result = (array) $result->Item;
  }

  if (!empty($result['@attributes'])) {
    $found_client = $result['@attributes'];

    if (!empty($found_client['ID'])) {
      // echo 'Найден клиент по почте ' . $client['email'] . "\n";
      return $found_client;
    }
  }

  if (!empty($result['message'])) {
    file_put_contents(ROOT . '/exa_pms_errors.log', date('d.m.Y H:i') . ' Ошибка exa_find_client_by_email ' . $email . '": ' . print_r($result, true), FILE_APPEND);
  }

  return $result;
}

/*
$params = [name, email, phone, passport_number, birthday]
 */
/**
 * @param $params
 * @return mixed
 */
function exa_register_client($params)
{
  $password = time() . uniqid();

  $client = [
    'command'      => 'RegisterClient',
    'newpassword1' => $password,
    'newpassword2' => $password,
  ];

  $client = array_merge($client, $params);

  // Ищем клиента по почте
  $result = exa_request($client);
  $result = (array) $result;

  if (!empty($result['@attributes'])) {
    $result['@attributes']['ID'] = $result['@attributes']['PersonID'];
    // echo 'Создан клиент ' . $client['email'] . "\n";
    return $result['@attributes'];
  } else {

    if (!empty($result['message'])) {
      file_put_contents(ROOT . '/exa_pms_errors.log', date('d.m.Y H:i') . ' Ошибка exa_register_client: ' . print_r($result, true) . "\n" . print_r($params, true), FILE_APPEND);
    }
    // print_r($result);
    return $result;
  }

}

/**
 * @param $date_from
 * @param $date_to
 * @param $item_id
 * @param $places
 * @return mixed
 */
function exa_create_order($date_from, $date_to, $item_id, $places = 1, $description = 'Создан через Aeroapart.ru')
{

  // Создаём заказ
  $result = exa_request([
    'command'          => 'CreateTemporaryOrder2',
    'date_from'        => $date_from,
    'date_to'          => $date_to,
    'item_' . $item_id => 1,
    'notes'            => $description,
    'places'           => $places,
  ]);



  if (isset($result->Order)) {
    $result = (array) $result->Order;
    if (!empty($result) && isset($result['@attributes'])) {
      $order = $result['@attributes'];
      return $order;
    }
  }

  return [
    'error'   => 1,
    'message' => "Не удалось создать заказ: {$result['message']}",
  ];
}

/*
$params = [id = order_id, customer = client_id, include_marks, state]
 */
/**
 * @param $params
 * @return mixed
 */
function exa_edit_order(array $params)
{
  $edit_order_args = [
    'command' => 'EditOrder',
  ];

  $edit_order_args = array_merge($edit_order_args, $params);
  $result          = exa_request($edit_order_args);

  if (!isset($result['error']) && !isset($result['message'])) {
    return $result;
  } else {
    return [
      'error'   => 1,
      'message' => "Не удалось отредактировать заказ ID {$params['id']}: {$result['message']}",
    ];
  }

}

/**
 * @param $key
 * @return mixed
 */
function exa_get_order_by_key($key)
{
  $args = [
    'command' => 'ViewOrderByKey',
    'key'     => $key,
  ];

  $result = exa_request($args);

  $result = (array) $result;
  if (!empty($result['@attributes'])) {
    $out['order'] = $result['@attributes'];
  }

  if (!empty($result['Clients'])) {
    $result['Clients'] = (array) $result['Clients'];

    if (!is_array($result['Clients']['Client'])) {
      $client            = (array) $result['Clients']['Client'];
      $out['clients'][0] = $client['@attributes'];
    } else {
      $clients = (array) $result['Clients']['Client'];
      foreach ($clients as $key => $value) {
        $value            = (array) $value;
        $out['clients'][] = $value['@attributes'];
      }
    }
  }

  if (!empty($result['OrderLivings'])) {
    $result['OrderLivings'] = (array) $result['OrderLivings'];

    if (!empty($result['OrderLivings']['OrderLiving'])) {
      $result['OrderLivings'] = (array) $result['OrderLivings']['OrderLiving'];
      $out['living']          = $result['OrderLivings']['@attributes'];
      if (isset($out['living']['Price'])) {
        $out['living']['Price'] = preg_replace('/[^\d]/', '', $out['living']['Price']);
      }
    }
  }

  if (!empty($result['Items'])) {
    $result['Items'] = (array) $result['Items'];

    if (!empty($result['Items']['Item'])) {
      $result['Items'] = (array) $result['Items']['Item'];
      $out['items']    = $result['Items']['@attributes'];
    }
  }

  if (!empty($out) && !isset($result['error']) && !isset($result['message'])) {
    // echo 'Найден заказ по ключу ' . $args['key'] . "\n";
    return $out;
  } else {
    return [
      'error'   => 1,
      'message' => "Не удалось получить заказ №{$key}: {$result['message']}",
    ];
  }
}

/**
 * @param $params
 * @return mixed
 */
function exa_edit_client($params)
{
  $edit_args = [
    'command' => 'EditClient',
  ];

  $edit_args = array_merge($edit_args, $params);

  $result = exa_request($edit_args);

  // echo 'Отредактирован клиент ' . $edit_args['id'] . "\n";
  return $result;
}
