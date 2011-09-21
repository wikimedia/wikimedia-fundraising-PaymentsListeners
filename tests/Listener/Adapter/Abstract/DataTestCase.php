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
 * @category	UnitTesting
 * @package		Fundraising_QueueHandling
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE
 * @since		r462
 * @author		Jeremy Postlethwaite <jpostlethwaite@wikimedia.org>
 */

/**
 * Require
 */
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . DIRECTORY_SEPARATOR . 'QueueHandlingTestCase.php';

/**
 * @group		Fundraising
 * @group		QueueHandling
 * @group		ClassMethod
 * @group		Listener
 *
 * @category	UnitTesting
 * @package		Fundraising_QueueHandling
 */
class Listener_Adapter_Abstract_DataTestCase extends QueueHandlingTestCase
{

	/**
	 * testGetDataIsEmptyByDefault
	 *
	 * @covers Listener_Adapter_Abstract::getData
	 */
	public function testGetDataIsEmptyByDefault() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertEmpty( $adapterInstance->getData() );
	}

	/**
	 * testGetDataIsNotEmptyAfterSettingWithPost
	 *
	 * @covers Listener_Adapter_Abstract::getData
	 * @covers Listener_Adapter_Abstract::setData
	 */
	public function testGetDataIsNotEmptyAfterSettingWithPost() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$_POST = array(
			'MERCHANTID'		=> "9990",
			'ORDERID'			=> "23",
			'EFFORTID'			=> "1",
			'ATTEMPTID'			=> "1",
			'AMOUNT'			=> "100",
			'CURRENCYCODE'		=> "EUR",
			'REFERENCE'			=> "20070406GC19",
			'PAYMENTREFERENCE'	=> "",
			'PAYMENTPRODUCTID'	=> "1",
			'PAYMENTMETHODID'	=> "1",
			'STATUSID'			=> "525",
			'STATUSDATE'		=> "20070406170059",
			'RECEIVEDDATE'		=> "20070406170057",
		);
		
		$adapterInstance->setData( $_POST );
		
		$this->assertNotEmpty( $adapterInstance->getData() );
	}
	
	/**
	 * testCheckRequiredFieldsShouldReturnFalseWhenARequiredFieldIsMissing
	 *
	 * In this case, MERCHANTID is omitted.
	 *
	 * @covers Listener_Adapter_Abstract::getData
	 * @covers Listener_Adapter_Abstract::setData
	 * @covers Listener_Adapter_Abstract::checkRequiredFields
	 */
	public function testCheckRequiredFieldsShouldReturnFalseWhenARequiredFieldIsMissing() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$_POST = array(
			'ORDERID'			=> "23",
			'EFFORTID'			=> "1",
			'ATTEMPTID'			=> "1",
			'AMOUNT'			=> "100",
			'CURRENCYCODE'		=> "EUR",
			'REFERENCE'			=> "20070406GC19",
			'PAYMENTREFERENCE'	=> "",
			'PAYMENTPRODUCTID'	=> "1",
			'PAYMENTMETHODID'	=> "1",
			'STATUSID'			=> "525",
			'STATUSDATE'		=> "20070406170059",
			'RECEIVEDDATE'		=> "20070406170057",
		);
		
		$adapterInstance->setData( $_POST );

		$adapterInstance->checkRequiredFields();
	}
	
	/**
	 * testGetDataShouldThrowAnExceptionWhenARequiredFieldIsMissing
	 *
	 * In this case, MERCHANTID is omitted.
	 *
	 * @covers Listener_Adapter_Abstract::getData
	 * @covers Listener_Adapter_Abstract::setData
	 */
	public function testGetDataShouldThrowAnExceptionWhenARequiredFieldIsMissing() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$_POST = array(
			'ORDERID'			=> "23",
			'EFFORTID'			=> "1",
			'ATTEMPTID'			=> "1",
			'AMOUNT'			=> "100",
			'CURRENCYCODE'		=> "EUR",
			'REFERENCE'			=> "20070406GC19",
			'PAYMENTREFERENCE'	=> "",
			'PAYMENTPRODUCTID'	=> "1",
			'PAYMENTMETHODID'	=> "1",
			'STATUSID'			=> "525",
			'STATUSDATE'		=> "20070406170059",
			'RECEIVEDDATE'		=> "20070406170057",
		);
		
		$adapterInstance->setData( $_POST );

		$key = 'MERCHANTID';
		//Debug::dump($key, eval(DUMP) . "\$key", false);

		$message = 'The required key is not set in data: ' . $key;
		$this->setExpectedException( 'Listener_Exception', $message );
		
		$adapterInstance->getData( $key, true );
	}
	
	/**
	 * testGetDataShouldReturnNullWhenANon_RequiredFieldIsMissing
	 *
	 * In this case, MERCHANTID is omitted.
	 *
	 * @covers Listener_Adapter_Abstract::getData
	 * @covers Listener_Adapter_Abstract::setData
	 */
	public function testGetDataShouldReturnNullWhenANon_RequiredFieldIsMissing() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$_POST = array(
			'ORDERID'			=> "23",
			'EFFORTID'			=> "1",
			'ATTEMPTID'			=> "1",
			'AMOUNT'			=> "100",
			'CURRENCYCODE'		=> "EUR",
			'REFERENCE'			=> "20070406GC19",
			'PAYMENTREFERENCE'	=> "",
			'PAYMENTPRODUCTID'	=> "1",
			'PAYMENTMETHODID'	=> "1",
			'STATUSID'			=> "525",
			'STATUSDATE'		=> "20070406170059",
			'RECEIVEDDATE'		=> "20070406170057",
		);
		
		$adapterInstance->setData( $_POST );

		$key = 'MERCHANTID';
		//Debug::dump($key, eval(DUMP) . "\$key", false);

		$this->assertNull( $adapterInstance->getData( $key ) );
	}
	
	/**
	 * testGetDataIsCallingForTheKeyMerchantidWhichShouldExist
	 *
	 * In this case, MERCHANTID is omitted.
	 *
	 * @covers Listener_Adapter_Abstract::getData
	 * @covers Listener_Adapter_Abstract::setData
	 */
	public function testGetDataIsCallingForTheKeyMerchantidWhichShouldExist() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = TESTS_LISTENER_ADAPTER_DEFAULT;

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$_POST = array(
			'MERCHANTID'		=> "9990",
			'ORDERID'			=> "23",
			'EFFORTID'			=> "1",
			'ATTEMPTID'			=> "1",
			'AMOUNT'			=> "100",
			'CURRENCYCODE'		=> "EUR",
			'REFERENCE'			=> "20070406GC19",
			'PAYMENTREFERENCE'	=> "",
			'PAYMENTPRODUCTID'	=> "1",
			'PAYMENTMETHODID'	=> "1",
			'STATUSID'			=> "525",
			'STATUSDATE'		=> "20070406170059",
			'RECEIVEDDATE'		=> "20070406170057",
		);
		
		$adapterInstance->setData( $_POST );

		$key = 'MERCHANTID';
		
		$this->assertEquals( $_POST[ $key ], $adapterInstance->getData( $key ) );
	}
}
