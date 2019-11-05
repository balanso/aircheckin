<?php
require_once ROOT . '/lib/sberbank.php';

$order_id = 'd379afdf-f318-7df3-b71b-741e0015d05b';

debug($sber_client->execute('/payment/rest/getOrderStatusExtended.do', [
      'orderId' => $order_id,
    ]));