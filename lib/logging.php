<?php

const LOG_LEVEL_EMERG   = 0;
const LOG_LEVEL_ALERT   = 1;
const LOG_LEVEL_CRIT    = 2;
const LOG_LEVEL_ERR     = 3;
const LOG_LEVEL_WARN    = 4;
const LOG_LEVEL_NOTICE  = 5;
const LOG_LEVEL_INFO    = 6;
const LOG_LEVEL_DEBUG   = 7;

class Logger
{
    static $log_threshold;
    static $log_file;

    static function init($threshold, $file)
    {
        self::$log_file = fopen($file, "a");
        self::$log_threshold = $threshold;
    }

    static function log($msg, $level = LOG_LEVEL_INFO)
    {
        if ( self::$log_threshold >= $level )
        {
            $out = date( 'c' ) . "\t" . "XXX-tx_id" . "\t" . $msg . "\n";
            fwrite( self::$log_file, $out );
        }
    }
}
