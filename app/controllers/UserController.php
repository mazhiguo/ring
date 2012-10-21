<?php
class UserController extends DefaultController
{
	public function on_index()
	{
		$this->view->display('header.html');
		$this->view->display('user/index.html');
		$this->view->display('footer.html');
	}

	public function on_show_tree()
	{
		$this->view->display('user/tree.html');
	}

	/**
	 * 显示用户列表
	 */
	public function on_list()
	{
		/*
		 *	$_SESSION['active_ug_id'] 记录在user操作当前显示的部门的 ug_id
		 *	$_SESSION['view_type'] 当前的查看方式 1 全部用户 2 活动用户 3 不活动用户 4 无GID用户 5 未分配单位用户
		 */

		//初始化
        if (!isset($_GET['ug_id']))
        {
            $_SESSION['active_ug_id'] = isset($_SESSION['active_ug_id']) ?  $_SESSION['active_ug_id'] : '0';
        }
		elseif ($_GET['ug_id'] == '')//从tree传过来的当前单位或部门的 ug_id
			$_SESSION['active_ug_id'] = isset($_SESSION['active_ug_id']) ?  $_SESSION['active_ug_id'] : '0';
		elseif ($_SESSION['active_ug_id'] != $_GET['ug_id'])
		{
			$_SESSION['active_ug_id'] = $_GET['ug_id'];
			$_SESSION['view_type']=1;
		}

		//user_view_action 查看方式
		if (!isset($_GET['view_type']))
			$_SESSION['view_type'] = isset($_SESSION['view_type']) ? $_SESSION['view_type'] : '1';
		else
			$_SESSION['view_type'] = $_GET['view_type'];

		$active_ug_id = $_SESSION['active_ug_id'];
		//由以前用session来存储ug_name 改为 通过ug_id来时时查询ug_name
		$this->UserDB = $this->LoadDB('UserDB');
		$ug_info = $this->UserDB->db_get_ug_by_ug_id($active_ug_id);
		$active_ug_name = $ug_info['name'];
		$view_type = $_SESSION['view_type'];

		$selected = array();
		$selected['s'.$view_type] = 'selected';

		$limit = !empty($_SESSION['user_limit']) ? $_SESSION['user_limit'] : 20 ;
		$in_url = "/user/list/view_type/$view_type/ug_id/$active_ug_id/";

		if ($active_ug_id == '0')//当前单位下人员查看
		{
			if ($view_type == '1')//查看单位下所有
			{
				$total_recode = $this->UserDB->db_count_all_users();
				$total_pages = ceil($total_recode/$limit);
				$curr_page = isset($_GET['page']) ? $_GET['page'] : 1;
				$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
				$offset = ($curr_page-1)*$limit;
				$user_list = $this->UserDB->db_get_all_users($limit, $offset);
			}
			else
			{
				if ($view_type == '2')//活动用户
					$user_list = $this->UserDB->db_get_corp_active_users();
				else if ($view_type == '3')//不活动用户
					$user_list = $this->UserDB->db_get_corp_unactive_users();
				else if ($view_type == '4')//无部门
					$user_list = $this->UserDB->db_get_nodept_users();
            
				$total_recode = count($user_list);
				$total_pages = ceil($total_recode/$limit);
				$curr_page = isset($_GET['page']) ? $_GET['page'] : 1;
				$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
				$offset = ($curr_page-1)*$limit;
				$user_list = @array_slice($user_list, $offset, $limit);
			}
		}else
		{
            // 当前部门下人员查看
			// 得到部门的ID，拼成string
            
			if (isset($_GET['offspring']) && $_GET['offspring'] == 'y')
			{
				$this->view->assign('checked', 'checked');
                $current_depts =  $this->GetPosterity($active_ug_id, '1');//找后代
				if (empty($current_depts))//没有后代
				{
                    $ug_ids_str = "'".$active_ug_id."'";
				}
				// 有后代
				else
				{
                    $ug_ids[0] = $active_ug_id;
					foreach ($current_depts as $v)
                        $ug_ids[] = $v['ug_id'];
					$ug_ids_str = "'".implode("','", $ug_ids)."'";
					$in_url = "/user/user_list/view_type/$view_type/ug_id/$active_ug_id/offspring/y/"; //重置url
				}
			}
			else
			{
			     // 仅显示当前部门下的用户
				 $ug_ids_str = "'".$active_ug_id."'";
			}

			// 根据部门的id取用户
			if ($view_type == '1')
				$user_list = $this->UserDB->db_get_dept_all_users($ug_ids_str);
			else if ($view_type == '2')
				$user_list = $this->UserDB->db_get_dept_active_users($ug_ids_str);
			else if ($view_type == '3')
				$user_list = $this->UserDB->db_get_dept_unactive_users($ug_ids_str);

			$total_recode = count($user_list);
			$total_pages = ceil($total_recode/$limit);
			$curr_page = isset($_GET['page']) ? $_GET['page'] : 1;
			$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
			$offset = ($curr_page-1)*$limit;

			$user_list = array_slice($user_list, $offset, $limit);
		}

		$out_url = $this->Page($total_recode, $limit, $curr_page, $in_url);
        $is_hd = $active_ug_id == '0' ? '0' : $this->UserDB->db_get_sub_dept_count($active_ug_id);

		$option_str_limit = make_option_string($limit);
		$this->view->assign('option_str_limit', $option_str_limit);
		$this->view->assign('selected', $selected);
		$this->view->assign('active_ug_id', $active_ug_id);
		$this->view->assign('cur_ug_name', $active_ug_name);
		$this->view->assign('pages', $out_url);
		$this->view->assign('user_list', $user_list);
		$this->view->assign('debug_time', debug_time());
		$this->view->assign('is_hd', $is_hd);
		$this->view->display('header.html');
		$this->view->display('user/list.html');
		$this->view->display('footer.html');
	}

	/**
	 * 用户帐号检查是否已存在
	 */
	public function on_check_user_account()
	{
		$account = $_POST['account'];
		// 用户帐号检查
		$this->UserDB = $this->LoadDB('UserDB');
		if ($this->UserDB->db_check_user_account($account) >0)
		{
			echo "<script>user_account_flag=false</script>";
			echo "<img src='/images/check_error.gif'> 用户账号已存在!";
		}
		else
		{
			echo "<script>user_account_flag=true</script>";
			echo "<img src='/images/check_right.gif'>";
		}
	}

	/**
	 * 显示增加用户
	 */
	public function on_show_add($user)
	{
		if ($user != '' && $user['back'] == 'back')
		{
			$this->view->assign('user', $user);
		}
		$this->view->assign('active_ug_id', $_SESSION['active_ug_id']);
		$ug_info = $this->UgDB->db_get_ug_by_ug_id($_SESSION['active_ug_id']);
		$this->view->assign('active_ug_name', $ug_info['name']);
        $this->UserDB = $this->LoadDB('UserDB');
        $roles = $this->UserDB->db_get_all_roles();
        $tmp_arr = $this->UserDB->db_get_ug_role_ids($_SESSION['active_ug_id']);
        $dept_roles = array();
        foreach ($tmp_arr as $role_id) $dept_roles[] = $role_id['role_id'];
        $check_str_role = "";
        foreach($roles as $role)
        {
            $checked = in_array($role['role_id'], $dept_roles) ? 'checked' : '';
            $check_str_role .= "<input type='checkbox' name='roles[]' value ='$role[role_id]' $checked >$role[name]&nbsp;";
        }

		$this->view->assign('creator_account', $_SESSION['AdminAccount']);
		$this->view->assign('creator_time', date('Y-m-d'));
        $this->view->assign('check_str_role', $check_str_role);
		$this->view->display('header.html');
		$this->view->display('user/add.html');
		$this->view->display('footer.html');
	}
    
	/**
	 * 增加用户
	 */
	public function on_add()
	{
		$this->UserDB = $this->LoadDB('UserDB');
		$user['account'] = $_POST['account'];
		$user['user_id'] = uuid();
		$user['name'] = $_POST['name'];
		$pwd = strtoupper(md5($_POST['password']));
		$user['pwd'] = getpass($pwd);
		$user['sex'] = $_POST['sex'];
		$user['office_tel'] = $_POST['office_tel'];
		$user['remark'] = $_POST['remark'];
		$user['email'] = $_POST['email'];
		$user['creator_account'] = $_SESSION['AdminAccount'];
		$user['mender_account'] = $_SESSION['AdminAccount'];
		$user['mobile'] = $_POST['mobile'];
        $user['position'] = $_POST['position'];
		$user['location'] = trim($_POST['location']) != '' ? intval($_POST['location']) : $this->UserDB->db_get_max_location()+1;
		$user['state'] =  $_POST['state'];
        $user['ug_id'] = $_POST['parent_id'];

		// 用户帐户检查
		if ($this->UserDB->db_check_user_account($user['account']) >0)
		{
			$this->show_msg_ext('BACK', array('msg'=>'用户帐户已经存在！'));
			exit;
		}

		// 增加用户 增加到userinfo表中,表中ug_id指该用户的单位
        $msg = "";
		if ($this->UserDB->db_add_user($user) >0 )
		{
			$msg .= msg('增加用户至单位成功！');
			$this->_writeLog("增加用户[$user[name]]成功！");
		}
		else
		{
			$this->show_msg_ext('BACK', array('msg'=>'增加用户至单位失败！'));
			exit;
		}
        $rule_ids_arr = $_POST['roles'];
        $n = 0;
        foreach ($rule_ids_arr as $rule_id)
        {
            $n += $this->UserDB->db_add_user_role($user['user_id'], $rule_id);
        }
        $msg .= $n>0 ? msg("分配了{$n}个角色！") : '';
        
		$this->show_msg_ext('GOTO', array('url'=>'/user/list/', 'msg'=>$msg, 'timeout'=>5000));

	}

	/**
	 * 显示编辑用户界面
	 */
	public function on_show_upd()
	{
		$this->UserDB = $this->LoadDB('UserDB'); 
		$user_id = $_GET['user_id'];
		$user = $this->UserDB->db_get_user($user_id);

        $roles = $this->UserDB->db_get_all_roles();
        $tmp_arr = $this->UserDB->db_get_user_role_ids($user_id);
        $user_roles = array();
        foreach ($tmp_arr as $role_id) $user_roles[] = $role_id['role_id'];
        $check_str_role = "";
        foreach($roles as $role)
        {
            $checked = in_array($role['role_id'], $user_roles) ? 'checked' : '';
            $check_str_role .= "<input type='checkbox' name='roles[]' value ='$role[role_id]' $checked >$role[name]&nbsp;";
        }

        $this->view->assign('check_str_role', $check_str_role);
		$this->view->assign('user', $user);
		$this->view->display('header.html');
		$this->view->display('user/upd.html');
		$this->view->display('footer.html');
	}
    
	/**
	 * 编辑用户
	 */
	public function on_upd()
	{
		$this->UserDB = $this->LoadDB('UserDB');
		$user['account'] = $_POST['account'];
		$user['name'] = $_POST['name'];
		$user['sex'] = $_POST['sex'];
		$user['office_tel'] = $_POST['office_tel'];
		$user['position'] = $_POST['position'];
		$user['remark'] = $_POST['remark'];
		$user['email'] = $_POST['email'];
		$user['mender_account'] = $_SESSION['AdminAccount'];
		$user['mobile'] = $_POST['mobile'];
		$user['user_id'] = $_POST['user_id'];
		$user['location'] = trim($_POST['location']) != '' ? intval($_POST['location']) : $this->UserDB->db_get_max_location()+1;
		$user['state'] =  $_POST['state'];
        $user['ug_id'] = $_POST['parent_id'];

		// 准许修改帐户，检查帐户，除了自己是否还有同帐户的。
		if ($this->UserDB->db_check_user_account_expect_self($user['account'], $user['user_id']) >0 )
		{
			$this->show_msg_ext('BACK', array('msg'=>'系统内已存在相同的帐户的用户！'));
			exit;
		}

		// 编辑用户基本信息
		if ($this->UserDB->db_upd_user($user) <= 0)
		{
			$this->show_msg_ext('BACK', array('msg'=>'编辑用户信息失败！'));
			exit;
		}else
            $msg = msg('编辑用户信息成功！');
        
        $rule_ids_arr = $_POST['roles'];
        $this->UserDB->db_del_user_role($user['user_id']);
        $n = 0;
        foreach ($rule_ids_arr as $rule_id)
        {
            $n += $this->UserDB->db_add_user_role($user['user_id'], $rule_id);
        }
        $msg .= $n>0 ? msg("分配了{$n}个角色！") : '';

		$this->_writeLog("编辑用户[$user[name]]成功！");
		$this->show_msg_ext('GOTO', array('url'=>'/user/list/', 'msg'=>$msg, 'timeout'=>5000));
	}

	/**
	 * 删除用户
	 */
	public function on_del()
	{
		$this->UserDB = $this->LoadDB('UserDB');

		$users_arr = $_POST['del'];
		$user_num = count($users_arr);
		if (empty($users_arr))
		{
			$this->show_msg_ext('BACK', array('msg'=>'没有选中删除的用户！'));
			exit;
		}
        $record_num = 0;
		foreach ($users_arr as $user_id)
		{
			$record_num += $this->UserDB->db_del_user($user_id);
		}
        $msg = "";
		if ($record_num > 0)
		{
			$msg .= msg("共删除{$user_num}个用户！");
			$msg .= msg("共删除{$record_num}条相关记录！");
		}

		$this->show_msg_ext('GOTO', array('url'=>'/user/list', 'msg'=>$msg));
		$this->_writeLog("删除用户成功！");
	}

	/**
	 * 重置用户密码
	 */
	public function on_reset_pwd()
	{
		$this->UserDB = $this->LoadDB('UserDB');
		if ($_POST)
		{
			$user_id = $_POST['user_id'];
			$password = $_POST['password'];
			$repassword = $_POST['repassword'];
			if ($password != $repassword)
			{
				$this->show_msg_ext('BACK', array('msg'=>'两次密码输入不一致'));
				exit;
			}
			$pwd = getpass(strtoupper(md5($password)));
            $msg = "";
			if ($this->UserDB->db_reset_pwd($user_id, $pwd) > 0)
			{
				$msg .= msg('修改用户密码成功！');
				$msg .= msg("请记住新密码：<strong>{$password}</strong>");

				$this->show_msg_ext('GOTO', array('msg'=>$msg, 'url'=>'/user/list'));
				$this->_writeLog("重置用户密码成功！");
			}else
				$this->show_msg_ext('GOTO', array('msg'=>'用户密码未变更！', 'url'=>'/user/list'));
		}
		else
		{
			$user = $this->UserDB->db_get_user($_GET['user_id']);
			$this->view->assign('user', $user);
			$this->view->display('header.html');
			$this->view->display('user/reset_pwd.html');
			$this->view->display('footer.html');
		}
	}

	/**
	 * 用户模糊搜索
	 */
	public function on_search()
	{
		$content = trim($_GET['content']);
		$this->UserDB = $this->LoadDB('UserDB');
		$user_list = $this->UserDB->db_search($content);

		$total_recode = count($user_list);
		$limit = !empty($_SESSION['user_limit']) ? $_SESSION['user_limit'] : 20 ;
		$total_pages = ceil($total_recode/$limit);
		$curr_page = isset($_GET['page']) ? max(intval($_GET['page']), 1) : 1;
		$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
		$offset = ($curr_page-1)*$limit;
		$in_url = $content == '' ? "/user/search/" : "/user/search/content/$content/";
		$out_url = $this->Page($total_recode, $limit, $curr_page, $in_url);

		if ($total_recode > $limit)
		{
			$user_list = array_slice($user_list, $offset, $limit);
		}

		$option_str = make_option_string($limit);
		$this->view->assign('search_content', $content);
		$this->view->assign('option_str_limit', $option_str);
		$this->view->assign('pages', $out_url);
		$this->view->assign('is_search', 1);
		$this->view->assign('user_list', $user_list);
		$this->view->display('header.html');
		$this->view->display('user/list.html');
		$this->view->display('footer.html');
	}









	/**
	 * 显示增加用户编辑用户，部门树（弹出窗口）
	 *
	 * @author zhangqitao
	 * $whencreated 2008-08-14
	 */
	public function on_show_user_ug_tree()
	{
		// add
		if (isset($_GET['active_ug_id']))
		{
			$this->view->assign('active_ug_id', $_GET['active_ug_id']);
		}
		else if(isset($_GET['user_id']))
		{
			$this->view->assign('user_id', $_GET['user_id']);
		}
		$this->view->assign('current_corp_id', $_SESSION['current_corp_id']);
		$this->view->display('header.html');
		$this->view->display('user/user_ug_tree.html');
		$this->view->display('footer.html');
	}

	/**
	 * 分配GID，请求到G剩余的GID列表
	 *
	 * @author zhangqitao
	 * $whencreated 2007-12-25
	 * $whenmodifed 2008-07-23
	 */
	public function on_get_unused_gid()
	{
		$this->UserDB = $this->LoadDB('UserDB');
		// 是否还能使用gid的判断
		$licenseinfo = $this->GetLicenseinfo();
		if (empty($licenseinfo))
		{
			echo "当前系统还没有license，请申请license！";
			exit;
		}
		$canbeusednum = $licenseinfo['activenum'];
		$usednum = $this->UserDB->db_count_used_gid();
		if ($canbeusednum <= $usednum)
		{
			$this->view->assign('canbeusednum' ,$canbeusednum);
			$this->view->assign('usednum' ,$usednum);
		}
		else
		{
			$curr_page = max(intval($_GET['page']), 1);
			$limit = 50;
			$offset = ($curr_page-1)*$limit;
			$in_url = "/user/get_unused_gid/";
			$total_recode = $this->UserDB->db_count_unused_gid();
			$out_url = $this->Page($total_recode, $limit, $curr_page, $in_url, 'get_unused_gid');
			$gid_list = $this->UserDB->db_get_unused_gid($limit, $offset);
			$this->view->assign('pages', $out_url);
			$this->view->assign('total_num', $total_recode);
			$this->view->assign('gid_list', $gid_list);
		}
		$this->view->display('user/unused_gid.html');
	}

	/**
	 * 批量分配用户到某个部门
	 *
	 * @2007-12-26 by zhangqitao
	 */
	public function on_set_batch_dept()
	{
		if ($_POST['dept_ids'] == '0' || empty($_POST['dept_ids']))
		{
			echo '请选择部门！';
			exit;
		}
		else if (empty($_POST['user_ids']))
		{
			echo '请选择用户！';
			exit;
		}
		$this->UserDB = $this->LoadDB('UserDB');
		$this->ContactsDB = $this->LoadDB('ContactsDB');

		$user_ids_arr = array();
		$user_ids_arr = explode(':', $_POST['user_ids']);
		array_pop($user_ids_arr);
		$user_ids_arr = array_unique($user_ids_arr);
		$dept_ids_arr = array_unique(explode(',', $_POST['dept_ids']));
		foreach ($user_ids_arr as $user_id)
		{
			if ($user_id != '' && $dept_ids_arr)
			{
				foreach($dept_ids_arr as $dept_id)
				{
					if ($dept_id != $_SESSION['current_corp_id'])
						$this->UserDB->db_add_user_dept($user_id, $dept_id);
				}
			}
			else
			{
				echo 'unknown error';
				exit;
			}
		}
		//如果有分配部门且有GID，发送通讯录消息
		$contacts_arr = $this->ContactsDB->db_get_corp_all_contacts($_SESSION['current_corp_id']);
		foreach ($contacts_arr as $contacts)
		{
			$contacts_ids_arr[] = $contacts['contacts_id'];
		}
		if (!empty($contacts_ids_arr))
		{
			$this->ContactsDB->db_upd_mender_time($_SESSION['current_corp_id']);
			$this->notice->ContactsNotice($contacts_ids_arr, $_SESSION['current_corp_id']);
		}
		echo "处理完成，本页面将自动刷新！";
		js_reload('', 3000);
	}

	/**
	 * 给用户批量分配gid
	 *
	 * @2007-12-26 by zhangqitao
	 */
	public function on_set_batch_gid()
	{
		$user_ids = array();
		$user_ids = explode(':', $_POST['user_ids']);
		array_pop($user_ids);
		$users_ids = array_unique($user_ids);
		$user_num = count($user_ids);
		if ($user_num == 0)
		{
			echo '没有选中的用户';
			exit;
		}
		$this->UserDB = $this->LoadDB('UserDB');
		//还能使用的GID个数（最大激活数-已使用数）
		$useableness_gid = $this->UserDB->db_get_useableness_gidnum();
		//取回的能分配的GID
		$gid_arr = $this->UserDB->db_get_all_unused_gid();
		$gid_num = count($gid_arr);
		if ($useableness_gid <=0)
		{
			echo '已使用的GID已经超过允许的最大GID激活数，请联系开发商[北京点击科技有限公司]！';
			exit;
		}
		if ($gid_num == 0)
		{
			echo '系统已无GID可用，请联系开发商[北京点击科技有限公司]！';
			exit;
		}

		//正常情况下，如果没有丢失gid:: $useableness_gid <= $gid_num,,,取两者之间小值作为界点进行循环,小值，即能分配的GID数
		$n = min($gid_num, $useableness_gid);

		//取用户数和能分配的GID数的 小值$j，进行分配前$j位用户的GID
		$j = min($user_num, $n);

		// gid,user_id绑定；gid,mobile绑定;
		$buddylist = "<buddylist>";
		for ($i=0; $i<$j;$i++)
		{
			$this->UserDB->db_bind_user_with_gid($user_ids[$i], $gid_arr[$i], $_SESSION['AdminAccount']);
			$user = $this->UserDB->db_get_user($user_ids[$i]);
			$this->UserDB->db_bind_gid_with_mobile($user['mobile'], $gid_arr[$i], $_SESSION['AdminAccount']);


			$tmp_arr = array();
			$tmp_arr = $this->UserDB->db_get_user_sms($gid);
			if (!$tmp_arr)
				$msg .= $this->UserDB->db_add_mobile_smsgw($user['mobile'], $gid_arr) > 0 ? msg('发送短信网关成功！') : '';
			else //更新短信网关
				$msg .= $this->UserDB->db_upd_mobile_smsgw($_POST['old_gid'], $_POST['mobile']) > 0 ? msg('更新短信网关成功！') : '';


			//$this->UserDB->db_add_mobile_smsgw($user['mobile'], $gid_arr[$i]);
			$this->UserDB->db_add_default_role_for_user($user['user_id'], $_SESSION['current_corp_id']);
			$this->UserDB->db_re_user_ver($gid_arr[$i]);
			$buddylist .= "<buddy gid='$gid_arr[$i][gid]' zoneid='$gid_arr[$i][zoneid]' gs='$gid_arr[$i][gs]'/>";
		}
		$buddylist .= "</buddylist>";
		$this->UserDB->upd_verinfo('clientpara');
		if ($this->notice->SynchNotice($buddylist) > 0)
		{
			$this->notice->ParaNotice($_SESSION['current_corp_id']);
			$this->notice->PrivNotice($buddylist);
			$this->ContactsDB = $this->LoadDB('ContactsDB');
			$contacts_arr = $this->ContactsDB->db_get_corp_all_contacts($_SESSION['current_corp_id']);
			foreach ($contacts_arr as $contacts)
			{
				$contacts_ids_arr[] = $contacts['contacts_id'];
			}
			if (!empty($contacts_ids_arr))
			{//先更新时间，再发送消息
				$this->ContactsDB->db_upd_mender_time($_SESSION['current_corp_id']);
				$this->notice->ContactsNotice($contacts_ids_arr, $_SESSION['current_corp_id']);
			}
		}
		if ($user_num <= $n)
		{
			echo '&nbsp;选中用户，全部随机分配了GID！';
		}
		else if ($user_num > $n)
		{
			echo "&nbsp;前{$n}位用户分配了GID，系统GID已不够，请联系点击科技！";
		}
		js_reload('', 3000);
		$this->_writeLog("批量给用户设置gid成功！");
	}

	public function on_dn()
	{
		$this->UserDB = $this->LoadDB('UserDB');
		$account = base64_decode($_REQUEST['account']);
		$users = $this->UserDB->db_get_users_by_account($account);
		if (empty($users))
		{
			echo "The account '$account' does not exists!";
			return;
		}

		echo "<UserList>\r\n";
		foreach ($users as $user)
		{
			echo "<User>\r\n";
			echo "	<Description>[$user[gid]]$user[remark]</Description>\r\n";
			echo "	<DN>$user[dn]</DN>\r\n";
			echo "</User>\r\n";
		}
		echo "</UserList>";
	}	
	
	public function on_gid($lgid)
	{
		parsegid($lgid, $zoneid, $gid);
		$user = $this->ApiDB->db_get_user_by_gid($gid);
		
		$s = "";
		$s .= "<User>";
		$s .= "<Gid>$user[gid]</Gid>";
		$s .= "<Account>$user[account]</Account>";
		$s .= "<Zoneid>$user[zoneid]</Zoneid>";
		$s .= "<Gs>$user[gs]</Gs>";
		$s .= "</User>"; 
		echo $s;
 	}
}
?>