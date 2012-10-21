<?php
global $sysconfs;
$sysconfs=array(
	array(
	'id' => 0,
	'name' => '服务器模块配置',
	'filename' =>INSTALLPATH.'/ms/gkexpsvr.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	array(
	'id' => 1,
	'name' => 'TOPGS配置',
	'filename' => '/etc/topgs.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	array(
	'id' => 2,
	'name' => 'GS配置',
	'filename' => '/etc/gs.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	array(
	'id' => 3,
	'name' => 'NS配置',
	'filename' => '/etc/ns.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	array(
	'id' => 4,
	'name' => 'MS配置',
	'filename' => INSTALLPATH.'/ms/ms.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	array(
	'id' => 5,
	'name' => '短信网关配置',
	'filename' => INSTALLPATH.'/ms/sms.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	array(
	'id' => 6,
	'name' => 'SCENT 服务器设置',
	'filename' => '/ring/conf/scent.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),	
	array(
	'id' => 7,
	'name' => '版本升级配置',
	'filename' => '/usr/local/apache2/cgi-bin/update.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	array(
	'id' => 8,
	'name' => '自动升级URL配置',
	'filename' => '/usr/local/apache2/cgi-bin/updatebase.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => true,
	),
	array(
	'id' => 9,
	'name' => 'Apache配置',
	'filename' => '/usr/local/apache2/conf/httpd.conf',
	'charset' => 'gb2312',
	'updated' => '',
	'iscfg' => false,
	),
	array(
	'id' => 10,
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
	'filename' => INSTALLPATH.'/ms/log/ms_log_msweb.txt',
	'charset' => 'gb2312',
	'updated' => '',
	),
	array (
	'id' => 1,
	'name' => '客户端请求日志',
	'filename' => INSTALLPATH.'/ms/log/ms_log_scproxy.txt',
	'charset' => 'gb2312',
	'updated' => '',
	),
	array (
	'id' => 2,
	'name' => 'gs日志',
	'fromcfg' => array('/etc/gs.conf', 'gs{general}{gsid}', 'logfile' ),
	'charset' => 'gb2312',
	'updated' => '',
	),	
	array (
	'id' => 3,
	'name' => 'ns日志',
	'fromcfg' => array('/etc/ns.conf', 'ns{general}{nsid}', 'logfile' ),
	'charset' => 'gb2312',
	'updated' => '',
	),	
	array (
	'id' => 4,
	'name' => 'proxy日志',
	'fromcfg' => array('/etc/ns.conf', 'proxy{general}{nsid}', 'logfile' ),
	'charset' => 'gb2312',
	'updated' => '',
	),	
	array (
	'id' => 5,
	'name' => 'scent日志',
//	'fromcfg' => array('/ring/conf/scent.conf', 'general', 'logfile' ),
	'filename' => '/tmp/scent.log',
	'charset' => 'gb2312',
	'updated' => '',
	),	
);


global $sysstatus;
$sysstatus = array(
        array(
        id => 0,
        name => 'SCENT',
        service => 'scent',
        allowop => true,
        status => 'stopped'
        ),
        array(
        id => 1,
        name => 'TOPGS',
        service => 'topgs',
        allowop => true,
        status => 'stopped'
        ),
        array(
        id => 2,
        name => 'IMLISTD',
        service => 'imlistd',
        allowop => true,
        status => 'stopped'
        ),
        array(
        id => 3,
        name => 'GS',
        service => 'gs',
        allowop => true,
        status => 'stopped'
        ),
        array(
        id => 4,
        name => 'GSSENDIM',
        service => 'gssendim',
        allowop => true,
        status => 'stopped'
        ),
        array(
        id => 5,
        name => 'NS',
        service => 'ns',
        allowop => true,
        status => 'stopped'
        ),
        array(
        id => 6,
        name => 'PROXY',
        service => 'scproxy',
        allowop => true,
        status => 'stopped'
        ),
        array(
        id => 7,
        name => 'MS',
        service => 'gkexpsvr',
        allowop => true,
        status => 'stopped'
        ),
        array(
        id => 8,
        name => 'FS',
        service => 'fs',
        allowop => true,
        status => 'stopped'
        ),
        array(
        id => 9,
        name => 'DELFILE',
        service => 'delfile',
        allowop => true,
        status => 'stopped'
        ),
        array(
        id => 10,
        name => 'TUNNEL',
        service => 'tunnel',
        allowop => true,
        status => 'stopped'
        ),
        array(
        id => 11,
        name => 'UTUNNEL',
        service => 'utunnel',
        allowop => true,
        status => 'stopped'
        )
);
?>