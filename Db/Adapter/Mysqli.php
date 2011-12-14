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
 * @see Db_Adapter_Abstract
 */
require_once 'Db/Adapter/Abstract.php';

/**
 * Db_Adapter_Abstract
 */
class Db_Adapter_Mysqli extends Db_Adapter_Abstract
{

	/**
	 * The port
	 *
	 * @var integer $port
	 */
	protected $port = 3306;

	/**
	 * Get the error code.
	 *
	 * @return mixed	Returns false if there is no error, otherwise returns the error code.
	 */
	public function getErrorCode() {
		
		$code = false;
		
		if ( isset( $this->connection->errno ) && !empty( $this->connection->errno ) ) {
			$code = (integer) $this->connection->errno;
		}
		//Debug::dump($code, eval(DUMP) . __FUNCTION__ . PN . _ . "\$code");
		//Debug::dump($this->connection, eval(DUMP) . __FUNCTION__ . PN . _ . "\$this->connection");
		
		return $code;
	}

	/**
	 * Get the error error.
	 *
	 * @return mixed	Returns false if there is no error, otherwise returns the error error.
	 */
	public function getErrorMessage() {
		
		$error = false;
		
		if ( isset( $this->connection->error ) && !empty( $this->connection->error ) ) {
			$error = $this->connection->error;
		}
		
		return $error;
	}

	/**
	 * Initialize the class
	 *
	 * init() is called at the end of the constructor to allow automatic settings for adapters.
	 */
	protected function init() {
		
	}

	////////////////////////////////////////////////////////////////////////////
	//
	// Connection handling
	//
	////////////////////////////////////////////////////////////////////////////

	/**
	 * connect
	 *
	 * @param boolean $reset Reset the connection
	 */
	protected function connect( $reset = false )
	{
		$reset = (boolean) $reset;
		// @codeCoverageIgnoreStart
		// Make sure the extension is loaded.
		if ( !extension_loaded('mysqli') ) {
			$message = 'The Mysqli extension is not loaded.';
			throw new Db_Exception( $message );
		}
		// @codeCoverageIgnoreEnd
		
		if ( $this->isConnected() && !$reset ) {
			return;
		}
		
		$this->connection = mysqli_init();
		
		$hasConnection = @mysqli_real_connect(
			$this->connection,
			$this->getHost(),
			$this->getUsername(),
			$this->getPassword(),
			$this->getDatabase(),
			$this->getPort(),
			$this->getSocket(),
			$this->getFlags()
		);
		
		// Throw errors
		if ( $hasConnection === false || mysqli_connect_errno() ) {
			
			$this->closeConnection();
			
			$message = mysqli_connect_error();
			throw new Db_Exception( $message );
		}
		
		// Set the character encoding.
		mysqli_set_charset( $this->connection, $this->getCharacterEncoding() );
		
	}

	/**
	 * Check to see if the adapter is connected.
	 *
	 * @return boolean Return true if connected to the database
	 */
	public function isConnected()
	{
		return $this->connection instanceof mysqli;
	}

	/**
	 * Check to see if the adapter is connected.
	 *
	 * @return boolean Return true if connected to the database
	 */
	public function closeConnection()
	{
		if ( $this->isConnected() ) {
			$this->connection->close();
		}

		$this->connection = null;
	}

	/**
	 * Escape a parameter for the database
	 *
	 * @param string $value The value to escape with the database adapter.
	 *
	 * @return string
	 */
	public function escape( $value )
	{
		return $this->connection->real_escape_string( $value );
	}

	/**
	 * Fetch
	 *
	 * fetch() a row from the query result
	 *
	 * @return string
	 */
	public function fetch( $options = array() )
	{
		if ( !( $this->result instanceof MySQLi_Result ) ) {
			$message = 'The result is not an instance of MySQLi_Result';
			throw new Db_Exception( $message );
		}

		return $this->getResult()->fetch_assoc();
	}

	/**
	 * Fetch all
	 *
	 * fetchAll() rows from the query result
	 *
	 * @param array		$options	OPTIONAL
	 *
	 * @return string
	 */
	public function fetchAll( $options = array() ) {
		
		extract( $options );
		
		$key = isset( $key ) ? $key : '';
		$this->resetResultSet();
		
		while ( $row = $this->fetch() ) {

			if ( !empty( $key ) && !isset( $row[ $key ] ) ) {
				$message = 'The key (' . $key . ') is not set in the row of the result set.';
				throw new Db_Exception( $message );
			}
			
			if ( empty( $key ) ) {
				$this->resultSet[] = $row;
			}
			else {
				$this->resultSet[ $row[ $key ] ] = $row;
				
			}
		}

		//Debug::puke( $this->resultSet, eval(DUMP) . "\$this->resultSet");
		
		//$result->free();
		
		return $this->getResultSet();
	}

	/**
	 * Fetch all and save result by a key in the row result.
	 *
	 * This can be used to index the result set by primary key
	 *
	 * fetchAllByKey() rows from the query result
	 *
	 * @param string	$key
	 * @param array		$options	OPTIONAL
	 *
	 * @return string
	 */
	public function fetchAllByKey( $key, $options = array() ) {
		
		$options['key'] = $key;
		
		return $this->fetchAll( $options );
	}
	
	/**
	 * setFlags
	 *
	 * It will be necessary to set this in the adapter.
	 *
	 * @param mixed $value The flags
	 */
	public function setFlags( $value )
	{
		if ( empty( $value ) ) {
			$this->flags = 0;
		}
		else {
			$this->flags = $value;
		}
	}

	/**
	 * Limit a select statement
	 *
	 * @param integer	$count
	 * @param integer	$offset OPTIONAL
	 * @return string
	 */
	public function limit( $count, $offset = 0 ) {
		
		$return = '';
		
		// Sanitize the parameters
		$count	= (integer) $count;
		$offset = (integer) $offset;
		
		if ($count <= 0) {
			$message = 'LIMIT count value must be greater than or equal to zero: ' . $count;
			throw new Db_Exception( $message );
		}
		
		if ($offset < 0) {
			$message = 'LIMIT offset value must be greater than zero: ' . $offset;
			throw new Db_Exception( $message );
		}
		
		$return .= ' LIMIT ' . $count;
		
		if ($offset > 0) {
		
			$return .= ' OFFSET ' . $offset;
		}
		
		return $return;
	}

	/**
	 * Query the database server with a statement
	 *
	 * @param string	$query
	 * @param array		$options	OPTIONAL
	 * @return mixed|Db_Adapter_Abstract
	 */
	public function lastInsertId( $id = '' ) {
		
		$id = empty( $id ) ? $this->connection->insert_id : $id;

		$this->setLastInsertId( $id );

		return $this->getLastInsertId();
	}
	
	////////////////////////////////////////////////////////////////////////////
	//
	// Query handling
	//
	////////////////////////////////////////////////////////////////////////////

	/**
	 * Returns the number of affected rows from the previous query.
	 *
	 * @return integer
	 */
	public function affectedRows() {
		
		return $this->getConnection()->affected_rows;
	}

	/**
	 * Query the database server with a statement
	 *
	 * @param string	$query
	 * @param array		$options	OPTIONAL
	 * @return mixed|Db_Adapter_Abstract
	 */
	public function query( $query, $options = array() ) {
		
		extract( $options );
		
		$storeResult = ( isset( $storeResult ) && $storeResult ) ? (boolean) $storeResult : false;  
		//Debug::dump( $storeResult, eval(DUMP) . "\$storeResult");
		$storeResult = $storeResult ? MYSQLI_STORE_RESULT : MYSQLI_USE_RESULT;	
		//Debug::dump( $storeResult, eval(DUMP) . "\$storeResult");
		//Debug::dump( $query, eval(DUMP) . "\$query");
		
		//$this->result = $this->getConnection()->query( $query, $storeResult );
		//$connection = $this->getConnection();
		//Debug::puke( $connection, eval(DUMP) . "\$connection");
		
		//$this->result = $connection->query( $query, $storeResult );
		$this->result = $this->getConnection()->query( $query, $storeResult );
		//Debug::dump( $this->result, eval(DUMP) . "\$this->result");
		
		if ( $this->getErrorCode() ) {
			$message = $this->getErrorMessage();
			//Debug::puke($message, eval(DUMP) . __FUNCTION__ . PN . _ . "\$message");
			throw new Db_Exception( $message );
		}
		
		//$this->result = $this->getConnection()->query( $query);
		//$message = mysqli_connect_error();
		//Debug::puke( $message, eval(DUMP) . "\$message");
		//Debug::puke( $this->result, eval(DUMP) . "\$this->result");
		
		return ( $storeResult ) ? $this : $this->getResult();
	}
}
