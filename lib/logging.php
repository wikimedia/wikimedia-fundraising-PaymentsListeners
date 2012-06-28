<?php

class Logger
{
    static $log_threshold;
    static $log_file;
    static $id;
    static $severities = array(
        'emerg'   => 0,
        'alert'   => 1,
        'crit'    => 2,
        'err'     => 3,
        'warn'    => 4,
        'notice'  => 5,
        'info'    => 6,
        'debug'   => 7,
    );

    static function init($threshold, $file, $id)
    {
        self::$log_file = fopen($file, "a");
        self::$log_threshold = $threshold;
        self::$id = $id;
    }

    static function log($msg, $level = 'info')
    {
        if ( self::$severities[strtolower(self::$log_threshold)] >= self::$severities[strtolower($level)] )
        {
            $out = date( 'c' ) . "\t" . self::$id . "\t" . strtoupper($level) . "\t" . $msg . "\n";
            fwrite( self::$log_file, $out );
        }
    }
}
