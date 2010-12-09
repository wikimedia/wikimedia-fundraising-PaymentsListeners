<?php
/**
 * The IPN listener for recurring payments from PayPal
 * 
 * This works almost exactly like the regular PayPal IPN listener -
 * except for subscription-based transactions.
 */

// include the original IPN listner
require_once( dirname(__FILE__) . '/PaypalIPNListener.php' );

class PaypalRecurringIPNProcessor extends PaypalIPNProcessor {

	/**
	 * Class constructor, sets configurable parameters
	 *
	 * @param $opts array of key, value pairs where the 'key' is the parameter name and the
	 *	value is the value you wish to set
	 */
	function __construct( $opts = array() ) {
		parent::__construct( $opts );
	}

	/**
	 * Overload parent's field checking
	 * 
	 * We really only want to make sure that the trxn we're seeing is
	 * something related to subscriptions.  (At least for now)
	 * 
	 * @param array $data
	 * @return bool
	 */
	public function msg_check_reqd_fields( $data ) {
		$pass = true;
		
		// for now, we just check that this is a subscription-related trxn
		if ( substr( $data[ 'txn_type' ], 0, 7 ) != 'subscr_' ) {
			$pass = false;
		}
		
		return $pass;
	}
}
