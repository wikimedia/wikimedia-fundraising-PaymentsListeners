<?php

$config_defaults = array(
    'log_level' => LOG_LEVEL_INFO,
    'log_file' => 'php://stderr',

    //'stomp_path' => "../../activemq_stomp/Stomp.php",

    'activemq_stomp_uri' => 'tcp://localhost:61613',

    'verified_queue' => '/queue/donations',

    //'paypal_postback_url' => 'https://www.sandbox.paypal.com/cgi-bin/webscr',
    'paypal_postback_url' => 'https://www.paypal.com/cgi-bin/webscr',
    'paypal_pending_queue' => '/queue/pending_paypal',

    'globalcollect_pending_queue' => '/queue/pending_globalcollect',
);
