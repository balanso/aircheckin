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
$crud->unsetEdit();
$crud->unsetAdd();
$crud->unsetRead();

$crud->setTable('owners');
$crud->columns(['name', 'user_id']);
$crud->unsetFields(['tg_chat_id', 'payu_token']);
$crud->displayAs(['name' => 'Собственник', 'user_id'=>'Логин',
  'tg_chat_id' => 'Telegram ID чата',
  'card_number' => 'Номер карты',
  'card_expiration' => 'Срок карты',
  'card_name' => 'Имя на карте',
]);
// $crud->setRelation('user_id', 'users', 'login');

$crud->callbackColumn('user_id', function ($value) {
  return 'id'.$value;
});

$crud->callbackReadField('user_id', function ($value) {
  return 'id'.$value;
});

$crud->defaultOrdering('owners.id', 'DESC');

$crud->setActionButton('Объекты собственника', 'fa fa-home', function ($row) {
    return '/admin/aparts?owner_id='.$row->id;
}, true);

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
load_tpl('/views/admin/owners/table.tpl', ['css_files' => $css_files, 'js_files' => $js_files, 'output' => $output]);
load_tpl('/views/admin/template/footer.tpl');
