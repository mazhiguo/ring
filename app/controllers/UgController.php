<?php
class UgController extends DefaultController
{
	public function on_index( )
	{
		$this->view->display("header.html");
		$this->view->display("ug/index.htm");
		$this->view->display("footer.html");
	}

	// 显示组织树
	public function on_show_ug_tree()
	{
		$this->view->display('ug/tree.html');
	}
    
   	/**
	 * 当前单位的部门结构管理列表
	 */
	public function on_ug_dept_list()
	{
		//		print_r($_SESSION);
		$this->UgDB = $this->LoadDB('UgDB');
		/*
		使用$_SESSION['current_ug_id']记录当前管理的子部门或当前单位的切换
		如果$_SESSION['current_ug_id']不为空，则为之前的session记录的，否则使用当前单位作默认
		如果有GET数据的ug_id，则进行$_SESSION['current_ug_id']切换
		*/
		$_SESSION['current_ug_id'] = empty($_SESSION['current_ug_id']) ? '0' : $_SESSION['current_ug_id'];
		$_SESSION['current_ug_id'] = isset($_GET['ug_id']) ? $_GET['ug_id'] : $_SESSION['current_ug_id'];

		//当前管理的组织（当前单位或某个子部门）信息
		$current_ug = $this->UgDB->db_get_ug_by_ug_id($_SESSION['current_ug_id']);

		//同步用session记录current_ug的name,方便其他处使用name时不必再查找数据库
		$_SESSION['current_ug_name'] = $current_ug['name'];

		$limit = !empty($_SESSION['ug_limit']) ? $_SESSION['ug_limit'] : 20 ;
		//显示所有子部门
		if (isset($_GET['control']) && $_GET['control'] == 'offspring')
		{
			$sub_depts =  $this->GetPosterity($_SESSION['current_ug_id'], '1');

			$total_recode = count($sub_depts);
			$total_pages = ceil($total_recode/$limit);
			$curr_page = isset($_GET['page']) ? max(intval($_GET['page']), 1) : 1;
			$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
			$offset = ($curr_page-1)*$limit;
			$in_url = "/ug/ug_dept_list/control/offspring/";
			for ($i=$offset; $i<($offset+$limit); $i++)
			{
				if (!empty($sub_depts[$i]))
				$tmp[] = $sub_depts[$i];
			}
			$sub_depts1 = $tmp;
			$this->view->assign('checked', 'checked');
		}
		//仅显示下级子部门
		else
		{
			!isset($_GET['page']) && $_GET['page'] = 1;

			$total_recode = $this->UgDB->db_count_sub_depts($_SESSION['current_ug_id']);
			$total_pages = ceil($total_recode/$limit);
			$curr_page = max(intval($_GET['page']), 1);
			$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
			$offset = ($curr_page-1)*$limit;
			$in_url = "/ug/ug_dept_list/";

			$sub_depts1 = $this->UgDB->db_get_sub_depts($_SESSION['current_ug_id'], $limit, $offset);
		}
		$out_url = $this->Page($total_recode, $limit, $curr_page, $in_url);
		//如果是当前的单位ug_id，模板标识，控制显示
		$corp_sign = $current_ug['sign'];

		$option_str = make_option_string($limit);
		/*****************************信息摘要*************************************************/
		//如为当前单位
		if ($_SESSION['current_ug_id'] == '0')
		{
			$this->UserDB = $this->LoadDB('UserDB');
			$total_users_num = $this->UserDB->db_count_all_users();
			$this->view->assign('total_users_num', $total_users_num);
		}
		//如果为记录的某个部门
		else
		{
			$cur_users_num = $this->UgDB->db_count_dept_users($_SESSION['current_ug_id']);
			$sub_depts =  $this->GetPosterity($_SESSION['current_ug_id'], '1');
			$total_users_num = $cur_users_num;
			if (!empty($sub_depts))
			{
				foreach ($sub_depts as $v) $total_users_num +=  $this->UgDB->db_count_dept_users($v['ug_id']);
			}
			$role_arr = $this->UgDB->db_get_dept_roles($_SESSION['current_ug_id']);
            $role_str = "";
            for ($i=0; $i<count($role_arr); $i++) 
            {
                $role_str .= $role_arr[$i]['name'].' ';
                if ($i == count($role_arr)-1) $role_str .= '...';
            }

			$cur_users_num = $cur_users_num == ''? 0 : $cur_users_num;
			$total_users_num = $total_users_num == '' ? 0 : $total_users_num;

			$this->view->assign('role_str', $role_str);
			$this->view->assign('cur_users_num', $cur_users_num);
			$this->view->assign('total_users_num', $total_users_num);
		}
		$this->view->assign('corp_sign', $corp_sign);//标识是否为单位
		$this->view->assign('pages', $out_url);
		$this->view->assign('option_str', $option_str);
		$this->view->assign('sub_depts', $sub_depts1);
		$this->view->assign('current_ug', $current_ug);
		$this->view->display('header.html');
		$this->view->display('ug/dept_list.html');
		$this->view->display('footer.html');
	}

	/**
	 * 显示增加部门
	 */
	public function on_show_add_dept()
	{
		$this->view->display('header.html');
		$this->view->display('ug/add_dept.html');
		$this->view->display('footer.html');
	}

	/**
	 * 增加单位或部门
	 */
	public function on_add_ug()
	{
		$this->UgDB = $this->LoadDB('UgDB');

		$ug['sign'] = $_POST['sign'];
		$ug['ug_id'] = uuid();
		$ug['name'] = $_POST['name'];
		$ug['email'] = $_POST['email'];
		$ug['remark'] = $_POST['remark'];
		$ug['location'] = $this->UgDB->db_get_max_location()+1;
		$ug['creator_account'] = $_SESSION['AdminAccount'];
		$ug['mender_account'] = $_SESSION['AdminAccount'];

		//如果编码为空，生成随机串作编码
		$ug['code'] = trim($_POST['code']) != '' ? $_POST['code'] : get_rnd_str(9, 0);
		//父组织id处理
		//增加部门：如果指定的不为空，则为指定的，否则使用当前SESSION中记录的 激活状态的 当前单位或其某个部门的id
		$ug['parent_id'] = $_POST['parent_id'] == '' ? $_SESSION['current_ug_id'] : $_POST['parent_id'] ;

		if ($this->UgDB->db_check_name($_POST['name'], $ug['parent_id']) > 0)
		{
			$this->show_msg_ext('BACK', array('msg'=>'同一父组织下已存在同名子组织！'));
			exit;
		}
		if (trim($_POST['name']) == '')
		{
			$this->show_msg_ext('BACK', array('msg'=>'组织名不能为空！'));
			exit;
		}
		if ($this->UgDB->db_check_code($ug['code']) > 0)
		{
			$this->show_msg_ext('BACK', array('msg'=>'组织编码已存在！'));
			exit;
		}
		//增加当前单位的部门

		if ($this->UgDB->db_add_ug($ug) <= 0)
		{
			$this->show_msg_ext('BACK', array('msg'=>'增加新部门失败！'));
			exit;
		}
		else
		{
			js_reload('top.content.tree');
            $this->show_msg_ext('GOTO', array('url'=>'/ug/ug_dept_list/', 'msg'=>'增加新部门成功！'));
		}
		
		$this->_writeLog("增加部门[$ug[name]]成功！");
	}

	/**
	 * 显示组织结构编辑
	 */
	public function on_show_upd_dept()
	{
		$this->UgDB = $this->LoadDB('UgDB');
		$ug_id = $_GET['ug_id'];

		//如果是当前单位的编辑，自动切换到单位编辑show_upd_corp
		if ($ug_id == '0')
		{
			echo "<script>self.location='/ug/show_upd_corp/ug_id/0'</script>";
			exit;
		}
		//部门编辑显示，提取部门信息
		$dept = $this->UgDB->db_get_ug_by_ug_id($ug_id);

		$this->view->assign('dept', $dept);
		$this->view->display('header.html');
		$this->view->display('ug/upd_dept.html');
		$this->view->display('footer.html');
	}

	/**
	 * 组织结构编辑
	 */
	public function on_upd_ug()
	{
		//如果有$_POST['parent_id'] ,则是部门更新的父组织，检查更新的parent_id是否等于自己。
		if (isset($_POST['parent_id']) && $_POST['parent_id'] === $_POST['ug_id'])
		{
			$this->show_msg_ext('BACK', array('msg'=>'父组织不能为自己！'));
			exit;
		}
		$ug['ug_id'] = $_POST['ug_id'];
		$ug['name'] = $_POST['name'];
		$ug['sign'] = $_POST['sign'];
		$ug['email'] = $_POST['email'];
		$ug['remark'] = $_POST['remark'];
		$ug['mender_account'] = $_SESSION['AdminAccount'];
		$ug['parent_id'] = isset($_POST['parent_id']) ? $_POST['parent_id'] : '';
		$ug['code'] = $_POST['code'] == '' ? 'Rand'.get_rnd_str() : $_POST['code'];

		$this->UgDB = $this->LoadDB('UgDB');
		$this->UserDB = $this->LoadDB('UserDB');
		//父组织检查1.检查是否有后代，父亲不能变为孩子的孩子，即，parent_id不能为后代的id
		if ($ug['parent_id'] != '')
		{
			$offspring_depts =  $this->GetPosterity($ug['ug_id'], '1');//当前单位的所有部门
			foreach ($offspring_depts as $dept)
			{
				$offspring_depts_ids[] = $dept['ug_id'];
			}
			if (in_array($ug['parent_id'], $offspring_depts_ids))
			{
				$this->show_msg_ext('BACK', array('msg'=>'不能将父组织设为其初始的后代组织！'));
				exit;
			}
    		// name检查同一父组织下，除了是否有其他同名称的组织
    		if ($this->UgDB->db_check_expect_self_name($_POST['name'], $ug['parent_id'], $ug['ug_id']) >0 )
    		{
    			$this->show_msg_ext('BACK', array('msg'=>'指定的父组织下已存在同名子组织！'));
    			exit;
    		}
		}
		//code检查，新的code(旧的)，不能与其他的组织的code相同。
		if ($this->UgDB->db_check_expect_self_code($ug['code'], $ug['ug_id']) >0 )
		{
			$this->show_msg_ext('BACK', array('msg'=>'组织编码已存在！'));
			exit;
		}

        $msg = "";
		if ($this->UgDB->db_upd_ug($ug) <=0)
		{
			$msg .= msg('修改组织信息失败！', 0);
			$this->show_msg_ext('BACK', array('msg'=>$msg));
			exit;
		}
		else
		{
			js_reload('top.content.tree');
			js_reload('top.nav');

			$msg .= msg('修改组织信息成功！');
		}

		//如果是编辑当前单位或部门，跳转至部门列表.如果是编辑下级单位，跳转到下级单位列表
		$url = '/ug/ug_dept_list/';
		$this->show_msg_ext('GOTO', array('url'=>$url, 'msg'=>$msg));
		
		$this->_writeLog("编辑部门[$ug[name]]成功！"); 
	}

	/**
	 * 显示单位信息编辑
	 */
	public function on_show_upd_corp()
	{
		$this->UgDB = $this->LoadDB('UgDB');
		$ug_id = $_GET['ug_id'];

		$corp = $this->UgDB->db_get_ug_by_ug_id($ug_id);
		$this->view->assign('corp', $corp);
		$this->view->display('header.html');
		$this->view->display('ug/upd_corp.html');
		$this->view->display('footer.html');
	}

	/**
	 * 删除部门(仅为本单位内操作)
	 */
	public function on_del_dept()
	{
		if ($_GET) //删除当前部门或单位
		{
			$ug_ids_arr[] = $_GET['ug_id'];
		}
		else if ($_POST['del'])//批量删除
		{
			$ug_ids_arr = $_POST['del'];
		}
		if (empty($ug_ids_arr))
		{
			$this->show_msg_ext('BACK', array('msg'=>'没有被选中删除的组织！'));
			exit;
		}
		//找出这些部门的所有后代部门
		$this->UgDB = $this->LoadDB('UgDB');
		$child_depts_id_arr = array();
		foreach ($ug_ids_arr as $val)
		{
			$tmp = array();
			$tmp = $this->GetPosterity($val, '1');
			if (is_array($tmp) && !empty($tmp))
			{
				foreach ($tmp as $val2)
				$child_depts_id_arr[] = $val2['ug_id'];
			}
		}
		if (!empty($child_depts_id_arr))
		$ug_ids_arr = array_merge($ug_ids_arr, $child_depts_id_arr);
		$ug_ids_str = "'".implode("','", $ug_ids_arr)."'";
		$dept_num = count($ug_ids_arr);

		// 删除部门，并删除部门关联信息
		foreach ($ug_ids_arr as $ug_id)
		{
			$dept_record_num += $this->UgDB->db_del_dept($ug_id);
		}
		$msg = msg("共删除了{$dept_num}个部门！");
		$msg .= msg("共删除了{$dept_record_num}个部门关联信息！");
		// 删除部门，如果是当前部门删除，再新建，$_SESSION['current_ug_id']
		$_SESSION['current_ug_id'] = '0';

		$this->show_msg_ext('GOTO', array('url'=>'/ug/ug_dept_list/', 'msg'=>$msg, 'timeout'=>5000));
		echo "<script>parent.tree.location.reload();</script>";
		$this->_writeLog("删除部门成功！");
	}

	/**
	 * ajax请求部门结构
	 */
	public function on_ajax_show_dept($ug_id)
	{
		$this->UgDB = $this->LoadDB('UgDB');
		$selected_ug_id = $ug_id != '' ? $ug_id : $_SESSION['current_ug_id'];
		// 当前单位的所有部门
		$current_depts =  $this->GetPosterity('0', '1');
		$tmp_depts = array();
        $current_corp = $this->UgDB->db_get_ug_by_ug_id('0');
		$tmp_depts[0]['id'] = $current_corp['ug_id'];
		$tmp_depts[0]['pid'] = $current_corp['parent_id'];
		$tmp_depts[0]['name'] = $current_corp['name'];
		foreach ($current_depts as $v)
		{
			$tmp_depts[$v['ug_id']] = array('id'=>$v['ug_id'],'pid'=>$v['parent_id'],'name'=>$v['name'],);
		}
		$option_str = '<select name="parent_id" id="parent_id" class="intext">';
		$option_str .= $this->show_struct_tree($tmp_depts, '-1', $level=1, $selected_ug_id);
		$option_str .= '</select>&nbsp;<span class="red-font">*</span>';
		echo $option_str;
	}

	/**
	 * 无限分类模拟树形结构显示<select><select/>
	 *
	 * @param array $arr = array(
									3=>array('id'=>'3','pid'=>'1','name'=>'111'),
									11=>array('id'=>'11','pid'=>'1','name'=>'222'),
								}
	 * @param int $pid
	 * @param int $level
	 * @return string 如<option>┣销售中心</option><option>&nbsp;┣销售一部</option>...
	 */
	public function show_struct_tree($arr, $pid = 0, $level = 1, $selected_id='')
	{
		$s = '';
		$space = $level == 1 ? '' : str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
		foreach ($arr as $id=>$arr2)
		{
			if ($arr2['pid'] == $pid)
			{
				if ($selected_id == $arr2['id'])
				{
					$s .= "<option value=\"$arr2[id]\" selected=\"selected\">$space 【$arr2[name]】</option>\n";
				}
				else
				{
					$s .= "<option value=\"$arr2[id]\" >$space 【$arr2[name]】</option>\n";
				}
				$s .= $this->show_struct_tree($arr, $arr2['id'], $level+1, $selected_id);
			}
		}
		return $s;
	}

	/**
	 * 组织位置上下移动
	 */
	public function on_up_down()
	{
		$ug_id = $_GET['ug_id'];
		$parent_id = $_GET['parent_id'];
		$action = $_GET['action'];
		$this->UgDB = $this->LoadDB('UgDB');
		$ug_id == $_SESSION['current_corp_id'] && exit;
		$ug_1 = $this->UgDB->db_get_ug_by_ug_id($ug_id);
		$ug_2 = $this->UgDB->db_get_next_location($action, $ug_1['parent_id'], $ug_1['location']);
		
		//如果没有指定移动的兄弟，则退出，不排序,防止字段location置0
		empty($ug_2) && exit;
		$this->UgDB->db_apply_up_down($ug_1['ug_id'], $ug_1['location'], $ug_2['ug_id'], $ug_2['location']);
		$this->notice->UgNotice($_SESSION['current_corp_id']);
		echo "<script>parent.tree.location='/ug/show_ug_tree'</script>";
	}










	//*************************************ajax

	public function on_set_per_page()
	{
		$name = $_GET['name'];
		$num = $_GET['num'];
		$_SESSION[$name] = $num;
		js_reload();
	}

	public function on_goto_user_list()
	{
		$this->UgDB = $this->LoadDB('UgDB');
		$ug_id = $_POST['ug_id'];
		$_SESSION['active_ug_id'] = $ug_id;
		$ug = $this->UgDB->db_get_ug_by_ug_id($ug_id);
		$_SESSION['active_ug_name'] = $ug['name'];
	}


	// -----------------------------------------------------------------------



	public function make_option_str_corps($corps)
	{
		$option_str = '';
		foreach ($corps as $v)
		{
			if ($v['ug_id'] == $_SESSION['current_corp_id'])
				$option_str .= "<option value='$v[ug_id]' selected>$v[name]</option>";
			else
				$option_str .= "<option value='$v[ug_id]'>$v[name]</option>";
		}
		return $option_str;
	}
	
	//人员管理 转向 用户管理页面
	public function on_go_to_user_list()
	{
		$ug_id = $_GET['ug_id'];
		$ug_name = $_GET['name'];
		$_SESSION['active_ug_id'] = $ug_id;
		$_SESSION['active_ug_name'] = $ug_name;
		echo "<script>parent.location='/user/'</script>";
	}
}
?>