<?php
require_once 'djoa_path.php';
require_once DJOA.'common.php';
require_once DJOA.'include/xml_class.php';
//http://mail.dianji.com/login.php?gid=$11000069&zoneid=$5000&passport=$EROAFSA

//$gid = str_replace('$','',$_GET['gid']);
//$zoneid = str_replace('$','',$_GET['zoneid']);
//$passport = str_replace('$','',$_GET['passport']);
if(!empty($GID) && !empty($passport)) {
	if(!empty($_POST['check_passport_submit'])) {
		//处理验证
		$xml = '
		<request type="login" subtype="passport" msid=" ">
		<message>
		<user GID="'.$_POST['GID'].'">
		<passport>'.$_POST['passport'].'</passport>
		</user>
		</message>
		</request>
		';
		$data =  fopenurl($gkmsapi_url,500000,rawurlencode($xml));	
		$data_xml = re_xml($data);
		$data_xml_view = htmlspecialchars($data_xml);
		$data_arr = XML_unserialize($data_xml);
		$code = $data_arr['response']['result attr']['code'];
		$result = $data_arr['response']['result'];
		
		if($code != '') {
			$status = 'result_view';
		} 	
	} else {
		$status = 'right_argu';
	}
} else {
	$status = 'error';	
}	
include template('demo_check_passport');
?>
