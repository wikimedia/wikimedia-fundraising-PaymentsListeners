<?php

// FIXME: emergency shutdown duct tape:
exit(0);

$conf_path = '/etc/fundraising/legacy_paypal_config.php';

require_once( $conf_path );

$config['output_handle'] = fopen( $config['recurring_log_file'], 'a' );
require_once( $config['recurring_script_path'] );

// instantaite the listener with our config options
$listener = new PaypalRecurringIPNProcessor( $config );

// pass some data to the listner, usually this will be posted from PayPal's IPN
$listener->execute( $_POST );

// shutdown the listener
unset( $listener );

// cleanly close the file pointer for output
fclose( $output_handle );
