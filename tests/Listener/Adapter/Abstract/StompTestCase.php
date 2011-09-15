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
 * @group		Stomp
 *
 * @category	UnitTesting
 * @package		Fundraising_QueueHandling
 */
class Listener_Adapter_Abstract_StompTestCase extends QueueHandlingTestCase
{

	/**
	 * testGetStompPathIsEmptyByDefault
	 *
	 * @covers Listener_Adapter_Abstract::getStompPath
	 */
	public function testGetStompPathIsNotEmptyByDefault() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		// $this->assertEquals( '/www/sites/localhost/fundraising-civicrm.localhost.wikimedia.org/sites/all/modules/queue2civicrm/Stomp.php', $adapterInstance->getStompPath() );
		$this->assertEquals( 'Stomp.php', $adapterInstance->getStompPath() );
	}

	/**
	 * testSetLogFileWithNonExistentScript
	 *
	 * @covers Listener_Adapter_Abstract::setStompPath
	 */
	public function testSetStompPathWithNonExistentScript() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$path = BASE_PATH . '/tests/resources/i-do-not-exist/Stomp.php';

		$message = 'The stomp script does not exist: ' . $path;
		$this->setExpectedException( 'Listener_Exception', $message );

		$adapterInstance->setStompPath( $path );
	}

	/**
	 * testSetLogFileWithATestStompFile
	 *
	 * @covers Listener_Adapter_Abstract::setStompPath
	 * @covers Listener_Adapter_Abstract::getStompPath
	 */
	public function testSetStompPathWithATestStompFile() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		// This is not a path to a real Stomp class, just an empty file.
		$path = BASE_PATH . '/tests/resources/activeMQ/Stomp.php';

		$adapterInstance->setStompPath( $path );

		$this->assertSame( $path, $adapterInstance->getStompPath() );
	}

	/**
	 * testGetActiveMqStompUriDefault
	 *
	 * @covers Listener_Adapter_Abstract::getActiveMqStompUri
	 */
	public function testGetActiveMqStompUriDefault() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		// This is the default port on the localhost for ActiveMQ.
		$activeMqStompUri = 'tcp://localhost:61613';

		$this->assertSame( $activeMqStompUri, $adapterInstance->getActiveMqStompUri() );
	}

	/**
	 * testSetActiveMqStompUriWithLocalhostAndPort61610
	 *
	 * @covers Listener_Adapter_Abstract::getActiveMqStompUri
	 * @covers Listener_Adapter_Abstract::setActiveMqStompUri
	 */
	public function testSetActiveMqStompUriWithLocalhostAndPort61610() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		// This is NOT the default port on the localhost for a tunneled instance.
		$activeMqStompUri = 'tcp://localhost:61610';
		$adapterInstance->setActiveMqStompUri( $activeMqStompUri );

		$this->assertSame( $activeMqStompUri, $adapterInstance->getActiveMqStompUri() );
	}

	/**
	 * testSetStompWithFakeStompClass
	 *
	 * @covers Listener_Adapter_Abstract::getStomp
	 * @covers Listener_Adapter_Abstract::setStomp
	 * @covers Listener_Adapter_Abstract::setStompPath
	 */
	public function testSetStompWithFakeStompClass() {

		// Set up stompPath to a fake class

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$stompPath = BASE_PATH . '/tests/resources/activeMQ/Stomp.php';

		$message = 'The Stomp class does not exist in: ' . $stompPath;
		// Debug::dump($message, eval(DUMP) . "\$message", false);

		$adapterInstance->setStompPath( $stompPath );

		$this->setExpectedException( 'Listener_Exception', $message );
		$adapterInstance->setStomp();
		// Debug::dump($adapterInstance->getLogLevel(), eval(DUMP) . "\$adapterInstance->getLogLevel()", false);
	}

	/**
	 * testSetStomp
	 *
	 * @covers Listener_Adapter_Abstract::getStomp
	 * @covers Listener_Adapter_Abstract::setStomp
	 */
	public function testSetStomp() {
		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );
		// Debug::dump($adapterInstance->getLogLevel(), eval(DUMP) . "\$adapterInstance->getLogLevel()", false);

		// $file = BASE_PATH . '/logs/' . strtolower( $adapterInstance->getAdapterType() ) . '/' . date( 'Ymd' ) . '.log';
		// Debug::dump($adapterInstance->hasOutputHandle(), eval(DUMP) . "\$adapterInstance->hasOutputHandle()", false);
		// Debug::dump($adapterInstance->hasOutputHandle(), eval(DUMP) . "\$adapterInstance->hasOutputHandle()", false);

		$this->assertInstanceOf( 'Stomp', $adapterInstance->getStomp() );
	}

	/**
	 * testSetStompConnection
	 *
	 * @covers Listener_Adapter_Abstract::getStomp
	 * @covers Listener_Adapter_Abstract::setStomp
	 * @covers Listener_Adapter_Abstract::connectStomp
	 */
	public function testSetStompConnection() {
		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		// $message = 'The Stomp class does not exist in: ' . $stompPath;
		// Debug::dump($message, eval(DUMP) . "\$message", false);

		// $adapterInstance->setStompPath( $stompPath );

		// $this->setExpectedException( 'Listener_Exception', $message );
		$adapterInstance->connectStomp();

		$this->assertTrue( $adapterInstance->getStomp()->isConnected() );
	}

	/**
	 * testStompQueueMessageToPending
	 *
	 * @covers Listener_Adapter_Abstract::stompQueueMessage
	 */
	public function testStompQueueMessageToPending() {
		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		// $message = 'The Stomp class does not exist in: ' . $stompPath;
		// Debug::dump($message, eval(DUMP) . "\$message", false);

		// $adapterInstance->setStompPath( $stompPath );

		// $this->setExpectedException( 'Listener_Exception', $message );
		$adapterInstance->connectStomp();

		$this->assertTrue( $adapterInstance->getStomp()->isConnected() );

		$queue = $adapterInstance->getQueuePending();

		$fakeData = array(
			'things'	=> 'stuff',
			'this'		=> 'that',
			'count'		=> 3,
			'errors'	=> false,
		);

		$message = json_encode( $fakeData );
		// $message = 'Testing a string message.';
		$properties = array( 'JMSCorrelationID' => $adapterInstance->getTxId() );
		$this->assertTrue( $adapterInstance->stompQueueMessage( $queue, $message, $properties ) );

		return $adapterInstance->getTxId();
	}

	/**
	 * testStompEmptyQueueIfEmptyAddSomeAndRemoveThem
	 *
	 * This needs to be called after @see $this->testStompQueueMessageToPending().
	 * There should be messages in the queue to remove.
	 *
	 * This will be done in a transaction. If this removes more than 10
	 * messages, it will roll back the transaction. The queue should be purged
	 * manually.
	 *
	 * WARNING: This method is for a test environment only.
	 *
	 * @covers Listener_Adapter_Abstract::stompFetchMessage
	 */
	public function testStompEmptyQueueIfEmptyAddSomeAndRemoveThem() {
		// The parameters to pass to the factory.
		$parameters = array(
			'logLevel' => Listener::LOG_LEVEL_DEBUG,
		);

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		// $message = 'The Stomp class does not exist in: ' . $stompPath;
		// Debug::dump($message, eval(DUMP) . "\$message", false);

		// $adapterInstance->setStompPath( $stompPath );

		// $this->setExpectedException( 'Listener_Exception', $message );

		// Connect to Stomp
		$adapterInstance->connectStomp();

		// Start a transaction
		$adapterInstance->getStomp()->begin( __FUNCTION__ );

		// The queue we wish to query
		$queue = $adapterInstance->getQueuePending();

		$properties = array();

		$count = 0;
		$maxFetch = 10;

		$commit = true;

		$messagesRemoved = false;

		// Fetch messages until there are none left or we hit $maxFetch
		do {

			$fetchedMessage = $adapterInstance->stompFetchMessage( $queue, $properties );
			// Debug::dump($fetchedMessage, eval(DUMP) . "\$fetchedMessage", false);
			$count++;

			$commit = ( $count > $maxFetch ) ? false : true;

			// Remove message from the queue.
			if ( $fetchedMessage instanceof Stomp_Frame ) {

				if ( $adapterInstance->stompDequeueMessage( $fetchedMessage ) ) {

					$messagesRemoved = true;
				}

			}

		} while ( $commit && ( $fetchedMessage instanceof Stomp_Frame ) );

		// If we did not exceed $maxFetch, commit the transaction.
		if ( $commit ) {

			$adapterInstance->getStomp()->commit( __FUNCTION__ );
		}
		else {

			$messagesRemoved = false;
			$adapterInstance->getStomp()->abort( __FUNCTION__ );
		}

		$message = 'Unabled to remove messages: ';

		if ( $count === 0 ) {
			$message .= 'No messages found with: stompFetchMessage( "' . $queue . '", ' . print_r( $properties, true ) . ' )';
		}

		if ( !$messagesRemoved ) {
			$message .= 'Unable to dequeue message: stompDequeueMessage()';
		}

		if ( !$commit ) {
			$message .= 'Transaction aborted.';
		}

		if ( $count > $maxFetch ) {
			$message .= ' Attempted to remove [ ' . $count . ' ] messages. This test is limited to removing [ ' . $maxFetch . ' ] messages. Remove some from the queue and verify this is done on a test environment.';
		}

		$this->assertTrue( $messagesRemoved, $message );
	}

	/**
	 * testStompFetchMessageFromPending
	 *
	 * @param string	$txId
	 * @depends testStompQueueMessageToPending
	 * @covers Listener_Adapter_Abstract::stompFetchMessage
	 */
	public function testStompFetchMessageFromPending( $txId ) {
		$this->markTestIncomplete( TESTS_NOT_IMPLEMENTED_MESSAGE );
		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		// $message = 'The Stomp class does not exist in: ' . $stompPath;
		// Debug::dump($message, eval(DUMP) . "\$message", false);

		// $adapterInstance->setStompPath( $stompPath );

		// $this->setExpectedException( 'Listener_Exception', $message );
		$adapterInstance->connectStomp();

		$this->assertTrue( $adapterInstance->getStomp()->isConnected() );

		$queue = $adapterInstance->getQueuePending();

		$properties = array();
		// $properties['selector'] = "JMSCorrelationID = '" . $txId . "1'";

		// $properties = array( 'JMSCorrelationID' => $adapterInstance->getTxId() );
		$properties = array( 'JMSCorrelationID' => $txId );
		Debug::dump( $properties, eval( DUMP ) . "\$properties", false );
		Debug::dump( $txId, eval( DUMP ) . "\$txId", false );

		$stompMessage = $adapterInstance->stompFetchMessage( $queue, $properties );
		Debug::dump( $stompMessage, eval( DUMP ) . "\$stompMessage", false );

		$this->assertInstanceOf( 'Stomp_Frame', $stompMessage );
	}

	/**
	 * testStompFetchMessage
	 *
	 * @covers Listener_Adapter_Abstract::stompFetchMessage
	 */
	public function testStompFetchMessage() {
		$this->markTestIncomplete( TESTS_NOT_IMPLEMENTED_MESSAGE );
	}

}
