<?php
global $sysconfs;
/*
1. 配置文件路径必须配置为绝对路径！
2. 如果更改了相关配置文件的位置，需在此修改为新的位置的绝对路径！
*/
$sysconfs=array(
	'gkexpsvr' => array(
	'id' => 'gkexpsvr',
	'name' => '服务器模块配置',
	'filename' =>INSTALLPATH.'/services/gkexpsvr.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	'topgs' => array(
	'id' => 'topgs',
	'name' => 'TOPGS配置',
	'filename' => INSTALLPATH.'/services/topgs.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	'gs' => array(
	'id' => 'gs',
	'name' => 'GS配置',
	'filename' => INSTALLPATH.'/services/gs.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	'ns' => array(
	'id' => 'ns',
	'name' => 'NS配置',
	'filename' => INSTALLPATH.'/services/ns.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	'ms' => array(
	'id' => 'ms',
	'name' => 'MS配置',
	'filename' => INSTALLPATH.'/services/ms.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	'sms' => array(
	'id' => 'sms',
	'name' => '短信网关配置',
	'filename' => INSTALLPATH.'/services/sms.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	'fs' => array(
	'id' => 'fs',
	'name' => 'FS 文件服务器设置',
	'filename' => INSTALLPATH.'/services/fs.ini',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	'tunnel' => array(
	'id' => 'tunnel',
	'name' => 'Tunnel 服务器设置',
	'filename' => INSTALLPATH.'/services/tunnel.ini',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	'sc' => array(
	'id' => 'sc',
	'name' => 'SCENT 服务器设置',
	'filename' => INSTALLPATH.'/services/sc.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	'update' => array(
	'id' => 'update',
	'name' => '版本升级配置',
	'filename' => INSTALLPATH.'/Apache2.2/cgi-bin/update.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	'updatebase' => array(
	'id' => 'updatebase',
	'name' => '自动升级URL配置',
	'filename' => INSTALLPATH.'/Apache2.2/cgi-bin/updatebase.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	'httpd' => array(
	'id' => 'httpd',
	'name' => 'Apache配置',
	'filename' => INSTALLPATH.'/Apache2.2/conf/httpd.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => false,
	),
	'ms-web' => array(
	'id' => 'ms-web',
	'name' => 'msweb 设置',
	'filename' => INSTALLPATH.'/webapp/ms-web/app/ms-web.ini',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
);

global $_logs;
$_logs = array(
	array(
	'id' => 0,
	'name' => 'MS-WEB日志',
	'filename' => INSTALLPATH.'/services/log/ms_log_msweb.txt',
	'charset' => 'gb2312',
	'updated' => '',
	),
	array (
	'id' => 1,
	'name' => '客户端请求日志',
	'filename' => INSTALLPATH.'/services/log/ms_log_scproxy.txt',
	'charset' => 'gb2312',
	'updated' => '',
	),
	array (
	'id' => 2,
	'name' => 'gs日志',
	'fromcfg' => array(INSTALLPATH.'/Services/gs.conf', 'gs{general}{gsid}', 'logfile' ),
	'charset' => 'gb2312',
	'updated' => '',
	),
	array (
	'id' => 3,
	'name' => 'ns日志',
	'fromcfg' => array(INSTALLPATH.'/Services/ns.conf', 'ns{general}{nsid}', 'logfile' ),
	'charset' => 'gb2312',
	'updated' => '',
	),
	array (
	'id' => 4,
	'name' => 'proxy日志',
	'fromcfg' => array(INSTALLPATH.'/Services/ns.conf', 'proxy1', 'logfile' ),
	'charset' => 'gb2312',
	'updated' => '',
	),
	array (
	'id' => 5,
	'name' => 'scent日志',
	'fromcfg' => array(INSTALLPATH.'/Services/sc.conf', 'general', 'logfile' ),
	'charset' => 'gb2312',
	'updated' => '',
	),
);

global $sysstatus;
$sysstatus = array(
	array(
	id => 0,
	name => 'Mysql数据库',
	service => 'MySqlGKE',
	allowop => false,
	status => 'stopped'
	),
	array(
	id => 1,
	name => '企业版服务器',
	service => 'gkexpsvr',
	allowop => true,
	status => 'stopped'
	)
);


//# ------------------------------------------------------------------------------------------------>>
//# 以下是生成服务器参数设置
//# 提供给基本设置使用
//# ------------------------------------------------------------------------------------------------>>
global $_mscfg;
//#
//# SC 服务器的IP地址
//#
$_mscfg['scip']=array('id' => 'scip',
	'display_name'  => 'SC服务IP',
	'remark'  => '中央服务器，提供通讯录查找，用户联系人关系表维护等。',
	'name' => 'sc_ip',
	'value' => '127.0.0.1',
	'cfg_file' => array (
			array(INSTALLPATH.'/Services/ms.conf', 'sc', 'sc_ip','host'=>'127.0.0.1', 'port'=>'8899' ),
			array(INSTALLPATH.'/Services/topgs.conf', 'sc', 'ip','host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/gs.conf', 'sc', 'ip','host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/ns.conf', 'sc', 'ip','host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/lavags.conf', 'sc', 'ip','host'=>'127.0.0.1', 'port'=>'8899'  ),
			),
);
//#
//# SC 服务器的端口
//#
$_mscfg['scport']=array('id' => 'scport',
	'display_name'  => 'SC服务端口',
	'remark'  => '',
	'name' => 'sc_port',
	'value' => '10009',
	'cfg_file' => array (
			array(INSTALLPATH.'/Services/ms.conf', 'sc', 'sc_port','host'=>'127.0.0.1', 'port'=>'8899' ),
			array(INSTALLPATH.'/Services/topgs.conf', 'sc', 'port','host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/gs.conf', 'sc', 'port','host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/ns.conf', 'sc', 'port','host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/lavags.conf', 'sc', 'port','host'=>'127.0.0.1', 'port'=>'8899'  ),
			),
);
//#
//# TOPGS 服务器的IP地址
//#
$_mscfg['topgsip']=array('id' => 'topgsip',
	'display_name'  => 'TOPGS服务IP',
	'remark'  => '负责多个群组服务器（GS）之间的协调。',
	'name' => 'topgs_ip',
	'value' => '127.0.0.1',
	'cfg_file' => array (
			array(INSTALLPATH.'/Services/gs.conf', 'topgs', 'ip', 'host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/scent.conf', 'topgs', 'ip','host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/lavags.conf', 'topgs', 'ip','host'=>'127.0.0.1', 'port'=>'8899'  ),
			),
);
//#
//# TOPGS 服务器的PORT
//#
$_mscfg['topgsport']=array('id' => 'topgsport',
	'display_name'  => 'TOPGS服务端口',
	'remark'  => '',
	'name' => 'topgs_port',
	'value' => '10002',
	'cfg_file' => array (
			array(INSTALLPATH.'/Services/topgs.conf', 'topgs', 'port', 'host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/gs.conf', 'topgs', 'port', 'host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/scent.conf', 'topgs', 'port','host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/lavags.conf', 'topgs', 'port','host'=>'127.0.0.1', 'port'=>'8899'  ),
			),
);
//#
//# GS 服务器的EIP地址
//#
$_mscfg['gseip']=array('id' => 'gseip',
	'display_name'  => 'GS服务EIP（对外服务IP）',
	'remark'  => '外网访问GS服务的IP地址，需要公网地址，如果没有可以不用设置。',
	'name' => 'gs_eip',
	'value' => '127.0.0.1',
	'cfg_file' => array (
			array(INSTALLPATH.'/Services/gs.conf', 'gs{general}{gsid}', 'eip', 'host'=>'127.0.0.1', 'port'=>'8899'  ),
			),
);
//#
//# GS 服务器的IP地址
//#
$_mscfg['gsip']=array('id' => 'gsip',
	'display_name'  => 'GS服务IP（内网IP）',
	'remark'  => '提供离线消息及转发服务。如果单机部署，可以设置为127.0.0.1，否则设置为内网地址。',
	'name' => 'gs_ip',
	'value' => '127.0.0.1',
	'cfg_file' => array (
			array(INSTALLPATH.'/Services/ns.conf', 'gs', 'ip','host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/ms.conf', 'gs', 'gs_ip', 'host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Apache2.2/cgi-bin/lavags.conf', 'topgs', 'ip', 'host'=>'127.0.0.1', 'port'=>'8899'  ),
			),
);
//#
//# GS 服务器的PORT
//#
$_mscfg['gsport']=array('id' => 'gsport',
	'display_name'  => 'GS服务端口',
	'remark'  => '',
	'name' => 'gs_port',
	'value' => '10004',
	'cfg_file' => array (
			array(INSTALLPATH.'/Services/gs.conf', 'gs{general}{gsid}', 'port', 'host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/ns.conf', 'gs', 'port','host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/ms.conf', 'gs', 'gs_port', 'host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Apache2.2/cgi-bin/lavags.conf', 'topgs', 'port', 'host'=>'127.0.0.1', 'port'=>'8899'  ),
			),
);
//#
//# NS 服务器的EIP地址
//#
$_mscfg['nseip1']=array('id' => 'nseip1',
	'display_name'  => 'NS服务EIP（对外服务）',
	'remark'  => 'NS对外服务器IP，提供给外部访问NS的IP。修改到[ns(X)] eip 和 [proxy1] ns_ip。',
	'name' => 'ns_eip1',
	'value' => '127.0.0.1',
	'cfg_file' => array (
			array(INSTALLPATH.'/Services/ns.conf', 'ns{general}{nsid}', 'eip','host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/ns.conf', 'proxy1', 'ns_ip','host'=>'127.0.0.1', 'port'=>'8899'  ),
			),
);
//#
//# NS 服务器的IP地址
//#
$_mscfg['nsip1']=array('id' => 'nsip1',
	'display_name'  => 'NS服务IP',
	'remark'  => '在线服务器，提供用户在线保持，在线状态跟踪等。',
	'name' => 'ns_ip1',
	'value' => '127.0.0.1',
	'cfg_file' => array (
			array(INSTALLPATH.'/Services/ns.conf', 'gsinfo1', 'ns_ip1','host'=>'127.0.0.1', 'port'=>'8899'  )
			),
);
//#
//# NS 服务器的PORT
//#
$_mscfg['nsport1']=array('id' => 'nsport1',
	'display_name'  => 'NS服务端口',
	'remark'  => '',
	'name' => 'ns_port1',
	'value' => '10010',
	'cfg_file' => array (
			array(INSTALLPATH.'/Services/ns.conf', 'ns1', 'port','host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/ns.conf', 'proxy1', 'ns_port','host'=>'127.0.0.1', 'port'=>'8899'  ),
			array(INSTALLPATH.'/Services/ns.conf', 'gsinfo1', 'ns_port1','host'=>'127.0.0.1', 'port'=>'8899'  )
			),
);
//#
//# TUNNEL 服务器的IP地址
//#
$_mscfg['tunnelip']=array('id' => 'tunnelip',
	'display_name'  => 'TUNNEL服务的IP',
	'remark'  => 'TUNNEL服务IP，客户端允许从外网登录，则TUNNEL服务IP要设置为公网IP。',
	'name' => 'tunnel_ip',
	'value' => '127.0.0.1',
	'cfg_file' => array (
			array(INSTALLPATH.'/Services/gs.conf', 'tunnel', 'ip', 'host'=>'127.0.0.1', 'port'=>'8899'  ),
			),
);
//#
//# 客户端登录的服务器端口
//#
$_mscfg['proxyport']=array('id' => 'proxyport',
	'display_name'  => '客户端登陆的端口',
	'remark'  => '在客户端登录界面上设置的登录服务器的端口。',
	'name' => 'proxy_port',
	'value' => '10000',
	'cfg_file' => array (
			array(INSTALLPATH.'/Services/ns.conf', 'proxy1', 'port', 'host'=>'127.0.0.1', 'port'=>'8899'  ),
			),
);

?>