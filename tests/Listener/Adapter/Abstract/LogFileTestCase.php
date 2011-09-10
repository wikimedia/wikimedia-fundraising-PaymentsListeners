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
 * @group		ListenerAdapter
 *
 * @category	UnitTesting
 * @package		Fundraising_QueueHandling
 */
class Listener_Adapter_Abstract_LogFileTestCase extends QueueHandlingTestCase
{

	/**
	 * testGetLogFileWhichShouldBeEmpty
	 *
	 * @covers Listener_Adapter_Abstract::getLogFile
	 */
	public function testGetLogFileWhichShouldBeEmpty() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertEmpty( $adapterInstance->getLogFile() );
	}

	/**
	 * testSetLogFileWithDefaultLogFile
	 *
	 * @covers Listener_Adapter_Abstract::getLogFile
	 * @covers Listener_Adapter_Abstract::setLogFile
	 */
	public function testSetLogFileWithDefaultLogFile() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$file = BASE_PATH . '/logs/' . strtolower( $adapterInstance->getAdapterType() ) . '/' . date( 'Ymd' ) . '.log';
		$adapterInstance->setLogFile();

		$this->assertSame( $file, $adapterInstance->getLogFile() );
	}

	/**
	 * testSetLogFileWithNonExistentDirectory
	 *
	 * @covers Listener_Adapter_Abstract::setLogFile
	 */
	public function testSetLogFileWithNonExistentDirectory() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$file = BASE_PATH . '/tests/logs/i-do-not-exist/out.log';
		$directory = dirname( $file );

		$message = 'The directory for the output log does not exist. Please create: ' . $directory;
		$this->setExpectedException( 'Listener_Exception', $message );

		$adapterInstance->setLogFile( $file );
	}

	/**
	 * testSetLogFileWithReadOnlyDirectory
	 *
	 * @covers Listener_Adapter_Abstract::setLogFile
	 */
	public function testSetLogFileWithReadOnlyDirectory() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$file = BASE_PATH . '/tests/resources/read-only-directory/out.log';
		$directory = dirname( $file );

		$message = 'The directory for the output log is not writable. Please chmod +rw: ' . $directory;
		$this->setExpectedException( 'Listener_Exception', $message );

		$adapterInstance->setLogFile( $file );
	}

	/**
	 * testSetOutputHandleWithDefaultLogFile
	 *
	 * @covers Listener_Adapter_Abstract::getLogFile
	 * @covers Listener_Adapter_Abstract::setLogFile
	 * @covers Listener_Adapter_Abstract::setOutputHandle
	 * @covers Listener_Adapter_Abstract::getOutputHandle
	 */
	public function testSetOutputHandleWithDefaultLogFile() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$file = BASE_PATH . '/logs/' . strtolower( $adapterInstance->getAdapterType() ) . '/' . date( 'Ymd' ) . '.log';
		$adapterInstance->setOutputHandle();

		// Assert log file is the default
		$this->assertSame( $file, $adapterInstance->getLogFile() );
		
		// Assert log file has a valid resource handle.
		$this->assertInternalType( 'resource', $adapterInstance->getOutputHandle() );
		//Debug::dump($adapterInstance->getOutputHandle(), eval(DUMP) . "\$adapterInstance->getOutputHandle()", false);
	}
}
