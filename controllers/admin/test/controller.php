<?php
$order_text = file_get_contents(ROOT . '/views/registration/order_template.tpl');
$mpdf         = new \Mpdf\Mpdf(['tempDir' => ROOT . '/public/orders/']);
$mpdf->WriteHTML($order_text);
$mpdf->Output('Отчёт по апартаменту Е152 за июнь 2019.pdf', 'D');