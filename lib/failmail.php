<?php

function failmail($params)
{
    Logger::log( "The message could not be verified." );
    #Logger::log( "Returned with status: $status", LOG_LEVEL_DEBUG );
    
    if ( is_array($params['email_recipients']) && count( $params['email_recipients'] ))
    {
        $to = implode( ", ", $params['email_recipients'] );
        $subject = "Listener verification failure for message " . $params['tx_id'];
        $payload = print_r( $params['data'], true );
        $msg =<<<EOS
Greetings!

You are receiving this message because a transaction that was posted to so-called real-time {$params['listener_class']} failed verification.  Bog willing, this message has been placed on the '{$params['failed_queue']}' queue.  The contents of the original payload are below:

{$payload}

The IPN listener-assigned trxn id for this transaction is: {$params['tx_id']}

Good luck figuring out wtf happened!

Love always,
Your faithful IPN listener
EOS;
        mail( $to, $subject, $msg );
        Logger::log( "Verification failure email sent to " . $to );
        return true;
    }
    throw new Exception("Failed to send failmail!");
}
