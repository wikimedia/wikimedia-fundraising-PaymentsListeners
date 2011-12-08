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
 * @see QueueHandlingTestCase
 */
require_once dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'QueueHandlingTestCase.php';

/**
 * @group		Fundraising
 * @group		QueueHandling
 * @group		ClassMethod
 * @group		Db
 *
 * Db_FactoryTestCase
 */
class Db_FactoryTestCase extends QueueHandlingTestCase
{

	/**
	 * testFactoryWithAStringForParameters
	 *
	 * Set parameters to a string to generate an Exception
	 *
	 * @covers Db::factory
	 */
	public function testFactoryWithAStringForParameters() {

		// The parameters to pass to the factory.
		$parameters = 'Parameters should be in the form of an array, not a string!';

		// The adapter to pass to the factory.
		$adapter = TESTS_DB_ADAPTER_DEFAULT;

		$message = 'Adapter options must be in the form of an array.';
		$this->setExpectedException( 'Db_Exception', $message );

		Db::factory( $adapter, $parameters );
	}

	/**
	 * testFactoryWithNoAdapter
	 *
	 * Set adapter to an empty string to generate an Exception
	 *
	 * @covers Db::factory
	 */
	public function testFactoryWithNoAdapter() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = '';

		$message = 'You must choose an adapter.';
		$this->setExpectedException( 'Db_Exception', $message );

		Db::factory( $adapter, $parameters );
	}

	/**
	 * testFactoryWithAnArrayForAnAdapter
	 *
	 * Set adapter to an array to generate an Exception
	 *
	 * @covers Db::factory
	 */
	public function testFactoryWithAnArrayForAnAdapter() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = array();

		$message = 'You must choose an adapter.';
		$this->setExpectedException( 'Db_Exception', $message );

		Db::factory( $adapter, $parameters );
	}

	/**
	 * testFactoryWithAPhonyAdapter
	 *
	 * Set adapter to a phony adapter to generate an Exception
	 *
	 * @covers Db::factory
	 */
	public function testFactoryWithAPhonyAdapter() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'PhonyAdapter';

		$message = 'You must choose a valid adapter. Adapters available: ' . implode( ', ', Db::getAdapters() );
		$this->setExpectedException( 'Db_Exception', $message );

		Db::factory( $adapter, $parameters );
	}

	/**
	 * testFactoryWithTheValidAdapterMysqli
	 *
	 * Set adapter to the valid adapter Mysqli and verify it is the same
	 * class.
	 *
	 * @covers Db::factory
	 */
	public function testFactoryWithTheValidAdapterMysqli() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'Mysqli';

		$adapterInstance = Db::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Db_Adapter_Mysqli', $adapterInstance );

	}
}
