<?php
require_once 'djoa_path.php';
require_once DJOA.'common.php';
require_once DJOA.'include/xml_class.php';
if($login_submit) {
	$login_pw = strtoupper(md5($login_pw));
	echo '<script>window.open("elava://login?gid=5000.'.$login_gid.'&pwd='.$login_pw.'")</script>';
}
if($addtribe_submit) {
	echo '<script>window.open("elava://addtribe?id=5000.'.$addtribe_id.'")</script>';
}

if($entertribe_submit) {
	echo '<script>window.open("elava://entertribe?id=5000.'.$entertribe_id.'")</script>';
}
include template('demo_ctt');
?>
