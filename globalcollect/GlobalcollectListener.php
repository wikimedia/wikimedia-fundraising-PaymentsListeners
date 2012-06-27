<?php

require_once dirname(__FILE__).'/../lib/app.php';

class GlobalcollectListener extends BaseListener
{
    var $param_description = array(
        'MERCHANTID'           => array('type' => 'numeric', 'length' => 10),
        'ORDERID'              => array('map' => 'order_id', 'type' => 'numeric', 'length' => 10),
        'EFFORTID'             => array('map' => 'effort_id', 'type' => 'numeric', 'length' => 5),
        'ATTEMPTID'            => array('map' => 'attempt_id', 'type' => 'numeric', 'length' => 5),
        'AMOUNT'               => array('map' => 'amount', 'type' => 'numeric', 'length' => 12),
        'CURRENCYCODE'         => array('map' => 'currency_code', 'type' => 'string', 'length' => 3),
        'ADDITIONALREFERENCE'  => array('type' => 'string', 'length' => 30),
        'PAYMENTREFERENCE'     => array('map' => 'payment_reference', 'type' => 'string', 'length' => 20),
        'PAYMENTMETHODID'      => array('type' => 'numeric', 'length' => 5),
        'PAYMENTPRODUCTID'     => array('map' => 'payment_product', 'type' => 'numeric', 'length' => 5),
        'STATUSID'             => array('type' => 'numeric', 'length' => 5),
        'STATUSDATE'           => array('type' => 'numeric', 'length' => 14),
        'RECEIVEDDATE'         => array('map' => 'date', 'type' => 'numeric', 'length' => 14),
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

        $tracking_data = $this->copy_tracking_data($data['XXX'], $contribution);
        
        $contribution['gateway'] = 'globalcollect';
        $contribution['gateway_txn_id'] = $contribution['order_id'];

        //$contribution['original_currency'] = $post_data['mc_currency'];
        //$contribution['original_gross'] = $post_data['mc_gross'];
        //$contribution['fee'] = $post_data['mc_fee'];  
        //$contribution['gross'] = $post_data['mc_gross']; 
        //$contribution['net'] = $contribution['gross'] - $contribution['fee'];
        //$contribution['date'] = $timestamp;
        
        return $contribution;
    }

    function msg_sanity_check($msg)
    {
        return TRUE;
    }
}
