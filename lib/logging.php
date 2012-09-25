<?php

class Logger
{
    static $syslog_identity = 'listener';
    static $syslog_facility;

    static $log_init = false;
    static $log_threshold;
    static $id;
    static $severities = array(
        'emergency' => LOG_EMERG,
        'alert' => LOG_ALERT,
        'critical' => LOG_CRIT,
        'error' => LOG_ERR,
        'warning' => LOG_WARNING,
        'notice' => LOG_NOTICE,
        'info' => LOG_INFO,
        'debug' => LOG_DEBUG,
    );

    static function init( $name, $threshold, $id )
    {
        if ( !self::$log_init ) {
            if ( defined( 'LOG_LOCAL0' ) ) {
                self::$syslog_facility = LOG_LOCAL0;
            } else {
                self::$syslog_facility = LOG_USER;
            }
            self::$syslog_identity = $name;

            openlog( self::$syslog_identity, LOG_NDELAY, self::$syslog_facility );

            self::$log_init = true;
            register_shutdown_function( 'Logger::close' );
        }
        self::$log_threshold = $threshold;
        self::$id = $id;
    }

    static function close()
    {
        if ( self::$log_init ) {
            closelog();
        }
    }

    static function log( $level = 'info', $msg )
    {
        if ( self::$severities[strtolower(self::$log_threshold)] >= self::$severities[strtolower($level)] )
        {
            $out = date( 'c' ) . "|" .
                self::$id . "|" .
                strtoupper($level) . "|" .
                $msg . "|";

            syslog( self::$severities[ strtolower( $level ) ], $out );
        }
    }
}
