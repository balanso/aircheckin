<?
include ROOT . "/lib/vendor/grocery_crud/autoload.php";
use GroceryCrud\Core\GroceryCrud;

$crud_config   = include ROOT . '/lib/vendor/grocery_crud/crud_config.php';
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

$crud->columns([
  'id',
  'name',
  'phone',
  'email',
  'apart_id',
  'status',
  'date_in',
  'date_out',
]);
$crud->displayAs([
  'id'                      => '№',
  'name'                    => 'ФИО',
  'phone'                   => 'Телефон',
  'email'                   => 'Почта',
  'passport_type'           => 'Тип паспорта',
  'passport_number'         => 'Номер паспорта',
  'birthdate'               => 'День рождения',
  'comment'                 => 'Комментарий',
  'file_name'               => 'Файл договора',
  'date_created'            => 'Дата создания договора',
  'date_in'                 => 'Дата заезда',
  'date_out'                => 'Дата выезда',
  'deposit'                 => 'Депозит',
  'tarif'                   => 'Тариф',
  'tarif_total'             => 'Тариф итого',
  'guests'                  => 'Кол-во гостей',
  'apart_id'                => 'Апартамент',
  'pms_id'                  => 'ID заказа Exa',
  'service_agent_id'        => 'Сервис',
  'service_agent_percent'   => '%',
  'marketing_agent_id'      => 'Маркетинг',
  'marketing_agent_percent' => '%',
  'payment_method_id'       => 'Метод оплаты',
  'cleaner_id'              => 'Уборщица',
  'cleanings'               => 'Кол-во уборок',
  'status'                  => 'Статус',
  'status_updated_at'       => 'Дата обновления статуса',
  'custom_conditions'       => 'Дополнительные условия',
]);

$crud->setRelation('apart_id', 'aparts', 'name');
$crud->setRelation('service_agent_id', 'service_agents', 'name');
$crud->setRelation('marketing_agent_id', 'marketing_agents', 'name');
$crud->setRelation('payment_method_id', 'payment_methods', 'name');
$crud->setRelation('status', 'order_statuses', 'description');
$crud->setRelation('cleaner_id', 'cleaners', 'name');

$crud->callbackColumn('date_in', function ($value, $row) {
  return date('d.m.Y', $value);
});
$crud->callbackColumn('date_out', function ($value, $row) {
  return date('d.m.Y', $value);
});
$crud->callbackColumn('date_created', function ($value, $row) {
  return date('d.m.Y H:i', $value);
});

$crud->callbackReadField('date_in', function ($value, $row) {
  $date = date('d.m.Y H:i', $value);
  return '<div class="form-control gc-read-only-input">' . $date . '</div>';
});
$crud->callbackReadField('date_out', function ($value, $row) {
  $date = date('d.m.Y H:i', $value);
  return '<div class="form-control gc-read-only-input">' . $date . '</div>';
});
$crud->callbackReadField('date_created', function ($value, $row) {
  $date = date('d.m.Y H:i', $value);
  return '<div class="form-control gc-read-only-input">' . $date . '</div>';
});

$crud->callbackReadField('status_updated_at', function ($value, $row) {
  return '<div class="form-control gc-read-only-input">' . date('d.m.Y H:i', $value) . '</div>';
});
$crud->callbackReadField('deposit', function ($value, $row) {
  return '<div class="form-control gc-read-only-input">' . $value . '₽</div>';
});
$crud->callbackReadField('tarif', function ($value, $row) {
  return '<div class="form-control gc-read-only-input">' . $value . '₽</div>';
});
$crud->callbackReadField('tarif_total', function ($value, $row) {
  return '<div class="form-control gc-read-only-input">' . $value . '₽</div>';
});
$crud->callbackReadField('marketing_agent_percent', function ($value, $row) {
  return '<div class="form-control gc-read-only-input">' . $value . '%</div>';
});
$crud->callbackReadField('service_agent_percent', function ($value, $row) {
  return '<div class="form-control gc-read-only-input">' . $value . '%</div>';
});
$crud->callbackReadField('passport_type', function ($value, $row) {
  if ($value == 'rus') {
    return '<div class="form-control gc-read-only-input">₽оссия</div>';
  } else {
    return '<div class="form-control gc-read-only-input">' . $value . '</div>';
  }

});
$crud->callbackReadField('file_name', function ($value, $row) {
  return '<div class="form-control gc-read-only-input"><a href="' . WEB_ROOT . '/public/orders/' . $value . '">Скачать</a></div>';
});

$crud->setTable('orders');

if (isset($_GET['status'])) {
  if (is_array($_GET['status'])) {
    $where['orders.status'] = $_GET['status'];
  } elseif (is_numeric($_GET['status'])) {
    $where['orders.status'] = $_GET['status'];
  }
}

if (isset($_GET['status_updated_at_less']) && is_numeric($_GET['status_updated_at_less'])) {
  $where['orders.status_updated_at < ?'] = $_GET['status_updated_at_less'];
}

if (!empty($where)) {
  $crud->where($where);
}

$crud->defaultOrdering('orders.id', 'DESC');
// $crud->editFields(['status']);

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
load_tpl('/views/admin/orders/table.tpl', ['css_files' => $css_files, 'js_files' => $js_files, 'output' => $output]);
load_tpl('/views/admin/template/footer.tpl');
