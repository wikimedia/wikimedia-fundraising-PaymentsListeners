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
 * This is a simple form for testing the GlobalCollect PSC Listener
 */
 
$debug = isset( $_GET['debug'] ) ? (boolean) $_GET['debug'] : false;

$keys = array(
	'MERCHANTID'		=> '9990',
	'ORDERID'			=> '23',
	'EFFORTID'			=> '1',
	'ATTEMPTID'			=> '1',
	'AMOUNT'			=> '100',
	'CURRENCYCODE'		=> 'EUR',
	'REFERENCE'			=> '20070406GC19',
	'PAYMENTREFERENCE'	=> '',
	'PAYMENTPRODUCTID'	=> '1',
	'PAYMENTMETHODID'	=> '1',
	'STATUSID'			=> '525',
	'STATUSDATE'		=> '20070406170059',
	'RECEIVEDDATE'		=> '20070406170057',
);
if ( empty( $_POST ) ) {

	foreach ( $keys as $key => $value ) {
		
		// Clear each value
		$_POST[ $key ] = $debug ? $value : '';
	}
	
}
$title = 'Test form for GlobalCollect PSC Listener';
?>
<!DOCTYPE html>
<html id="home" lang="en">
	<head>
		<meta charset="utf-8" />
		<title><?php echo $title ?></title>
	</head>
	<body>
	
		<h1><?php echo $title ?></h1>

		<div>
			
			<form action="/queue_handling/Listener.GlobalCollect.php" method="post">
				
				<fieldset>
					
					<legend>Keys</legend>
					
					<table>
						
						<?php foreach ( $keys as $key => $value ): ?>
						
						<tr>
							<td>
								<label for="<?php echo $key ?>"><?php echo $key ?>:</label>
							</td>
							<td>
								<input type="text" name="<?php echo $key ?>" id="<?php echo $key ?>" value="<?php echo $_POST[ $key ] ?>" />
							</td>
						</tr>
						
						<?php endforeach; ?>
						
						<tr>
							<td>
								<input type="submit" value="Submit" />
							</td>
							<td>
								<input type="reset" value="Reset" />
							</td>
						</tr>
						
						<tr>
							<td>
								<a href="?debug=1">Debug</a>
							</td>
							<td>
								<a href="?debug=0">Clear</a>
							</td>
						</tr>
						
					</table>
				
				</fieldset>
				
			</form>
			
		</div>
	
	</body>

</html>
