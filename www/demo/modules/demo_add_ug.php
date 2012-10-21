<?php
require_once 'djoa_path.php';
require_once DJOA.'common.php';
require_once DJOA.'include/xml_class.php';
if(!empty($_POST['add_ug_submit'])) {
	$xml = '
<request type="ug" subtype="addug" msid="">
	<message>
		<ug 
		code="'.$ug_code.'" 
		name="'.$ug_name.'" 
		parent_code="'.$parent_code.'" 
		sign="'.$sign.'" 
		location="'.$location.'" 
		email="heda@dianji.com"
		remark="0000" />
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
	if($code == 0) {
		$arr = "array('code'=>'$ug_code','name'=>'$ug_name','parent_code'=>'$parent_code','sign'=>'$sign','location'=>'$location'),\r\n";
		$file = DJOA.'include/ug_info.php';
		if(write_to_file($arr,$file,'a+')){
			$result .='<br /><strong>返回数据存储至OA成功</strong>';	
		}
	}
} else {
	$add_ug_state = 'view';
}
include template('demo_add_ug');
?>
