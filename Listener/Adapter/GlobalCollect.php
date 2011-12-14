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
 *
 * @category	Fundraising
 * @package		Fundraising_QueueHandling
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE
 * @since		r462
 * @author		Jeremy Postlethwaite <jpostlethwaite@wikimedia.org>
 */

/**
 * @see Listener_Adapter_Abstract
 */
require_once 'Listener/Adapter/Abstract.php';

/**
 * @see Listener_Adapter_GlobalCollectPaymentMethods
 */
require_once 'Listener/Adapter/GlobalCollectPaymentMethods.php';

/**
 *
 * @todo
 * - Implement factory
 *
 * @category	Fundraising
 * @package		Fundraising_QueueHandling
 */
class Listener_Adapter_GlobalCollect extends Listener_Adapter_Abstract
{

	/**
	 * Adapter name
	 */
	 const ADAPTER = 'GlobalCollect';

	/**
	 * Get the decision on whether or not the message will undergo further
	 * processing.
	 *
	 * This method provides the adapter with the ability handle messages.
	 *
	 * @return boolean	Returns true if the message can be handled by @see Listener_Adapter_Abstract::receive
	 */
	public function getProcessDecision() {

		$paymentMethods = new Listener_Adapter_GlobalCollectPaymentMethods();
		
		$method = $paymentMethods->getPaymentMethods( $this->getData('PAYMENTMETHODID') );

		if ( isset( $method['queue'] ) ) {
			$path = '/queue/' . $method['queue'];
			$this->setQueueLimbo( $path );
		}
		
		$message = 'Making decision on whether or not to process a payment with PAYMENTMETHODID:' . $this->getData('PAYMENTMETHODID');
		$this->log( $message, Listener::LOG_LEVEL_DEBUG );
	
		return $paymentMethods->getProcessDecision( $this->getData('PAYMENTMETHODID') );
	}
	 
	/**
	 * Initialize the class
	 *
	 * init() is called at the end of the constructor to allow automatic settings for adapters.
	 */
	protected function init() {
		
		// Global Collect needs to pull from the limbo queue.
		$this->setPullFromLimbo( true );
		
		// ORDERID maps to JMSCorrelationID in the limbo queue.
		$this->setLimboIdName( 'ORDERID' );
	}

	/**
	 * Parse the data and format for Contribution Tracking
	 *
	 * @return array	Return the formatted data
	 */
	public function parse() {
	
		return $this->getData();
	}

	/**
	 * Generate a response for the merchant provider
	 *
	 * @param array $status The status for the message
	 *
	 * @return string Returns 'OK' for success 'NOK' for failure
	 */
	public function receiveReturn( $status ) {
	
		return $status ? 'OK' : 'NOK' ;
	}

	/**
	 * Verify the data has the required fields
	 *
	 * @param boolean $rethrowExceptions	If true, exceptions will be rethrown in the catch.
	 *
	 * Field types:
	 * - 
	 * - MERCHANTID: N10 -> 1
	 * - ORDERID: N10 -> 1
	 * - EFFORTID: N5 -> 1
	 * - ATTEMPTID: N5 -> 1
	 * - AMOUNT: N12 -> 100 (=1.00)
	 * - CURRENCYCODE: AN3 -> USD
	 * - REFERENCE: AN30 -> 000000000100002121210000100001
	 * - PAYMENTREFERENCE: AN20 -> 191900000001
	 * - PAYMENTMETHODID: N5 -> 1 (credit card online)
	 * - PAYMENTPRODUCTID: N5 -> 1 (VISA)
	 * - STATUSID: N5 -> See WebCollection status codes
	 * - STATUSDATE: N14 -> 20030828152500 (ccyymmddhh24miss)
	 * - RECEIVEDDATE: N14 -> 20030828152500 (ccyymmddhh24miss)
	 *
	 * @return boolean Returns true on success
	 */
	public function checkRequiredFields( $rethrowExceptions = false ) {

		$rethrowExceptions = (boolean) $rethrowExceptions;
		$return = false;
		
		try {
			
			$sanitized = array(
				'MERCHANTID'		=> (integer)	$this->getData('MERCHANTID', true),
				'ORDERID'			=> (integer) 	$this->getData('ORDERID', true),
				'EFFORTID'			=> (integer) 	$this->getData('EFFORTID', true),
				'ATTEMPTID'			=> (integer) 	$this->getData('ATTEMPTID', true),
				'AMOUNT'			=> (float) 		( $this->getData('AMOUNT', true) / 100 ),
				'CURRENCYCODE'		=> (string) 	$this->getData('CURRENCYCODE', true),
				'REFERENCE'			=> (string) 	$this->getData('REFERENCE', true),
				'PAYMENTREFERENCE'	=> (string) 	$this->getData('PAYMENTREFERENCE', true),
				'PAYMENTMETHODID'	=> (integer)	$this->getData('PAYMENTMETHODID', true),
				'PAYMENTPRODUCTID'	=> (integer)	$this->getData('PAYMENTPRODUCTID', true),
				'STATUSID'			=> (integer)	$this->getData('STATUSID', true),
				'STATUSDATE'		=> (integer)	$this->getData('STATUSDATE', true),
				'RECEIVEDDATE'		=> (integer)	$this->getData('RECEIVEDDATE', true),
			);

			
			if ( empty( $sanitized['ORDERID'] ) ) {
				$message = 'ORDERID cannot be empty.';
				throw new Listener_Exception( $message );
			}

			$return = true;

			$this->setData( $sanitized );
			
			
		} catch ( Listener_Exception $e ) {

			$message = 'Unable to check required fields: ' . $e->getMessage();
			$this->log( $message, Listener::LOG_LEVEL_EMERG );
			
			if ( $rethrowExceptions ) {
				throw new Listener_Exception( $e->getMessage() );
			}
			
		}

		return $return;
	}

	/**
	 * Verify the payment was made.
	 *
	 * Currently, we are only verifying the STATUSID >= 800 
	 *
	 * @return boolean Returns true on success
	 */
	public function verifyPaymentNotification() {
	 
		return ( $this->getData('STATUSID') >= 800 );
	}
}
