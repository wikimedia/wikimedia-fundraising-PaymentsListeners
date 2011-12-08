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
 */

/**
 * Class for SQL SELECT fragments.
 *
 * This class simply holds a string, so that fragments of SQL statements can be
 * distinguished from identifiers and values that should be implicitly quoted
 * when interpolated into SQL statements.
 *
 * Db_Expression
 */
class Db_Expression
{
	/**
	 * Storage for the SQL expression.
	 *
	 * @var string
	 */
	protected $_expression;

	/**
	 * Instantiate an expression, which is just a string stored as
	 * an instance member variable.
	 *
	 * @param string $expression The string containing a SQL expression.
	 */
	public function __construct($expression)
	{
		$this->_expression = (string) $expression;
	}

	/**
	 * @return string The string of the SQL expression stored in this object.
	 */
	public function __toString()
	{
		return $this->_expression;
	}

}
