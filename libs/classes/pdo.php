<?php
class Database extends PDO
{
    function __construct() 
    {
		$dsn = DB_TYPE.":host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME;
		$username = DB_USER; 
		$password = DB_PASSWORD; 
		$driver_options = array(PDO::ERRMODE_WARNING);
		
		try 
		{
			parent::__construct($dsn, $username, $password, $driver_options);
		} 
		catch (Exception $e) 
		{
			if ($e) 
			{
				show_msg('TEXT', array('hint'=>'DB ERROR','msg'=>$e->getMessage()));
				exit;
			}
		}
		$this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		@$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('DBStatement', array($this)));
		$this->exec("SET NAMES ".DB_CHARSET);
		$this->exec("set sql_mode=''");
    }

	function __destruct()
	{
		$this->dbh = null;
	}   
	 
	public function limit($limit, $offset=0)
	{
		$sl = ' limit ';
		if ($offset>0) $sl .= $offset.', ';
		if ($limit>0) $sl .= ' ' . $limit;
		else $sl = '';
		return $sl;
	}
	
	public function queryAll($sql)
	{
		$query = $this->query($sql);
		return $query->fetchAll(Database::FETCH_ASSOC);
	}
}

class DBStatement extends PDOStatement 
{
    public $db;
    function __construct($db)
    {
		$this->db = $db;
	}
}
?>