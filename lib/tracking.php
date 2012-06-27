<?php

class ContributionTracking
{
    function __construct($config)
    {
        $this->contrib_db = mysql_connect(
            $config['contrib_db_host'],
            $config['contrib_db_username'],
            $config['contrib_db_password']
        );
        mysql_select_db($config['contrib_db_name'], $this->contrib_db);
    }

    function get_tracking_data( $id )
    {
        $id = mysql_escape_string( $id );
        $query = "SELECT * FROM contribution_tracking WHERE id=$id";
        $result = mysql_query( $query );
        $row = mysql_fetch_assoc( $result );
        return $row;
    }
}
