<?php
class ModelsColController extends DefaultController
{
	public function on_index()
	{
        if (isset($_GET['id']))
            $_SESSION['USERCOLID'] = $_GET['id'];
		$this->view->display('header.html');
		$this->view->display('modelscol/index.html');
		$this->view->display('footer.html');
	}

	public function on_show_tree()
	{
		$this->view->display('modelscol/tree.html');
	}

	// 用户采集内容
	public function on_list()
	{
		if (isset($_GET['id']))
			$id = $_GET['id'];
		elseif (isset($_SESSION['USERCOLID'])) $id = $_SESSION['USERCOLID'];
		else
		{
			$id = '1';
		}

		$col = $this->ColDB->db_get_col_by_id($id);
        // 根据id判断sign(0:显示采集活动列表, 1:显示采集内容列表(一个采集内容对应一个模板), 2:显示模板数据列表)
        if($col['sign'] == 0)
        {
            $_SESSION['USERCOLID'] = $id;
            // 获取采集活动总数
            $count = $this->ColDB->db_count_sub_cols_by_sign(1);
    		$this->view->assign('count', $count);
    		$this->view->assign('col', $col);
            $this->view->display('header.html');
    		$this->view->display('modelscol/actives.html');
    		$this->view->display('footer.html');
        }
        else if($col['sign'] == 1)
        {
            $_SESSION['USERCOLID'] = $id;
            // 获取采集内容总数
            $count = $this->ColDB->db_count_sub_cols_by_sign($id, 2);
            $num = $this->ColDB->db_count_sub_cols_by_sign_status($id, 2, 0);
    		$this->view->assign('num', $num);
    		$this->view->assign('count', $count);
    		$this->view->assign('col', $col);
            $this->view->display('header.html');
    		$this->view->display('modelscol/models.html');
    		$this->view->display('footer.html');
        }
        else if($col['sign'] == 2)
        {
            $_SESSION['USERCOLID'] = $id;
            $mid = $col['tmpl_id'];
    		$t = $this->ModelsDB->db_get_one($mid);
    		$tname = $t['name'];

    		$total_recode = $this->ModelsDB->db_datalist_count($tname, $id);
    		$limit = !empty( $_SESSION['tmpl_limit']) ? $_SESSION['tmpl_limit'] : 10;
    		$total_pages = ceil($total_recode/$limit);
    		$curr_page = isset($_GET['page']) ? max(intval($_GET['page']), 1) : 1;
    		$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
    		$offset = ($curr_page-1)*$limit;
    		$in_url = "/modelsCol/list/id/$id";
    		$out_url = $this->Page($total_recode, $limit, $curr_page, $in_url);

    		$option_str = make_option_string($limit);
    		$list = $this->ModelsDB->db_get_datalist($tname, $id, $limit, $offset);
    		$this->view->assign('list', $list);
    		$this->view->assign('option_str', $option_str);
    		$this->view->assign('pages', $out_url);

            // 需要显示的字段
    		$fields = $this->ModelsDB->db_get_all_fields_by_isshow($mid, 1);
    		$this->view->assign('fields', $fields);

    		$this->view->assign('col', $col);
    		$this->view->assign('tname', $tname);
    		$this->view->assign('mid', $mid);
            $this->view->assign('privs', $_SESSION['AdminPrivs']);
            $this->view->assign('dataprivs', $_SESSION['AdminPrivs'][$col['id']]);
    		$this->view->display('header.html');
    		$this->view->display('modelscol/datalist.html');
    		$this->view->display('footer.html');
        }
        else if ($col['sign'] == 3)
        {
            $_SESSION['current_ugcol_id'] = $id;
			$mid = $col['tmpl_id'];
    		$t = $this->ModelsDB->db_get_one($mid);
    		$tname = $t['name'];

    		$total_recode = $this->ModelsDB->db_datalist_count($tname, $id);
    		$limit = !empty( $_SESSION['tmpl_limit']) ? $_SESSION['tmpl_limit'] : 10;
    		$total_pages = ceil($total_recode/$limit);
    		$curr_page = isset($_GET['page']) ? max(intval($_GET['page']), 1) : 1;
    		$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
    		$offset = ($curr_page-1)*$limit;
    		$in_url = "/modelsCol/list/id/$id";
    		$out_url = $this->Page($total_recode, $limit, $curr_page, $in_url);

    		$option_str = make_option_string($limit);
    		$list = $this->ModelsDB->db_get_datalist($tname, $id, $limit, $offset);
    		$this->view->assign('list', $list);
    		$this->view->assign('option_str', $option_str);
    		$this->view->assign('pages', $out_url);

            // 需要显示的字段
    		$fields = $this->ModelsDB->db_get_all_fields_by_isshow($mid, 1);
    		$this->view->assign('fields', $fields);

    		$this->view->assign('col', $col);
    		$this->view->assign('tname', $tname);
    		$this->view->assign('mid', $mid);
            $this->view->assign('privs', $_SESSION['AdminPrivs']);
            $this->view->assign('dataprivs', $_SESSION['AdminPrivs'][$col['id']]);
    		$this->view->display('header.html');
    		$this->view->display('modelscol/datalist.html');
    		$this->view->display('footer.html');
        }
	}

    // 采集活动-采集内容
    public function on_tree()
    {
        header("Content-type:text/xml");
		$treeid = $_GET['id'];
        $col_id = $treeid == 'root' ? '1' : $treeid;//ajax请求一次根节点和第一级子节点
		$treexml = "<tree id='$treeid'>";

		// 第一次加载显示ROOT
		if ($treeid == 'root')
		{
			$collection = $this->TreeDB->db_get_col($col_id);
			$treexml .= "<item child='1' open='1' checked='' select='1' id='$collection[id]'
							text='$collection[name]' im0='root.gif' im1='root.gif' im2='root.gif'>
							<userdata name='ug_name'>$collection[name]</userdata>
							<userdata name='parent_id'>$collection[parent_id]</userdata>";
		}

		// 加载采集活动
		$items = $this->TreeDB->db_get_childcols($col_id);
		// sub item xml data
		foreach ($items as $item)
		{
			$select = isset($_SESSION['USERCOLID']) && $item['id']==$_SESSION['USERCOLID'] ? '1' : '';

			$_items = $this->TreeDB->db_get_childcols_ug($item['id'], $_SESSION['AdminUgId']);
			$child = empty($_items) ? 0 : 1;

			$treexml .= "<item child='$child' id='$item[id]' select='$select' open='1' checked='' text='$item[name]'>
							<userdata name='ug_name'>$item[name]</userdata>
							<userdata name='parent_id'>$item[parent_id]</userdata>
						";

			foreach ($_items as $_item)
			{
				$select = isset($_SESSION['USERCOLID']) && $_item['id']==$_SESSION['USERCOLID'] ? '1' : '';

				$_item_1 = $this->TreeDB->db_get_childcols($_item['id']);
				$child = empty($_item_1) ? 0 : 1;

				$treexml .= "<item child='$child' id='$_item[id]' select='$select' open='1' checked='' text='$_item[name]'>
								<userdata name='ug_name'>$_item[name]</userdata>
								<userdata name='parent_id'>$_item[parent_id]</userdata>
							</item>";
			}

			$treexml .= "</item>";
		}

		//第一次加载树结束item标签
		if ($treeid == 'root') $treexml .= "</item>";
	 	$treexml .= "</tree>";

	 	// 调用ob_gzip 方法 来实现树的快速输出
		ob_start('ob_gzip');
		echo $treexml;
		//输出压缩成果
		ob_end_flush();
    }

    // 修改采集内容状态
    public function on_updstatus()
    {
        $cid = $_GET['cid'];
        $status = $_GET['status'];
        $_status = 0;
        if ($status == 0) $_status = 1;
        $res = $this->ColDB->db_upd_status($cid, $_status);

        header("Location: /modelsCol/list/?id=$cid");
    }
}
?>