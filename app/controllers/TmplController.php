<?php
class TmplController extends DefaultController 
{
  public function on_set_per_page()
	{
		$name = $_GET['name'];
		$num = $_GET['num'];
		$_SESSION[$name] = $num;
		js_reload();
	}

	/**
	 * 模板管理
	 */
	public function on_list()
	{
		$this->TmplDB = $this->LoadDB('TmplDB');
		
		$total_recode = $this->TmplDB->db_count_tmpl();
		$limit = !empty( $_SESSION['tmpl_limit']) ? $_SESSION['tmpl_limit'] : 10;	
		$total_pages = ceil($total_recode/$limit);
		$curr_page = max(intval($_GET['page']), 1);
		$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
		$offset = ($curr_page-1)*$limit;
		$in_url = "/tmpl/list/";
		$out_url = $this->Page($total_recode, $limit, $curr_page, $in_url);
		
		$option_str = make_option_string($limit);
		$list = $this->TmplDB->db_get_tmpls($limit, $offset);
		$this->view->assign('list', $list);
		$this->view->assign('option_str', $option_str);
		$this->view->assign('pages', $out_url);		
		$this->view->display('header.html');
		$this->view->display('tmpl/list.html');
		$this->view->display('footer.html');
	}

	/**
	 * 增加模板
	 */	
	public function on_add()
	{
		$this->TmplDB = $this->LoadDB('TmplDB');
        
		if ($_POST)
		{
            $creator = isset($_SESSION['AdminAccount']) ? $_SESSION['AdminAccount'] : '';
			$name = isset($_POST['name']) ? $_POST['name'] : '';
            $remark = isset($_POST['remark']) ? $_POST['remark'] : '';
			$num = isset($_POST['num']) ? $_POST['num'] : '';
            
            $field_arr = array();
            if ($num > 0)
            {
                for($i=1; $i<=$num; $i++)
                {
                    if (!isset($_POST['tmplname'.$i])) continue;
                    
                    $field_arr[] = array(
                        'name'=>$_POST['tmplname'.$i],
                        'type'=>$_POST['tmpltype'.$i],
                        'length'=>$_POST['tmpllength'.$i],
                        'defvalue'=>$_POST['tmpldefvalue'.$i],
                        'required'=>$_POST['tmplrequired'.$i],
                        'rule'=>$_POST['tmplrule'.$i],
                    );
                }
            }
            
            // 拼接xml
            $fileds = '<fileds>';
            foreach($field_arr as $filed)
            {
                $fileds .= '<filed>';
                foreach($filed as $k=>$v)
                {
                    if ($k=='defvalue' && strpos($v, ',')!==false)
                    {
                        $values = explode(',', $v);
                        $fileds .= '<defvalues>';
                        for($n=0; $n<count($values); $n++)
                        {
                            if (!isset($values[$n])) continue;
                            $fileds .= "<value key=\"{$n}\">".htmlspecialchars($values[$n])."</value>";   
                        }
                        $fileds .= '</defvalues>';
                    }                    
                    else $fileds .= "<{$k}>".htmlspecialchars($v)."</{$k}>";
                }
                $fileds .= '</filed>';
            }
            $fileds .= '</fileds>';
            
            // 数据入库
            $res = $this->TmplDB->db_create($name, $remark, $fileds, $creator);
            if ($res)
			     show_msg('GOTO', array('url'=>'/tmpl/list/', 'msg'=>'保存成功.'));
            else show_msg('GOTO', array('url'=>'/tmpl/list/', 'msg'=>'保存失败.'));
		}
		else
		{
			$this->view->display('header.html');
			$this->view->display('tmpl/add.html');
			$this->view->display('footer.html');
		}
	}
	
	/**
	 * 显示模板
	 * http://123.cn/tmpl/show?tid=2
	 */
	public function on_show()
	{
		$tid = $_REQUEST['tid'];
		
		$tmpl = $this->TmplDB->db_get_tmpl($tid);
		$form = $this->TmplDB->parse_xml($tmpl['xml']);
		$this->view->assign('form',$form);
		$this->view->display('tmpl/_view.html');
	}
	
	/**
	 * --------------------------------------------------------------------------  编辑角色
	 *
	 * 最后修改时间：2007-12-21 BY 章启涛 
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
			if ($role['role_id'] == $_SESSION['current_corp_id'] && $role['name'] != '默认角色' )
			{
				$msg .= msg('默认角色名称不能修改！');
				$role['name'] = '默认角色';
			}
			//编辑
			if ($this->RoleDB->db_upd_role($role) >0 )
			{
				$msg .= msg('编辑角色信息成功！');
				$this->_writeLog("编辑角色[{$role[name]}]成功！");
			}
			//删除权限关系
			$old_priv = $this->RoleDB->db_get_used_priv_in_role($role['role_id']);
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
			
			// -----------------------------同步权限消息处理
			$new_priv_ids_arr = $priv_ids;
			//比较权限前后是否发生了变化，如果发生了变化，则发送消息给用户
			foreach (@$old_priv as $v)
			{
				$old_priv_ids_arr[] = $v['priv_id'];
			}
			$intersection = @array_diff_assoc($new_priv_ids_arr, $old_priv_ids_arr);
			if ($intersection == '' && !empty($new_priv_ids_arr) && !empty($old_priv_ids_arr))
			{
				$this->show_msg_ext('GOTO', array('url'=>'/role/list/', 'msg'=>$msg));
				exit;
			}
			/**
			 * 1.如果权限发生变化，则发送消息，客户端用户权限变化
			 * 2.提高效率，取用户分开取再合并
			 */
			$users1 = array();
			$users2 = array();
			$users1 = $this->RoleDB->db_get_role_users($role['role_id']);
			$users2 = $this->RoleDB->db_get_role_dept_users($role['role_id']);
			
			if (!empty($users1) && !empty($users2))
			{
				$users = $users1 + $users2;
			}
			else if (!empty($users1) && empty($users2))
			{
				$users = $users1;
			}
			else if (empty($users1) && !empty($users2))
			{
				$users = $users2;
			}
			else 
			{
				$this->show_msg_ext('GOTO', array('url'=>'/role/list/', 'msg'=>$msg));
				exit;
			}
			//发送用户权限变化消息，先更新时间，再发送消息
			$buddylist = "<buddylist>";
			foreach ($users as $v)
			{
				$buddylist .= "<buddy gid='$v[gid]' zoneid='$v[zoneid]' gs='$v[gs]'/>";
				$this->RoleDB->db_upd_re_user_ver('priv', $v['gid']);
			}
			$buddylist .= "</buddylist>";
			$this->notice->PrivNotice($buddylist);
			$this->show_msg_ext('GOTO', array('url'=>'/role/list/', 'msg'=>$msg));
		}
		else
		{
			$role_id = $_GET['role_id'];
			$role = $this->RoleDB->db_get_role($role_id);
			//初始化已分配的权限名称和权限id
/*			$valuelist = '';
			$textlist = '';
			$used = $this->RoleDB->db_get_used_priv_in_role($role_id);
			if (!empty($used))
			{
				foreach ($used as $v)
				{
					$valuelist .= $v['priv_id'].':';
					$names[] = $v['name'];
				}
				$textlist = implode(',',$names);
				$this->view->assign('textlist',$textlist);				
			}*/
			
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
	 * del role
	 *
	 * @author zhangqitao
	 * $whenmodified 2008-8-27 
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
		$s = '';
		foreach ($del_arr as $role_id)
		{ 
			if ($role_id != $_SESSION['current_corp_id'])//如果不是默认角色则删除
			{
				$re_user_role_users = $this->RoleDB->db_get_role_users($role_id);
				$re_user_ug_users = $this->RoleDB->db_get_role_dept_users($role_id);
				$re_user_role_users_gid = get_id_from_array($re_user_role_users, 'gid');
				foreach ($re_user_role_users as $user)
				{
					$s .= "<buddy gid='$user[gid]' zoneid='$user[zoneid]' gs='$user[gs]' />";
					$this->RoleDB->db_upd_re_user_ver('priv', $user['gid']);	
				}
				foreach ($re_user_ug_users as $user)
				{
					if (!in_array($re_user_role_users_gid, $user['gid']))
					{
						$s .= "<buddy gid='$user[gid]' zoneid='$user[zoneid]' gs='$user[gs]' />";
						$this->RoleDB->db_upd_re_user_ver('priv', $user['gid']);
					}
				}
				$this->RoleDB->db_del_role_by_id($role_id);
			}
			else 
			{
				$msg = '默认角色保留不删除！';
			}
		}
		if ($s != '')
		{
			$buddylist = '<buddylist>'.$s.'</buddylist>';	
			$this->notice->PrivNotice($buddylist);
		}
		$this->show_msg_ext('GOTO', array('url'=>'/role/list/', 'msg'=>$msg.'删除完成！'));
		$this->_writeLog("删除角色成功！");
	}
	
	
	/**
	 * ------------------------------------------------------------------------ 搜索角色
	 *
	 * 最后修改时间：2007-12-21 BY 章启涛 
	 */
	
	public function on_search()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		$content = $_GET['content'];
		
		$total_recode =  $this->RoleDB->db_search_count($content, $_SESSION['current_corp_id']);
		$limit = !empty( $_SESSION['role_limit']) ? $_SESSION['role_limit'] : 10;
		$total_pages = ceil($total_recode/$limit);
		$curr_page = max(intval($_GET['page']), 1);
		$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
		$offset = ($curr_page-1)*$limit;
		$in_url = $content=='' ? "/role/search/"  : "/role/search/content/".urlencode($content)."/";
		$out_url = $this->Page($total_recode, $limit, $curr_page, $in_url);
		$arr = $this->RoleDB->db_search($content, $_SESSION['current_corp_id'], $limit, $offset );	
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
	 * -----------------------------------------------------------  显示增加和编辑时，角色的权限选择
	 *
	 * 最后修改时间：2007-12-21 BY 章启涛 
	 */
	public function on_show_priv()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		
		$action = $_GET['action'];
		$role_id = $_GET['role_id'];
		$option_str = '';
		$option_str_used = '';
		if ($action == 'add')//增加显示全部权限
		{
			//全部权限
			$rolelist = $this->RoleDB->db_get_all_priv();
			foreach ($rolelist as $v)
			{
				$option_str .= "<option value='$v[priv_id]'>$v[name]</option>";
			}		
		}
		else if ($action == 'upd')//编辑显示未分配权限和已分配权限
		{
			//未分配 
			$nbused = $this->RoleDB->db_get_unused_priv_in_role( $role_id );
			if (!empty($nbused))
			{
				foreach ( $nbused as $v)
				{
					$option_str .= "<option value='$v[priv_id]'>$v[name]</option>";
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
		}
		$this->view->assign('option_str_used',$option_str_used);
		$this->view->assign('option_str',$option_str);
		$this->view->display('header.html');	
		$this->view->display('role/priv.html');	
		$this->view->display('footer.html');	
	}

	/**
	 * -------------------------------------------------------------------------- 把角色分配给当前单位的某些部门
	 *
	 * 最后修改时间：2007-12-21 BY 章启涛 
	 */
	public function on_set_role_depts()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		$role_id = $_POST['role_id'];
		$old_depts_arr = $this->RoleDB->db_get_setted_role_depts($role_id);
		foreach ($old_depts_arr as $v)
		{
			$old_depts_ids_arr[] = $v['ug_id'];
		}
		$depts = $_POST['treechecked'];
		$new_depts_ids_arr = str_explode_deep($depts, ',');
		if ($new_depts_ids_arr[0] == $_SESSION['current_corp_id'])
			unset($new_depts_ids_arr[0]);
		//先执行删除
		$this->RoleDB->db_del_role_depts($role_id);
		
		//执行重新分配
		foreach ($new_depts_ids_arr as $dept_id)
		{
			$ret += $this->RoleDB->db_set_role_dept($role_id, $dept_id);
		}
		
		if (empty($old_depts_ids_arr) && empty($new_depts_ids_arr))
		{
			js_close();
			exit;
		}
		
		if (!empty($old_depts_ids_arr) && empty($new_depts_ids_arr))
		{
			$tmp = $old_depts_ids_arr;
		}
		else if (empty($old_depts_ids_arr) && !empty($new_depts_ids_arr))
		{
			$tmp = $new_depts_ids_arr;
		}
		else 
		{
			foreach ($old_depts_ids_arr as $v)
			{
				if (!in_array($v, $new_depts_ids_arr))
				{
					$tmp[] = $v;
				}
			}
			foreach ($new_depts_ids_arr as $v)
			{
				if (!in_array($v, $old_depts_ids_arr))
				{
					$tmp[] = $v;
				}
			}
		}
		
		$buddylist = "<buddylist>";
		foreach ($tmp as $ug_id)
		{
			$users = $this->RoleDB->db_get_dept_gid_users($ug_id);
			foreach ($users as $user)
			{
				$buddylist .= "<buddy gid='$user[gid]' zoneid='$user[zoneid]' gs='$user[gs]'/>";
				$this->RoleDB->db_upd_re_user_ver_by_gid($user['gid']);
			}
		}
		$buddylist .= "</buddylist>";	
		$this->notice->PrivNotice($buddylist);
		js_close();
		$this->_writeLog("角色分配给部门！");
	}
	
	
	/**
	 * 显示某个角色的已分配和未分配的部门
	 *
	 * 最后修改时间：2007-12-21 BY 章启涛 
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
		//取部门下用户
		$users = array();
		$users = $this->RoleDB->db_get_dept_gid_users($ug_id);
		if (!empty($users))
		{
			$buddylist = "<buddylist>";
			foreach ($users as $user)
			{
				$buddylist .= "<buddy gid='$user[gid]' zoneid='$user[zoneid]' gs='$user[gs]'/>";
				$this->RoleDB->db_upd_re_user_ver_by_gid($user['gid']);
			}
			$buddylist .= "</buddylist>";	
			$this->notice->PrivNotice($buddylist);		
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
		$unsetted = $this->RoleDB->db_get_unsetted_roles_dept($ug_id, $_SESSION['current_corp_id']);
		$this->view->assign('cur_ug', $cur_ug);
		$this->view->assign('setted', $setted);
		$this->view->assign('unsetted', $unsetted);
		$this->view->display('header.html');
		$this->view->display('role/dept.html');
		$this->view->display('footer.html');
	}
	
	/**
	 * --------------------------------------------------------------------------  某个角色分配给多个用户
	 *
	 * 最后修改时间：2007-12-21 BY 章启涛 
	 */
	public function on_set_role_users()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		$role_id = $_POST['role_id'];
		$new_user_ids_str = $_POST['userlist']; 
		
		$old_users_arr = $this->RoleDB->db_get_setted_role_users($role_id, $_SESSION['current_corp_id']);
		foreach ($old_users_arr as $v)
		{
			$old_users_ids_arr[] = $v['user_id'];
		}
		$new_users_ids_arr = str_explode_deep($new_user_ids_str, ':');
				
		//先执行删除
		$this->RoleDB->db_del_role_users($role_id);
		//执行重新分配用户
		foreach ($new_users_ids_arr as $v)
		{
			$ret += $this->RoleDB->db_set_role_user($role_id, $v);
		}
		if (empty($old_users_ids_arr) && empty($new_users_ids_arr))
		{
			js_close();
//			$url = "/role/show_role_users/role_id/$role_id";
//			js_goto($url);			
//			exit;
		}
		//比较用户变化
		if (!empty($old_users_ids_arr) && empty($new_users_ids_arr))
		{
			$tmp = $old_users_ids_arr;
		}
		else if (empty($old_users_ids_arr) && !empty($new_users_ids_arr))
		{
			$tmp = $new_users_ids_arr;
		}
		else 
		{
			foreach ($old_users_ids_arr as $v)
			{
				if (!in_array($v,$new_users_ids_arr))
				{
					$tmp[] = $v;
				}
			}
			foreach ($new_users_ids_arr as $v)
			{
				if (!in_array($v,$old_users_ids_arr))
				{
					$tmp[] = $v;
				}				
			}
		}
		$buddylist = "<buddylist>";
		foreach ($tmp as $user_id)
		{
			$this->UserDB = $this->LoadDB('UserDB');
			$user = $this->UserDB->db_get_user($user_id);
			$buddylist .= "<buddy gid='$user[gid]' zoneid='$user[zoneid]' gs='$user[gs]'/>";
			$this->RoleDB->db_upd_re_user_ver_by_gid($user['gid']);
		}
		$buddylist .= "</buddylist>";	
		$this->notice->PrivNotice($buddylist);
		js_close();
		$this->_writeLog("角色分配给用户！");
	}
	
	/**
	 * --------------------------------------------------------------------------------- 显示当前角色的已分配和未分配的用户
	 *
	 * 最后修改时间：2007-12-21 BY 章启涛 
	 */
	public function on_show_role_users()
	{
		$this->RoleDB = $this->LoadDB('RoleDB');
		$role = $this->RoleDB->db_get_role($_GET['role_id']);
		//已经分配的用户
		$setted = $this->RoleDB->db_get_setted_role_users($_GET['role_id'], $_SESSION['current_corp_id']);
		$this->view->assign('role', $role);
		$this->view->assign('setted', $setted);
		$this->view->display('header.html');
		$this->view->display('role/users.html');
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
		$buddylist = "<buddylist>";
		$user = $this->UserDB->db_get_user($user_id);
		$buddylist .= "<buddy gid='$user[gid]' zoneid='$user[zoneid]' gs='$user[gs]'/>";
		$this->RoleDB->db_upd_re_user_ver_by_gid($user['gid']);
		$buddylist .= "</buddylist>";
		$this->notice->PrivNotice($buddylist);
		js_close();
		$this->_writeLog("给用户分配角色！");
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
		$unsetted = $this->RoleDB->db_get_unsetted_roles_user($user_id, $_SESSION['current_corp_id']);
		$this->view->assign('user', $user);
		$this->view->assign('setted', $setted);
		$this->view->assign('unsetted', $unsetted);
		$this->view->display('header.html');
		$this->view->display('role/user.html');
		$this->view->display('footer.html');
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