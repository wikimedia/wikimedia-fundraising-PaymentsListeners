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
 * @see Listener_Exception
 */
require_once 'Listener/Adapter/Abstract.php';

/**
 *
 * @todo
 * - Implement factory
 *
 * @category	Fundraising
 * @package		Fundraising_QueueHandling
 */
class Listener_Adapter_Paypal extends Listener_Adapter_Abstract
{

	/**
	 * Adapter name
	 */
	 const ADAPTER = 'Paypal';

	/**
	 * queuePending
	 *
	 * This is path to pending queue
	 *
	 * @var string queuePending
	 */
	protected $queuePending = '/queue/pending_paypal';

	/**
	 * queueVerified
	 *
	 * This is path to verified queue
	 *
	 * @var string queueVerified
	 */
	protected $queueVerified = '/queue/verified_paypal';

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
