<?php
class InitController extends DefaultController 
{
	private $is_utf8;
	private $type;
	private $corps_num = null;
	private $user_ug_ids;
    
    /**
	 * 显示导入界面
	 */
	public function on_show()
	{
		if (isset($_GET['basic']))
		{
			$this->view->assign('isbasic', 1);
		}
		$this->view->assign('isadmin', $this->IsAdmin());
		$this->view->display('header.html');
		$this->view->display('init/show.html');
		$this->view->display('footer.html');
	}

	/**
	 * 显示例子介绍
	 * 
	 * @author zhangqitao
	 * $whencreated 2008-05-30
	 */	
	public function on_example()
	{
		$id = $_GET['id'];
		$type = $_GET['type'];
		if ($id == 'xml')
		{
			ob_start();
			$this->view->assign('type', $type);
			$this->view->display('init/eg_xml.html');
			$xml = htmlspecialchars(ob_get_contents());
			ob_clean();
			echo "
				<link type='text/css' rel='stylesheet' href='/js/highlighter/highlighter.css'></link>
				<textarea name='code' class='xml' rows='30'>$xml</textarea>
				<script src='/js/highlighter/base.js'></script>
				<script src='/js/highlighter/xml.js'></script>
				<script>
					dp.SyntaxHighlighter.ClipboardSwf = '/js/highlighter/clipboard.swf';
					dp.SyntaxHighlighter.HighlightAll('code');			
				</script>
			";
		}
		else if ($id == 'csv-ug')
		{
			$this->view->assign('type', $type);
			$this->view->display('header.html');
			$this->view->display('init/eg_csv_ug.html');
			$this->view->display('footer.html');
		}
		else if ($id == 'csv-user')
		{
			$this->view->assign('type', $type);
			$this->view->display('header.html');
			$this->view->display('init/eg_csv_user.html');	
			$this->view->display('footer.html');
		}			
		else if ($id == 'txt-ug' || $id == 'txt-user' )
		{
			$this->view->assign('type', $type);
			$this->view->display('header.html');
			$this->view->display('init/eg_txt.html');	
			$this->view->display('footer.html');
		}
		else
		{
			echo "unknown!";
		}
	}    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
	
	
	
	/**
	 * 导入
	 * 
	 * @author zhangqitao
	 * $whencreated 2008-05-30
	 */	
	public function on_import()
	{
		set_time_limit(3600);
		ob_end_clean();
		ob_implicit_flush(true);
		if (!$_FILES)
		{
			$upload_max_size = get_cfg_var("upload_max_filesize") ? get_cfg_var("upload_max_filesize") : "";
			if ($upload_max_size)
				echo "导入文件大小必须在 0M-$upload_max_size 之间！";
			else 
				echo "当前系统设置禁止上传！";
			exit;
		}
		if ($_FILES['data']['size'] == 0)
		{
			echo "<strong>文件大小为0字节！</strong>";
			exit;			
		}
		$file = $_FILES['data']['tmp_name'];
		$content = file_get_contents($file);
		
		/* 文件编码如果是gbk 则转换为utf-8 */
		$this->is_utf8 = $content === mb_convert_encoding(mb_convert_encoding($content, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32') ? true : false; 		
		//echo $content.'aaaaa'.$this->is_utf8;
		if (!$this->is_utf8)
		{
			$content = iconv('gbk', 'utf-8', $content);
			file_put_contents($file, $content);	
		}
		
		$this->_writeLog("初始化数据导入！");
		$this->type = $_GET['type'];
		echo "<b><center>数据处理中，请耐心等待完成提示！</center></b></br><br/>";
		// -- 获取用户所在部门
		$this->user_ug_ids = array();
		/* 选择导入类型  */
		if ($_POST['import'] == 'xml')
		{
			if ($_FILES['data']['type'] != 'text/xml')
			{
				echo "<strong>导入文件不是xml文件！</strong>";
				exit;
			}
			$this->xml_import($content);
		}
		else if ($_POST['import'] == 'txt-ug') $this->csv_import_ug($content);
		else if ($_POST['import'] == 'txt-user') $this->csv_import_user($content);
		else if ($_POST['import'] == 'csv-ug') $this->csv_import_ug();
		else if ($_POST['import'] == 'csv-user') $this->csv_import_user();
		else 
		{
			echo "非法导入！";
			exit;			
		}
		// 修改部门时间mender_time
		$this->user_ug_ids = array_unique($this->user_ug_ids);
		$ug_ids = '';
		for($i=0; $i<count($this->user_ug_ids); $i++)
		{
			$ug_ids .= "'".$this->user_ug_ids[$i]."'";
			if (($i+1) != count($this->user_ug_ids))	$ug_ids .= ',';
		}
		$sql = "update uginfo set mender_time=now() where ug_id in ($ug_ids);";
		ms_web_log("ini-导入组织结构:\t$sql");
		$this->dbh->exec($sql);
		$this->notice->SyncAllNotice();
	}
	
	/**
	 * xml导入
	 *	
	 * @author zhangqitao
	 * $whencreated 2008-06-05
	 */
	private function xml_import($xml)
	{
		try 
		{
			$oxml = new SimpleXMLElement($xml);
		} 
		catch (Exception $e) 
		{
			if ($e) 
			{
				echo "错误的XML文件格式！";				
				exit;
			}
		}
		if (isset($oxml->ugs))
		{
			$ug_oxml = $oxml->ugs->ug;
			echo "<b>组织数据处理中......</b><br />";
			$i = 1;
			foreach ($ug_oxml as $val)
			{
				$ug = array();
				$attrs = $val->attributes();
			 	$ug['code'] = $attrs->code.'';
			 	$ug['parent_code'] = $attrs->parent_code.'';
			 	$ug['name'] = $attrs->name.'';
			 	$ug['sign'] = $attrs->sign.'';
			 	$ug['remark'] = $attrs->remark.'';
			 	$ug['url'] = $attrs->url.'';
			 	$ug['email'] = $attrs->email.'';
			 	$ug['location'] = intval($attrs->location.'');
		 		$this->import_ug($ug, $i);
			 	$i++;
			}
			echo "<b>组织数据处理完成！</b><br />";
		}
		if (isset($oxml->users))
		{
			$user_oxml = $oxml->users->user;
			$i = 1;
			echo "<b>用户数据处理中......</b><br />";
			foreach($user_oxml as $val)
			{
				$user = array();
				$attrs = $val->attributes();
				$user['account'] = $attrs->account."";
				$user['name'] = $attrs->name."";
				$user['display_name'] = $attrs->display_name."";
				$user['pwd'] = $attrs->pwd."";
				$user['ug_code'] = $attrs->ug_code."";
				$user['setgid'] = $attrs->setgid."";
				$user['state'] = $attrs->state."";
				$user['sex'] = $attrs->sex."";
				$user['remark'] = $attrs->remark."";
				$user['location'] = $attrs->location."";
				$user['position'] = $attrs->position."";
				$user['birthday'] = $attrs->birthday."";
				$user['mobile'] = $attrs->mobile."";
				$user['office_tel'] = $attrs->office_tel."";
				$user['fax'] = $attrs->fax."";
				$user['postcode'] = $attrs->postcode."";
				$user['webaddress'] = $attrs->webaddress."";
				$user['address'] = $attrs->address."";
				$user['email'] = $attrs->email."";
				$this->import_user($user, $i);
				$i++;
			}
			echo "<b>用户数据处理完成！</b><br />";
		}
	}
	
	/**
	 * 导入csv组织
	 *
	 * @author zhangqitao
	 * $whencreated 2008-06-05
	 */	
	private function csv_import_ug($content=null)
	{
		$this->read_csv($arr, $content);
		$i = 1;
		echo "<b>组织数据处理中......</b><br />";
		foreach ($arr as $val)
		{
			if (trim(join('', $val)) == '') continue;
			$ug = array();
		 	$ug['code'] = $val[0];
		 	$ug['parent_code'] = $val[1];
		 	$ug['name'] = $val[2];
		 	$ug['sign'] = $val[3];
		 	$ug['remark'] = $val[4];
		 	$ug['url'] = $val[5];
		 	$ug['email'] = $val[6];
		 	$ug['location'] = intval($val[7]);
	 		$this->import_ug($ug, $i);
			$i++;
		}
		echo "<b>组织数据处理完成！</b><br />";
	}
	
	/**
	 * 导入csv用户
	 *
	 * @author zhangqitao
	 * $whencreated 2008-06-05
	 */	
	private function csv_import_user($content=null)
	{
		$this->read_csv($arr, $content);
		$i = 1;
		echo "<b>用户数据处理中......</b><br />";
		foreach ($arr as $val)
		{
			if (trim(join('', $val)) == '') continue;
			$user = array();
			$user['account'] = $val[0];
			$user['name'] = $val[1];
			$user['display_name'] = $val[2];
			$user['pwd'] = $val[3];
			$user['ug_code'] = $val[4];
			$user['setgid'] = $val[5];
			$user['state'] = $val[6];
			$user['sex'] = $val[7];
			$user['remark'] = $val[8];
			$user['location'] = intval($val[9]);
			$user['position'] = $val[10];
			$user['birthday'] = $val[11];
			$user['mobile'] = $val[12];
			$user['office_tel'] = $val[13];
			$user['fax'] = $val[14];
			$user['postcode'] = $val[15];
			$user['webaddress'] = $val[16];
			$user['address'] = $val[17];
			$user['email'] = $val[18];
			
			$this->import_user($user, $i);
			$i++;
		}
		echo "<b>用户数据处理完成！</b><br />";
	}

	/**
	 * 导入组织
	 *
	 * @author zhangqitao
	 * $whencreated 2008-06-05
	 * $whenmodified 2008-07-30
	 */		
	private function import_ug($ug=array(),$i=1)	
	{
		$notice = "Ug:: Record $i, ";
		if (empty($ug))
		{
			echo "Ug:: record $i is empty data.<br />";
			return;
		}
		if ($ug['name'] == '')
		{
			echo "$notice Name could not be empty.<br />";
			return;
		}
		// 指定组织code检查
		if ($ug['code'] == '')
		{
			echo "$notice Code could not be empty.<br />";
			return;
		}
		$tmp = array();
		$tmp = $this->InitDB->db_get_ug_by_code($ug['code']);
		if (!empty($tmp))
		{
			echo "$notice The code[$ug[code]] already exists.<br />";
			return;			
		}
		
		// 父组织code检查，不能为空且必须存在，否则返回
		if ($ug['parent_code'] == '')
		{
			echo "$notice parent_code could not be empty.<br />";
			return;
		}
		$pug = array();
		$pug = $this->InitDB->db_get_ug_by_code($ug['parent_code']);
		if (empty($pug))
		{
			echo "$notice parent_code[$ug[parent_code]] does not exist.<br />";
			return;
		}
		
		// check sign
		if (!in_array($ug['sign'], array('0', '1')))
		{
			echo "$notice sign must be '0' or '1'. <br />";
			return;
		}
				
		 // --------------------  当前单位内导入的限制（与系统内导入不同限制处）
		 if ($this->type == 'corp')
		 {
		 	if ($ug['sign'] == '0')
		 	{ // 当前单位内不准许导入子单位
			 	echo "$notice you could not import sub corporation in current corporation.<br />";
			 	return;
		 	}
		 	else
		 	{ // 检查父组织是否是当前单位内的
				$current_corp_children = $this->GetPosterity($_SESSION['current_corp_id'], '1');
				$children_id_arr = get_id_from_array($current_corp_children);
				array_push($children_id_arr, $_SESSION['current_corp_id']);
				if (!in_array($pug['ug_id'], $children_id_arr))
				{
					echo "$notice parent ug does exist in current corporation!<br />";
					return;
				}
		 	}
		 }
		 // --------------------
		 
		// check sign
		if ($ug['sign'] == '0' && $pug['sign'] == '1')
		{
			echo "$notice corporation's parent could not be department.<br />";
			return;
		}
		
		//check name
		if ($this->InitDB->db_check_name_at_one_parent($ug['name'], $pug['ug_id']))
		{
			echo "$notice the name [$ug[name]] aleady exists at the same parent group.<br />";
			return;
		}
		
		$ug['ug_id'] = md5($ug['code'].$ug['parent_code']);
		$ug['parent_id'] = $pug['ug_id'];
		$ug['creator_account'] = 'admin';
		$ug['mender_acccount'] = 'admin';
		
		if ($this->InitDB->db_add_ug($ug) == 0)
		{
			echo "$notice add ug failed.";
			return;
		}
		if ($ug['sign'] == '1') return;
		// add admin's priv
		$admin = $this->InitDB->db_get_admin_by_account('admin');
		$corps_id = str_explode_deep($admin['attach_ug_id'], ':');
		if (!in_array($ug['ug_id'], $corps_id))
		{
			$admin['attach_ug_id'] = $admin['attach_ug_id'].$ug['ug_id'].":";
			$this->InitDB->db_upd_admin_priv($admin['admin_id'], $admin['attach_ug_id']);
		}				
		// add default role
		$role['role_id'] = $ug['ug_id'];
		$role['ug_id'] = $ug['ug_id'];
		$role['name'] = '默认角色';
		$role['parent_id'] = $ug['ug_id'];
		$role['creator_account'] = 'admin';
		$role['mender_account'] = 'admin';
		$role['remark'] = '默认角色';
		$role['location'] = $i;
		$this->InitDB->db_add_role($role);
		// add role's default priv
		$privs_id = array('4','5','7','8','9','10','11','12','16',);
		foreach ($privs_id as $priv_id)
		{
			$priv = $this->InitDB->db_get_priv($priv_id);
			$this->InitDB->db_add_priv_to_role($role['role_id'], $priv);
		}
		// add verinfo channel
		$this->InitDB->db_add_verinfo($ug['ug_id']);
		$this->InitDB->upd_verinfo('ug');
		return true;		 
	}
	
	/**
	 * 导入用户
	 *
	 * @author zhangqitao
	 * $whencreated 2008-06-05
	 * $whenmodified 2008-07-30
	 */		
	private function import_user($user=array(), $i=1)
	{	
		$notice = "User:: Record $i, ";
		if (empty($user))
		{
			echo "User:: record $i is empty data.<br />";
			return false;
		}
		if ($user['account'] == '')
		{
			echo "$notice the account[$user[account]] could not be empty!<br />";
			return false;			
		}
		if ($user['pwd'] == '')
		{
			echo "$notice Password could not be empty.<br />";
			return;
		}				
		// check account
		if ($this->InitDB->db_check_account($user['account']))
		{
			echo "$notice the account[$user[account]] aleady exists.<br />";
			return false;
		}		

		// 系统内导入用户 ug_code为空提示
		if ($this->type == 'sys')
		{
			if ($this->corps_num == null)
				$this->corps_num = $this->InitDB->db_get_corps_num();
			// ug_code 检查
			if ($user['ug_code'] == '' && $this->corps_num > 1)
			{
				echo "$notice 'ug_code' could not be empty, the current system has {$this->corps_num} corporation.";
				return false;
			}
		}
		$ug_codes_arr = array();
		$ug_codes_arr = str_explode_deep($user['ug_code'], '|');
		
		$corps = array();
		$depts = array();
		$j = $k = 0;
		foreach ($ug_codes_arr as $key=>$val)
		{
			$tmp = $this->InitDB->db_get_ug_by_code($val);
			if (!empty($tmp))
			{
				if ($tmp['sign'] == '1')
				{
					$depts[$j] = $tmp;
					$j++;
					continue;
				}
				$corps[$k] = $tmp;
				$k++;
			}
		}
		$corps_num = $k;
		// 用户所在单位id
		if ($this->type == 'corp')
			$user['ug_id'] = $_SESSION['current_corp_id'];
		if ($this->type == 'sys')
		{
			if ($corps_num > 1)
			{
				echo "$notice the user could not has one more corpration.";
				return;
			}
			else if ($corps_num == 0)
				$user['ug_id'] = $_SESSION['current_corp_id'];
			else
				$user['ug_id'] = $corps[0]['ug_id'];
		}
		// 用户的部门
		$corp_depts_arr = $this->GetPosterity($user['ug_id'], '1');
		$corp_depts_id_arr = get_id_from_array($corp_depts_arr, 'ug_id');		
		$depts_id_arr = get_id_from_array($depts, 'ug_id');
		$user_depts_id = array_intersect($depts_id_arr, $corp_depts_id_arr);	
		// --------------------------------------------------------------------------
		$user['pwd'] = getpass(strtoupper(md5($user['pwd'])));
		$user['user_id'] = uuid();
		// 自动修改用户不合法的属性
		$user['name'] = $user['name'] == '' ? $user['account'] : $user['name'];
		$user['display_name'] = $user['display_name'] == '' ? $user['account'] : $user['display_name'];
		$user['setgid'] = $user['setgid']=='0' ? '0' : '1';
		$user['state'] = $user['state']=='0' ? '0' : '1';
		if ($user['sex'] != '1' && $user['sex'] != '2') $user['sex'] = '0';
		//echo "<pre>"; print_r($user);
		$this->InitDB->db_add_user($user);
		if (!empty($user_depts_id))
		{
			foreach ($user_depts_id as $dept_id)
			{
				$this->user_ug_ids[] = $dept_id;
				$this->InitDB->db_add_user_dept($user['user_id'], $dept_id);
			}
		}		
		// get gid
		$gid_arr = array();
		if ($user['setgid'] == '1') $gid_arr = $this->GetOneUnusedGid();
		if (empty($gid_arr)) return true;
//		$this->InitDB->db_add_user($user);
		$this->InitDB->db_bind_user_with_gid($user['user_id'], $gid_arr, 'admin');
		$this->InitDB->db_bind_gid_with_mobile($user['mobile'], $gid_arr, 'admin');
		$this->InitDB->db_add_mobile_smsgw($user['mobile'], $gid_arr);
		$this->InitDB->db_add_default_role_for_user($user['user_id'], $user['ug_id']);
		$this->InitDB->upd_verinfo('clientpara');
		$this->InitDB->db_re_user_ver($gid_arr);
		/*$buddylist = "<buddylist>";
		$buddylist .= "<buddy gid='$gid_arr[gid]' zoneid='$gid_arr[zoneid]' gs='$gid_arr[gs]'/>";
		$buddylist .= "</buddylist>";
		if ($this->notice->SynchNotice($buddylist)>0)
		{
			$this->notice->ParaNotice($_SESSION['current_corp_id']);
			$this->notice->PrivNotice($buddylist);
			if (!empty($user_depts_id))
			{
				$contacts_arr = $this->ContactsDB->db_get_corp_all_contacts($user['ug_id']);
				foreach ($contacts_arr as $contacts)
					$contacts_ids_arr[] = $contacts['contacts_id'];
				if (!empty($contacts_ids_arr))
				{
					$this->ContactsDB->db_upd_mender_time($user['ug_id']);
					$this->notice->ContactsNotice($contacts_ids_arr, $user['ug_id']);
				}				
			}
		}*/
		return true;	
	}
	
	/**
	 * 读取csv
	 *
	 * @param array $arr
	 * @author zhangqitao
	 * $whencreated 2008-06-05
	 */
	private function read_csv(&$arr = array(), $content=null)
	{
		// -- qiongtao
		if ($content != null)
		{
			$rows = explode("\n", $content);			
			foreach($rows as $row)
			{
				$vs = explode(",", $row);
				$arr_row = array();
				foreach($vs as $v)
				{
					$arr_row[] = $v;
				}
				if ($arr_row) $arr[] = $arr_row;
			}
			return;
		}

		if ($_FILES['data']['type'] != 'application/vnd.ms-excel' && $_FILES['data']['type'] != 'text/plain')
		{
			echo "<strong>Error: It's not csv file.</strong>";
			exit;
		}
		$i = 0;
		$row = 1;
		$handle = fopen($_FILES['data']['tmp_name'],"r");
		while ($data = fgetcsv($handle, 4096, ",")) 
		{
		    $num = count($data);
		    $row++;
		    for ($c=0; $c < $num; $c++) 
		    {
					$arr[$i][$c] = $data[$c];
		    }
		    $i++;
		}
		fclose($handle);
	}

	public function on_mk_eg()
	{
		$xml = "<?xml version='1.0' encoding='utf-8'?>\r\n";
		$xml .= "<root>\r\n";
		$xml .= "	<ugs>\r\n";
		
		// 建立当前单位的部门
		for ($i=1; $i<=30; $i++)
		{
			$location = $i;
			$xml .= "		<ug code='$i' parent_code='0' name='测试部门$i' sign='1' remark='测试一级部门$i' email='test@dianji.com' url='' location='{$location}' />\r\n";
			// 当前单位下的人员，分配到新建的部门
			$s .= "		<user account='testuser$i' name='testuser$i' display_name='testuser$i' pwd='123456' ug_code='0|$i' setgid='1' state='1' sex='0' remark='备注' location='10' position='普通员工' birthday='1979-11-11' mobile='13439009377' office_tel='010-82151080' fax='010-82151080' postcode='100084' webaddress='www.dianji.com' address='' email='qitao@dianji.com' />\r\n";
			for ($j=1; $j<=3; $j++)
			{
				// 建立用户
				$location = $i.$j;
				$xml .= "		<ug code='{$i}_{$j}' parent_code='$i' name='测试部门{$i}_{$j}' sign='1' remark='测试二级部门{$i}_{$j}' email='test@dianji.com' url='' location='$location' />\r\n";	
				for ($m=1; $m<=2; $m++)
				{
					$s .= "		<user account='testuser{$m}_{$i}_{$j}' name='testuser{$m}_{$i}_{$j}' display_name='testuser{$m}_{$i}_{$j}' pwd='123456' ug_code='0|{$i}_{$j}' setgid='1' state='1' sex='0' remark='备注' location='10' position='普通员工' birthday='1979-11-11' mobile='13439009377' office_tel='010-82151080' fax='010-82151080' postcode='100084' webaddress='www.dianji.com' address='' email='qitao@dianji.com' />\r\n";
				}
				for ($k=1; $k<=5; $k++)
				{
					$location = $i.$j.$k;
					$xml .= "		<ug code='{$i}_{$j}_{$k}' parent_code='{$i}_{$j}' name='测试部门{$i}_{$j}_{$k}' sign='1' remark='测试三级部门{$i}_{$j}_{$k}' email='test@dianji.com' url='' location='$location' />\r\n";		
					for ($n=1; $n<=5; $n++)
					{
//						$s .= "		<user account='testuser{$n}_{$i}_{$j}_{$k}' name='testuser{$n}_{$i}_{$j}_{$k}' display_name='testuser{$n}_{$i}_{$j}_{$k}' pwd='123456' ug_code='0|{$i}_{$j}_{$k}' setgid='1' state='1' sex='1' remark='备注' location='10' position='普通员工' birthday='1979-11-11' mobile='13439009377' office_tel='010-82151080' fax='010-82151080' postcode='100084' webaddress='www.dianji.com' address='' email='qitao@dianji.com' />\r\n";
					}
				}
			}
		}
		
		// 建立子单位，子部门
		for ($i=1; $i<=20; $i++)
		{
			$location = $i;
			$xml .= "		<ug code='corp_{$i}' parent_code='0' name='测试子单位$i' sign='0' remark='测试子单位$i' email='test@dianji.com' url='' location='{$location}' />\r\n";
			for ($j=1; $j<1; $j++)
			{
				$location = $i.$j;
				$xml .= "		<ug code='dept_{$i}_{$j}' parent_code='corp_{$i}' name='测试子单位部门{$i}_{$j}' sign='1' remark='测试子单位{$i}_{$j}' email='test@dianji.com' url='' location='{$location}' />\r\n";
			}
		}
		$xml .= "	</ugs>\r\n";
		
		// connect user xml
		$xml .= "	<users>\r\n";
		$xml .= $s;
		$xml .= "	</users>\r\n";
		$xml .="</root>";
//		file_put_contents('d:/import.xml', $xml);
	}
}
?>