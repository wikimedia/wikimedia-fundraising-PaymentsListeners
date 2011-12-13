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
 * @group		Log
 * @group		LogFile
 *
 * @category	UnitTesting
 * @package		Fundraising_QueueHandling
 */
class Listener_Adapter_Abstract_LogFileTestCase extends QueueHandlingTestCase
{

	/**
	 * setup
	 *
	 * @covers Listener_Adapter_Abstract::getLogFile
	 */
	public function setUp() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$adapterInstance->openOutputHandle();
		$adapterInstance->logTruncate();
	}

	/**
	 * testLogSendMessageToFile
	 *
	 * @covers Listener_Adapter_Abstract::openOutputHandle
	 * @covers Listener_Adapter_Abstract::closeOutputHandle
	 * @covers Listener_Adapter_Abstract::hasOutputHandle
	 */
	public function testOpenAndCloseOutputHandle() {
		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );
		// Debug::dump($adapterInstance->getLogLevel(), eval(DUMP) . "\$adapterInstance->getLogLevel()", false);

		// $file = BASE_PATH . '/logs/' . strtolower( $adapterInstance->getAdapterType() ) . '/' . date( 'Ymd' ) . '.log';
		// Debug::dump($adapterInstance->hasOutputHandle(), eval(DUMP) . "\$adapterInstance->hasOutputHandle()", false);
		$adapterInstance->openOutputHandle();
		$this->assertTrue( $adapterInstance->hasOutputHandle() );

		$adapterInstance->closeOutputHandle();
		$this->assertFalse( $adapterInstance->hasOutputHandle() );
		// Debug::dump($adapterInstance->hasOutputHandle(), eval(DUMP) . "\$adapterInstance->hasOutputHandle()", false);
	}

	/**
	 * testGetLogFileWhichShouldNotBeEmpty
	 *
	 * @covers Listener_Adapter_Abstract::getLogFile
	 */
	public function testGetLogFileWhichShouldNotBeEmpty() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertNotEmpty( $adapterInstance->getLogFile() );
	}

	/**
	 * testDoesHaveOutputHandleByDefault
	 *
	 * @covers Listener_Adapter_Abstract::hasOutputHandle
	 */
	public function testDoesHaveOutputHandleByDefault() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertTrue( $adapterInstance->hasOutputHandle() );
	}

	/**
	 * testSetLogFileWithDefaultLogFile
	 *
	 * @covers Listener_Adapter_Abstract::getLogFile
	 * @covers Listener_Adapter_Abstract::setLogFile
	 * @covers Listener_Adapter_Abstract::__destruct
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
	 * testOpenOutputHandleWithDefaultLogFile
	 *
	 * @covers Listener_Adapter_Abstract::getLogFile
	 * @covers Listener_Adapter_Abstract::setLogFile
	 * @covers Listener_Adapter_Abstract::openOutputHandle
	 * @covers Listener_Adapter_Abstract::getOutputHandle
	 */
	public function testOpenOutputHandleWithDefaultLogFile() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$file = BASE_PATH . '/logs/' . strtolower( $adapterInstance->getAdapterType() ) . '/' . date( 'Ymd' ) . '.log';
		$adapterInstance->openOutputHandle();

		// Assert log file is the default
		$this->assertSame( $file, $adapterInstance->getLogFile() );

		// Assert log file has a valid resource handle.
		$this->assertInternalType( 'resource', $adapterInstance->getOutputHandle() );
		// Debug::dump($adapterInstance->getOutputHandle(), eval(DUMP) . "\$adapterInstance->getOutputHandle()", false);
	}

	/**
	 * testLogByLevels
	 *
	 * @covers Listener_Adapter_Abstract::log
	 * @covers Listener_Adapter_Abstract::__construct
	 * @covers Listener_Adapter_Abstract::setLogLevel
	 * @covers Listener_Adapter_Abstract::closeOutputHandle
	 */
	public function testLogByLevelsWithNoNoticeMessagesBecauseWeAreListeningToAlertOrHigher() {
		// The parameters to pass to the factory.
		$parameters = array(
			'logLevel' => Listener::LOG_LEVEL_ALERT
		);

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );
		// Debug::dump($adapterInstance->getLogLevel(), eval(DUMP) . "\$adapterInstance->getLogLevel()", false);

		// $file = BASE_PATH . '/logs/' . strtolower( $adapterInstance->getAdapterType() ) . '/' . date( 'Ymd' ) . '.log';
		// Debug::dump($adapterInstance->hasOutputHandle(), eval(DUMP) . "\$adapterInstance->hasOutputHandle()", false);
		$adapterInstance->openOutputHandle();
		// Debug::dump($adapterInstance->hasOutputHandle(), eval(DUMP) . "\$adapterInstance->hasOutputHandle()", false);

		$message = 'I found a penny :)';
		// Debug::dump($message, eval(DUMP) . "\$message", false);

		// Log at the emergency level so it goes through.
		$adapterInstance->log( $message, Listener::LOG_LEVEL_NOTICE );
		// Assert log file is the default
		$this->assertEmpty( $adapterInstance->getLogContents() );

		$message = 'Look a million dollars!!!';
		// Debug::dump($message, eval(DUMP) . "\$message", false);

		// Log at the emergency level so it goes through.
		$adapterInstance->log( $message, Listener::LOG_LEVEL_EMERG );
		// Assert log file is the default
		$this->assertContains( $message, $adapterInstance->getLogContents() );
	}

	/**
	 * testLogSendMessageToFile
	 *
	 * @covers Listener_Adapter_Abstract::log
	 */
	public function testLogSendMessageToFile() {
		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );
		// Debug::dump($adapterInstance->getLogLevel(), eval(DUMP) . "\$adapterInstance->getLogLevel()", false);

		// $file = BASE_PATH . '/logs/' . strtolower( $adapterInstance->getAdapterType() ) . '/' . date( 'Ymd' ) . '.log';
		// Debug::dump($adapterInstance->hasOutputHandle(), eval(DUMP) . "\$adapterInstance->hasOutputHandle()", false);
		$adapterInstance->openOutputHandle();
		// Debug::dump($adapterInstance->hasOutputHandle(), eval(DUMP) . "\$adapterInstance->hasOutputHandle()", false);

		$message = 'Help, I got hit by a log.';
		// Debug::dump($message, eval(DUMP) . "\$message", false);

		// Log at the emergency level so it goes through.
		$adapterInstance->log( $message, Listener::LOG_LEVEL_EMERG );
		// Assert log file is the default
		$this->assertContains( $message, $adapterInstance->getLogContents() );
	}

	/**
	 * testLogSendMessageToFileAndTruncate
	 *
	 * @covers Listener_Adapter_Abstract::log
	 * @covers Listener_Adapter_Abstract::logTruncate
	 * @covers Listener_Adapter_Abstract::getLogContents
	 */
	public function testLogSendMessageToFileAndTruncate() {
		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );
		// Debug::dump($adapterInstance->getLogLevel(), eval(DUMP) . "\$adapterInstance->getLogLevel()", false);

		// $file = BASE_PATH . '/logs/' . strtolower( $adapterInstance->getAdapterType() ) . '/' . date( 'Ymd' ) . '.log';
		// Debug::dump($adapterInstance->hasOutputHandle(), eval(DUMP) . "\$adapterInstance->hasOutputHandle()", false);
		$adapterInstance->openOutputHandle();
		// Debug::dump($adapterInstance->hasOutputHandle(), eval(DUMP) . "\$adapterInstance->hasOutputHandle()", false);

		$message = 'Help, I got hit by a log.';
		// Debug::dump($message, eval(DUMP) . "\$message", false);

		// Log at the emergency level so it goes through.
		$adapterInstance->log( $message, Listener::LOG_LEVEL_EMERG );
		// Assert log file is the default
		$this->assertContains( $message, $adapterInstance->getLogContents() );

		// Delete the contents of the log.
		$adapterInstance->logTruncate();

		// Get the contents of the log. This should be empty
		$this->assertEmpty( $adapterInstance->getLogContents() );
	}
}
