<?
check_user_access('admin');

require ROOT . '/lib/exa_pms.php';

  $data = exa_get_accessible_items(date('d-m-Y', time()+60*60*24), date('d-m-Y', time()+60*60*24*2));

  debug($data);

  // qwe