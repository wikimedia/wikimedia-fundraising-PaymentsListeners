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
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . DIRECTORY_SEPARATOR . 'QueueHandlingTestCase.php';

/**
 * @group		Fundraising
 * @group		QueueHandling
 * @group		ClassMethod
 * @group		Db
 * @group		Mysqli
 *
 * Db_Adapter_Mysqli_QueryTestCase
 */
class Db_Adapter_Mysqli_QueryTestCase extends QueueHandlingTestCase
{

	/**
	 * testQueryWithStoreResult
	 *
	 * @covers Db_Adapter_Abstract::__construct
	 * @covers Db_Adapter_Mysqli::query
	 * @covers Db_Adapter_Mysqli::fetch
	 * @covers Db_Adapter_Mysqli::fetchAll
	 *
		CREATE TABLE IF NOT EXISTS `queue2civicrm_limbo` (
		  `queue2civicrm_limbo_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `contribution_tracking_id` int(10) unsigned DEFAULT NOT NULL,
		  `order_id` varchar(32) NOT NULL,
		  `timestamp` int(10) unsigned NOT NULL,
		  `data` text NOT NULL,
		  `gateway` varchar(32) NOT NULL,
		  `payment_method` varchar(32) NOT NULL,
		  `payment_submethod` varchar(32) NOT NULL,
		  PRIMARY KEY (`queue2civicrm_limbo_id`),
		  KEY `contribution_tracking_id` (`contribution_tracking_id`),
		  KEY `order_id` (`order_id`),
		  KEY `timestamp` (`timestamp`),
		  KEY `gateway` (`gateway`),
		  KEY `payment_method` (`payment_method`),
		  KEY `payment_submethod` (`payment_submethod`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

		CREATE TABLE IF NOT EXISTS `unit_test_table` (
		  `unit_test_table_id` int(11) NOT NULL AUTO_INCREMENT,
		  `unit_test_table` varchar(255) NOT NULL,
		  PRIMARY KEY (`unit_test_table_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
	 */
	public function testQueryWithStoreResult() {

		// The parameters to pass to the factory.
		$parameters = array(
			'database'	=> TESTS_DB_ADAPTER_DATABASE_FOR_TESTING,
			'host'		=> TESTS_DB_ADAPTER_HOST,
			'password'	=> TESTS_DB_ADAPTER_PASSWORD,
			'username'		=> TESTS_DB_ADAPTER_USERNAME,
			'port'		=> TESTS_DB_ADAPTER_PORT,
			'socket'	=> TESTS_DB_ADAPTER_SOCKET,
			'flags'		=> MYSQLI_CLIENT_INTERACTIVE,
		);

		// The adapter to pass to the factory.
		$adapter = 'Mysqli';

		$adapterInstance = Db::factory( $adapter, $parameters );

		$this->assertInstanceOf( 'Db_Adapter_Mysqli', $adapterInstance );
		$this->assertInstanceOf( 'mysqli', $adapterInstance->getConnection() );

		//$query = 'SHOW DATABASES LIKE ' . $adapterInstance->escape( TESTS_DB_ADAPTER_DATABASE_FOR_TESTING );
		$query = 'SELECT * FROM `queue2civicrm_limbo`';
		$adapterInstance->query( $query );
		$adapterInstance->fetchAll();
	}
}
