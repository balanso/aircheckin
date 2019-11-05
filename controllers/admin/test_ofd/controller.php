<?php
require_once ROOT . '/lib/ofd.php';
require_once ROOT . '/lib/db.php';

/*$receipt = ofd_generate_send_receipt('1', 'IncomeReturn', 'Аренда апартаментов Тест Income', 1, 'Соколов Лев Андреевич 6514 700', 'lev1t@yandex.ru', '+79785733360');

if (isset($receipt['ofd_receipt_id'])) {
  debug($receipt['ofd_receipt_id']);
}

sleep(5);*/
$receipt_url = ofd_get_receipt_data('4c93b398-cf9c-46ad-ac74-9e43d9c1fa21');
debug($receipt_url);
