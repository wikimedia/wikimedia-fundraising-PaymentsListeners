<?php

$config_defaults = array(
    'log_level' => 'info',
    'log_file' => 'php://stderr',

    'email_recipients' => array('fr_tech@wikimedia.org'),

    //'stomp_path' => "../../activemq_stomp/Stomp.php",
    'stomp_timeout' => 60,

    'activemq_stomp_uri' => 'tcp://localhost:61613',

    'limbo_queue' => '/queue/limbo',
    'verified_queue' => '/queue/donations',
    'failed_queue' => '/queue/failed',

    //'paypal_postback_url' => 'https://www.sandbox.paypal.com/cgi-bin/webscr',
    'paypal_postback_url' => 'https://www.paypal.com/cgi-bin/webscr',
    'paypal_pending_queue' => '/queue/pending_paypal',

    'globalcollect_pending_queue' => '/queue/pending_globalcollect',
);
