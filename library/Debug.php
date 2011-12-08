<?php
/**
 * @category	Debug
 * @package		Debug
 * @author		Jeremy Postlethwaite <jpostlethwaite@wikimedia.org>
 */

/**
 * Constants
 */
require_once 'constants.php';

/**
 * Base bootstrap class for modules
 *
 * @package	   Debug
 */
class Debug
{

	/**
	 * Formatted variable dumper with option to terminate script.
	 *
	 * <code>
	 * <?php
	 * // To Display the server variables and kill the script
	 * Debug::dump($_SERVER, eval(DUMP) . 'Server Variables', true);
	 * // To Display the server variables and allow the script to resume
	 * Debug::dump($_SERVER, eval(DUMP) . 'Server Variables', false);
	 * ?>
	 * </code>
	 *
	 * @param mixed		$var	The variable to dump.
	 * @param string	$label	The label to pass to the output.
	 * @param boolean	$die	If true, script will terminate.
	 * @return void
	 */
	public static function dump( $var, $label, $die = false )
	{
		global $wgCommandLineMode;

		$wgCommandLineMode = (boolean) $wgCommandLineMode;
$wgCommandLineMode = false;
		$pre = ( $wgCommandLineMode ) ? '<-----------' . $label . PHP_EOL : '<div style="clear: both">' . HR . PHP_EOL . $label . PHP_EOL . HR . PREo . PHP_EOL;
		$post = ( $wgCommandLineMode ) ? PHP_EOL . '----------->' . PHP_EOL : PHP_EOL . PREc . _ . HR . '</div>' . PHP_EOL;
		echo $pre;
		if ( is_string( $var ) ) {
			print_r( $var );
		}
		else {
			var_dump( $var );
		}
		echo $post;

		if ( $die === true ) {
			die( 'Terminating at: ' . eval( DUMP ) . 'From: ' . $label . PHP_EOL );
		}
	}

	/**
	 * Dumps the user defined constants and exits.
	 *
	 * @param string	$label	The label to pass to the output.
	 */
	private static function dump_user_defined_constants( $label )
	{
		$get_defined_constants = get_defined_constants( true );
		self::dump( $get_defined_constants['user'], $label, true );
	}

	/**
	 * Puke a stack trace and die.
	 *
	 * <code>
	 * <?php
	 * // To Display the server variables and kill the script
	 * Debug::puke($_SERVER, eval(DUMP) . 'Server Variables');
	 * ?>
	 * </code>
	 *
	 * @param mixed		$variable	The variable to dump.
	 * @param string	$label		The label to pass to the output.
	 * @param array		$options
	 * @return void
	 */
	public static function puke( $variable, $label, $options = array() )
	{
		try {
			self::dump( $variable, $label, false );
			throw new Exception( $label );
		} catch ( Exception $e ) {

			self::dump( $e->getTraceAsString(), eval( DUMP ), true );
		}
	}
}
