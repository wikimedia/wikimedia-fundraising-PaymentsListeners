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
class Listener_Adapter_Abstract_StompTestCase extends QueueHandlingTestCase
{

	/**
	 * testGetStompPathIsEmptyByDefault
	 *
	 * @covers Listener_Adapter_Abstract::getStompPath
	 */
	public function testGetStompPathIsEmptyByDefault() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		$this->assertEmpty( $adapterInstance->getStompPath() );
	}

	/**
	 * testSetLogFileWithNonExistentScript
	 *
	 * @covers Listener_Adapter_Abstract::setStompPath
	 */
	public function testSetLogFileWithNonExistentScript() {

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
	public function testSetLogFileWithATestStompFile() {

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

		// This is the default port on the localhost for a tunneled instance.
		$activeMqStompUri = 'tcp://localhost:61613';

		$this->assertSame( $activeMqStompUri, $adapterInstance->getActiveMqStompUri() );
	}

	/**
	 * testSetActiveMqStompUriWithLocalhostAndPort61614
	 *
	 * @covers Listener_Adapter_Abstract::getActiveMqStompUri
	 * @covers Listener_Adapter_Abstract::setActiveMqStompUri
	 */
	public function testSetActiveMqStompUriWithLocalhostAndPort61614() {

		// The parameters to pass to the factory.
		$parameters = array();

		// The adapter to pass to the factory.
		$adapter = 'GlobalCollect';

		$adapterInstance = Listener::factory( $adapter, $parameters );

		// This is NOT the default port on the localhost for a tunneled instance.
		$activeMqStompUri = 'tcp://localhost:61614';
		$adapterInstance->setActiveMqStompUri( $activeMqStompUri );

		$this->assertSame( $activeMqStompUri, $adapterInstance->getActiveMqStompUri() );
	}
}
