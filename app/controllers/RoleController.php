<?php
class RoleController extends DefaultController 
{
	/**
	 * 角色列表
	 */
	public function on_list()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		
		$total_recode = $this->RoleDB->db_count_corp_roles();
		$limit = !empty( $_SESSION['role_limit']) ? $_SESSION['role_limit'] : 10;	
		$total_pages = ceil($total_recode/$limit);
		$curr_page = isset($_GET['page']) ? $_GET['page'] : 1;
		$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
		$offset = ($curr_page-1)*$limit;
		$in_url = "/role/list/";
		$out_url = $this->Page($total_recode, $limit, $curr_page, $in_url);
		
		$option_str = make_option_string($limit);
		$list = $this->RoleDB->db_get_corp_roles($limit, $offset);
		$this->view->assign('list', $list);
		$this->view->assign('option_str', $option_str);
		$this->view->assign('pages', $out_url);		
		$this->view->display('header.html');
		$this->view->display('role/list.html');
		$this->view->display('footer.html');
	}


	/**
	 * 增加角色
	 */	
	public function on_add()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		if ($_POST)
		{
			$role['role_id'] = uuid();
			$role['name'] = $_POST['name'];
			$role['creator_account'] = $_SESSION['AdminAccount'];
			$role['remark'] = $_POST['remark'];
			
			//检查当前单位内角色名称是否存在
			if ( $this->RoleDB->db_check_name($_POST['name']) > 0)
			{
				$this->show_msg_ext('BACK', array('msg'=>"已存在角色 [ $_POST[name]] ！"));
				exit;
			}
			
			//增加角色
            $msg = "";
		 	if ($this->RoleDB->db_add_role($role) > 0 )
		 	{
		 		$msg .= msg('增加角色成功！');
		 	}
		 	else 
		 	{
		 		$this->show_msg_ext('BACK', array('msg'=>'增加角色失败！'));
		 		exit;
		 	}
			//增加角色权限关系
			if (!empty($_POST['valuelist']))
			{
				$priv_ids = explode(':',$_POST['valuelist']);
				array_pop($priv_ids);
				foreach ($priv_ids as $priv_id)
				{
					$priv_arr = $this->RoleDB->db_get_priv($priv_id);
					$this->RoleDB->db_add_priv_to_role($role['role_id'], $priv_arr);
				}
				$msg .= msg('增加角色权限关系成功！');
			}
			show_msg('GOTO', array('url'=>'/role/list/', 'msg'=>$msg));
			$this->_writeLog("增加角色[{$role['name']}]！");
		}
		else
		{
			//全部权限
			$rolelist = $this->RoleDB->db_get_all_priv();
            $option_str = "";
			foreach ($rolelist as $v)
			{
				$option_str .= "<option value='$v[priv_id]'>$v[name]</option>";
			}
			$this->view->assign('option_str',$option_str);			
			$this->view->display('header.html');
			$this->view->display('role/add.html');
			$this->view->display('footer.html');
		}
	}
	
	/**
	 * 编辑角色
	 */
	public function on_upd()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		if ($_POST)
		{
			$role['role_id'] = $_POST['role_id'];
			$role['name'] = $_POST['name'];
			$role['mender_account'] = $_SESSION['AdminAccount'];
			$role['remark'] = $_POST['remark'];

			//编辑
            $msg = "";
			if ($this->RoleDB->db_upd_role($role) >0 )
			{
				$msg .= msg('编辑角色信息成功！');
				$this->_writeLog("编辑角色[{$role['name']}]成功！");
			}
			//删除权限关系
			$this->RoleDB->db_del_priv_in_role($role['role_id']);
			//重新增加权限关系
			if (!empty($_POST['valuelist']))
			{
				$priv_ids = explode(':',$_POST['valuelist']);
				array_pop($priv_ids);
				foreach ($priv_ids as $priv_id)
				{
					$priv_arr = $this->RoleDB->db_get_priv($priv_id);
					$this->RoleDB->db_add_priv_to_role($role['role_id'], $priv_arr);
				}
				$msg .= msg('编辑权限关系成功！');
			}
			
			$this->show_msg_ext('GOTO', array('url'=>'/role/list/', 'msg'=>$msg));
		}
		else
		{
			$role_id = $_GET['role_id'];
			$role = $this->RoleDB->db_get_role($role_id);
			
            //初始化已分配的权限名称和权限id
			$option_str_unused = "";
            $option_str_used = "";
			$nbused = $this->RoleDB->db_get_unused_priv_in_role( $role_id );
			if (!empty($nbused))
			{
				foreach ( $nbused as $v)
				{
					$option_str_unused .= "<option value='$v[priv_id]'>$v[name]</option>";
				}			
			}
			//已分配
			$used = $this->RoleDB->db_get_used_priv_in_role($role_id);
			if (!empty($used))
			{
				foreach ($used as $v)
				{
					$option_str_used .= "<option value='$v[priv_id]'>$v[name]</option>";
				}			
			}	
			
			$this->view->assign('option_str_unused', $option_str_unused);
			$this->view->assign('option_str_used', $option_str_used);
			$this->view->assign('role',$role);
			$this->view->display('header.html');
			$this->view->display('role/upd.html');
			$this->view->display('footer.html');
		}
	}
	
	/**
	 * 删除角色
	 */
	public function on_del()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		$del_arr = $_POST['del'];
		if (empty($del_arr))
		{
			$this->show_msg_ext('BACK', array('msg'=>'没有选中要删除的角色！'));
			exit;
		}
		foreach ($del_arr as $role_id)
		{ 
			$this->RoleDB->db_del_role_by_id($role_id);
		}

		$this->show_msg_ext('GOTO', array('url'=>'/role/list/', 'msg'=>'删除完成！'));
		$this->_writeLog("删除角色成功！");
	}
	
	
	/**
	 * 搜索角色
	 */
	public function on_search()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		$content = $_GET['content'];
		
		$total_recode =  $this->RoleDB->db_search_count($content);
		$limit = !empty( $_SESSION['role_limit']) ? $_SESSION['role_limit'] : 10;
		$total_pages = ceil($total_recode/$limit);
		$curr_page = isset($_GET['page']) ? max(intval($_GET['page']), 1) : 1;
		$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
		$offset = ($curr_page-1)*$limit;
		$in_url = $content=='' ? "/role/search/"  : "/role/search/content/".urlencode($content)."/";
		$out_url = $this->Page($total_recode, $limit, $curr_page, $in_url);
		$arr = $this->RoleDB->db_search($content, $limit, $offset );	
		$option_str = make_option_string($limit);
		$this->view->assign('content', $content);
		$this->view->assign('pages', $out_url);
		$this->view->assign('option_str',$option_str);
		$this->view->assign('list',$arr);
		$this->view->display('header.html');
		$this->view->display('role/list.html');
		$this->view->display('footer.html');	
	}
	
	/**
	 * 把多个角色分配给一个部门
	 *
	 */
	public function on_set_roles_dept()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		
		$ug_id = $_POST['ug_id'];
		$role_list = $_POST['rolelist'];
		$role_arr = explode(':', $role_list);
		array_pop($role_arr);
		//删除某个部门下的所有角色
		$this->RoleDB->db_del_roles_dept($ug_id);
		//给某个部门重新分配角色 
		foreach ($role_arr as $v)
		{
			$ret += $this->RoleDB->db_set_role_dept($v, $ug_id);
		}

		if ($ret >0)
			$msg = msg('分配完成！');
		js_close();
		$this->_writeLog("部门分配角色！");
	}
	
	/**
	 * 显示把多个角色分配给一个部门
	 *
	 */
	public function on_show_roles_dept()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		$this->UgDB = $this->LoadDB('UgDB');
		$ug_id = $_GET['ug_id'];	
		$cur_ug = $this->UgDB->db_get_ug_by_ug_id($ug_id);
		$setted = $this->RoleDB->db_get_setted_roles_dept($ug_id);
		$unsetted = $this->RoleDB->db_get_unsetted_roles_dept($ug_id);
		$this->view->assign('cur_ug', $cur_ug);
		$this->view->assign('setted', $setted);
		$this->view->assign('unsetted', $unsetted);
		$this->view->display('header.html');
		$this->view->display('role/dept.html');
		$this->view->display('footer.html');
	}
    
    /**
	 * 某个用户分配多个角色，显示已分配和未分配角色
	 *
	 */
	public function on_show_roles_user()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		$this->UserDB = $this->LoadDB('UserDB');
		
		$user_id = $_GET['user_id'];
		$user = $this->UserDB->db_get_user($user_id);
		//该用户已经分配的角色
		$setted = $this->RoleDB->db_get_setted_roles_user($user_id);
		//该用户未分配的角色
		$unsetted = $this->RoleDB->db_get_unsetted_roles_user($user_id);
		$this->view->assign('user', $user);
		$this->view->assign('setted', $setted);
		$this->view->assign('unsetted', $unsetted);
		$this->view->display('header.html');
		$this->view->display('role/user.html');
		$this->view->display('footer.html');
	}
    
	/**
	 * 某个用户分配多个角色
	 *
	 */
	public function on_set_roles_user()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		$this->UserDB = $this->LoadDB('UserDB');
		$user_id = $_POST['user_id'];
		$role_list = $_POST['rolelist'];
		$roles_arr = explode(':', $role_list);
		array_pop($roles_arr);
		//先执行该用户已经分配的角色的删除
		$this->RoleDB->db_del_roles_user($user_id);
		//执行重新分配角色
		foreach ($roles_arr as $v)
		{
			$ret += $this->RoleDB->db_set_role_user($v, $user_id);
		}
		$msg = msg('分配权限完成！');
		js_close();
		$this->_writeLog("给用户分配角色！");
	}
    
	/**
	 * 显示当前角色的已分配和未分配的用户
	 */
	public function on_show_role_users()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		$role = $this->RoleDB->db_get_role($_GET['role_id']);
		//已经分配的用户
		$setted = $this->RoleDB->db_get_setted_role_users($_GET['role_id']);
		$this->view->assign('role', $role);
		$this->view->assign('setted', $setted);
		$this->view->display('header.html');
		$this->view->display('role/users.html');
		$this->view->display('footer.html');		
	}

	/**
	 * 某个部门的用户
	 */
	public function request_dept_users()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		$this->UgDB = $this->LoadDB('UgDB');
		$str = "var arr = new Array();";
		$ug_id = $_GET['ug_id'];
		$ugs_id_arr = array();

		$uginfo = $this->UgDB->db_get_ug_by_ug_id($ug_id);
		if (isset($_GET['branch']))
		{
			$child_depts = $this->GetPosterity($ug_id, '1');
			$ugs_id_arr = get_id_from_array($child_depts);
			$ugs_id_arr[] = $ug_id;
		}
		else
		{
			$ugs_id_arr[] = $ug_id;
		}
		$ug_ids = "'".implode("','", $ugs_id_arr)."'";
		//echo "$ug_ids";
		$users_arr = $this->RoleDB->db_get_dept_users($ug_ids);

		if (!empty($users_arr))
		{
			$i = 0;
			foreach ($users_arr as $user)
			{
				$str .= "arr[$i] = ['$user[name]', '$user[user_id]'];";
				$i++;
			}
		}
		// 如果是单位 特殊处理:显示增加未分配部门人员
		// 1.get ug 2.check ug is corp 3.get users in corp without  dept;
		echo $str;
	}
    
 	/**
	 * 某个角色分配给多个用户
	 */
	public function on_set_role_users()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		$role_id = $_POST['role_id'];
		$new_user_ids_str = $_POST['userlist']; 
		
		$new_users_ids_arr = str_explode_deep($new_user_ids_str, ':');
				
		//先执行删除
		$this->RoleDB->db_del_role_users($role_id);
		//执行重新分配用户
		foreach ($new_users_ids_arr as $v)
		{
			$ret += $this->RoleDB->db_set_role_user($role_id, $v);
		}

		js_close();
		$this->_writeLog("角色分配给用户！");
	}   
    
 	/**
	 * 显示某个角色的已分配和未分配的部门
	 */
	public function on_show_role_depts()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		$role_id = $_GET['role_id'];
		$role = $this->RoleDB->db_get_role($role_id);
		$this->view->assign('role', $role);
		$this->view->display('header.html');
		$this->view->display('role/depts.html');
		$this->view->display('footer.html');
	}
       
	/**
	 * 把角色分配给当前单位的某些部门
	 */
	public function on_set_role_depts()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		$role_id = $_POST['role_id'];

		$depts = $_POST['treechecked'];
		$new_depts_ids_arr = str_explode_deep($depts, ',');

		//先执行删除
		$this->RoleDB->db_del_role_depts($role_id);
		
		//执行重新分配
		foreach ($new_depts_ids_arr as $dept_id)
		{
			$ret += $this->RoleDB->db_set_role_dept($role_id, $dept_id);
		}
		
		js_close();
		$this->_writeLog("角色分配给部门！");
	}    
    
    
    
    
    
	

	
	

	


	/**
	 * ajax检查角色名是否已经存在
	 *
	 */
	public function check_role_is_rename()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		$role_name = $_GET['name'];
		if (trim($role_name) == '')
		{
			echo "<img src='/images/check_error.gif' align='absmiddle'/>&nbsp;";
			echo '角色名不能为空！';
			exit;
		}
		if ($this->RoleDB->db_check_name($role_name, $_SESSION['current_corp_id']) > 0)
		{
			echo "<img src='/images/check_error.gif' align='absmiddle'/>&nbsp;";
			echo '角色名已经存在，请重新输入！';
		}
		else 
		{
			echo "<img src='/images/check_right.gif' align='absmiddle'/>";
		}
	}
	
	
	public function adddefaultrole()
	{
		$this->UgDB = $this->LoadDB("UgDB");
		$this->RoleDB = $this->LoadDB("RoleDB");
		
		$allcorps = $this->UgDB->db_get_all_corps();
		$n = 0;
		foreach ($allcorps as $ug)
		{
			$role = array();
			$role = $this->RoleDB->db_get_role($ug['ug_id']);
			if (!empty($role)) continue;
			// 如果没有默认角色则增加默认角色
			$role['name'] = '默认角色';
			$role['role_id'] = $ug['ug_id'];
			$role['ug_id'] = $ug['ug_id'];
			$role['parent_id'] = $ug['ug_id'];
			$role['remark'] = '默认角色';
			$n += $this->RoleDB->db_add_role($role, $ug['ug_id']);				
		}
		echo "为单位增加默认角色{$n}个！";
	}
}
?>