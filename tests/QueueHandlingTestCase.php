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
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE
 * @author		Jeremy Postlethwaite <jpostlethwaite@wikimedia.org>
 */

require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'TestHelper.php';

/**
 * QueueHandlingTestCase
 */
abstract class QueueHandlingTestCase extends PHPUnit_Framework_TestCase
{
	
	/**
	 * getPostDataForGlobalCollect
	 *
	 * This is used to generate posted form data from the PSC Listener
	 *
	 * Everything should be returned as strings, in the array, since that is how
	 * they will be sent by the form.
	 *
	 * Anything set in $data will be converted to a string.
	 *
	 * If a value is null, in $data, it will be removed from $return.
	 *
	 * @param array $data	Anything set in this array will be returned.
	 *
	 * @return array
	 */
	public function getPostDataForGlobalCollect( $data = array()) {

		// Everything should be returned as strings since that is how they will be sent by the form
		$return = array(
			'MERCHANTID'		=> '9990',
			'ORDERID'			=> '23',
			'EFFORTID'			=> '1',
			'ATTEMPTID'			=> '1',
			'AMOUNT'			=> '100',
			'CURRENCYCODE'		=> 'EUR',
			'REFERENCE'			=> '20070406GC19',
			'PAYMENTREFERENCE'	=> '',
			'PAYMENTPRODUCTID'	=> '1',
			'PAYMENTMETHODID'	=> '8',
			'STATUSID'			=> '800',
			'STATUSDATE'		=> '20070406170059',
			'RECEIVEDDATE'		=> '20070406170057',
		);

		if ( is_array( $data ) ) {

			foreach( $data as $key => $value ) {
				
				// Remove values from return if $value is null.
				if ( is_null( $value ) ) {
				
					if ( isset( $return[ $key ] ) ) {
						
						unset( $return[ $key ] );
					}
				}
				else {
					$return[ $key ] = (string) $value;
				}
				
				
			}
		}
		
		return $return;
	}
	
	/**
	 * getPostDataForGlobalCollectWithEmptyOrderId
	 *
 	 * @see QueueHandlingTestCase::getPostDataForGlobalCollect
	 *
	 * @param array $data	Anything set in this array will be returned.
	 *
	 * @return array
	 */
	public function getPostDataForGlobalCollectWithEmptyOrderId( $data = array()) {

		$data['ORDERID'] = '';
		
		return $this->getPostDataForGlobalCollect( $data );
	}
	
	/**
	 * getPostDataForGlobalCollectWithOutOrderId
	 *
 	 * @see QueueHandlingTestCase::getPostDataForGlobalCollect
	 *
	 * @param array $data	Anything set in this array will be returned.
	 *
	 * @return array
	 */
	public function getPostDataForGlobalCollectWithOutOrderId( $data = array()) {

		$data['ORDERID'] = null;
		
		return $this->getPostDataForGlobalCollect( $data );
	}
}
