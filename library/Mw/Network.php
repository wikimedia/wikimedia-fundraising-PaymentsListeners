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
 *
 */
class Mw_Network
{
    
    /**
     * output
     *
     * @var array|null
     */
    public $output;
       
    /**
     * result
     *
     * @var string|boolean
     */
    public $result;
 
    /**
     * returnValue
     *
     * @var integer
     */
    public $returnValue;

	/**
	 * Use a terminal connection
	 *
	 * This executes one terminal command at a time.
	 *
	 * @param	string	$command	The command to pass to the terminal
	 * @param	string	$execute	The method to execute on the PHP side to get to the terminal. Defaults to: 'exec'
	 *
	 * $execute:
	 * - 'passthru': @link http://php.net/manual/en/function.passthru.php 
	 * - 'exec': @link http://php.net/manual/en/function.exec.php 
	 *
	 * @return string|boolean Returns @see Mw_Network::$result
	 */
	final public function terminal( $command, $execute = 'exec' )
	{
		
		$execute = empty( $execute ) ? 'exec' : $execute ;
		
		$this->output = null;
		$this->returnValue = null;
		$this->result = null;
		
		if ( $execute == 'exec' ) {
			// exec() returns the last line
			$this->result = exec( $command, $this->output, $this->returnValue );
		}
		elseif ( $execute == 'passthru' ) {
			$command .= ' 2>&1';
			ob_start();
			passthru( $command, $this->returnValue );
			$this->output = ob_get_contents();
			$this->result = explode( "\n", $this->output );
			ob_end_clean();
		}
		else {
			$message = 'Invalid $execute: ' . $execute;
			throw new Mw_Network_Exception( $message );
		}

		//$return = `$command`;
		//$return = passthru($command, $this->returnValue);
		//Debug::dump($command, eval(DUMP) . __FUNCTION__ . PN . _ . "\$command");
		//Debug::dump($this->result, eval(DUMP) . __FUNCTION__ . PN . _ . "\$this->result");
		//Debug::dump($this->output, eval(DUMP) . __FUNCTION__ . PN . _ . "\$this->output");
		//Debug::dump($this->returnValue, eval(DUMP) . __FUNCTION__ . PN . _ . "\$this->returnValue");
		
		return $this->result;
	}
}
