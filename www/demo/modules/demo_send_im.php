<?php
require_once 'djoa_path.php';
require_once DJOA.'common.php';
require_once DJOA.'include/xml_class.php';
//本地用户信息
if(!empty($_POST['send_im_submit'])) {
	if($radio1 == 'account') {
		$sender_account = $select1;
	} else {
		$sender_gid = $select1;
	}
	if($radio3 == 'account') {
		$receiver_account = $select3;
	} else {
		$receiver_gid = $select3;
	}
	
	$xml = '
<request type="im" subtype="" msid="uuid">
	<message type="'.$im_type.'" subtype="subtype">
		<sender account="'.$sender_account.'" gid="'.$sender_gid.'"/>
		<receivers size="2">
		<receiver account="'.$receiver_account.'" />
		<receiver gid="'.$receiver_gid.'" zoneid="'.$receiver_zoneid.'"/>
		</receivers>
		<body>'.$im_content.'</body>
	</message>
</request>
	';
	$data =  fopenurl($gkmsapi_url,500000,urlencode($xml));
	$data_xml = re_xml($data);
	$data_xml_view = htmlspecialchars($data_xml);
	$data_arr = XML_unserialize($data_xml);
	$code = $data_arr['response']['result attr']['code'];
	$result = $data_arr['response']['result'];
	
	$xml_view = '<?xml version="1.0" encoding="UTF-8"?>'.$xml;
	$xml_view = re_xml($xml);
	$xml_view = htmlspecialchars($xml_view);
} else {
	$send_im_state = 'view';
}
include template('demo_send_im');


//   http://127.0.0.1/cgi-bin/chkonline/5000.33156275:1
   
   
   
?>
