<?php

function failmail( $data, $email_recipients, $tx_id )
{
    Logger::log( "The message could not be verified." );
    #Logger::log( "Returned with status: $status", LOG_LEVEL_DEBUG );
    
    // send email to configured recipients notifying them of the PayPal verification failure
    if ( $email_recipients && count( $email_recipients )) {
        $to = implode( ", ", $email_recipients );
        $subject = "Listener verification failure for message " . $tx_id;
        $msg = "Greetings!\n\n";
        $msg .= "You are receiving this message because a transaction that was psoted to the ";
        $msg .= "PayPal IPN listener failed PayPal verification.  The contents of the original ";
        $msg .= "payload are below:\n\n";
        $msg .= print_r( $data, true );
        $msg .= "\n\n";
        $msg .= "The IPN listener-assigned trxn id for this transaction is: " . $tx_id . "\n\n";
        $msg .= "Good luck figuring out wtf happened!\n\n";
        $msg .= "Love always,\n";
        $msg .= "Your faithful IPN listener";
        mail( $to, $subject, $msg );
        Logger::log( "Verification failure email sent to " . $to );
        return true;
    }
    
    return false;
}
