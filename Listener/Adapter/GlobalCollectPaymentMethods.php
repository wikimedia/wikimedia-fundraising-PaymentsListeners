<?php
/**
 * Wikimedia Foundation
 *
 * LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE
 */

/**
 *
 * @todo
 *
 */
class Listener_Adapter_GlobalCollectPaymentMethods
{

	/**
	 * The array of payment methods and the corresponding payment products.
	 *
	 * @var array $paymentMethods
	 */
	protected $paymentMethods = array();
	 
	/**
	 * Initialize the class
	 *
	 */
	public function __construct() {
		
		$this->setPaymentMethods();
	}

	/**
	 * setPaymentMethods
	 *
	 * Set payment methods available from GlobalCollect
	 */
	protected function setPaymentMethods() {
		
		$this->paymentMethods = array(
			1	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'credit',	'label' => 'Credit card online',),
			2	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Credit card batch - offline',),
			3	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Direct Debit',),
			4	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Online Bank Transfer',),
			5	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Check',),
			6	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Invoice',),
			7	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Bank transfer',),
			8	=> array(	'paymentProducts' => array(),	'process'	=> true,	'queue' => 'limbo',		'label' => 'Real-time bank transfer, eWallets',),
			10	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Bank refunds',),
			12	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Payouts',),
			14	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Pre-paid methods',),
			15	=> array(	'paymentProducts' => array(),	'process'	=> false,	'queue' => 'limbo',		'label' => 'Cash',),
		);
	}

	/**
	 * getPaymentMethods
	 *
	 * If $paymentMethodId is empty, then this method will return all payment
	 * methods, otherwise it will return the requested payment method.
	 *
	 * @param string	$paymentMethodId 	The payment method ID from GlobalCollect
	 *
	 * @return array
	 */
	public function getPaymentMethods( $paymentMethodId = '' ) {
		
		if ( empty( $paymentMethodId ) ) {
			return $this->paymentMethods;
		}
		
		if ( isset( $this->paymentMethods[ $paymentMethodId ] ) ) {
		
			return $this->paymentMethods[ $paymentMethodId ];
		}
		
		$message = 'The payment method id was not found: ' . $paymentMethodId;
		throw new Listener_Exception( $message );
		
	}

	/**
	 * getProcessDecision
	 *
	 * If $paymentMethodId is empty, then this method will return all payment
	 * methods, otherwise it will return the requested payment method.
	 *
	 * @param string	$paymentMethodId 	The payment method ID from GlobalCollect
	 *
	 * @return boolean
	 */
	public function getProcessDecision( $paymentMethodId ) {
		
		$method = $this->getPaymentMethods( $paymentMethodId );
		
		return isset( $method['process'] ) ? $method['process'] : false;
	}
}
