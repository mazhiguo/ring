<?php
if(!defined('IN_OA')) {
	exit('Nonlicet Access Denied');
}
class dbstuff {
	var $link;
	function connect($dbhost, $dbuser, $dbpw, $dbname = '',$halt = TRUE) {
		if(!$this->link = @mysql_connect($dbhost, $dbuser, $dbpw)) {
			echo 'Can not connect to MySQL server';
		}
		if($this->version() > '4.1') {
			global $charset, $dbcharset;
			if(!$dbcharset && in_array(strtolower($charset), array('gbk', 'big5', 'utf-8'))) {
				$dbcharset = str_replace('-', '', $charset);
			}

			if($dbcharset) {
				@mysql_query("SET character_set_connection='utf-8', character_set_results='utf-8', character_set_client=binary", $this->link);
			}

			if($this->version() > '5.0.1') {
				@mysql_query("SET sql_mode=''", $this->link);
			}
		}

		if($dbname) {
			@mysql_select_db($dbname, $this->link);
		}

	}
	
	function select_db($dbname) {
		return mysql_select_db($dbname, $this->link);
	}
	
	function escape_string($str) {
		return mysql_escape_string($str); 
	}
	
	function query($sql, $type = '') {
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ?
			'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql, $this->link)) && $type != 'SILENT') {
			echo '语句'.$sql.'查询错误!';
		}
		return $query;
	}
	
	function fetch_assoc($query) {
		return mysql_fetch_assoc($query);
	}
	
	function affected_rows() {
		return mysql_affected_rows($this->link);
	}
	
	function result($query, $row) {
		$query = @mysql_result($query, $row);
		return $query;
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}	
	
	function close() {
		return mysql_close($this->link);
	}
	
	function version() {
		return mysql_get_server_info($this->link);
	}
			
	function error() {
		return (($this->link) ? mysql_error($this->link) : mysql_error());
	}

	function errno() {
		return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
	}
	
}
?>
