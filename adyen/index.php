<?php

require_once 'AdyenListener.php';

$test_data_1 = array(
    'live' => 'true',
    'eventCode' => 'AUTHORISATION',
    'pspReference' => '123212312',
    'originalReference' => '121',
    'merchantReference' => '1111',
    'merchantAccountCode' => '111222',
    'eventDate' => '2009-01-01T01:02:01.111+02:00',
    'success' => 'true',
    'paymentMethod' => 'visa',//optional
    'operations' => 'CAPTURE,REFUND',
    'reason' => '83123:1212:11/2014', // or false
    'amount' => 'USD 1.12',
);

$listener = new AdyenListener();
//$listener->execute($_POST);
$listener->execute( $test_data_1 );
