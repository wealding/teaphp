<?php

class tea_saemysql implements idb
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
        $this->conn = new SaeMysql();
        if ($dbconfig['dbname']) {
            $this->conn->setAppname($dbconfig['dbname']);
            $this->conn->setAuth($dbconfig['dbuser'], $dbconfig['dbpass']);
        }
        if ($dbconfig['charset']) {
            if ($dbconfig['charset'] == 'utf-8') {
                $dbconfig['charset'] = 'UTF8';
            }
            $this->conn->setCharset(strtoupper($dbconfig['charset']));
        }
    }

    public function query($sql)
    {
        $cmd = trim(strtoupper(substr($sql, 0, strpos($sql, ' '))));
        if ($cmd === 'SELECT') {
            $res = $this->conn->getData($sql);

            return new mysqlrecord($res);
        } else {
            $res = $this->conn->runSql($sql);
            if ($cmd === 'UPDATE' || $cmd === 'DELETE') {
                return $this->affected_rows();
            }
            if ($cmd === 'INSERT') {
                return $this->insert_id();
            }
        }
    }

    public function affected_rows()
    {
        return $this->conn->affectedRows();
    }

    public function insert_id()
    {
        return $this->conn->lastId();
    }

    public function ping()
    {
        if (!$this->conn) {
            return false;
        } else {
            return true;
        }
    }

    public function close()
    {
        $this->conn->closeDb();
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
