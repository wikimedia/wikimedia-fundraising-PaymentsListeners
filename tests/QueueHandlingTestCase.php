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
	 * getLimboDataForGlobalCollect
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
	public function getLimboDataForGlobalCollect( $data = array() ) {

		// Everything should be returned as strings since that is how they will be sent by the form
		$return = array(
			'response'					=> '{"MERCHANTID":"9990","ORDERID":"23","EFFORTID":"1","ATTEMPTID":"1","AMOUNT":"100","CURRENCYCODE":"EUR","REFERENCE":"20070406GC19","PAYMENTREFERENCE":"","PAYMENTPRODUCTID":"809","PAYMENTMETHODID":"8","STATUSID":"800","STATUSDATE":"20070406170059","RECEIVEDDATE":"20070406170057"}',
			'date' 						=> time(),
			'gateway'					=> 'globalcollect',
			'gateway_txn_id'			=> '23',
			'correlation-id'			=> 'globalcollect-23',
			'payment_method'			=> 'rtbt',
			'payment_submethod'			=> 'rtbt_ideal',
			'contribution_tracking_id'	=> '23',
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
	public function getPostDataForGlobalCollect( $data = array() ) {

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
	 * getRowDataForGlobalCollect
	 *
	 * This is used to create rows in the CiviCRM database
	 *
	 * Everything should be returned as strings, in the array, since that is how
	 * they will be sent by the database.
	 *
	 * Anything set in $data will be converted to a string.
	 *
	 * If a value is null, in $data, it will be removed from $return.
	 *
	 * @param array $data	Anything set in this array will be returned.
	 *
	 * @return array
	 */
	public function getRowDataForGlobalCollect( $data = array() ) {

		// Everything should be returned as strings since that is how they will be sent by the form
		$return = array(
			'queue2civicrm_limbo_id'		=> 1,
			'contribution_tracking_id'		=> 1,
			'order_id'						=> 23,
			'timestamp'						=> time(),
			'data'							=> 'Just some data that should be in json format.',
			'gateway'						=> strtolower( TESTS_LISTENER_ADAPTER_DEFAULT ),
			'payment_method'				=> 'rtbt',
			'payment_submethod'				=> 'rtbt_ideal',
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
	public function getPostDataForGlobalCollectWithEmptyOrderId( $data = array() ) {

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
	public function getPostDataForGlobalCollectWithOutOrderId( $data = array() ) {

		$data['ORDERID'] = null;
		
		return $this->getPostDataForGlobalCollect( $data );
	}
	
	/**
	 * removeFromDatabaseByOrderId
	 *
	 * @param string $orderId	The order id to delete
	 *
	 * @return boolean	Returns true if records by orderId were deleted
	 */
	public function removeFromDatabaseByOrderId( $orderId ) {

		// The parameters to pass to the factory.
		$parameters = array(
			'database'	=> TESTS_DB_ADAPTER_DATABASE_FOR_TESTING,
			'host'		=> TESTS_DB_ADAPTER_HOST,
			'password'	=> TESTS_DB_ADAPTER_PASSWORD,
			'username'	=> TESTS_DB_ADAPTER_USERNAME,
			'port'		=> TESTS_DB_ADAPTER_PORT,
			'socket'	=> TESTS_DB_ADAPTER_SOCKET,
			'flags'		=> MYSQLI_CLIENT_INTERACTIVE,
		);

		// The adapter to pass to the factory.
		$adapter = 'Mysqli';

		$adapterInstance = Db::factory( $adapter, $parameters );
		$table = 'queue2civicrm_limbo';
		$key = 'order_id';
		
		// Delete the record if it exists
		return (boolean) $adapterInstance->delete( $table, $key, $orderId );
	}
	
	/**
	 * removeFromDatabaseByOrderId
	 *
	 * @param string $id	The queue2civicrm_limbo_id to delete
	 *
	 * @return boolean	Returns true if records by queue2civicrm_limbo_id were deleted
	 */
	public function removeFromDatabaseByQueue2CivicrmLimboId( $id ) {

		// The parameters to pass to the factory.
		$parameters = array(
			'database'	=> TESTS_DB_ADAPTER_DATABASE_FOR_TESTING,
			'host'		=> TESTS_DB_ADAPTER_HOST,
			'password'	=> TESTS_DB_ADAPTER_PASSWORD,
			'username'	=> TESTS_DB_ADAPTER_USERNAME,
			'port'		=> TESTS_DB_ADAPTER_PORT,
			'socket'	=> TESTS_DB_ADAPTER_SOCKET,
			'flags'		=> MYSQLI_CLIENT_INTERACTIVE,
		);

		// The adapter to pass to the factory.
		$adapter = 'Mysqli';

		$adapterInstance = Db::factory( $adapter, $parameters );
		$table = 'queue2civicrm_limbo';
		$key = 'queue2civicrm_limbo_id';
		
		// Delete the record if it exists
		return (boolean) $adapterInstance->delete( $table, $key, $id );
	}
	
	/**
	 * removeFromLimboByOrderId
	 *
	 * @param string $orderId	The order id to delete
	 *
	 * @return boolean	Returns true if records by orderId were deleted
	 */
	public function removeFromLimboByOrderId( $orderId = '' ) {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );
		
		$limboId = '';
		
		if ( !empty( $orderId ) ) {
			$limboId = $adapterInstance->getAdapterTypeLowerCase() . '-' . $orderId;
		}
		else {
			$_POST = $this->getPostDataForGlobalCollect();
			
			// Set the default data to clear the queue.
			$adapterInstance->setData( $_POST );
		}
		
		$attemptToDelete = true;
		
		$count = 0;
		while ( $attemptToDelete ) {
			$attemptToDelete = $adapterInstance->fetchFromLimboAndDequeue( $limboId );
			
			$count++;
			
			if ( $count > 100 ) {
				$message = 'Deleting to many messages from limbo: ' . $count;
				throw new Exception( $message );
			}
		}
		

	}
	/**
	 * removeFromLimboByOrderId
	 *
	 * @param string $orderId	The order id to delete
	 *
	 * @return boolean	Returns true if records by orderId were deleted
	 */
	public function removeFromVerifiedByOrderId( $orderId = '' ) {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );
		
		$limboId = '';
		
		if ( !empty( $orderId ) ) {
			$limboId = $adapterInstance->getAdapterTypeLowerCase() . '-' . $orderId;
		}
		else {
			$_POST = $this->getPostDataForGlobalCollect();
			
			// Set the default data to clear the queue.
			$adapterInstance->setData( $_POST );
		}
		
		$attemptToDelete = true;
		
		$count = 0;
		while ( $attemptToDelete ) {
			$attemptToDelete = $adapterInstance->fetchFromPendingAndDequeue( $limboId );
			
			$count++;
			
			if ( $count > 100 ) {
				$message = 'Deleting to many messages from pending: ' . $count;
				throw new Exception( $message );
			}
		}
		

	}
	
	/**
	 * removeDefaultTestRecordsFromEverywhere
	 *
	 * @param string $orderId	The order id to delete
	 *
	 * @return boolean	Returns true if records by orderId were deleted
	 */
	public function removeDefaultTestRecordsFromEverywhere( $orderId = '' ) {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );
		
		$data = array();
		
		if ( !empty( $orderId ) ) {
			$data[ $adapterInstance->getLimboIdName() ] = $orderId;
		}
		
		$_POST = $this->getPostDataForGlobalCollect( $data );
		
		// Set the default data to clear the queue.
		$adapterInstance->setData( $_POST );

		// Remove methods
		$this->removeFromLimboByOrderId( $orderId );
		$this->removeFromPendingByOrderId( $orderId );
		//$this->removeFromVerifiedByOrderId( $orderId );
		$this->removeFromDatabaseByOrderId( $orderId );
		
		
	}
}
