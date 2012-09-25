<?php

require_once dirname(__FILE__).'/../lib/app.php';

class AdyenListener extends BaseListener
{
    protected $gateway = 'adyen';
    protected $gateway_pks = array(
        'pspReference',
        'gateway_txn_id',
    );

    function execute( $data )
    {
        parent::execute( $data );
        echo "[accepted]\n";
    }

    public function parse_data( $data )
    {
        $contribution = array();

        if ( !filter_var( $data['live'], FILTER_VALIDATE_BOOLEAN ) ) {
            throw new Exception( 'Ignoring incoming test-mode message' );
        }

        $contribution['currency'] = $data['currency'];
        $contribution['date'] = strtotime( $data['eventDate'] );
        $contribution['event_code'] = $data['eventCode'];
        $contribution['gateway_account_code'] = $data['merchantAccountCode'];
        $contribution['gateway'] = 'adyen';
        $contribution['gateway_txn_id'] = $data['pspReference'];
        $contribution['gross'] = $data['value'];
        $contribution['operations'] = $data['operations'];
        $contribution['order_id'] = $data['merchantReference'];
        $contribution['payment_method'] = $data['paymentMethod'];
        $contribution['reason'] = $data['reason'];
        $contribution['success'] = $data['success'];

        $found_limbo = $this->merge_limbo_data( $contribution );
        if ( !$found_limbo ) {
            //TODO don't throw away the data!
            throw new Exception("Unable to proceed without the matching limbo message.");
        }

        return $contribution;
    }

    function msg_sanity_check($contribution)
    {
        return array_key_exists( 'email', $contribution ) && $contribution['email'];
    }
}
