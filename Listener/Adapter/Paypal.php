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
 * @author		Jeremy Postlethwaite <jpostlethwaite@wikimedia.org>
 */

/**
 * @see Listener_Exception
 */
require_once 'Listener/Adapter/Abstract.php';

/**
 * Listener_Adapter_Paypal is not usable. This is a placeholder, in case it is
 *  decided to implement the PSC for Paypal.
 *
 * @todo
 * - Implement factory
 *
 */
class Listener_Adapter_Paypal extends Listener_Adapter_Abstract
{

	/**
	 * Adapter name
	 */
	 const ADAPTER = 'Paypal';
	 
	/**
	 * Initialize the class
	 *
	 * init() is called at the end of the constructor to allow automatic settings for adapters.
	 */
	protected function init() {
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
	 * Get the decision on whether or not the message will undergo further
	 * processing.
	 *
	 * This method provides the adapter with the ability handle messages.
	 *
	 * @return boolean	Returns true if the message can be handled by @see Listener_Adapter_Abstract::receive
	 */
	public function getProcessDecision() {
	
		return true;
	}
	
	/**
	 * Generate a response for the merchant provider
	 *
	 * @param array $status The status for the message
	 *
	 * @return boolean Returns true on success
	 */
	public function receiveReturn( $status ) {
	
		return (boolean) $status;
	}

	/**
	 * Verify the data has the required fields
	 *
	 * @todo
	 * - implement
	 *
	 * @return boolean Returns true on success
	 */
	public function checkRequiredFields() {
	
		return false;
	}

	/**
	 * Verify the payment was made
	 *
	 * @return boolean Returns true on success
	 */
	public function verifyPaymentNotification() {
	
		return false;
	}
}
