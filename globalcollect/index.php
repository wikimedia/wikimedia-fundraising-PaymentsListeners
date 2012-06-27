<?php

require_once 'GlobalcollectListener.php';

$test_data_1 = array(
    'MERCHANTID' => 9991,
    'ORDERID' => 12345678,
    'EFFORTID' => 1,
    'ATTEMPTID' => 1,
    'PAYMENTREFERENCE' => '0',
    'ADDITIONALREFERENCE' => '1234567890',
    'PAYMENTMETHODID' => 1,
    'PAYMENTPRODUCTID' => 3,
    'STATUSID' => 625,
    'STATUSDATE' => 20120314144539,
    'RECEIVEDDATE' => 20120314144334,
    'CURRENCYCODE' => 'EUR',
    'AMOUNT' => 1000,
    'CVVRESULT' => 0,
    'FRAUDRESULT' => 'A',
    'CCLASTFOURDIGITS' => 1111,
    'EXPIRYDATE' => 0113
);

$listener = new GlobalcollectListener();
$listener->execute($_POST);
