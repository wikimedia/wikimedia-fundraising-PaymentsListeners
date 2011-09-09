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
require_once dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'QueueHandlingTestCase.php';

/**
 * @group		Fundraising
 * @group		QueueHandling
 * @group		ClassMethod
 * @group		Listener
 *
 * @category	UnitTesting
 * @package		Fundraising_QueueHandling
 */
class Listener_FactoryTestCase extends QueueHandlingTestCase
{

	/**
	 * testFactoryWithAStringForParameters
	 *
	 * Set parameters to a string to generate an Exception
	 *
	 * @covers Listener::factory
	 */
	public function testFactoryWithAStringForParameters() {

		// The parameters to pass to the factory.
		$parameters = 'Parameters should be in the form of an array, not a string!';

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$message = 'Adapter options must be in the form of an array.';
		$this->setExpectedException( 'Listener_Exception', $message );

		Listener::factory( $adapter, $parameters );
	}

	/**
	 * testFactoryWithNoAdapter
	 *
	 * Set adapter to an empty string to generate an Exception
	 *
	 * @covers Listener::factory
	 */
	public function testFactoryWithNoAdapter() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = '';

		$message = 'You must choose an adapter.';
		$this->setExpectedException( 'Listener_Exception', $message );

		Listener::factory( $adapter, $parameters );
	}

	/**
	 * testFactoryWithAnArrayForAnAdapter
	 *
	 * Set adapter to an array to generate an Exception
	 *
	 * @covers Listener::factory
	 */
	public function testFactoryWithAnArrayForAnAdapter() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = array();

		$message = 'You must choose an adapter.';
		$this->setExpectedException( 'Listener_Exception', $message );

		Listener::factory( $adapter, $parameters );
	}

	/**
	 * testFactoryWithAPhonyAdapter
	 *
	 * Set adapter to a phony adapter to generate an Exception
	 *
	 * @covers Listener::factory
	 */
	public function testFactoryWithAPhonyAdapter() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'PhonyAdapter';

		$message = 'You must choose a valid adapter. Adapters available: ' . implode( ', ', Listener::getAdapters() );
		$this->setExpectedException( 'Listener_Exception', $message );

		Listener::factory( $adapter, $parameters );
	}

	/**
	 * testFactoryWithTheValidAdapterGlobalCollect
	 *
	 * Set adapter to the valid adapter GlobalCollect and verify it is the same
	 * class.
	 *
	 * @covers Listener::factory
	 */
	public function testFactoryWithTheValidAdapterGlobalCollect() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Listener_Adapter_GlobalCollect', $adapterInstance );

	}
}
