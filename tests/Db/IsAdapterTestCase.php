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
require_once dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'QueueHandlingTestCase.php';

/**
 * @group		Fundraising
 * @group		QueueHandling
 * @group		ClassMethod
 * @group		Db
 *
 * Db_IsAdapterTestCase
 */
class Db_IsAdapterTestCase extends QueueHandlingTestCase
{

	/**
	 * testIsAdapterHasMysqli
	 *
	 * @covers Db::isAdapter
	 */
	public function testIsAdapterHasMysqli() {

		$adapter = 'Mysqli';

		$this->assertTrue( Db::isAdapter( $adapter ) );
	}

	/**
	 * testIsAdapterDoesNotHaveSomePhonyAdapter
	 *
	 * @covers Db::isAdapter
	 */
	public function testIsAdapterDoesNotHaveSomePhonyAdapter() {

		$adapter = 'SomePhonyAdapter';

		$this->assertFalse( Db::isAdapter( $adapter ) );
	}
}
