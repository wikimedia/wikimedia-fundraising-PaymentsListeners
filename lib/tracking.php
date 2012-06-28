<?php

class ContributionTracking
{
    function __construct($config)
    {
        $this->config = $config;
    }

    protected function db_connect()
    {
        if (!array_key_exists($this->config, 'contrib_db_name'))
            return FALSE;

        $this->contrib_db = mysql_connect(
            $this->config['contrib_db_host'],
            $this->config['contrib_db_username'],
            $this->config['contrib_db_password']
        );
        return mysql_select_db($this->config['contrib_db_name'], $this->contrib_db);
    }

    function get_tracking_data( $id )
    {
        if (!$this->db_connect())
            return;

        $id = mysql_escape_string( $id );
        $query = "SELECT * FROM contribution_tracking WHERE id=$id";
        $result = mysql_query( $query );
        $row = mysql_fetch_assoc( $result );
        return $row;
    }
}
