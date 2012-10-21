<?php
error_reporting(2047);
require_once 'djoa_path.php';
require_once DJOA.'common.php';
require_once DJOA.'include/xml_class.php';
require_once DJOA.'config.php';
if(!empty($setting_getuser)) {
	$xml ='<request type="user" subtype="getalluser" msid="'.uuid().'">
		</request>';
	$data =  fopenurl($gkmsapi_url,500000,rawurlencode($xml));
	$pos_s = strpos($data,'<?xml');
	$data = substr($data,$pos_s);
	$data_xml = trim($data);
	
	$data_xml_view = htmlspecialchars($data_xml);
	$data_arr = XML_unserialize($data_xml);
	$size = $data_arr['response']['message']['users attr']['size'];
	$result = $data_arr['response']['result'];
	$users_arr = $data_arr['response']['message']['users']['user'];
	$num = count($users_arr);
	for($i=0;$i<$num;$i++) {
		unset($users_arr[$i]);
	}
	foreach($users_arr as $v){
		$s_users .="array('account'=>'$v[account]','display_name'=>'$v[display_name]','user_name'=>'$v[name]','gid'=>'$v[gid]','zoneid'=>'$v[zoneid]'),\r\n";
	}
	if(!is_writable(DJOA.'include/user_info.php')) {
		showmessage('文件 include/user_info.php 不可写，请设置文件写权限','demo_setting.php');
		exit;
	}
	$fp = fopen(DJOA.'include/user_info.php','w+');
	fwrite($fp,$s_users);
	fclose;
	showmessage('ok','demo_setting.php');
} elseif(!empty($setting_url)) {
	$s = '<?php if(!defined(\'IN_OA\')) { exit(\'Nonlicet Access Denied\'); }
		$gkmsapi_url = \'';
	$s .= $gkmsapi;
	$s .= '\';$ctt_url = \'';
	$s .= $ctt_url;
	$s .= '\';$ctt_ico = \'';
	$s .= $ctt_ico;	
	$s .= '\';?>';
	if(!is_writable(DJOA.'config.php')) {
		showmessage('文件 config.php 不可写，请设置文件写权限','demo_setting.php');
		exit;
	}
	$fp = fopen(DJOA.'config.php','w+');
	fwrite($fp,$s);
	fclose($fp);
	showmessage('<b>ok</b>','demo_setting.php');
} 
include template('demo_setting');
?>
