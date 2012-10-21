<?php
require_once 'djoa_path.php';
require_once DJOA.'common.php';
require_once DJOA.'include/xml_class.php';
if(!empty($_POST['send_sms_submit'])) {
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
<request type="sms" subtype="" msid="uuid">
	<message>
		<sender account="'.$sender_account.'" gid="'.$sender_gid.'"/>
		<receivers size="1">
		<receiver gid="'.$receiver_gid.'" account="'.$receiver_account.'" />
		<receiver mobile="'.$mobile_phone.'" />
		</receivers>
		<body>'.$sms_content.'</body>
		<time>'.date("Y-m-d H:i:s").'</time>
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
	$send_sms_state = 'view';
}
include template('demo_send_sms');
?>
