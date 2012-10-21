<?php
if(!defined('IN_OA')) {
	exit('Nonlicet Access Denied');
}

function uuid() 
{
	return md5(getmypid().uniqid(rand()).$_SERVER['SERVER_NAME']);
}
//addslashes to array
function daddslashes($string, $force = 0) {
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if(!MAGIC_QUOTES_GPC || $force) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = daddslashes($val, $force);
			}
		} else {
			$string = addslashes($string);
		}
	}
	return $string;
}

function showmessage($msg, $control = '') {
	ob_end_clean();
	ob_start();
	header('Content-Type:text/html;charset=utf-8');
	echo '<table align="center" style="background:#F1F1F1;color:#555555;width:400px;height:200px;border:dotted 1px #CCCCCC;
	margin-left:auto;margin-right:auto;padding-top:20px;margin-top:150px"><tr><td align="center">';
	echo $msg.'<br />';
	if( $control == 'BACK') {
		echo "<a href='javascript:history.back();'>return</a>";
	} elseif($control != '') {
		echo '<a href="'.$control."\">jump to next page automatically...</a><script>setTimeout(\"window.location='$control'\", 3000)</script>";
	}
	echo "</td></tr></table>";
	die();
}

function fopenurl($url, $limit = 500000, $post = '', $cookie = '', $referer = '') {
	$return = '';
	preg_match("/http\:\/\/([^\/]+)(.*)/is", $url, $matches);
	$host = $matches[1];
	$script = $matches[2];
	if($post) {
		$out = "POST $script HTTP/1.1\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Referer: ".($referer ? $referer : $url)."\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "Accept-Encoding: none\r\n";
		$out .= "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: '.strlen($post)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $script HTTP/1.1\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Referer: ".($referer ? $referer : $url)."\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Accept-Encoding:\r\n";
		$out .= "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}
	$fp = fsockopen($host, 80, $errno, $errstr, 30);
	if (!$fp) {
		return "$errstr : $errno \r\n";
	} else {
		fwrite($fp, $out);
		while(!feof($fp) && $limit > -1) {
			$limit = $limit && $limit > -1 ? $limit - 524 : $limit;
			$return .= fread($fp, 524);
		}
		fclose($fp);
		return $return;
	}
}

function re_xml($data) {
	$pos = strpos($data,'<?xml');
	$data = substr($data,$pos);
	$data = trim($data);
	return $data;
}

function write_to_file($arr,$file,$openmode) {
	$fp = fopen($file,$openmode);
//	$s = fread($fp,10000);
//	$s .=$arr;
	fwrite($fp,$arr);	
	fclose($fp);
	return $fp;
}

//parse template
function template($file) {
	$tplfile = DJOA.'templates/default/'.$file.'.htm';
	$objfile = DJOA.'data/'.$file.'.tpl.php';
	if(@filemtime($tplfile) > @filemtime($objfile)) {
		parse_template($tplfile, $objfile);
	}
	return $objfile;
}

function parse_template($tplfile, $objfile) {

	define('PHP_CLOSE_TAG', '?>');
	define('PHP_NEXTLINE', "\r\n");
	
	$template = file_get_contents($tplfile);
	
	$var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9\.\[\]_\"\'\$\x7f-\xff]+\])*)"; 
	$const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";

	$template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
	$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
	$template = str_replace("{LF}", "<?=\"\\n\"?".">", $template);

	$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\}/s", "<?=\\1".PHP_CLOSE_TAG, $template);

	$template = preg_replace("/$var_regexp/es", "addquote('<?=\\1?'.'>')", $template);
	$template = preg_replace("/\<\?\=\<\?\=$var_regexp\?\>\?\>/es", "addquote('<?=\\1?>')", $template); 
	

	$template = preg_replace("/\s*\{template\s+(.+?)\}\s*/is", "\n<? include template('\\1'); ".PHP_CLOSE_TAG."\n", $template);
	$template = preg_replace("/\s*\{eval\s+(.+?)\}\s*/ies", "stripvtags('\n<? \\1  '.PHP_CLOSE_TAG.'\n','')", $template);
	$template = preg_replace("/\s*\{echo\s+(.+?)\}\s*/ies", "stripvtags('\n<? echo \\1; '.PHP_CLOSE_TAG.'\n','')", $template);
	$template = preg_replace("/\s*\{elseif\s+(.+?)\}\s*/ies", "stripvtags('\n<? } elseif(\\1) { '.PHP_CLOSE_TAG.'\n','')", $template);
	$template = preg_replace("/\s*\{else\}\s*/is", "\n<? } else { ".PHP_CLOSE_TAG."\n", $template);

	for($i = 0; $i < 5; $i++) {
		$template = preg_replace("/\s*\{loop\s+(\S+)\s+(\S+)\}\s*(.+?)\s*\{\/loop\}\s*/ies", "stripvtags('\n<? if(is_array(\\1)) { foreach(\\1 as \\2) { '.PHP_CLOSE_TAG,'\n\\3\n<? } } '.PHP_CLOSE_TAG.'\n')", $template);
		$template = preg_replace("/\s*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}\s*(.+?)\s*\{\/loop\}\s*/ies", "stripvtags('\n<? if(is_array(\\1)) { foreach(\\1 as \\2 => \\3) { '.PHP_CLOSE_TAG,'\n\\4\n<? } } '.PHP_CLOSE_TAG.'\n')", $template);
		$template = preg_replace("/\s*\{if\s+(.+?)\}\s*(.+?)\s*\{\/if\}\s*/ies", "stripvtags('\n<? if(\\1) { '.PHP_CLOSE_TAG,'\n\\2\n<? } '.PHP_CLOSE_TAG.'\n')", $template);
	}
	$template = preg_replace("/\{$const_regexp\}/s", "<?=\\1".PHP_CLOSE_TAG, $template);
	$template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);

	$template = preg_replace("/ \?\>[\n\r]*\<\?=/s", ";\n echo ", $template);
	
	$template = "<? if(!defined('IN_OA')) exit('Access Denied'); ".PHP_CLOSE_TAG."\n$template";

	$fp = fopen($objfile, 'w');
	flock($fp, 3);
	fwrite($fp, $template);
	fclose($fp);
}

function addquote($var) {
	return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\]/s", "['\\1']", $var));
}

function stripvtags($expr, $statement) {
	$expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\[\]\"\'\$\x7f-\xff]*)\?\>/s", "\\1", $expr));
	$statement = str_replace("\\\"", "\"", $statement);
	return $expr.$statement;
}



?>
