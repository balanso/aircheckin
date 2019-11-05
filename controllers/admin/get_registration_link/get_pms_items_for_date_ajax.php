<?
check_user_access('admin');

require ROOT . '/lib/exa_pms.php';

if (isset($_POST['timestamp_in']) && isset($_POST['timestamp_out'])) {

  $data = exa_get_accessible_items(date('d-m-Y', $_POST['timestamp_in']), date('d-m-Y', $_POST['timestamp_out']));
  $apart_names = [];

  if (!empty($data)) {
    foreach ($data as $key => $value) {
      $apart_names[]              = $value['Name'];
      $apart_cost[$value['Name']] = $value['Price'];
    }

    $aparts = $db->getAll("SELECT name, id, pms_id FROM aparts WHERE name IN (?a)", $apart_names);

    if (!empty($aparts)) {
      foreach ($aparts as $key => $value) {
        if (!empty($apart_cost[$value['name']])) {
          $aparts[$key]['tarif'] = $apart_cost[$value['name']] / $_POST['total_days'];
        }
      }

      $aparts_select_options = load_tpl('/views/admin/get_registration_link/aparts_select_options.tpl', ['aparts'=>$aparts], true);

      exit(json_encode(['status' => 'ok', 'html' => $aparts_select_options], JSON_HEX_QUOT | JSON_HEX_TAG));
    } else {
      $answer = ['status' => 'warning', 'message' => 'Найдены свободные апартаменты в Exa но нет апартаментов с таким названием в нашей БД'];
    }

  } else {
    $answer = ['status' => 'warning', 'message' => 'Нету свободных апартаментов на эти даты'];
  }

} else {
  $answer = ['status' => 'error', 'message' => 'Не указаны даты поиска апартаментов'];
}

exit(json_encode($answer));
