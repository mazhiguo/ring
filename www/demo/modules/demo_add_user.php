<?php
require_once 'djoa_path.php';
require_once DJOA.'common.php';
require_once DJOA.'include/xml_class.php';
if(!empty($_POST['add_user_submit'])) {
	$xml = '
<request type="user" subtype="adduser" msid=" ">
	<message>
		<user 
			account="'.$user_account.'" 
			name="'.$user_name.'" 
			display_name="'.$display_name.'" 
			pwd="'.$user_pw.'" 
			state="0" 
			sex="'.$sex.'" 
			birthday="'.$birthday.'" 
			email="'.$email.'" 
			ug_name="'.$ug_name.'" 
			mobile="'.$mobile_tel.'" 
			office_tel="'.$office_tel.'" 
			fax="'.$fax.'" 
			webaddress="'.$webaddress.'" 
			postcode="'.$postcode.'" 
			address="'.$address.'" 
			position="'.$postion.'" 
			remark="'.$remark.'" />
	</message>
</request>
	';
	$data =  fopenurl($gkmsapi_url,500000,urlencode($xml));
	$data_xml = re_xml($data);
	$data_xml_view = htmlspecialchars($data_xml);
	$data_arr = XML_unserialize($data_xml);
	$code = $data_arr['response']['result attr']['code'];//返回code
	$result = $data_arr['response']['result'];//返回结果状态，原因
	$gid = $data_arr['response']['message']['user attr']['gid'];//返回ok，则返回id,gid,zoneid,gs
	$zoneid = $data_arr['response']['message']['user attr']['zoneid'];//返回ok，则返回id,gid,zoneid,gs
	
	$xml_view = '<?xml version="1.0" encoding="UTF-8"?>'.$xml;
	$xml_view = re_xml($xml);
	$xml_view = htmlspecialchars($xml_view);
	
	//写入数组
	if($code == 0) {
		$arr = "array('account'=>'$user_account','display_name'=>'$display_name','user_name'=>'$user_name','gid'=>'$gid','zoneid'=>'$zoneid'),\r\n";
		$file = DJOA.'include/user_info.php';
		
		if(write_to_file($arr,$file,'a+')){
			$result .='<br /><strong>返回数据存储至OA成功</strong>';	
		}
	}
} else {
	$add_user_state = 'view';
}
include template('demo_add_user');
?>
