<?php
require_once 'djoa_path.php';
require_once DJOA.'common.php';
require_once DJOA.'include/xml_class.php';
//本地用户信息
if(!empty($_POST['query_user_submit'])) {
	$xml = '
<request type="user" subtype="getuser" msid="">
	<message>
		<user account="'.$user_account.'" />
	</message>
</request>
	';
	$data =  fopenurl($gkmsapi_url,500000,urlencode($xml));
	$data_xml = re_xml($data);
	$data_xml_view = htmlspecialchars($data_xml);
	$data_arr = XML_unserialize($data_xml);
	$code = $data_arr['response']['result attr']['code'];
	$result = $data_arr['response']['result'];

	$account = $data_arr['response']['message']['users']['user attr']['account'];
	$name = $data_arr['response']['message']['users']['user attr']['name'];
	$display_name = $data_arr['response']['message']['users']['user attr']['display_name'];
	
	$xml_view = '<?xml version="1.0" encoding="UTF-8"?>'.$xml;
	$xml_view = re_xml($xml);
	$xml_view = htmlspecialchars($xml_view);
} else {
	$query_user_state = 'view';
}
include template('demo_query_user');
?>
