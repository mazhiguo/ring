<?php
class ColController extends DefaultController
{
    public $trans_status = array(
    	'0'=>'采集中',
    	'1'=>'采集完毕',
    	'2'=>'冻结',
    );
    
	public function on_index( )
	{
		$this->view->display("header.html");
		$this->view->display("col/index.htm");
		$this->view->display("footer.html");
	}

    /**
	* 显示采集树
    */
	public function on_show_col_tree()
	{
		$this->view->display('col/tree.html');
	}
    
    /**
	 * 采集活动及内容列表
	 */
	public function on_col_list()
	{
		//		print_r($_SESSION);
		$this->ColDB = $this->LoadDB('ColDB');

		$_SESSION['current_col_id'] = empty($_SESSION['current_col_id']) ? '1' : $_SESSION['current_col_id'];
		$_SESSION['current_col_id'] = isset($_GET['ug_id']) ? $_GET['ug_id'] : $_SESSION['current_col_id'];

		//当前管理的组织（当前单位或某个子部门）信息
		$current_col = $this->ColDB->db_get_col_by_id($_SESSION['current_col_id']);

		//同步用session记录current_ug的name,方便其他处使用name时不必再查找数据库
		$_SESSION['current_col_name'] = $current_col['name'];

		$limit = !empty($_SESSION['col_limit']) ? $_SESSION['col_limit'] : 20 ;
		
        !isset($_GET['page']) && $_GET['page'] = 1;

		$total_recode = $this->ColDB->db_count_sub_cols($_SESSION['current_col_id']);
		$total_pages = ceil($total_recode/$limit);
		$curr_page = max(intval($_GET['page']), 1);
		$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
		$offset = ($curr_page-1)*$limit;
		$in_url = "/col/col_list/";

		$sub_cols1 = $this->ColDB->db_get_sub_cols($_SESSION['current_col_id'], $limit, $offset);
        
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
		$this->view->display('col/list.html');
		$this->view->display('footer.html');
	}

	/**
	 * 增加采集活动或采集内容
	 */
	public function on_add_ug()
	{
		$this->ColDB = $this->LoadDB('ColDB');

		$ug['sign'] = $_POST['sign'];
		$ug['name'] = $_POST['name'];
		$ug['remark'] = $_POST['remark'];
		$ug['location'] = $this->ColDB->db_get_max_location()+1;
		$ug['create_account'] = $_SESSION['AdminAccount'];

		//如果编码为空，生成随机串作编码
		$ug['code'] = trim($_POST['code']) != '' ? $_POST['code'] : get_rnd_str(9, 0);
        
        if ($_POST['sign'] == '1')
        {
            // 添加采集活动
            $ug['parent_id'] = "1";
            if (trim($_POST['name']) == '')
            {
                $this->show_msg_ext('BACK', array('msg'=>'名称不能为空！'));
                exit;
            }
            if ($this->ColDB->db_check_name($_POST['name'], "1") > 0)
            {
        	   $this->show_msg_ext('BACK', array('msg'=>'同名活动已存在 请重新命名！'));
        	   exit;
		    }
            if ($this->ColDB->db_check_code($ug['code']) > 0)
            {
                $this->show_msg_ext('BACK', array('msg'=>'编码已存在！'));
                exit;
            }
            
            // 入库
            if ($this->ColDB->db_add_ug($ug) <= 0)
			{
                $this->show_msg_ext('BACK', array('msg'=>'增加新活动失败！'));
                exit;
			}
			else
			{
				js_reload('top.content.tree');
                $msg .= msg('增加新活动成功！');
			}
			$this->show_msg_ext('GOTO', array('url'=>'/col/col_list/', 'msg'=>$msg));
            $this->_writeLog("增加新活动[$ug[name]]成功！");
        }elseif ($_POST['sign'] == '2')
        {
            // 添加采集内容
            $ug['parent_id'] = $_POST['parent_id'];
            $ug['tmpl_id'] = $_POST['tmpl_id'];
            if (trim($_POST['name']) == '')
    		{
    			$this->show_msg_ext('BACK', array('msg'=>'名称不能为空！'));
    			exit;
    		}
            if ($this->ColDB->db_check_name($_POST['name'], $ug['parent_id']) > 0)
    		{
                $this->show_msg_ext('BACK', array('msg'=>'该活动中已存在同名采集内容 请重新命名！'));
    			exit;
    		}
            if ($this->ColDB->db_check_code($ug['code']) > 0)
    		{
    			$this->show_msg_ext('BACK', array('msg'=>'编码已存在！'));
    			exit;
    		}
            
            // 入库
            $msg = '';
            $new_id = $this->ColDB->db_add_content($ug); 
            if ($new_id == "0")
			{
                $this->show_msg_ext('BACK', array('msg'=>'增加新采集内容失败！'));
				exit;
			}else
            {
                $msg .= msg('增加新采集内容成功！');
            }
            if (!empty($_POST['valuelist']))
			{
                $ug_ids_arr = str_explode_deep($_POST['valuelist'], ',');
                foreach ($ug_ids_arr as $ug_id)
				{
					$this->ColDB->db_add_ug_to_content($new_id, $ug_id);
				}
				$msg .= msg('增加采集内容执行部门关系成功！');
			}

			$this->show_msg_ext('GOTO', array('url'=>'/col/col_list/', 'msg'=>$msg));
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
        $this->view->assign('parent_id', $_GET['parent_id']);
		$this->view->display('header.html');
		$this->view->display('col/add_corp.html');
		$this->view->display('footer.html');
	}

	/**
	 * 显示增加采集活动
	 */
	public function on_show_add_dept()
	{
		$this->view->display('header.html');
		$this->view->display('col/add_dept.html');
		$this->view->display('footer.html');
	}

    /**
	 * 显示修改采集活动页面
	 */
	public function on_show_upd_dept()
	{
		$this->ColDB = $this->LoadDB('ColDB');
		$ug_id = $_GET['ug_id'];

		// 采集活动编辑显示，提取采集活动信息
		$dept = $this->ColDB->db_get_col_by_id($ug_id);

		$this->view->assign('dept', $dept);
		$this->view->display('header.html');
		$this->view->display('col/upd_dept.html');
		$this->view->display('footer.html');
	}
    
    /**
	 * 显示采集内容编辑页面
	 */
 	public function on_show_upd_corp()
	{
		$this->ColDB = $this->LoadDB('ColDB');
		$col_id = $_GET['ug_id'];

		$corp = $this->ColDB->db_get_col_by_id($col_id);
        $tmpls = $this->ColDB->db_get_all_tmpls();
        $option_str_tmpl = "";
        foreach ($tmpls as $v)
        {
            if ($v['id'] == $corp['tmpl_id'])
                $option_str_tmpl .= "<option selected value=\"$v[id]\">【$v[name]】</option>\n";
            else
                $option_str_tmpl .= "<option value=\"$v[id]\">【$v[name]】</option>\n";
        }
        $this->view->assign('option_str_tmpl', $option_str_tmpl);
		$this->view->assign('corp', $corp);
		$this->view->display('header.html');
		$this->view->display('col/upd_corp.html');
		$this->view->display('footer.html');
	}
   
    /**
	 * 采集活动编辑
	 */
	public function on_upd_ug()
	{
		$ug['id'] = $_POST['ug_id'];
		$ug['name'] = $_POST['name'];
        $ug['parent_id'] = $_POST['parent_id'];
		$ug['remark'] = $_POST['remark'];
		$ug['code'] = $_POST['code'] == '' ? 'Rand'.get_rnd_str() : $_POST['code'];

		$this->ColDB = $this->LoadDB('ColDB');
		//code检查
		if ($this->ColDB->db_check_expect_self_code($ug['code'], $ug['id']) >0 )
		{
			$this->show_msg_ext('BACK', array('msg'=>'编码已存在！'));
			exit;
		}
		// name检查是否有其他同名称的采集活动
		if ($this->ColDB->db_check_expect_self_name($_POST['name'], $ug['parent_id'], $ug['id']) >0 )
		{
			$this->show_msg_ext('BACK', array('msg'=>'已存在同名采集活动！'));
			exit;
		}

		if ($this->ColDB->db_upd_ug($ug) <=0)
		{
			$msg .= msg('修改采集活动信息失败！', 0);
			$this->show_msg_ext('BACK', array('msg'=>$msg));
			exit;
		}
		else
		{
			js_reload('top.content.tree');
			js_reload('top.nav');

			$msg .= msg('修改采集活动信息成功！');
		}

		$url = '/col/col_list/';
		$this->show_msg_ext('GOTO', array('url'=>$url, 'msg'=>$msg));
		
		$this->_writeLog("编辑采集活动[$ug[name]]成功！"); 
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

		$this->ColDB = $this->LoadDB('ColDB');
		//code检查。
		if ($this->ColDB->db_check_expect_self_code($ug['code'], $ug['id']) >0 )
		{
			$this->show_msg_ext('BACK', array('msg'=>'内容编码已存在！'));
			exit;
		}
		// name检查同一父活动下，除了是否有其他同名称的内容
		if ($this->ColDB->db_check_expect_self_name($_POST['name'], $ug['parent_id'], $ug['id']) >0 )
		{
			$this->show_msg_ext('BACK', array('msg'=>'同一采集活动下已存在同名采集内容！'));
			exit;
		}

		if ($this->ColDB->db_upd_content($ug) <=0)
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

        if (!empty($_POST['valuelist']))
        {
            $this->ColDB->db_del_ug_from_content($ug['id']);
            $ug_ids_arr = str_explode_deep($_POST['valuelist'], ',');
            foreach ($ug_ids_arr as $ug_id)
			{
				$this->ColDB->db_add_ug_to_content($ug['id'], $ug_id);
			}
			
			$msg .= msg('更新采集内容执行小组关系成功！');
        }
            
		$url = '/col/col_list/';
		$this->show_msg_ext('GOTO', array('url'=>$url, 'msg'=>$msg));
		
		$this->_writeLog("编辑采集内容[$ug[name]]成功！"); 
	}    
    
    /**
	 * ajax请求模板类型
	 *
	 */
	public function on_ajax_show_tmpls()
	{
		$this->ColDB = $this->LoadDB('ColDB');
        $tmpls = $this->ColDB->db_get_all_tmpls();
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
        
		$this->ColDB = $this->LoadDB('ColDB');
        $col_num = count($ug_ids_arr);
        $col_record_num = 0;
        foreach ($ug_ids_arr as $var)
        {
            $col_record_num += $this->ColDB->db_del_col($var);
        }

		$msg = msg("共删除了{$col_num}个对象！");
		$msg .= msg("共删除了{$col_record_num}个关联信息！");
		$_SESSION['current_col_id'] = '1';

		$this->show_msg_ext('GOTO', array('url'=>'/col/col_list/', 'msg'=>$msg, 'timeout'=>5000));
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