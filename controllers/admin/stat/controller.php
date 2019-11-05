<?
if (isset($_GET['pdf'])) {
  include __DIR__ . '/apart/generate_pdf.php';
}


load_tpl('/views/admin/template/header.tpl');

if (isset($param1)) {
  switch ($param1) {
    case 'current':
      include __DIR__ . '/current/main.php';
      break;
    case 'apart':
      include __DIR__ . '/apart/main.php';
      break;
    case 'apart_new':
      include __DIR__ . '/apart_new/main.php';
      break;
    case 'tenants':
      include __DIR__ . '/tenants/main.php';
      break;

    default:
      include __DIR__ . '/current/main.php';
      break;
  }
}

load_tpl('/views/admin/template/footer.tpl');
