<?php
@session_start();
error_reporting(E_ERROR);
error_reporting(2047);
set_time_limit(300);

$start_time = array_sum(explode(' ', microtime()));
/* define path */
define('ROOTPATH', str_replace('\\', '/', substr(__FILE__, 0, -13)));
!is_dir(ROOTPATH) && exit("ROOTPATH '".ROOTPATH."' is incorrect!");
define('APPPATH', ROOTPATH.'app/');
define('MODPATH', ROOTPATH.'app/models/');
define('CTRLPATH', ROOTPATH.'app/controllers/');
define('LIBPATH', ROOTPATH.'libs/');
define('WWWPATH', ROOTPATH.'www/');

define('INSTALLPATH', str_replace('\\', '/', substr(__FILE__,0,-28)));

define('ISWIN', preg_match('/WIN/', PHP_OS));

if (ISWIN)
{
	define('SERVICEPATH', INSTALLPATH.'/services');
}
else
{
	define('SERVICEPATH', INSTALLPATH.'/ms');
}

define('UPLOADSPATH', INSTALLPATH.'/uploads');
define('LICENSEUPLOADPATH', '../uploads/license/');

$func_global_file = LIBPATH.'funcs/func_global.php';
!file_exists($func_global_file) && exit("The file '$func_global_file' does not exist!");
include($func_global_file);
$config_file = APPPATH.'/config.php';
!file_exists($config_file) && show_msg("TEXT",array("hint"=>"File not found", "msg"=>"The file '$config_file' does not exist!")) && exit;
include($config_file);
$common_file = LIBPATH.'common.php';
!file_exists($common_file) && show_msg("TEXT",array("hint"=>"File not found", "msg"=>"The file '$common_file' does not exist!")) && exit;
include($common_file);
?>