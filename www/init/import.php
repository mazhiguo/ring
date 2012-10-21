<?php
//  D:/dianji/gke/GKEXsrv/php/php.exe  D:/dianji/cvs/dianji/ring/server/_projects/cb_bank/import.php
set_time_limit(3600);
$start_time = array_sum(explode(' ', microtime()));
echo "\r\n\r\n";
echo "start------------------------------------------------------------\r\n";
echo "processing...\r\n";

// --------------------------------------------------------------- config
// the data's dir:::: $xml_dir.'/ug/dept.xml'  $xml_dir.'/user/*.xml' 
$xml_dir = '/usr/local/ring/webapp/ms-web/www/init'; 
!is_dir($xml_dir) && exit("you must set the data directory correctly");

$host = "localhost";
$db_user = "msdb";
$db_pwd = "msdb";
$db_name = "msdb";

$default_user_pwd = "123456";
$clear_all = false;
// ---------------------------------------------------------------
$link = mysql_connect($host, $db_user, $db_pwd) or die ('Not connected : ' . mysql_error());
mysql_select_db($db_name, $link) or die (mysql_error());
mysql_query("set names utf8") or die("Invalid query: " . mysql_error());

// --------------------------------------------------------------- clear all data
if (isset($_GET['clear_all']) || $clear_all)
{
	// clear all data
	mysql_query("delete from admininfo");
	mysql_query("delete from uginfo");
	mysql_query("delete from userinfo");
	mysql_query("delete from re_user_ug");
	mysql_query("delete from re_user_role");
	mysql_query("delete from re_gid_user");
	mysql_query("delete from re_id_dn");
	mysql_query("delete from re_role_priv");
	mysql_query("delete from re_user_ver");
	mysql_query("delete from verinfo where name='channel'");
	mysql_query("delete from roleinfo");
	mysql_query("update gidinfo set state=0");
	mysql_query("delete from `smsgw`.`tableuser`");
	mysql_query("delete from `smsgw`.`tablesms`");
	$sql = "insert into uginfo (ug_id, name, parent_id, sign, code, remark, location, creator_account,mender_account,creator_time,mender_time) values 
			('0', '中国人民银行', '-1', 0, '0', '', '0', 'admin','admin', now(), now())";
	mysql_query($sql);
	$sql = "insert into admininfo values('0', 'admin', 'admin','1', '21232f297a57a5a743894a0e4a801fc3', 'admin', now(), 'admin', now(), '', '0', '0:')";
	mysql_query($sql);
	echo "clear all data sucessfully!\r\n";
	echo "end--------------------------------------------------------------\r\n";
	exit;
}

// --------------------------------------------------------------- import group
$ugxml = file_get_contents($xml_dir.'/ug/dept.xml');
$ugxml = str_replace('encoding="GBK"', 'encoding="utf-8"', $ugxml);
$ugxml =  mb_convert_encoding($ugxml, "utf-8", "gbk");
$ougxml = new SimpleXMLElement($ugxml);
unset($ugxml);
$i = 0;

$new_ugs_num = 0;
$old_ugs_num = 0;
foreach ($ougxml as $val)
{
	//根组织id,pid转换
	if ($val->groupid == '1') 
	{
		$val->groupid = '0';
		$val->parentid = '-1';
		$val->groupdn = 'GID=0,TYPEID=1,ROOTID=1,O=pbc'; //修改GID
	}
	//根组织child的pid转换
	if ($val->parentid == '1')
	{
		$val->parentid = '0';
	}
	$sign = $val->groupleve < 2 ? '0' : '1';
	
	// 检查是否已经存在
	$query = mysql_query("select * from uginfo where ug_id='$val->groupid'");
	$is_exist = mysql_fetch_assoc($query);
	if (!empty($is_exist)) 
	{
		$old_ugs_num++ ;
		continue;
	}
	
	//1.增加到uginfo表
	$sql = "insert into uginfo (ug_id, name, parent_id, sign, code, remark, location, creator_account,mender_account,creator_time,mender_time) values 
			('$val->groupid', '$val->groupname', '$val->parentid', $sign, '$val->groupid', '$val->wholename', '$i', 'admin','admin', now(), now())";
	mysql_query($sql);
	// 2. 增加到关联表
	mysql_query("insert into re_id_dn (id, dn, type) values ('$val->groupid', '$val->groupdn', '$sign')");
	// 3. 如果是单位，增加管理员，增加默认角色，增加频道参数
	if ($sign == '0') 
	{
		//管理员
		$query = mysql_query("select * from admininfo where account='admin'");
		$admin = mysql_fetch_assoc($query);
		$attach_ug_id = $admin['attach_ug_id'].":".$val->groupid;	
		mysql_query("update admininfo set attach_ug_id='$attach_ug_id' where admin_id='0'");
				
		//增加默认角色
		mysql_query("INSERT INTO roleinfo SET role_id='$val->groupid', name='默认角色', parent_id='$val->groupid',
				creator_account='admin', creator_time=now(), mender_account='admin',
				mender_time=now(), remark='$val->wholename', location='1', ug_id='$val->groupid'");
		//增加默认角色权限
		$privs_arr = array('4', '5', '7', '8', '9', '10', '11', '12', '16');
		foreach ($privs_arr as $priv_id) 
		{
			$priv_arr = array();
			$query = mysql_query("SELECT * FROM privinfo where priv_id='$priv_id'");
			$priv_arr = mysql_fetch_assoc($query);				
			mysql_query("INSERT INTO re_role_priv SET role_id='$val->groupid', priv_id='$priv_arr[priv_id]', value='1', 
			para_value='$priv_arr[para_value]', type='$priv_arr[type]'");		
		}
		//新单位的版本参数
		mysql_query("INSERT INTO verinfo SET name='channel', mender_time=now(),ug_id='$val->groupid'");
	}
	$new_ugs_num++;
//	break;
}
echo "new ugs num:$new_ugs_num"."\r\n";
echo "old ugs num:$old_ugs_num"."\r\n";
// --------------------------------------------------------------- import user

function tochar($x) {
	if($x<10) $x='0'+$x;
	else if($x<26+10) $x=$x-10+'a';
	else if($x<62+10) $x=$x-36+'A';
}

function getpass ($pass) {
	$i=3;
  $salt="$1$12345678$";
  for($i=3;$i<11;++$i) {
  	$salt[$i]=rand()%62;
   	tochar($salt[$i]);
  }
  return crypt($pass,$salt);
}
$defaultpwd = getpass(strtoupper(md5($default_user_pwd)));
$userfiles = array();
$userfiles = scandir($xml_dir.'/user');
rsort($userfiles);
array_pop($userfiles);
array_pop($userfiles);

$old_users_num = 0;
$new_users_num = 0;
$j = 0;
foreach ($userfiles as $val)
{
	$userxml = file_get_contents($xml_dir.'/user/'.$val);
	$userxml = str_replace('encoding="GBK"', 'encoding="utf-8"', $userxml);
	$userxml =  mb_convert_encoding($userxml, "utf-8", "gbk");
	$ouserxml = new SimpleXMLElement($userxml);
	unset($userxml);
	foreach ($ouserxml->person as $val2)
	{
		$j++;
		$user = array();
		$user['user_id'] = $val2->personid;
		$user['account'] = $val2->personname;
		$user['name'] = $val2->personname;
		$user['display_name'] = $val2->personname;
		$user['state'] = '1';
		$user['pwd'] = $defaultpwd;
		$user['remark'] = $val2->deptwholename;
		$user['sex'] = '2';
		$user['location'] = $j;
		
//		print_r($user);
//		break;
			
		// 检查是否已经存在
		$query = mysql_query("select * from userinfo where user_id='$val2->personid'");	
		$is_exist = mysql_fetch_assoc($query);
		if (!empty($is_exist)) {$old_users_num++;continue;}
		//增加persondn到关联表
		mysql_query("insert into re_id_dn (id, dn, type) values ('$user[user_id]','$val2->persondn', '2' )");
		//deptwholename[1] 得到单位
		$seg = array();
		$seg = explode('/',$val2->deptwholename);
		$query = mysql_query("select ug_id from uginfo where name='$seg[1]'");
		$rs = mysql_fetch_assoc($query);
		$user['ug_id'] = $rs['ug_id']; //------------------??????????????????????//ug_id空
		// 增加到userinfo表
		mysql_query("insert into userinfo values('$user[user_id]','$user[account]','$user[name]',
		'$user[display_name]', '$user[state]','$user[pwd]', 'admin', now(), 'admin', now(),'$user[remark]',
		'$user[sex]','0000-00-00','$user[ug_id]', '','','','','','','','','$user[location]'
		)");
		// 增加到re_user_ug表,得到部门
		$query = mysql_query("select ug_id from uginfo where remark='$val2->deptwholename'");
		$rs = mysql_fetch_assoc($query);
		$user['dept_id'] = $rs['ug_id'];
		if ($user['dept_id'] != '')
		{
			mysql_query("insert into re_user_ug (user_id,ug_id) values ('$user[user_id]', '$user[dept_id]')");
		}
		// 增加默认角色
		//mysql_query("INSERT INTO re_user_role SET user_id='$user[user_id]', role_id='$user[ug_id]'");
		$new_users_num++;
		$user_id = $user['user_id'];
		$result = mysql_query("select * from gidinfo where state=0 limit 1");
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$gid = $row['gid'];
		$zoneid = $row['zoneid'];
		$gs = $row['gs'];
		if ($gid != '')
		{
			mysql_query("update gidinfo set state=1 where gid='$gid' and zoneid='$zoneid'");
			mysql_query("insert into re_gid_user(gid,zoneid,gs,user_id,creator_account,creator_time) values('$gid','$zoneid','$gs','$user_id','admin',now())");
			mysql_query("insert into re_user_ver(gid,zoneid,name,value,mender_time) values('$gid','$zoneid','priv','',now())");
		}
		mysql_free_result($result);		
	}
}
mysql_query("update verinfo set mender_time=now() where name='ug'");
mysql_query("update verinfo set mender_time=now() where name='clientpara'");

echo "new users num:$new_users_num"."\r\n";
echo "old users num:$old_users_num"."\r\n";

$end_time = array_sum(explode(' ', microtime()));
$debug_time = ($end_time - $start_time)/60;
echo "excute time: ".$debug_time." minutes\r\n";
echo "complete!";
echo "\r\n";
echo "end--------------------------------------------------------------";
echo "\r\n\r\n";
exit;
?>