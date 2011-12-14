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
 * Require
 */
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . DIRECTORY_SEPARATOR . 'QueueHandlingTestCase.php';

/**
 * @group		Fundraising
 * @group		QueueHandling
 * @group		ClassMethod
 * @group		ListenerAdapter
 * @group		GlobalCollect
 */
class Listener_Adapter_GlobalCollect_CheckRequiredFieldsTestCase extends QueueHandlingTestCase
{
	
	/**
	 * testCheckRequiredFieldsIsASuccess
	 *
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::setData
	 * @covers Listener_Adapter_GlobalCollect::checkRequiredFields
	 */
	public function testCheckRequiredFieldsIsASuccess() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$_POST = $this->getPostDataForGlobalCollect();
		
		$adapterInstance->setData( $_POST );
		
		$this->assertTrue( $adapterInstance->checkRequiredFields() );
		
	}
	
	/**
	 * testCheckRequiredFieldsIsFalseWhenOrderIdIsEmpty
	 *
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::setData
	 * @covers Listener_Adapter_GlobalCollect::checkRequiredFields
	 */
	public function testCheckRequiredFieldsIsFalseWhenOrderIdIsEmpty() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$_POST = $this->getPostDataForGlobalCollectWithEmptyOrderId();
		
		$adapterInstance->setData( $_POST );
		
		$this->assertFalse( $adapterInstance->checkRequiredFields() );
		
	}
	
	/**
	 * testCheckRequiredFieldsIsFalseWhenOrderIdIsEmpty
	 *
	 * @covers Listener_Adapter_GlobalCollect::init
	 * @covers Listener_Adapter_Abstract::setData
	 * @covers Listener_Adapter_GlobalCollect::checkRequiredFields
	 */
	public function testCheckRequiredFieldsIsFalseWhenOrderIdIsEmptyAndRethrowTheException() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

		$_POST = $this->getPostDataForGlobalCollectWithEmptyOrderId();
		
		$adapterInstance->setData( $_POST );
		
		$message = 'ORDERID cannot be empty.';
		$this->setExpectedException( 'Listener_Exception', $message);
		
		$adapterInstance->checkRequiredFields( true );
		
	}
}

