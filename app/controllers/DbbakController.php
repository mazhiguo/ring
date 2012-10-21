<?php
if ( ISWIN )
	require_once APPPATH.'sys-conf-windows.php';
else
	require_once APPPATH.'linux-servers-configure.php';

class DbbakController extends DefaultController
{
	public function on_ls()
	{
		$dir = APPPATH."data/dbsqlbak/";
		!is_dir($dir) && @mkdir($dir, 0777);
		$ls = dir_list($dir);
		sort($ls);
		$this->view->assign('bak_path', $dir);
		$this->view->assign('ls', $ls);
		$this->view->display('header.html');
		$this->view->display('db/ls.html');
		$this->view->display('footer.html');
	}

	public function on_del($filename)
	{
		if ($filename == '') return;
		$file = APPPATH."data/dbsqlbak/".$filename;
		@unlink($file);
		!file_exists($file) && js_reload();
	}

	/**
	 * 数据备份
	 * 对license.xml，配置文件，数据库sql语句备份。
	 * @author zhangqitao
	 * $whencreated 2008-04-21
	 * $whenmodified 2008-8-5
	 */
	public function on_bak()
	{
		set_time_limit(3600);
		global $sysconfs;
		// 备份license文件，orignal-license.dat是3.3的原始备份文件，如果不存在，则备份license.xml文件，以此来兼容3.2
		// 特殊情况：license 在win,linux存的都是相对路径
		if (ISWIN)
		{
			$license_xml_path = '/Services/license/orignal-license.dat';
			if (!file_exists(INSTALLPATH.$license_xml_path)) $license_xml_path = '/Services/license/license.xml';
		}
		else
		{
			 $license_xml_path = '/ms/license/orignal-license.dat';
			 if (!file_exists(INSTALLPATH.$license_xml_path)) $license_xml_path = '/ring/conf/sclicense.dat';
		}
		$file = INSTALLPATH.$license_xml_path;
		ms_web_log("bak-license-file:".$file);
		$license = base64_encode(file_get_contents($file));
		$this->SysDB->db_set_cfg('license.xml', $license, $license_xml_path);
		foreach ($sysconfs as $key=>$val)
		{
			if (!$val['iscfg']) continue;
			// 配置文件使用绝对路径，在配置文件管理模块使用了绝对路径
			$file = $val['filename'];
			// 根据配置文件的绝对路径读取内容，
			$s = file_get_contents($file);
			// windows下数据库存相对路径， linux下数据库存绝对路径
			if(ISWIN) $file = substr($file, strlen(INSTALLPATH));
//			$s = iconv('gbk', 'utf-8', $s);
//			$s = str_replace("\\", "\\\\", $s);
			$s = base64_encode($s);
			$file_name = basename($file);
			$this->SysDB->db_set_cfg($file_name, $s, $file);
		}
		$time = mktime();
		$file = APPPATH."data/dbsqlbak/".$time.".gke";
		ms_web_log("bak-file:".$file);
		$sql = '';
		self::get_datasql_from_db($sql);
		if ($sql && file_put_contents($file, base64_encode($sql)))
		{
			ms_web_log("bak-success......");
			unset($sql);
			echo "备份成功！<img src='/images/check_right.gif'/>";
			$this->_writeLog("系统备份！");
			js_reload('',1000);
		}
		else echo "备份失败！<img src='/images/check_error.gif'/>";
	}

	/**
	 * 数据下载
	 *
	 * @author zhangqitao
	 * $whencreated 2008-07-23
	 */
	public function on_download()
	{
		$name = $_GET['name'];
		$file = APPPATH."data/dbsqlbak/".$name;
		@header("Content-Type: text/html; charset=utf-8");
		@header("Content-Type:application/x-msdownload");
		@header("Content-Disposition:".(strstr($_SERVER[TTP_USER_AGENT],"MSIE")?"":"attachment;")."filename=$name");
		@header("Content-Lengthfilesize");
		$content = file_get_contents($file);
		$this->_writeLog("下载备份数据[$name]！");
		echo $content;
	}

	/**
	 * 数据恢复
	 *
	 * @author zhangqitao
	 * $whencreated 2008-05-05
	 */
	public function on_resume()
	{
		set_time_limit(3600);
		$file = APPPATH."data/dbsqlbak/".$_GET['name'];
		$sqls = base64_decode(file_get_contents($file));

		if ($sqls == '') echo "db_resume_failed";
		$this->SysDB = $this->LoadDB('SysDB');

		foreach (array('msdb', 'smsgw') as $db_name)
		{
			$msdb_tables = $this->SysDB->db_get_tables($db_name);
			foreach ($msdb_tables as $table)
			{
				if ($table == 'monmsg') continue;
				$tables_in_db = 'Tables_in_'.$db_name;
				$this->dbh->exec("delete from $db_name.$table[$tables_in_db]");
			}
		}

		$sql_array = explode(";\r\n", $sqls);
		foreach ($sql_array as $sql) $this->dbh->exec($sql);
		// resume licensefile,  ms cfg file;
		$cfg = $this->SysDB->db_get_cfg();
		$i = 0;
		//停止服务，判断写入license.xml文件，在启动服务。
		foreach ($cfg as $val)
		{
			if (preg_match('/license/', $val['name']))
			{
				$var_file =INSTALLPATH.$val['file'];
				$license_xml = $val['value'];
			}
			else if (ISWIN) $var_file = INSTALLPATH.$val['file'];
			else
			{
				global $bak_files;
				$file_path = $bak_files[$val['name']];
				$var_file = $file_path.$val['name'];
			}
			ms_web_log("resume: $var_file");
			file_put_contents($var_file, base64_decode($val['value'])) && $i++;
		}
		$mon_svr = $this->InitMonService(MS_MONITOR, MS_MONITOR_PORT);
		self::import_license_from_db($license_xml) && $i++;
		if (ISWIN)
		{
			!$mon_svr->status_gkexpsvr() && $mon_svr->start_gkexpsvr();
		}
		else
		{
			$mon_svr->excute("service topgs restart && service gs restart && service ns restart && service scent restart && service gkexpsvr restart && service scproxy restart");
		}
		// goto import gid
		if ($i)
		{
			//发送组织变更消息，
			$this->notice->UgNotice($_SESSION['current_corp_id']);

			//发送用户权限下推消息
			$user_list = $this->UserDB->db_get_corp_active_users($_SESSION['current_corp_id']);  //有gid并且是活动用户
			if ($user_list && count($user_list) > 0)
			{
				$buddylist = "<buddylist>";
				foreach ($user_list as $user)
					$buddylist .= "<buddy gid='$user[gid]' zoneid='$user[zoneid]' gs='$user[gs]'/>";
				$buddylist .= "</buddylist>";
				$SynchNotice = $this->notice->SynchNotice($buddylist);
				$msg .= $this->notice->SynchNotice($buddylist) > 0 ? msg('发送用户信息同步消息成功！') : '' ;		 //发送用户信息同步消息成功
				$this->UserDB->upd_verinfo('clientpara');
				$msg .= $this->notice->ParaNotice($_SESSION['current_corp_id']) > 0 ? msg('下推参数成功！') : '' ;
				$msg .= $this->notice->PrivNotice($buddylist) > 0 ? msg('用户发送权限通知消息成功！', 0) : '' ;
			}

			//发送通讯录消息
			$this->ContactsDB = $this->LoadDB('ContactsDB');
			$contacts_arr = $this->ContactsDB->db_get_corp_all_contacts($_SESSION['current_corp_id']);
			if ($contacts_arr && count($contacts_arr) > 0)
			{
				foreach ($contacts_arr as $contacts)
				{
					$contacts_ids_arr[] = $contacts['contacts_id'];
				}
				if (!empty($contacts_ids_arr))
				{
					//先更新时间，再发送消息
					$this->ContactsDB->db_upd_mender_time($_SESSION['current_corp_id']);
					$msg .= $this->notice->ContactsNotice($contacts_ids_arr, $_SESSION['current_corp_id']) > 0 ? msg('通讯录更新成功！') : '';
				}
			}
			echo "db_resume_success";
		}
		else echo "import_license_failed";
	}

	/**
	 * 导入数据库中的license
	 *
	 * @author zhangqitao
	 * $whencreated 2008-06-30
	 */
	private function import_license_from_db($license_content='')
	{
		ms_web_log("dbbak-license-content:\t$license_content");
		if ($license_content == '') return;
		$file_name = mktime().'.xml';
		if (ISWIN)
		{
			$license_file = INSTALLPATH.'/uploads/license/'.$file_name;
			file_put_contents($license_file, base64_decode($license_content));
			$license_file = './uploads/license/'.$file_name;
		}
		else
		{
			$license_file = UPLOADSPATH.'/license/'.$file_name;
			file_put_contents($license_file, base64_decode($license_content));
		}
		ms_web_log("dbbak-license-file:\t$license_file");
		require_once 'LicenseController.php';
		$olicense = new LicenseController();
		if ($olicense->import_license_from_file($license_file))
			return true;
		return false;
	}

	/**
	 * 提取数据库中的sql语句
	 *
	 * @author zhangqitao
	 * $whencreated 2008-8-5
	 */
	private function get_datasql_from_db(&$sql)
	{
		$db_array = array('msdb', 'smsgw');
		$sql = '';
		foreach ($db_array as $db_name)
		{
			$msdb_tables = $this->SysDB->db_get_tables($db_name);
			foreach ($msdb_tables as $table)
			{
				$tables_in_db = 'Tables_in_'.$db_name;
				if ($table[$tables_in_db] == 'monmsg') continue;
				$table_datas = $this->SysDB->db_get_table_data($table[$tables_in_db], $db_name);
				if (count($table_datas) == 0) continue;
				ms_web_log("table_name:".$table[$tables_in_db]."------count:".count($table_datas)."------------");
				foreach ($table_datas as $data)
				{
					$s = '';
					foreach ($data as $row) $s .= "'".$row."',";
					$s = substr($s, 0, -1);
					$sql .= "insert into $db_name.$table[$tables_in_db] values($s);\r\n";
					unset($s);
				}
				ms_web_log($sql);
			}
		}
		ms_web_log("db-table..........return...sql : $sql");
	}

	/**
	 * 根据传入的数据库的名字来判断数据库是否存在。
	 */
	public function check_db_has($db_name)
	{
		$db_names = $this->SysDB->db_names();
		foreach($db_names as $names)
		{
			if (in_array($db_name, $names))
			{
				return true;
			}
		}
		return false;
	}
}
?>