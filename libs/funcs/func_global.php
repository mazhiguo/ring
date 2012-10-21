<?php

function _exit($val, $a='1')
{
	echo "<pre>";
	if ($a == '0') echo $val;
	if ($a == '1') print_r($val);
	if ($a == '2') var_dump($val);
	if ($a == '3') var_export($val);
	exit('</pre>');
}

function addslashes_deep($value, $force = 0)
{
	if (!get_magic_quotes_gpc() || $force)
	{
		if (is_array($value) && !empty($value))
			foreach ($value as $key => $val) $value[$key] = addslashes_deep($val, $force);
		else
			@$value = addslashes(trim($value));
	}
	return $value;
}

function array_implode($arr=array())
{
	return "'".implode("','", $arr)."'";
}
function str_explode_deep($str, $needle=':')
{
	if (!is_string($str)) return array();
	$arr = array();
	$arr = explode("$needle", $str);
	return array_values(array_filter($arr, create_function('$val', 'return $val!=\'\';')));
}

function trim_deep($totrim, $charlist=null)
{
	if (is_array($totrim))
		foreach ($totrim as $key => $val)
			$totrim[$key] = trim_deep($val, $charlist);
	else if (is_object($totrim))
		foreach ($totrim as $key => $val)
			$totrim->$key = trim_deep($val, $charlist);
	else
		$totrim = trim($totrim, $charlist);
	return $totrim;
}

function get_ext($file='text.txt')
{
	$basename = basename($file);
	$len = strlen($basename);
	$pos = 0;
	for ($i=$len-1;$i>0;$i--)
	{
		if ($basename[$i] == '.')
		{
			$pos = $i;
			break;
		}
	}
	return substr($basename, $pos+1);
}

function js_alert($msg)
{
	echo "<script>alert('$msg');</script>";
}

function js_reload($target='', $time=0)
{
	$target = $target == "" ? "." : ".$target.";
	$starget = 'window'.$target.'location.reload()';
	if ($time == 0) echo "<script>$starget</script>";
	else echo "<script>setTimeout(\"$starget\", $time);</script>";
}

function js_goto($url, $target='', $time=0)
{
	$target = $target == ""? "." : ".$target.";
	$starget = 'window'.$target.'location=';
	$goto = $starget."'$url'";
	if ($time == 0) echo "<script>$goto</script>";
	else echo "<script>setTimeout(\"$goto\", $time);</script>";
}

function js_close()
{
	echo "<script>window.close();</script>";
}

function show_msg($type='TEXT', $ctrl = array())
{
	!isset($ctrl['msg']) && $ctrl['msg'] = '';
	!isset($ctrl['url']) && $ctrl['url'] = '/';
	!isset($ctrl['hint']) && $ctrl['hint'] = '提示';
	!isset($ctrl['timeout']) && $ctrl['timeout'] = 3000;
	$s = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN'
			'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
			<html xmlns='http://www.w3.org/1999/xhtml' lang='utf-8'>
			<head>";
	$s .= "<meta http-equiv='Content-Type' content='text/html;charset=utf-8' />";
	$s .= "<meta http-equiv='Content-Language' content='utf-8' />";
	$s .= "<style>body{background:#F7F7FF;}
			.show_msg {font-size:12px;border:1px solid #109ACE;margin-top:50px;width:600px;}
			.show_msg th {height:20px;padding-left:10px;color:#fff;background:#6392C6}
			.show_msg td {background:#fff;}
			.show_msg p {padding:0;margin:0}
			.msg {margin-top:50px;margin-bottom:50px;margin-left:auto;margin-right:auto;padding:10px;padding-left:2em;
				text-align:left;width:80%;background:#DEE7F7;border:1px solid #109ACE;font-weight:800}
			.smsg {margin-left:auto;margin-right:auto;padding:2px;text-align:left;width:80%;font-weight:800	}
			.control {margin-top:25px;margin-bottom:20px;margin-left:auto;margin-right:auto;padding:10px;padding-left:2em;text-align:left;width:80%;}</style>
			";
	$s .= "</head><body><center>";
		$s .= "<table align='center' cellpadding='5' cellspacing='1' align='center' class='show_msg'>";
		$s .= "<tr><th align='left'>$ctrl[hint]</th></tr>";
		$s .= "<tr><td align='center'>";
	if ($type == "BACK") {
		$s .= "<div class='msg'>$ctrl[msg]</div>";
		$s .= "<div class='control'><a href='#' onclick='window.history.back();'>点击这里返回上一页</a></div>";
	} else if ($type == "CLOSE") {
		$s .= "<div class='msg'>$ctrl[msg]</div>";
		$s .= "<div class='control'><br />本页面将在".substr($ctrl['timeout'] ,0, 1)."秒后自动关闭！</div>";
		$s .= "<script>setTimeout(\"window.close()\", $ctrl[timeout])</script>";
	} else if ($type == "GOTO") {
		$s .= "<div class='msg'>$ctrl[msg]</div>";
		$s .= "<div class='control'><a href='$ctrl[url]'>如果您的浏览器没有跳转，请点击这里。</a>
			  <script>setTimeout(\"window.location='$ctrl[url]'\", $ctrl[timeout])</script></div>";
	} else if ($type == "TEXT") {
		$s .= "<div class='msg'>$ctrl[msg]</div>";
	} else if ($type == "JSFUNC") {
		$s .= "<div class='msg'>$ctrl[msg]</div>";
		$s .= "<div class='control'> <script>setTimeout(\"$ctrl[jsfunc]\", $ctrl[timeout])</script></div>";
	}
	$s .= "</td></tr></table></center></body></html>";
	echo $s;
	return true;
}

function msg($msg)
{
	return "<div id='smsg'><li>{$msg}</li></div>";
}

function _parse_ini_file($file, $process_sections = false)
{
	$process_sections = $process_sections !== true ? false : true;
	$ini = file($file);
	if (count($ini) == 0) return array();
	$sections = array();
	$values = array();
	$result = array();
	$globals = array();
	$i = 0;
	foreach ($ini as $line)
	{
		$line = trim($line);
		$line = str_replace("\t", " ", $line);
//		$line = str_replace("\\", "\\\\", $line);
		if (!preg_match('/^[a-zA-Z0-9[]/', $line)) continue;
		if ($line{0} == '[')
		{
		  $tmp = explode(']', $line);
		  $sections[] = trim(substr($tmp[0], 1));
		  $i++;
		  continue;
		}
		list($key, $value) = explode('=', $line, 2);
		$key = trim($key);
		$value = trim($value);
		if (strstr($value, ";"))
		{
			$tmp = explode(';', $value);
			if (count($tmp) == 2)
			{
				if ((($value{0} != '"') && ($value{0} != "'")) ||
				    preg_match('/^".*"\s*;/', $value) || preg_match('/^".*;[^"]*$/', $value) ||
				    preg_match("/^'.*'\s*;/", $value) || preg_match("/^'.*;[^']*$/", $value))
				{
				  $value = $tmp[0];
				}
			}
			else
			{
				if ($value{0} == '"')
					$value = preg_replace('/^"(.*)".*/', '$1', $value);
				else if ($value{0} == "'")
					$value = preg_replace("/^'(.*)'.*/", '$1', $value);
				else
					$value = $tmp[0];
			}
		}
		$value = trim($value);
		$value = trim($value, "'\"");
		if ($i == 0)
		{
		  if (substr($line, -1, 2) == '[]')
		    $globals[$key][] = $value;
		  else
		    $globals[$key] = $value;
		}
		else
		{
		  if (substr($line, -1, 2) == '[]')
		    $values[$i-1][$key][] = $value;
		  else
		    $values[$i-1][$key] = $value;
		}
	}
	for($j = 0; $j < $i; $j++)
	{
		if ($process_sections === true)
			$result[$sections[$j]] = $values[$j];
		else
			$result[] = $values[$j];
	}
	return $result + $globals;
}

function write_ini_file($file, $assoc_array)
{
    $content = '';
    $sections = '';
    foreach ($assoc_array as $key => $item)
    {
        if (is_array($item))
        {
            $sections .= "[{$key}]\r\n";
            foreach ($item as $key2 => $item2)
            {
                if (is_numeric($item2) || is_bool($item2))
                    $sections .= "{$key2} = {$item2}\r\n";
                else
                    $sections .= "{$key2} = {$item2}\r\n";
            }
        }
        else
        {
            if(is_numeric($item) || is_bool($item))
                $content .= "{$key} = {$item}\r\n";
            else
                $content .= "{$key} = {$item}\r\n";
        }
    }
    $content .= $sections;
    if (!file_put_contents($file, $content))
    	return false;
    return true;
}

function get_client_ip()
{
  if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
     $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
  else if (isset($_SERVER["HTTP_CLIENT_IP"]))
     $realip = $_SERVER["HTTP_CLIENT_IP"];
  else
     $realip = $_SERVER["REMOTE_ADDR"];
  return $realip;
}

function get_rnd_str($length=9, $source=1)
{
	$length = $length > 1 ? $length : 1;
	if ($source)  $source = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	else $source = '1234567890abcdefghijklmnopqrstuvwxyz';
	$rndstr = '';
	for ($i=0; $i<=$length; $i++)
	{
		$j = rand(0, strlen($source)-1);
		$rndstr .= $source[$j];
	}
	return $rndstr;
}

function make_option_string($value='20', $name='', $arr = array('10'=>'10', '20'=>'20', '30'=>'30', '50'=>'50', '100'=>'100'))
{
	$option_str = '';
	$name = empty($name) ? $value : $name;
	foreach ($arr as $key=>$val)
		$option_str .= $val == $value ? "<option value='$val' selected>$key</option>" : "<option value='$val'>$key</option>";
	return $option_str;
}

function get_id_from_array($arr=array(), $id_name='ug_id')
{
	$i = 0;
	$ret = array();
	if (empty($arr)) return $ret;
	foreach ($arr as $val)
	{
		$ret[] = $val[$id_name];
		$i++;
	}
	return $ret;
}

function permission($filename)
{
    $perms = fileperms($filename);
    if      (($perms & 0xC000) == 0xC000) { $info = 's'; }
    else if (($perms & 0xA000) == 0xA000) { $info = 'l'; }
    else if (($perms & 0x8000) == 0x8000) { $info = '-'; }
    else if (($perms & 0x6000) == 0x6000) { $info = 'b'; }
    else if (($perms & 0x4000) == 0x4000) { $info = 'd'; }
    else if (($perms & 0x2000) == 0x2000) { $info = 'c'; }
    else if (($perms & 0x1000) == 0x1000) { $info = 'p'; }
    else                                  { $info = 'u'; }
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
    return $info;
}

function dir_list($dir)
{
    if ($dir[strlen($dir)-1] != '/') $dir .= '/';
    if (!is_dir($dir)) return array();
    $dir_handle  = opendir($dir);
    $dir_objects = array();
    while ($object = readdir($dir_handle))
        if (!in_array($object, array('.','..')))
        {
            $filename = $dir.$object;
            $file_object = array(
                                    'name' => $object,
                                    'size' => filesize($filename),
                                    'perm' => permission($filename),
                                    'type' => filetype($filename),
                                    'time' => date("Y-m-d H:i:s", filemtime($filename)),
                                    'is_writable' => is_writable($filename),
                                    'is_readable' => is_readable($filename),
                                );
            $dir_objects[] = $file_object;
        }
    return $dir_objects;
}

function write_array_to_file ($filename, $arrname, $arr)
{ //File Writing
	$savetext = '';
    $savetext.="<?PHP\n";
    foreach ($arr as $key=>$v)
    {
        $savetext.="\$".$arrname."[] = array( \n";
        foreach ($v as $k1=>$v1)
        {
            $savetext.=" '$k1'=>'$v1', \n ";
        }
        $savetext.="); \n";
    }
    $savetext.="?>";

	$filenum = @fopen($filename,"w");
	if (!$filenum)
	{
		return false;
	}
	flock($filenum,LOCK_EX);
	$file_data=fwrite($filenum,$savetext);
	flock($filenum, LOCK_UN);
	fclose($filenum);
	return true;
}

function write_file($filename, &$data)
{ //File Writing
	$filenum=@fopen($filename,"w");
	if (!$filenum) return false;
	flock($filenum,LOCK_EX);
	$file_data = fwrite($filenum,$data);
	flock($filenum, LOCK_UN);
	fclose($filenum);
	return true;
}

function debug_time()
{
	global $start_time;
	if (!isset($start_time) || $start_time=='') return;
	$end_time = array_sum(explode(' ', microtime()));
	$debug_time = $end_time-$start_time;
	$pos = strpos($debug_time, '.');
	return "Processed in ".substr($debug_time, 0, $pos+7)." second(s)";
}

function uuid()
{
//	return md5(getmypid().uniqid(rand()).$_SERVER['SERVER_NAME']);
	return md5(get_rnd_str(64));
}

function makegid($gid,$zoneid=0)
{
	$base = pow(2,32);
	$rt = $base*$zoneid+$gid;
	$tmp = sprintf("%f",$rt);
	$rt = substr($tmp,0,-7);
	return $rt;
}

function parsegid($strgid,&$zoneid,&$gid)
{
	$zoneid = 5000;
	$base =pow(2,32);
	$zoneid=floor($strgid/$base);
	$gid=$strgid - $zoneid*$base;
}

function tochar($x) {
	if($x<10) $x='0'+$x;
	else if($x<26+10) $x=$x-10+'a';
	else if($x<62+10) $x=$x-36+'A';
}

function getpass($pass) {
	$i=3;
  $salt="$1$12345678$";
  for($i=3;$i<11;++$i) {
  	$salt[$i]=rand()%62;
   	tochar($salt[$i]);
  }
  return crypt($pass,$salt);
}

function checkpass($pass, $cipher)
{
	if (strcmp($cipher, crypt($pass, $cipher))==0)
  		return 0;
	else
  		return 1;
}

// 根据不同的操作系统环境, 产生不同的log输出
// str, path
function ms_web_log($str, $path=null)
{
	$cfg = _parse_ini_file(APPPATH.'ms-web.ini', true);
	$flag = $cfg['ms-web-log']['flag'];
	if ($flag)
	{
		if (!isset($path) && ISWIN) $path = 'c:\\temp\\ms-web.log.txt';
		else if (!isset($path) && !ISWIN) $path = '/tmp/ms-web.log.txt';
		$msg = "[--".date("Y-m-d H:i:s")."--]\r\n$str\r\n";
		file_put_contents($path, $msg, FILE_APPEND);
	}
}
?>