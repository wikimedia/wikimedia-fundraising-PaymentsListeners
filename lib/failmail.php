<?php

function failmail( $data )
{
    Logger::log( "The message could not be verified." );
    Logger::log( "Returned with status: $status", LOG_LEVEL_DEBUG );
    
    // send email to configured recipients notifying them of the PayPal verification failure
    if ( $this->email_recipients && count( $this->email_recipients )) {
        $to = implode( ", ", $this->email_recipients );
        $subject = "IPN Listener verification failure for message " . $this->tx_id;
        $msg = "Greetings!\n\n";
        $msg .= "You are receiving this message because a transaction that was psoted to the ";
        $msg .= "PayPal IPN listener failed PayPal verification.  The contents of the original ";
        $msg .= "payload are below:\n\n";
        $msg .= print_r( $data, true );
        $msg .= "\n\n";
        $msg .= "The IPN listener-assigned trxn id for this transaction is: " . $this->tx_id . "\n\n";
        $msg .= "Good luck figuring out wtf happened!\n\n";
        $msg .= "Love always,\n";
        $msg .= "Your faithful IPN listener";
        mail( $to, $subject, $msg );
        Logger::log( "Verification failure email sent to " . $to );
    }
    
    return false;
}
