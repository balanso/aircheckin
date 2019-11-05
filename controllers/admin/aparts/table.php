<?
include ROOT . "/lib/vendor/grocery_crud/autoload.php";
use GroceryCrud\Core\GroceryCrud;

$crud_config = include ROOT . '/lib/vendor/grocery_crud/crud_config.php';
$db_config     = get_config('db');
$crud_database = [
  'adapter' => [
    'driver'   => 'Pdo_Mysql',
    'database' => $db_config['db'],
    'username' => $db_config['user'],
    'password' => $db_config['pass'],
    'charset'  => 'utf8',
  ]];

$crud = new GroceryCrud($crud_config, $crud_database);
$crud->setSkin('bootstrap-v4');
$crud->unsetBootstrap();
$crud->unsetJquery();
$crud->unsetDeleteMultiple();
$crud->unsetRead();

$crud->setTable('aparts');
$crud->columns(['name', 'address', 'owner_id', 'wifi_name', 'wifi_pass', 'price', 'pms_id']);
$crud->uniqueFields(['name', 'pms_id']);
$crud->requiredFields(['name', 'pms_id', 'owner_id', 'address']);

$crud->displayAs('name', 'Название апартамента');
$crud->displayAs('address', 'Адрес');
$crud->displayAs('wifi_name', 'WiFi сеть');
$crud->displayAs('wifi_pass', 'WiFi пароль');
$crud->displayAs('price', 'Тариф');
$crud->displayAs('pms_id', 'Exa ID');
$crud->displayAs('owner_id', 'Собственник');

// Почему то не работает на локальном / not works on local dev server :(
// https://www.grocerycrud.com/forums/topic/136804-problem/
if (MODE != 'dev') {
  $crud->setRelation('owner_id', 'owners', 'name');
}

$crud->defaultOrdering('owners.id', 'DESC');


$crud->defaultOrdering('aparts.id', 'DESC');

if (!empty($_GET['owner_id'])) {
  $crud->where([
      'owner_id' => $_GET['owner_id']
  ]);
}

$output = $crud->render();

if ($output->isJSONResponse) {
  header('Content-Type: application/json; charset=utf-8');
  echo $output->output;
  exit;
}

$css_files = $output->css_files;
$js_files  = $output->js_files;
$output    = $output->output;

load_tpl('/views/admin/template/header.tpl', ['show_menu' => true]);
load_tpl('/views/admin/aparts/table.tpl', ['css_files' => $css_files, 'js_files' => $js_files, 'output' => $output]);
load_tpl('/views/admin/template/footer.tpl');

