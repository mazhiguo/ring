<?php
/**
 * common.php
 * @author zhangqitao
 * $whencreated 2008-04-28
 */
set_magic_quotes_runtime(0);
/*if (ini_get('register_global'))
{
	show_msg('TEXT',array('hint'=>'Security Warning', 'msg'=>"Please set register_global's status is off in php.ini"));
	exit;
}
if (!get_magic_quotes_gpc())
{
	show_msg('TEXT',array('hint'=>'Security Warning', 'msg'=>"Please set magic_quotes_gpc's status is on in php.ini"));
	exit;
}*/
date_default_timezone_set("PRC");
addslashes_deep(array($_POST, $_GET, $_COOKIE));
// uri
require_once APPPATH.'controllers/DefaultController.php';
$original_uri = preg_replace("|/(.*)|", "\\1", str_replace("\\", "/", $_SERVER['REQUEST_URI']));
$tmp = array();
$tmp = explode('?', $original_uri);
$original_segments = array();
$original_segments = explode('/', $tmp[0]);
unset($tmp);
$segments = array();
foreach ($original_segments as $v) 
{
	if ($v !== '') 
		$segments[] = addslashes_deep($v, 1);
}
!isset($segments[0]) && $segments[0] = 'index';
if ($segments[0] == 'index.php' || $segments[0] == '')
{ 
	$segments[0] = 'index';
}
$controller = @ucfirst($segments[0]).'Controller';
if (!isset($segments[1]))
{
	$segments[1] = 'index';
}
$method_arr = @array('on_'.$segments[1], $segments[1]);
// call method on controller
$ctrl_file = CTRLPATH.$controller.'.php';
if (!file_exists($ctrl_file))
{
	show_msg("TEXT", array("hint"=>"404 Page Not Found", "msg"=>"The file '$ctrl_file' does not exist!"));
	exit;
}
require_once($ctrl_file);
$v = '';
$method = $method_arr[1];//global在defalutcontroller中check不需要登陆的方法
$obj = new $controller();
foreach ($method_arr as $v)	
{
	if (method_exists($obj, $v)) 
	{
		$method = $v; 
		break;
	} else $method = '';
}
// 禁止访问'_'开头的方法,禁止访问初始化参数了的方法,禁止访问Private, Protected方法
if (empty($method) || substr($method, 0, 1) == '_')
{
	show_msg("TEXT", array("hint"=>'404 Page Not Found', "msg"=>"The file you request does not exist!"));
	exit;
}
$segments = array_slice($segments, 2);
$Reflection = new ReflectionMethod($controller, $method);
$args_num = $Reflection->getNumberOfParameters();
$args_required_num = $Reflection->getNumberOfRequiredParameters();
if ($args_num > $args_required_num || $Reflection->isPrivate() || $Reflection->isProtected()) 
{
	show_msg("TEXT", array("hint"=>'HTTP1.1 403 Request Forbidden', "msg"=>"You could not request this method!"));
	exit;
}
//获得方法的参数，如果不足够，则补空。
$args = array();
$args = array_slice($segments, 0, $args_num);
$n = count($args);
if ($args_num>$n)
{
	for($i=0; $i<$args_num-$n; $i++) 
		$args[] = '';
}
//获得以'/'分割的片段，转化为$_GET
$get_arr = array();
$get_arr = array_slice($segments, $args_num);
$n = count($get_arr);
if (!empty($get_arr)) 
{
	for ($i=$n-1; $i>=0; $i=$i-2)
		if (isset($get_arr[$i-1]))
			$_GET[$get_arr[$i-1]] = urldecode($get_arr[$i]);	
}

function _call_user_func_array($func, $args) 
{ 
        $argString = ''; 
        $comma = ''; 
        for ($i = 0; $i < count($args); $i ++) 
        { 
            $argString .= $comma . "$args[$i]"; 
            $comma = ', '; 
        }
        if (is_array($func))
        {
            $obj = & $func[0]; 
            $meth = $func[1]; 
            if (is_string($func[0])) eval('$retval = $obj::$meth($argString);'); 
            else eval('$retval = $obj->$meth($argString);'); 
        }
        else 
        { 
            eval('$retval = $func($argString);'); 
        } 
        return $retval; 
}

//_call_user_func_array(array(&$obj, $method), $args);
call_user_func_array(array(&$obj, $method), $args);
?>