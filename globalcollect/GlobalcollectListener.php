<?php

require_once dirname(__FILE__).'/../lib/app.php';

class GlobalcollectListener extends BaseListener
{
	/**
	 * All incoming parameters, their expected type, and our normalized key name for them (where applicable)
	 */
    var $param_description = array(
        'ADDITIONALREFERENCE'  => array('type' => 'string', 'length' => 30),
        'AMOUNT'               => array('map' => 'gross', 'type' => 'numeric', 'length' => 12),
        'ATTEMPTID'            => array('map' => 'attempt_id', 'type' => 'numeric', 'length' => 5),
        'CURRENCYCODE'         => array('map' => 'currency', 'type' => 'string', 'length' => 3),
        'EFFORTID'             => array('map' => 'effort_id', 'type' => 'numeric', 'length' => 5),
        'MERCHANTID'           => array('type' => 'numeric', 'length' => 10),
        'ORDERID'              => array('map' => 'order_id', 'type' => 'numeric', 'length' => 10),
        'PAYMENTMETHODID'      => array('type' => 'numeric', 'length' => 5),
        'PAYMENTPRODUCTID'     => array('map' => 'payment_product', 'type' => 'numeric', 'length' => 5),
        'PAYMENTREFERENCE'     => array('map' => 'payment_reference', 'type' => 'string', 'length' => 20),
        'RECEIVEDDATE'         => array('map' => 'date', 'type' => 'numeric', 'length' => 14),
        'STATUSDATE'           => array('type' => 'numeric', 'length' => 14),
        'STATUSID'             => array('type' => 'numeric', 'length' => 5),
    );
	
	/**
	 * An array of keys (either original message, or our own) that we should always deliberately remove from emails
	 */
	var $dont_email = array();
	
	/**
	 * The gateway we are.
	 */
	var $gateway = 'globalcollect';
	
	/**
	 * An array of the keys that the gateway could possibly be using for their 
	 * own primary keys, to identify either the transaction, or the specific 
	 * message they have just sent us.
	 * These could be the keys they send us, or our normalized keys.
	 * These will be listed in order of preference and/or likelihood.
	 */
	var $gateway_pks = array(
		'ORDERID',
		'order_id',
	);

    public function parse_data( $data )
    {
        $contribution = array();
        foreach ($this->param_description as $name => $info)
        {
            // XXX no provision for missing fields
            if (array_key_exists($name, $data))
            {
                $filter = "is_{$info['type']}";
                if ($filter($data[$name]) === FALSE
                    || strlen($data[$name]) > $info['length'])
                {
                    throw new Exception("incoming value is illegal: {$name} = {$data[$name]}");
                }
                if (!empty($info['map']))
                {
                    $contribution[$info['map']] = $data[$name];
                }
            }
        }

        $contribution['gross'] = round($contribution['gross'], 2);
        $contribution['gateway'] = $this->gateway;
        $contribution['gateway_txn_id'] = $contribution['order_id'];
        $found_limbo = $this->merge_limbo_data($contribution);
        if (!$found_limbo)
            throw new Exception("Unable to proceed without the matching limbo message.");

        return $contribution;
    }

    function msg_sanity_check($contribution)
    {
        return (!empty($contribution['email']));
    }
}
