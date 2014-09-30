<?php

class tea_mysqli extends mysqli implements idb
{
    public $conn = null;
    public $config;

    function __construct($dbconfig)
    {
        $this->config = $dbconfig;
    }

    function connect()
    {
        $dbconfig = &$this->config;
        parent::connect($dbconfig['host'],$dbconfig['dbuser'],$dbconfig['dbpass'],$dbconfig['dbname']);
        if(mysqli_connect_errno())  debug::error('Mysqli Error',"Connect failed: %s\n".mysqli_connect_error());
        if($dbconfig['charset']) $this->set_charset($dbconfig['charset']);
    }
    
    function query($sql)
    {
        parent::real_escape_string($sql);
        $res = parent::query($sql);
        if(!$res) debug::error('SQL Line Error',$this->error."<hr/>$sql");
        return new mysqlirecord($res);
    }
    
    function insert_id()
    {
        return $this->insert_id;
    }
    
}


class mysqlirecord implements idbrecord 
{
    public $result;
    
    function __construct($result)
    {
        $this->result = $result;
    }

    function fetch()
    {
        if(empty($this->result))
        {
            return false;
        }
        return $this->result->fetch_assoc();
    }

    function fetchall()
    {
        if(empty($this->result))
        {
            return false;
        }
        $data = array();
        while($record = $this->result->fetch_assoc())
        {
            $data[] = $record;
        }
        return $data;
    }
    
    function free()
    {
        $this->result->free_result();
    }
    
}