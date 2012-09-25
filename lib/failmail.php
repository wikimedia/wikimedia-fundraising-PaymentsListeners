<?php

/**
 * failmail function. Mails us when we have fail. 
 * @param array $data The message that failed processing
 * @param string $failed_queue A queue where we stached the failed message
 * @param array $email_recipients Array of email recipients
 * @param string $listener_class The listener class that called failmail
 * @param string $tx_id The transaction ID, according to us.
 * @param array $pks The message and/or transaction ID(s), according to the gateway.
 * @param string $gateway The relevent gateway
 * @return boolean
 * @throws Exception
 */
function failmail( $data = array(), $failed_queue = '', $email_recipients = array(), $listener_class = '', $tx_id = '', $pks = '', $gateway = '' )
{
	//TODO: This function needs to be a lot dumber than this. 
	//#1 - There are a lot more reasons we can fail than just verification. 
	//#2 - If this just mailed a $message with a $subject to some $people, we 
	//wouldn't need to require so many params. 
	//...Looks like three, actually. The outside can sort it out on its own. 
    Logger::log( 'error', "The message could not be verified." );
    #Logger::log( 'debug', "Returned with status: $status" );
	
	$gateway = strtoupper( $gateway );
    
    if ( is_array($email_recipients) && count( $email_recipients ))
    {
        $to = implode( ", ", $email_recipients );
        $subject = $gateway . " Listener verification failure for message " . $tx_id;
        $payload = print_r( $data, true );
		$gateway_pk_info = print_r( $pks, true );
        $msg =<<<EOS
Greetings!

You are receiving this message because a transaction that was posted to the so-called real-time {$listener_class} failed verification.  Bog willing, this message has been placed on the '{$failed_queue}' queue.  The contents of the original payload are below:

{$payload}

The IPN listener-assigned trxn id for this transaction is: {$tx_id}, but the gateway will probably be more interested in this:
{$gateway_pk_info}

Good luck figuring out wtf happened!

Love always,
Your faithful IPN listener
EOS;
        mail( $to, $subject, $msg );
        Logger::log( 'info', "Verification failure email sent to " . $to );
        return true;
    }
    throw new Exception("Failed to send failmail!");
}
