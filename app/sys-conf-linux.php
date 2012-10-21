<?php

// 加载由安装脚本自动生成的服务器配置信息
include APPPATH."linux-servers-configure.php";



/*备份文件的存放位置  确保mscfginfo表的name字段与之一一对应*/
global $bak_files;
$bak_files = array(
	'license.dat' => '/ring/conf/',
	'license.xml' => '/ms/license/',
	'ns.conf' => '/services/',
	'gs.conf' => '/services/',
	'ms.conf' => '/services/',
	'sms.conf' => '/services/',
	'gkexpsvr.conf' => '/services/',
	'update.conf' => '/Apache2.2/cgi-bin/',
	'fs.ini' => '/services/',
	'tunnel.ini' => '/services/',
	'sc.conf' => '/services/',
	'ms-web.ini' => '/webapp/ms-web/app/',
	'updatebase.conf' => '/Apache2.2/cgi-bin/',
);
?>