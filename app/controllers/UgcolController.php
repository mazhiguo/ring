<?php
class UgColController extends DefaultController
{
    public $trans_status = array(
    	'0'=>'采集中',
    	'1'=>'采集完毕',
    	'2'=>'冻结',
    );

	public function on_index( )
	{
		$this->view->display("header.html");
		$this->view->display("ugcol/index.htm");
		$this->view->display("footer.html");
	}

    /**
	* 显示采集树
    */
	public function on_show_col_tree()
	{
		$this->view->display('ugcol/tree.html');
	}

    /**
	 * 采集活动及内容列表
	 */
	public function on_col_list()
	{
		//		print_r($_SESSION);
		$this->UgcolDB = $this->LoadDB('UgcolDB');

		$_SESSION['current_ugcol_id'] = empty($_SESSION['current_ugcol_id']) ? '1' : $_SESSION['current_ugcol_id'];
		$_SESSION['current_ugcol_id'] = isset($_GET['ug_id']) ? $_GET['ug_id'] : $_SESSION['current_ugcol_id'];

		$current_col = $this->UgcolDB->db_get_col_by_id($_SESSION['current_ugcol_id']);

		//同步用session记录current_ug的name,方便其他处使用name时不必再查找数据库
		$_SESSION['current_ugcol_name'] = $current_col['name'];

		$limit = !empty($_SESSION['ugcol_limit']) ? $_SESSION['ugcol_limit'] : 20 ;
        !isset($_GET['page']) && $_GET['page'] = 1;

        if ($current_col['sign'] == 3)
        {
			echo "<script>window.location.href='/modelsCol/list?id=".$_SESSION['current_ugcol_id']."'</script>";
			return;
		}

		$total_recode = $this->UgcolDB->db_count_sub_cols($_SESSION['current_ugcol_id'], $_SESSION['AdminUgId']);
		$total_pages = ceil($total_recode/$limit);
		$curr_page = max(intval($_GET['page']), 1);
		$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
		$offset = ($curr_page-1)*$limit;
		$in_url = "/ugcol/col_list/";

		$sub_cols1 = $this->UgcolDB->db_get_sub_cols($_SESSION['current_ugcol_id'], $_SESSION['AdminUgId'], $limit, $offset);

        $sub_depts1 = array();
        foreach ($sub_cols1 as $k=>$v)
        {
            $v['status'] = $this->trans_status[$v['status']];
            $sub_depts1[$k] = $v;
        }

		$out_url = $this->Page($total_recode, $limit, $curr_page, $in_url);

		$option_str = make_option_string($limit);
		/*****************************信息摘要*************************************************/
		//如为当前单位
//		if ($_SESSION['current_ug_id'] == $_SESSION['current_corp_id'])
//		{
//			$this->UserDB = $this->LoadDB('UserDB');
//			$total_users_num = $this->UserDB->db_count_all_users($_SESSION['current_corp_id']);
//			$this->view->assign('total_users_num', $total_users_num);
//		}
//		//如果为记录的某个部门
//		else
//		{
//			$cur_users_num = $this->UgDB->db_count_dept_users($_SESSION['current_ug_id']);
//			$all_depts = $this->UgDB->db_get_all_depts();
//			$sub_depts =  $this->GetPosterity($_SESSION['current_ug_id'], '1');
//			$total_users_num = $cur_users_num;
//			if (!empty($sub_depts))
//			{
//				foreach ($sub_depts as $v) $total_users_num +=  $this->UgDB->db_count_dept_users($v['ug_id']);
//			}
//			$role_arr = $this->UgDB->db_get_dept_roles($_SESSION['current_ug_id']);
//			$role_str = $role_arr[0]['name'].' '.$role_arr[1]['name'].' '.$role_arr[2]['name'].' ...';
//
//			$zero_sms_users_num = count($this->UgDB->db_count_zero_dept_sms_users($_SESSION['current_ug_id']));
//			$less_x_num = count($this->UgDB->db_count_lessx_dept_sms_users($_SESSION['current_ug_id'], 10));
//
//			$cur_users_num = $cur_users_num == ''? 0 : $cur_users_num;
//			$total_users_num = $total_users_num == '' ? 0 : $total_users_num;
//			$zero_sms_users_num = $zero_sms_users_num =='' ? 0 : $zero_sms_users_num;
//			$less_x_num = $less_x_num == '' ? 0 : $less_x_num;
//
//			$this->view->assign('zero_sms_users_num', $zero_sms_users_num);
//			$this->view->assign('less_x_num', $less_x_num);
//			$this->view->assign('role_str', $role_str);
//			$this->view->assign('cur_users_num', $cur_users_num);
//			$this->view->assign('total_users_num', $total_users_num);
//		}
		$this->view->assign('pages', $out_url);
		$this->view->assign('option_str', $option_str);
		$this->view->assign('sub_depts', $sub_depts1);
		$this->view->assign('current_col', $current_col);
		$this->view->display('header.html');
		$this->view->display('ugcol/list.html');
		$this->view->display('footer.html');
	}

	/**
	 * 增加采集活动或采集内容
	 */
	public function on_add_corp()
	{
		$this->UgcolDB = $this->LoadDB('UgcolDB');

		$ug['sign'] = $_POST['sign'];
		$ug['name'] = $_POST['name'];
		$ug['remark'] = $_POST['remark'];
		$ug['location'] = $this->UgcolDB->db_get_max_location()+1;
		$ug['create_account'] = $_SESSION['AdminAccount'];
        $ug['ug_id'] = $_SESSION['AdminUgId'];
        $ug['parent_id'] = $_POST['parent_id'];
        $ug['tmpl_id'] = $_POST['tmpl_id'];

		//如果编码为空，生成随机串作编码
		$ug['code'] = trim($_POST['code']) != '' ? $_POST['code'] : get_rnd_str(9, 0);

        if ($_POST['sign'] == '3')
        {
            // 添加采集内容
            if (trim($_POST['name']) == '')
    		{
    			$this->show_msg_ext('BACK', array('msg'=>'名称不能为空！'));
    			exit;
    		}
            if ($this->UgcolDB->db_check_name($_POST['name'], $ug['parent_id']) > 0)
    		{
                $this->show_msg_ext('BACK', array('msg'=>'该活动中已存在同名采集内容 请重新命名！'));
    			exit;
    		}
            if ($this->UgcolDB->db_check_code($ug['code']) > 0)
    		{
    			$this->show_msg_ext('BACK', array('msg'=>'编码已存在！'));
    			exit;
    		}

            // 入库
            $msg = '';
            $new_id = $this->UgcolDB->db_add_content($ug);
            if ($new_id == "0")
			{
                $this->show_msg_ext('BACK', array('msg'=>'增加新采集内容失败！'));
				exit;
			}else
            {
                $msg .= msg('增加新采集内容成功！');
            }
            $user_ids_arr = array();
            $user_ids_arr2 = array();
            if (!empty($_POST['valuelist']))
			{
                $user_ids_arr = str_explode_deep($_POST['valuelist'], ':');

			}
            if (!empty($_POST['valuelist2']))
			{
                $user_ids_arr2 = str_explode_deep($_POST['valuelist2'], ':');
			}
            foreach ($user_ids_arr as $user_id)
            {
			    if (in_array($user_id, $user_ids_arr2))
                    $this->UgcolDB->db_add_user_to_content($new_id, $user_id, 1,1);
                else
                    $this->UgcolDB->db_add_user_to_content($new_id, $user_id, 0,1);
			}
			$msg .= msg('增加采集内容执行人关系成功！');
            foreach ($user_ids_arr2 as $user_id)
			{
			    if (!in_array($user_id, $user_ids_arr))
                    $this->UgcolDB->db_add_user_to_content($new_id, $user_id,1, 0);
			}
			$msg .= msg('增加采集内容分享人关系成功！');

            js_reload('top.content.tree');
			$this->show_msg_ext('GOTO', array('url'=>'/ugcol/col_list/', 'msg'=>$msg));
            $this->_writeLog("增加新采集内容[$ug[name]]成功！");
        }else
        {
            exit('unknown request!');
        }
	}
	/**
	 * 显示增加采集内容
	 */
	public function on_show_add_corp()
	{
        $this->UgcolDB = $this->LoadDB('UgcolDB');
        //全部用户
        $userlist = $this->UgcolDB->db_get_user_by_ugid($_SESSION['AdminUgId']);
        $option_str = "";
		foreach ($userlist as $v)
		{
			$option_str .= "<option value='$v[user_id]'>$v[name]</option>";
		}
        $this->view->assign('parent_id', $_GET['parent_id']);
        $this->view->assign('option_str', $option_str);
		$this->view->display('header.html');
		$this->view->display('ugcol/add_corp.html');
		$this->view->display('footer.html');
	}

    /**
	 * 显示采集内容编辑页面
	 */
 	public function on_show_upd_corp()
	{
		$this->UgcolDB = $this->LoadDB('UgcolDB');
		$col_id = $_GET['ug_id'];

		$corp = $this->UgcolDB->db_get_col_by_id($col_id);
        $tmpls = $this->UgcolDB->db_get_all_tmpls();
        $option_str_tmpl = "";
        foreach ($tmpls as $v)
        {
            if ($v['id'] == $corp['tmpl_id'])
                $option_str_tmpl .= "<option selected value=\"$v[id]\">【$v[name]】</option>\n";
            else
                $option_str_tmpl .= "<option value=\"$v[id]\">【$v[name]】</option>\n";
        }

        //初始化已分配的权限名称和权限id
    	$option_str_unusedr = "";
        $option_str_usedr = "";
		$nbused = $this->UgcolDB->db_get_rusers_notin_content( $col_id, $_SESSION['AdminUgId'] );
		if (!empty($nbused))
		{
			foreach ( $nbused as $v)
			{
				$option_str_unusedr .= "<option value='$v[user_id]'>$v[name]</option>";
			}
		}
		//已分配
		$used = $this->UgcolDB->db_get_rusers_in_content($col_id);
		if (!empty($used))
		{
			foreach ($used as $v)
			{
				$option_str_usedr .= "<option value='$v[user_id]'>$v[name]</option>";
			}
		}
        $option_str_unusedw = "";
        $option_str_usedw = "";
		$nbused = $this->UgcolDB->db_get_wusers_notin_content( $col_id, $_SESSION['AdminUgId'] );
		if (!empty($nbused))
		{
			foreach ( $nbused as $v)
			{
				$option_str_unusedw .= "<option value='$v[user_id]'>$v[name]</option>";
			}
		}
		//已分配
		$used = $this->UgcolDB->db_get_wusers_in_content($col_id);
		if (!empty($used))
		{
			foreach ($used as $v)
			{
				$option_str_usedw .= "<option value='$v[user_id]'>$v[name]</option>";
			}
		}

		$this->view->assign('option_str_unusedr', $option_str_unusedr);
		$this->view->assign('option_str_usedr', $option_str_usedr);
		$this->view->assign('option_str_unusedw', $option_str_unusedw);
		$this->view->assign('option_str_usedw', $option_str_usedw);
        $this->view->assign('option_str_tmpl', $option_str_tmpl);
		$this->view->assign('corp', $corp);
		$this->view->display('header.html');
		$this->view->display('ugcol/upd_corp.html');
		$this->view->display('footer.html');
	}

    /**
	 * 采集内容编辑
	 */
	public function on_upd_corp()
	{
		$ug['id'] = $_POST['ug_id'];
		$ug['name'] = $_POST['name'];
        $ug['parent_id'] = $_POST['parent_id'];
        $ug['tmpl_id'] = $_POST['tmpl_id'];
		$ug['remark'] = $_POST['remark'];
		$ug['code'] = $_POST['code'] == '' ? 'Rand'.get_rnd_str() : $_POST['code'];
        $ug['ug_id'] = $_SESSION['AdminUgId'];

		$this->UgcolDB = $this->LoadDB('UgcolDB');
		//code检查。
		if ($this->UgcolDB->db_check_expect_self_code($ug['code'], $ug['id']) >0 )
		{
			$this->show_msg_ext('BACK', array('msg'=>'内容编码已存在！'));
			exit;
		}
		// name检查同一父活动下，除了是否有其他同名称的内容
		if ($this->UgcolDB->db_check_expect_self_name($_POST['name'], $ug['parent_id'], $ug['id']) >0 )
		{
			$this->show_msg_ext('BACK', array('msg'=>'同一采集活动下已存在同名采集内容！'));
			exit;
		}
        $msg = "";
		if ($this->UgcolDB->db_upd_content($ug) <=0)
		{
			$msg .= msg('修改采集内容信息失败！', 0);
			$this->show_msg_ext('BACK', array('msg'=>$msg));
			exit;
		}
		else
		{
			js_reload('top.content.tree');
			js_reload('top.nav');

			$msg .= msg('修改采集内容信息成功！');
		}

        $this->UgcolDB->db_del_user_from_content($ug['id']);
        $user_ids_arr = array();
        $user_ids_arr2 = array();
        if (!empty($_POST['valuelist']))
        {
            $user_ids_arr = str_explode_deep($_POST['valuelist'], ':');
		}
        if (!empty($_POST['valuelist2']))
		{
            $user_ids_arr2 = str_explode_deep($_POST['valuelist2'], ':');
		}
        foreach ($user_ids_arr as $user_id)
        {
		    if (in_array($user_id, $user_ids_arr2))
                $this->UgcolDB->db_add_user_to_content($ug['id'], $user_id, 1,1);
            else
                $this->UgcolDB->db_add_user_to_content($ug['id'], $user_id, 0,1);
		}
		$msg .= msg('更新采集内容执行人关系成功！');
        foreach ($user_ids_arr2 as $user_id)
		{
		    if (!in_array($user_id, $user_ids_arr))
                $this->UgcolDB->db_add_user_to_content($ug['id'], $user_id,1, 0);
		}
		$msg .= msg('更新采集内容分享人关系成功！');

		$url = '/ugcol/col_list/';
		$this->show_msg_ext('GOTO', array('url'=>$url, 'msg'=>$msg));

		$this->_writeLog("编辑采集内容[$ug[name]]成功！");
	}

    /**
	 * ajax请求模板类型
	 *
	 */
	public function on_ajax_show_tmpls()
	{
		$this->UgcolDB = $this->LoadDB('UgcolDB');
        $tmpls = $this->UgcolDB->db_get_all_tmpls();
        $option_str = '<select name="tmpl_id" id="tmpl_id" class="intext">';
        foreach ($tmpls as $v)
        {
            $option_str .= "<option value=\"$v[id]\">【$v[name]】</option>\n";
        }
		$option_str .= '</select>&nbsp;<span class="red-font">*</span>';

		echo $option_str;
	}

	/**
	 * 删除采集活动或内容
	 */
	public function on_del_col()
	{
		if ($_GET) //删除当前采集活动
		{
			$ug_ids_arr[] = $_GET['ug_id'];
		}
		else if ($_POST['del'])//批量删除
		{
			$ug_ids_arr = $_POST['del'];
		}
		if (empty($ug_ids_arr))
		{
			$this->show_msg_ext('BACK', array('msg'=>'没有被选中删除的对象！'));
			exit;
		}

		$this->UgcolDB = $this->LoadDB('UgcolDB');
        $col_num = count($ug_ids_arr);
        $col_record_num = 0;
        foreach ($ug_ids_arr as $var)
        {
            $col_record_num += $this->UgcolDB->db_del_col($var);
        }

		$msg = msg("共删除了{$col_num}个对象！");
		$msg .= msg("共删除了{$col_record_num}个关联信息！");
		$_SESSION['current_ugcol_id'] = '1';

		$this->show_msg_ext('GOTO', array('url'=>'/ugcol/col_list/', 'msg'=>$msg, 'timeout'=>5000));
		echo "<script>parent.tree.location.reload();</script>";
		$this->_writeLog("删除采集活动或内容成功！");
	}

	public function on_set_per_page()
	{
		$name = $_GET['name'];
		$num = $_GET['num'];
		$_SESSION[$name] = $num;
		js_reload();
	}
};
?>