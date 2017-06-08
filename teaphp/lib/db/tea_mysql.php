<?php

class tea_mysql implements idb
{
    public $conn = null;
    public $config;

    public function __construct($dbconfig)
    {
        $this->config = $dbconfig;
    }

    public function connect()
    {
        $dbconfig = &$this->config;
        if (isset($dbconfig['persistent']) and $dbconfig['persistent']) {
            $this->conn = mysql_pconnect($dbconfig['host'], $dbconfig['dbuser'], $dbconfig['dbpass']) or debug::error('Mysql Error', mysql_error());
        } else {
            $this->conn = mysql_connect($dbconfig['host'], $dbconfig['dbuser'], $dbconfig['dbpass']) or debug::error('Mysql Error', mysql_error());
        }

        mysql_select_db($dbconfig['dbname'], $this->conn) or debug::error('Mysql Error', mysql_error($this->conn));
        if ($dbconfig['charset']) {
            mysql_query('set names '.$dbconfig['charset'], $this->conn) or debug::error('Mysql Error', mysql_error($this->conn));
        }
    }

    public function query($sql)
    {
        //echo $sql.'<hr>';
        $res = mysql_query($sql, $this->conn) or debug::error('SQL Line Error', mysql_error($this->conn)."<hr/>$sql");
        if ($res) {
            $cmd = trim(strtoupper(substr($sql, 0, strpos($sql, ' '))));
            if ($cmd === 'SELECT') {
                return new mysqlrecord($res);
            } elseif ($cmd === 'UPDATE' || $cmd === 'DELETE') {
                return $this->affected_rows();
            } elseif ($cmd === 'INSERT') {
                return $this->insert_id();
            }
        }
    }

    public function affected_rows()
    {
        return mysql_affected_rows($this->conn);
    }

    public function insert_id()
    {
        return mysql_insert_id($this->conn);
    }

    public function ping()
    {
        if (!mysql_ping($this->conn)) {
            return false;
        } else {
            return true;
        }
    }

    public function close()
    {
        mysql_close($this->conn);
    }
}

class mysqlrecord implements idbrecord
{
    public $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    public function fetch()
    {
        return mysql_fetch_assoc($this->result);
    }

    public function fetchall()
    {
        $data = [];
        while ($record = mysql_fetch_assoc($this->result)) {
            $data[] = $record;
        }

        return $data;
    }

    public function free()
    {
        mysql_free_result($this->result);
    }
}
