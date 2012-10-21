<?php
error_reporting(7);
define('DJOA',substr(__FILE__,0,-10));
define('IN_OA',true);
require_once DJOA.'config.php';
require_once DJOA.'include/global_func.php';
require_once DJOA.'include/db_class.php';
//extract 
foreach(array('_POST', '_GET') as $_request) {
	foreach($$_request as $_key => $_value) {
		$_key{0} != '_' && $$_key = daddslashes($_value);
	}
}

//close magic
set_magic_quotes_runtime(0);
if(get_magic_quotes_gpc()) {
	function stripslashes_deep($value){
		$value = is_array($value) ?
		            array_map('stripslashes_deep', $value) :
		            stripslashes($value);
		
		return $value;
	}
	$_POST = array_map('stripslashes_deep', $_POST);
	$_GET = array_map('stripslashes_deep', $_GET);
	$_COOKIE = array_map('stripslashes_deep', $_COOKIE);
}


$action = $_GET['action'];

$fp = fopen(DJOA.'include/user_info.php','r');
$s_user = fread($fp,200000);
fclose($fp);
eval('$user_info = array('.$s_user.');');


$fp_ug = fopen(DJOA.'include/ug_info.php','a+');
$s_ug = fread($fp_ug,200000);
fclose($fp_ug);
eval('$ug_info = array('.$s_ug.');');

//$db = new dbstuff();
//$db->connect($dbhost,$dbuser,$dbpw,$dbname);
?>
