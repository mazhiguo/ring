<?php
class ModelsController extends DefaultController
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
		$q = isset($_GET['q']) ? $_GET['q'] : '';
		$this->view->assign('q', $q);

		$total_recode = $this->ModelsDB->db_count($q);
		$limit = !empty( $_SESSION['tmpl_limit']) ? $_SESSION['tmpl_limit'] : 10;
		$total_pages = ceil($total_recode/$limit);
		$curr_page = isset($_GET['page']) ? max(intval($_GET['page']), 1) : 1;
		$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
		$offset = ($curr_page-1)*$limit;
		$in_url = "/models/list/";
		$out_url = $this->Page($total_recode, $limit, $curr_page, $in_url);

		$option_str = make_option_string($limit);
		$list = $this->ModelsDB->db_get_all($limit, $offset, $q);
		$this->view->assign('list', $list);
		$this->view->assign('option_str', $option_str);
		$this->view->assign('pages', $out_url);

		$this->view->display('header.html');
		$this->view->display('models/list.html');
		$this->view->display('footer.html');
	}

	// 添加模板
	public function on_add()
	{
		if (isset($_REQUEST) && trim($_REQUEST['name'])!='')
		{
			$name = $_REQUEST['name'];
			$desc = $_REQUEST['desc'];

			// 判断name是否已经存在
			$m = $this->ModelsDB->db_get_one_by_name($name);
			if ($m)
			{
				show_msg('GOTO', array('url'=>'/models/list/', 'msg'=>"模板标示:$name 已经存在."));
				return;
			}

			$res = $this->ModelsDB->db_add_model($name, $desc);
			if ($res)
			{
				$this->ModelsDB->db_create_table($name);
				show_msg('GOTO', array('url'=>'/models/list/', 'msg'=>'保存成功.'));
			}
            else show_msg('GOTO', array('url'=>'/models/list/', 'msg'=>'保存失败.'));
            return;
		}

		$this->view->display('header.html');
		$this->view->display('models/add.html');
		$this->view->display('footer.html');
	}

	// 复制模板
	public function on_copy()
	{
		$id = $_REQUEST['id'];
		$model = $this->ModelsDB->db_get_one($id);

		if (isset($_REQUEST['name']) && trim($_REQUEST['name'])!='')
		{
			$tname = $_REQUEST['name'];
			$tdesc = $_REQUEST['desc'];

			// 判断name是否已经存在
			$m = $this->ModelsDB->db_get_one_by_name($tname);
			if ($m)
			{
				show_msg('GOTO', array('url'=>'/models/copy?id='+$id, 'msg'=>"模板标示:$tname 已经存在."));
				return;
			}

			$res = $this->ModelsDB->db_add_model($tname, $tdesc);
			$m = $this->ModelsDB->db_get_one_by_name($tname);
			if ($res)
			{
				$this->ModelsDB->db_create_table($tname);
				// 添加字段
				$fs = $this->ModelsDB->db_get_all_fields($id);
				foreach($fs as $f)
				{
					$mid = $m['id'];
					$res = $this->ModelsDB->db_add_field($mid, $f['name'], $f['desc'], $f['type'], $f['lenght'], $f['values'], $f['rules'], $f['rulesdesc'], $f['required'], $f['isshow']);
					$this->ModelsDB->db_add_table_field($tname, $f['name'], $f['lenght'], $f['values']);
				}
				show_msg('GOTO', array('url'=>'/models/list/', 'msg'=>'保存成功.'));
			}
            else show_msg('GOTO', array('url'=>'/models/list/', 'msg'=>'保存失败.'));
            return;
		}

		$this->view->assign('model', $model);
		$this->view->display('header.html');
		$this->view->display('models/copy.html');
		$this->view->display('footer.html');
	}

	// 修改模板
	public function on_upd()
	{
		$id = $_REQUEST['id'];
		$model = $this->ModelsDB->db_get_one($id);

		if (isset($_REQUEST['name']) && trim($_REQUEST['name'])!='')
		{
			$name = $_REQUEST['name'];
			$desc = $_REQUEST['desc'];

			// 判断name是否已经存在
			$m = $this->ModelsDB->db_get_one_by_name($name, $id);
			if ($m)
			{
				show_msg('GOTO', array('url'=>'/models/list/', 'msg'=>"模板标示:$name 已经存在."));
				return;
			}

			$res = $this->ModelsDB->db_upd_model($id, $name, $desc);
			if ($res != -1)
			{
				$this->ModelsDB->db_update_tablename($model['name'], $name);
				show_msg('GOTO', array('url'=>'/models/list/', 'msg'=>'保存成功.'));
			}
            else show_msg('GOTO', array('url'=>'/models/list/', 'msg'=>'保存失败.'));
            return;
		}

		$this->view->assign('model', $model);

		$this->view->display('header.html');
		$this->view->display('models/upd.html');
		$this->view->display('footer.html');
	}

	// 删除模板
	public function on_del()
	{
		$ids = isset($_REQUEST['ids']) ? explode(',', $_REQUEST['ids']) : array($_REQUEST['id']);
		foreach($ids as $id)
		{
			if (trim($id) == '') continue;
			$model = $this->ModelsDB->db_get_one($id);
			$this->ModelsDB->db_delete_table($model['name']);
			$res = $this->ModelsDB->db_del_model($id);
		}
		if ($res)
		{
			show_msg('GOTO', array('url'=>'/models/list/', 'msg'=>'删除模板成功.'));
		}
        else show_msg('GOTO', array('url'=>'/models/list/', 'msg'=>'删除模板失败.'));
	}

	// 显示字段列表
	public function on_fields()
	{
		$mid = $_REQUEST['mid'];

		$model = $this->ModelsDB->db_get_one($mid);
		$count = $this->ModelsDB->db_get_data_count($model['name']);
		$this->view->assign('count', $count);

		$list = $this->ModelsDB->db_get_all_fields($mid);
		$this->view->assign('list', $list);
		$this->view->assign('mid', $mid);
		$this->view->display('header.html');
		$this->view->display('fields/list.html');
		$this->view->display('footer.html');
	}

	// 添加字段
	public function on_addfields()
	{
		$mid = $_REQUEST['mid'];

		if (isset($_REQUEST['name']) && trim($_REQUEST['name'])!='')
		{
			$name = $_REQUEST['name'];
			$desc = $_REQUEST['desc'];
			$type = $_REQUEST['type'];
			$lenght = 64;
            if ($type=='file' && trim($_REQUEST['lenght'])=='') $lenght = 255;
            else if ($type=='textarea' && trim($_REQUEST['lenght'])=='') $lenght = 512;
            else if (trim($_REQUEST['lenght'])!= '') $lenght = $_REQUEST['lenght'];
			$values = $_REQUEST['values'];
			$rules = $_REQUEST['rules'];
			$rulesdesc = $_REQUEST['rulesdesc'];
			$required = isset($_REQUEST['required']) ? $_REQUEST['required'] : 0;
			$isshow = isset($_REQUEST['isshow']) ? $_REQUEST['isshow'] : 0;

			// 判断name是否已经存在
			$m = $this->ModelsDB->db_get_one_field_by_name($mid, $name);
			if ($m)
			{
				show_msg('GOTO', array('url'=>'/models/fields?mid='.$mid, 'msg'=>"字段标示:$name 已经存在."));
				return;
			}

			$res = $this->ModelsDB->db_add_field($mid, $name, $desc, $type, $lenght, $values, $rules, $rulesdesc, $required, $isshow);
			if ($res)
			{
				$t = $this->ModelsDB->db_get_one($mid);
				$this->ModelsDB->db_add_table_field($t['name'], $name, $lenght, $values);
				show_msg('GOTO', array('url'=>'/models/fields?mid='.$mid, 'msg'=>'保存成功.'));
			}
            else show_msg('GOTO', array('url'=>'/models/fields?mid='.$mid, 'msg'=>'保存失败.'));
            return;
		}

		$this->view->assign('mid', $mid);
		$this->view->display('header.html');
		$this->view->display('fields/add.html');
		$this->view->display('footer.html');
	}

	// 修改字段
	public function on_updfields()
	{
		$id = $_REQUEST['id'];
		$model = $this->ModelsDB->db_get_one_field($id);

		if (isset($_REQUEST['name']) && trim($_REQUEST['name'])!='')
		{
			$mid = $model['mid'];
			$name = $_REQUEST['name'];
			$desc = $_REQUEST['desc'];
			$type = $_REQUEST['type'];
			$lenght = 64;
            if ($type=='file' && trim($_REQUEST['lenght'])=='') $lenght = 255;
            else if ($type=='textarea' && trim($_REQUEST['lenght'])=='') $lenght = 512;
            else if (trim($_REQUEST['lenght'])!= '') $lenght = $_REQUEST['lenght'];
			$values = $_REQUEST['values'];
			$rules = $_REQUEST['rules'];
			$rulesdesc = $_REQUEST['rulesdesc'];
			$required = isset($_REQUEST['required']) ? $_REQUEST['required'] : 0;
			$isshow = isset($_REQUEST['isshow']) ? $_REQUEST['isshow'] : 0;

			// 判断name是否已经存在
			$m = $this->ModelsDB->db_get_one_field_by_name($mid, $name, $id);
			if ($m)
			{
				show_msg('GOTO', array('url'=>'/models/fields?mid='.$mid, 'msg'=>"字段标示:$name 已经存在."));
				return;
			}

			$res = $this->ModelsDB->db_upd_field($id, $name, $desc, $type, $lenght, $values, $rules, $rulesdesc, $required, $isshow);
			if ($res != -1)
			{
				$t = $this->ModelsDB->db_get_one($mid);
				$this->ModelsDB->db_upd_table_field($t['name'], $model['name'] ,$name ,$lenght ,$values);
				show_msg('GOTO', array('url'=>'/models/fields?mid='.$mid, 'msg'=>'保存成功.'));
			}
            else show_msg('GOTO', array('url'=>'/models/fields?mid='.$mid, 'msg'=>'保存失败.'));
            return;
		}

		$this->view->assign('model', $model);
		$this->view->display('header.html');
		$this->view->display('fields/upd.html');
		$this->view->display('footer.html');
	}

	// 删除字段
	public function on_delfields()
	{
		$ids = isset($_REQUEST['ids']) ? explode(',', $_REQUEST['ids']) : array($_REQUEST['id']);
		foreach($ids as $id)
		{
			if (trim($id) == '')continue;
			$model = $this->ModelsDB->db_get_one_field($id);
			$t = $this->ModelsDB->db_get_one($model['mid']);
			$this->ModelsDB->db_del_table_field($t['name'], $model['name']);
			$res = $this->ModelsDB->db_del_field($id);
		}
		if ($res)
		{
			show_msg('GOTO', array('url'=>'/models/fields?mid='.$model['mid'], 'msg'=>'删除字段成功.'));
		}
        else show_msg('GOTO', array('url'=>'/models/fields?mid='.$model['mid'], 'msg'=>'删除字段失败.'));
	}

	// 显示生成页面
	public function on_show()
	{
		$cid = $_REQUEST['cid'];
		$mid = $_REQUEST['mid'];
		$model = $this->ModelsDB->db_get_one($mid);

		$fields = $this->ModelsDB->db_get_all_fields($mid);
		$htmls = array();
  		$_scripts = '';
		foreach($fields as $field)
		{
			$name = $field['name'];
			$desc = $field['desc'];
			$type = $field['type'];
			$values = $field['values'];
			$rules = $field['rules'];
			$rulesdesc = $field['rulesdesc'];
            $required = $field['required'];

            $_r = $required==1 ? '<span style="color:red">*</span>' : '';

            if ($required)
            {
				$_scripts .= <<<HTML
	if (\$('#m_{$name}').val() == '')
	{
		alert('{$desc} 为必填项.');
		return false;
	}
	\r\n
HTML;
            }

			if ($rules)
			{
				$_scripts .= <<<HTML
	var r = {$rules};
	if (!r.test(\$('#m_{$name}').val()))
	{
		alert('{$rulesdesc}');
		return false;
	}
	\r\n
HTML;
			}

			if($type == 'text')
			{
				$_html = "<input type='text' id='m_{$name}' name='$name' value='$values'/>";
			}
			else if($type == 'password')
			{
				$_html = "<input type='password' id='m_{$name}' name='$name' value=''/>";
			}
			else if($type == 'textarea')
			{
				$_html = "<textarea id='m_{$name}' name='$name' style='width:400px;height:230px;'></textarea>";
			}
			else if($type == 'file')
			{
				$_html = "<input type='file' id='m_{$name}' name='$name' />";
			}
			else if($type == 'select')
			{
				$_html = "<select id='m_{$name}' name='$name'>";
				$arr = explode('|', $values);
				foreach($arr as $i)
				{
					list($k, $v) = explode('=', $i);
					if(isset($k) && isset($v)) $_html .= "<option value='$k'>$v</option>";
				}
				$_html .= "</select>";
			}
			else if($type == 'radio')
			{
				$_html = "";
				$arr = explode('|', $values);
				foreach($arr as $i)
				{
					list($k, $v) = explode('=', $i);
					if(isset($k) && isset($v)) $_html .= "<input type='radio' id='m_{$name}' name='$name' value='$k'/> $v &nbsp;&nbsp;";
				}
			}
			else if($type == 'checkbox')
			{
				$_html = "";
				$arr = explode('|', $values);
				foreach($arr as $i)
				{
					list($k, $v) = explode('=', $i);
					if(isset($k) && isset($v)) $_html .= "<input type='checkbox' id='m_{$name}[]' name='{$name}[]' value='$k'/> $v &nbsp;&nbsp;";
				}
			}
			else if($type == 'editor')
			{
				$_html = "<textarea id='m_{$name}' name='$name' class='meditor' style='width:650px;height:230px;'></textarea>";
			}
			else if($type == 'datetime')
			{
                $_html = "<input type='text' id='m_{$name}' name='$name' value='$values'/>";
			}

            $htmls[] = array($desc, $_html.'&nbsp;'.$_r);
		}
		$this->view->assign('_scripts', $_scripts);
		$this->view->assign('htmls', $htmls);

		$this->view->assign('model', $model);
		$this->view->assign('cid', $cid);
		$this->view->display('header.html');
		$this->view->display('models/show.html');
		$this->view->display('footer.html');
	}

	// 添加模板数据
	public function on_adddata()
	{
		$cid = $_REQUEST['cid'];
		$mid = $_REQUEST['mid'];
		$t = $this->ModelsDB->db_get_one($mid);
		$fields = $this->ModelsDB->db_get_all_fields($mid);

		// 判断表单数据

		$admin = $this->AdminDB->db_get_admin_by_account($_SESSION['AdminAccount']);
        $data = array(
        	'cid'=>$cid,
            'ugid'=>$admin['ug_id'],
            'creator'=>$_SESSION['AdminAccount'],
            'create_time'=>date('Y-m-d H:i:s', time()),
            'status'=>$_REQUEST['status'],
        );
		$res = $this->ModelsDB->db_add_table_data($t['name'], $fields, $_REQUEST, $data);
		if ($res) show_msg('GOTO', array('url'=>'/modelsCol/list', 'msg'=>'添加成功.'));
        else show_msg('GOTO', array('url'=>'/modelsCol/list', 'msg'=>'添加失败.'));
	}

	// 显示修改生成页面
	public function on_showupd()
	{
        $cid = $_REQUEST['cid'];
        $mid = $_REQUEST['mid'];
        $id = $_REQUEST['id'];
		$model = $this->ModelsDB->db_get_one($mid);
        $tname = $model['name'];
        $data = $this->ModelsDB->db_get_table_data($tname, $id);

		$fields = $this->ModelsDB->db_get_all_fields($mid);
		$htmls = array();
		$_scripts = '';
		foreach($fields as $field)
		{
			$name = $field['name'];
			$desc = $field['desc'];
			$type = $field['type'];
			$values = $field['values'];
			$rules = $field['rules'];
			$rulesdesc = $field['rulesdesc'];
            $required = $field['required'];

            $_r = $required==1 ? '<span style="color:red">*</span>' : '';
            $vv = $data[$name];

            if ($required)
            {
				$_scripts .= <<<HTML
	if (\$('#m_{$name}').val() == '')
	{
		alert('{$desc} 为必填项.');
		return false;
	}
	\r\n
HTML;
            }

			if ($rules)
			{
				$_scripts .= <<<HTML
	var r = {$rules};
	if (!r.test(\$('#m_{$name}').val()))
	{
		alert('{$rulesdesc}');
		return false;
	}
	\r\n
HTML;
			}

			if($type == 'text')
			{
				$_html = "<input type='text' name='$name' value='$vv'/>";
			}
			else if($type == 'password')
			{
				$_html = "<input type='password' name='$name' value=''/>";
			}
			else if($type == 'textarea')
			{
				$_html = "<textarea name='$name' style='width:400px;height:230px;'>$vv</textarea>";
			}
			else if($type == 'file')
			{
			     $_html = "<input type='file' name='$name' /> ";
                 if (trim($vv) != '') $_html .= "<a href='/models/downfile?file=".base64_encode($vv)."' title='".basename($vv)."'>[点击下载]</a>";
			}
			else if($type == 'select')
			{
				$_html = "<select name='$name'>";
				$arr = explode('|', $values);
				foreach($arr as $i)
				{
					list($k, $v) = explode('=', $i);
					if(isset($k) && isset($v))
                    {
                        $checked = $vv==$k ? 'selected' : '';
                        $_html .= "<option value='$k' $checked>$v</option>";
                    }
				}
				$_html .= "</select>";
			}
			else if($type == 'radio')
			{
				$_html = "";
				$arr = explode('|', $values);
				foreach($arr as $i)
				{
					list($k, $v) = explode('=', $i);
					if(isset($k) && isset($v))
                    {
                        $checked = $vv==$k ? 'checked' : '';
                        $_html .= "<input type='radio' name='$name' value='$k' $checked/> $v &nbsp;&nbsp;";
                    }
				}
			}
			else if($type == 'checkbox')
			{
				$_html = "";
				$arr = explode('|', $values);
				foreach($arr as $i)
				{
					list($k, $v) = explode('=', $i);
					if(isset($k) && isset($v))
                    {
                        $vv_arr = explode(',', $vv);
                        $checked = in_array($k, $vv_arr) ? 'checked' : '';
                        $_html .= "<input type='checkbox' name='{$name}[]' value='$k' $checked/> $v &nbsp;&nbsp;";
                    }
				}
			}
			else if($type == 'editor')
			{
				$_html = "<textarea name='$name' id='{$name}_meditor' class='meditor' style='width:650px;height:230px;'>$vv</textarea>";
			}
			else if($type == 'datetime')
			{
				$_html = "<input type='text' name='$name' value='$vv'/>";
			}
			$htmls[] = array($desc, $_html.'&nbsp;'.$_r);
		}
		$this->view->assign('_scripts', $_scripts);
		$this->view->assign('htmls', $htmls);

		$this->view->assign('cid', $cid);
		$this->view->assign('id', $id);
		$this->view->assign('data', $data);
		$this->view->assign('model', $model);
		$this->view->display('header.html');
		$this->view->display('models/showupd.html');
		$this->view->display('footer.html');
	}

    // 编辑模板数据
    public function on_upddata()
    {
        $cid = $_REQUEST['cid'];
        $mid = $_REQUEST['mid'];
        $id = $_REQUEST['id'];
		$t = $this->ModelsDB->db_get_one($mid);
		$fields = $this->ModelsDB->db_get_all_fields($mid);

		// 判断表单数据

		$admin = $this->AdminDB->db_get_admin_by_account($_SESSION['AdminAccount']);
        $data = array(
        	'cid'=>$cid,
            'ugid'=>$admin['ug_id'],
            'creator'=>$_SESSION['AdminAccount'],
            'status'=>$_REQUEST['status'],
        );
		$res = $this->ModelsDB->db_upd_table_data($t['name'], $id, $fields, $_REQUEST, $data);
		if ($res) show_msg('GOTO', array('url'=>'/modelsCol/list/cid/'.$cid, 'msg'=>'修改成功.'));
        else show_msg('GOTO', array('url'=>'/modelsCol/list/cid/'.$cid, 'msg'=>'修改失败.'));
    }

	// 删除模板数据
	public function on_deldata()
	{
		$cid = $_GET['cid'];
		$mid = $_GET['mid'];
		$id = $_GET['id'];

		$t = $this->ModelsDB->db_get_one($mid);
		$tname = $t['name'];
		$res = $this->ModelsDB->db_del_table_data($tname, $id);
		if ($res) show_msg('GOTO', array('url'=>"/modelsCol/list/id/$cid", 'msg'=>'删除成功.'));
        else show_msg('GOTO', array('url'=>"/modelsCol/list/id/$cid", 'msg'=>'删除失败.'));
	}

	// 查看列表
	public function on_datalist()
	{
		$mid = $_GET['mid'];
		$t = $this->ModelsDB->db_get_one($mid);
		$tname = $t['name'];

		$total_recode = $this->ModelsDB->db_datalist_count($tname);
		$limit = !empty( $_SESSION['tmpl_limit']) ? $_SESSION['tmpl_limit'] : 10;
		$total_pages = ceil($total_recode/$limit);
		$curr_page = max(intval($_GET['page']), 1);
		$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
		$offset = ($curr_page-1)*$limit;
		$in_url = "/models/datalist/mid/$mid";
		$out_url = $this->Page($total_recode, $limit, $curr_page, $in_url);

		$option_str = make_option_string($limit);
		$list = $this->ModelsDB->db_get_datalist($tname, $limit, $offset);
		$this->view->assign('list', $list);
		$this->view->assign('option_str', $option_str);
		$this->view->assign('pages', $out_url);

		$fields = $this->ModelsDB->db_get_all_fields($mid);
		$this->view->assign('fields', $fields);
		$this->view->assign('mid', $mid);

		$this->view->display('header.html');
		$this->view->display('models/datalist.html');
		$this->view->display('footer.html');
	}

    // 数据视图
    public function on_dataviewlist()
    {
		$cid = $_GET['cid'];
		$mid = $_GET['mid'];
		$t = $this->ModelsDB->db_get_one($mid);
		$tname = $t['name'];

        $list = $this->ModelsDB->db_get_datalist_all($tname, $cid);
		$this->view->assign('list', $list);

        $fields = $this->ModelsDB->db_get_all_fields($mid);
		$this->view->assign('fields', $fields);
		$this->view->assign('mid', $mid);
		$this->view->assign('cid', $cid);

        $this->view->display('header.html');
		$this->view->display('models/dataviewlist.html');
		$this->view->display('footer.html');
    }

    public function on_download()
    {
    	$A = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

		$objPHPExcel = $this->excel;
		$activeSheet = $objPHPExcel->getActiveSheet();

		$cid = $_GET['cid'];
		$col = $this->ColDB->db_get_col_by_id($cid);
		$cname = $col['name'];
		$mid = $col['tmpl_id'];
		$t = $this->ModelsDB->db_get_one($mid);
		$tname = $t['name'];

		// 设置表头
		$activeSheetStyle = $activeSheet->getStyle('A1');
		$activeSheetStyle->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$activeSheet->setCellValueExplicit('A1', "采集内容：$cname", PHPExcel_Cell_DataType::TYPE_STRING);
		$activeSheetStyle = $activeSheet->getStyle('A2');
		$activeSheetStyle->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$activeSheet->setCellValueExplicit('A2', "模板标示：$tname", PHPExcel_Cell_DataType::TYPE_STRING);

		$fields = $this->ModelsDB->db_get_all_fields($mid);
		$data = $this->ModelsDB->db_get_datalist_all($tname, $cid);
		$titles = array();
		// 添加id字段
		array_unshift($fields, array('name'=>'id', 'desc'=>'编号', 'type'=>'text', 'values'=>''));
		for($i=0; $i<count($fields); $i++)
		{
			$f = $fields[$i];
			if (!$f) continue;

			// 添加注释
			if ($f['type']=='radio' || $f['type']=='select' || $f['type']=='checkbox')
            {
            	$activeSheetStyle = $activeSheet->getStyle($A[$i].'3');
				$activeSheetStyle->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$activeSheet->setCellValueExplicit($A[$i].'3', "$f[desc] 字段示例: $f[values]", PHPExcel_Cell_DataType::TYPE_STRING);
            }

			// 添加表头
            $activeSheetStyle = $activeSheet->getStyle($A[$i].'4');
			$activeSheetStyle->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$activeSheet->setCellValueExplicit($A[$i].'4', $f['desc'], PHPExcel_Cell_DataType::TYPE_STRING);
		}

		// 添加内容列表
		$r = 5;
		foreach($data as $d)
		{
			for($i=0; $i<count($fields); $i++)
			{
				$f = $fields[$i];
				if (!$f) continue;

				$cell = $A[$i].$r;
                $name = $d[$f['name']];
                $type = $f['type'];

				// 设置单元格值
                $activeSheetStyle = $activeSheet->getStyle($cell);
				$activeSheetStyle->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($type == 'file')
                {
					$activeSheet->setCellValueExplicit($cell, basename($name), PHPExcel_Cell_DataType::TYPE_STRING);
                }
                else if ($type=='radio' || $type=='select')
                {
                    $vs_arr = explode('|', $f['values']);
                    foreach($vs_arr as $vs)
                    {
                        list($k, $v) = explode('=', $vs);
                        if (!isset($k) || !isset($v)) continue;
                        if ($k == $name)
                        {
							$activeSheet->setCellValueExplicit($cell, $v, PHPExcel_Cell_DataType::TYPE_STRING);
                            break;
                        }
                    }
                }
                else if ($type == 'checkbox')
                {
                    $vs_arr = explode('|', $f['values']);
                    $s_arr = array();
                    foreach($vs_arr as $vs)
                    {
                        list($k, $v) = explode('=', $vs);
                        if (!isset($k) || !isset($v)) continue;

                        $name_arr = explode(',', $name);
                        if (in_array($k, $name_arr))
                        {
                            $s_arr[] = $v;
                            continue;
                        }
                    }
					$activeSheet->setCellValueExplicit($cell, implode('、', $s_arr), PHPExcel_Cell_DataType::TYPE_STRING);
                }
                else
					$activeSheet->setCellValueExplicit($cell, $name, PHPExcel_Cell_DataType::TYPE_STRING);
			}
			$r++;
		}

		$activeSheet->setTitle($cname);
		foreach($A as $i)
			$activeSheet->getColumnDimension($i)->setAutoSize(true);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

		// 保存到文件
	 	//$objWriter->save(WWWPATH.'download/'.$cname.'-'.time().'.xls');
	 	$filename = WWWPATH.'download/'.time().'.xls';
	 	$objWriter->save($filename);
		//echo "<script>alert('下载成功！'); window.location.href='/modelsCol/list/?id=$cid'</script>";

		// 输出到浏览器
  		header('Content-Description: File Transfer');
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download; charset=gbk");
		header('Content-Disposition:inline;filename="'.$cname.'-'.time().'.xls"');
		header("Content-Transfer-Encoding: binary");
	    header('Expires: 0');
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Pragma: no-cache");
		ob_clean();
	    flush();
	    readfile($filename);
		exit;
		//$objWriter->save('php://output');
    }

	// 下载模板文件
	public function on_download_csv()
	{
		$cid = $_GET['cid'];
		$col = $this->ColDB->db_get_col_by_id($cid);
		$cname = $col['name'];
		$mid = $col['tmpl_id'];
		$t = $this->ModelsDB->db_get_one($mid);
		$tname = $t['name'];
		$s = "采集内容:$cname\r\n";
		$s .= "模板标示:$tname\r\n";

		$fields = $this->ModelsDB->db_get_all_fields($mid);
		$data = $this->ModelsDB->db_get_datalist_all($tname, $cid);

		$titles = array();
        foreach($fields as $f)
        {
            if ($f['type']=='radio' || $f['type']=='select' || $f['type']=='checkbox')
                $s .= "$f[desc] 字段示例: $f[values]\r\n";
            $titles[] = $f['desc'];
        }
		$s .= implode(',', $titles)."\r\n";

		foreach($data as $d)
		{
			$row = array();
			foreach($fields as $f)
			{
                $name = $d[$f['name']];
                $type = $f['type'];

                if ($type == 'file')
                {
                    $row[] = basename($name);
                }
                else if ($type=='radio' || $type=='select')
                {
                    $vs_arr = explode('|', $f['values']);
                    foreach($vs_arr as $vs)
                    {
                        list($k, $v) = explode('=', $vs);
                        if (!isset($k) || !isset($v)) continue;
                        if ($k == $name)
                        {
                            $row[] = $v;
                            break;
                        }
                    }
                }
                else if ($type == 'checkbox')
                {
                    $vs_arr = explode('|', $f['values']);
                    $s_arr = array();
                    foreach($vs_arr as $vs)
                    {
                        list($k, $v) = explode('=', $vs);
                        if (!isset($k) || !isset($v)) continue;

                        $name_arr = explode(',', $name);
                        if (in_array($k, $name_arr))
                        {
                            $s_arr[] = $v;
                            continue;
                        }
                    }
                    $row[] = implode('、', $s_arr);
                }
                else $row[] = $name;
			}
			$s .= implode(',', $row)."\r\n";
		}

		$fname = $cname.'-'.time().'.csv';
        $fname = iconv('utf-8', 'gbk', $fname);
		@header("Content-Type: text/html; charset=gbk");
		@header("Content-Type:application/x-msdownload");
		@header("Content-Disposition:".(strstr($_SERVER[TTP_USER_AGENT],"MSIE")?"":"attachment;")."filename=$fname");
        //$s = mb_convert_encoding($s, 'gbk', 'utf-8');
		echo $s;
	}

	public function on_upload()
	{
		error_reporting(2047);
		$cid = $_GET['cid'];

		if (!empty($_FILES['mfile']))
		{
			$tmp_file = $_FILES['mfile']['tmp_name'];
			$fname = $_FILES['mfile']['name'];
			list($cname,) = explode('-', $fname);

			if (!isset($cname) || trim($cname)=='')
			{
				show_msg('GOTO', array('url'=>'/models/upload?cid='.$cid, 'msg'=>'请检查上传文件的文件名是否正确合法(文件名示例: 采集内容名称-下载日期.excel).'));
				return;
			}

			$col = $this->ColDB->db_get_col_by_name($cname);
			$mid = $col['tmpl_id'];
			// 判断模板表是否存在
			$model = $this->ModelsDB->db_get_one($mid);
			if (!$model)
			{
				show_msg('GOTO', array('url'=>'/models/upload?cid='.$cid, 'msg'=>'请检查上传文件的文件名是否正确合法(文件名示例: 采集内容名称-下载日期.csv).'));
				return;
			}

			$mid = $model['id'];
			$tname = $model['name'];
			$_fields = array();
			$fields = $this->ModelsDB->db_get_all_fields($mid);

			// 开始读取文件
			require_once LIBPATH.'classes/PHPExcel/IOFactory.php';
			$objReader = PHPExcel_IOFactory::createReader('Excel2007');
			$objExcel = PHPExcel_IOFactory::load($tmp_file);
			$currentSheet = $objExcel->getSheet(0);
			// 取得一共有多少列
			$allColumn = $currentSheet->getHighestColumn();
			// 取得一共有多少行
			$allRow = array($currentSheet->getHighestRow());

			// 添加编号列
			array_unshift($fields, array('name'=>'id', 'desc'=>'编号', 'type'=>'text', 'values'=>''));
			// 获取表题对应标示
			foreach($fields as $fs)
			{
				$name = $fs['name'];
				$desc = $fs['desc'];
				// 读取第四行标题
				for($col='A'; $col<=$allColumn; $col++)
				{
					$address = $col.'4';
					$value = $currentSheet->getCell($address)->getValue();
					if ($value == '') break;
     				if ($value == $desc)
					{
      					$_fields[$col] = $name;
      					break;
					}
				}
			}
			//var_dump($_fields); exit('--------------------------');

			$rows_data = array();
			for($row=5; $row<=$allRow; $row++)
			{
				$row_data = array();
				$flag = false;
				foreach($_fields as $_col=>$_name)
				{
					$address = $_col.$row;
					$v = $currentSheet->getCell($address)->getValue();
					// 替换变量
					$_f = false;
					foreach($fields as $fs)
					{
						$name = $fs['name'];
      					$type = $fs['type'];
      					$values = $fs['values'];

      					if ($name != $_name) continue;

      					if ($type=='radio' || $type=='select')
      					{
							$values_arr = explode('|', $values);
							$_kk = '';
							foreach($values_arr as $_va)
							{
								list($_k, $_v) = explode('=', $_va);
								if ($_v == $v)
								{
									$_kk = $_k;
									break;
								}
							}
							$row_data[$_name] = $_kk;
							break;
      					}
      					else if ($type == 'checkbox')
      					{
							$values_arr = explode('|', $values);
							$_k_arr = array();
							foreach(explode(',', $v) as $vv)
							{
								foreach($values_arr as $_va)
								{
									list($_k, $_v) = explode('=', $_va);
									if ($_v == $vv)
									{
										$_k_arr[] = $_k;
									}
								}
							}
							$row_data[$_name] = implode(',', $_k_arr);
							break;
      					}
      					else {
      						$row_data[$_name] = $v;
      						break;
      					}
					}
					if($v) $flag = true;
				}
				if (!$flag) break;
				$rows_data[] = $row_data;
			}
			//var_dump($rows_data); exit('=======================');

			// 有编号修改，无编号插入数据库
			$n = 0;
			foreach($rows_data as $row)
			{
				$admin = $this->AdminDB->db_get_admin_by_account($_SESSION['AdminAccount']);

    			//$table_fields = '`'.implode('`,`', array_keys($row)).'`';
    			//$table_row_data = "'".implode("','", $row)."'";

				$vs = array();
    			if (trim($row['id']) != '')
    			{
    				foreach($row as $k=>$v)
    				{
    					if ($k == 'id') continue;
    					$vs[] = "`$k`='$v'";
    				}
					$sql = "update m_{$tname} set ".implode(',', $vs)." where id=$row[id]";
    			}
    			else
    			{
    				$_fdata = array(
						'cid'=>$cid,
						'ugid'=>$admin['ug_id'],
						'creator'=>$_SESSION['AdminAccount'],
	        			'create_time'=>date('Y-m-d H:i:s', time()),
						'status'=>0
					);
			        $row = array_merge($row, $_fdata);

    				foreach($row as $k=>$v)
    				{
    					$vs[] = "`$k`='$v'";
    				}
					$sql = "insert into m_{$tname} set ".implode(',', $vs);
    			}

				//echo "<pre>"; var_dump($sql);
				$n += $this->dbh->exec($sql);
			}

			// 页面提示
			$col = $this->ColDB->db_get_col_by_id($cid);
			$cid = $col['id'];
			if ($n) show_msg('GOTO', array('url'=>"/modelsCol/list/id/$cid", 'msg'=>'上传成功.'));
	        else show_msg('GOTO', array('url'=>"/modelsCol/list/id/$cid", 'msg'=>'上传失败.'));
	        return;
		}

		$this->view->assign('cid', $cid);
		$this->view->display('header.html');
		$this->view->display('models/upload.html');
		$this->view->display('footer.html');
	}

	// 上传采集文件
	public function on_upload_ext()
	{
		$cid = $_GET['cid'];

		if (!empty($_FILES['mfile']))
		{
			$tmp_file = $_FILES['mfile']['tmp_name'];
			$fname = $_FILES['mfile']['name'];
			list($cname,) = explode('-', $fname);

			if (!isset($cname) || trim($cname)=='')
			{
				show_msg('GOTO', array('url'=>'/models/upload?cid='.$cid, 'msg'=>'请检查上传文件的文件名是否正确合法(文件名示例: 采集内容名称-下载日期.csv).'));
				return;
			}

			$col = $this->ColDB->db_get_col_by_name($cname);
			$mid = $col['tmpl_id'];
			// 判断模板表是否存在
			$model = $this->ModelsDB->db_get_one($mid);
			if (!$model)
			{
				show_msg('GOTO', array('url'=>'/models/upload?cid='.$cid, 'msg'=>'请检查上传文件的文件名是否正确合法(文件名示例: 采集内容名称-下载日期.csv).'));
				return;
			}

			$mid = $model['id'];
			$tname = $model['name'];
			$_fields = array();
			$fields = $this->ModelsDB->db_get_all_fields($mid);
			foreach($fields as $fs)
			{
				$name = $fs['name'];
				$desc = $fs['desc'];
				$_fields[$name] = $desc;
			}

			$n = 0;
			$flag = false;
            $s = file_get_contents($tmp_file);
            $s = iconv('gbk', 'utf-8//ignore', $s);
			$rows = explode("\r\n", $s);
			foreach($rows as $row)
			{
				$fs = explode(',', $row);
				// 判断文件前几行是否为标题
				if (!$flag)
				{
					foreach($fs as $f)
					{
						if (in_array($f, $_fields))
						{
							$flag = true;
							break;
						}
					}
				}
				// 正式插入数据
				else
				{
					$admin = $this->AdminDB->db_get_admin_by_account($_SESSION['AdminAccount']);
					$_fdata = array(
						'cid'=>$cid,
						'ugid'=>$admin['ug_id'],
						'creator'=>$_SESSION['AdminAccount'],
            			'create_time'=>date('Y-m-d H:i:s', time()),
						'status'=>0
					);
			        $_fields = array_merge($_fields, $_fdata);
			        $fs = array_merge($fs, $_fdata);

					$_s = "'".implode("','", $fs)."'";
					$_fs = '`'.implode('`,`', array_keys($_fields)).'`';
					$sql = "insert into m_{$tname}({$_fs}) values($_s) ";
					$n += $this->dbh->exec($sql);
				}
			}

			$col = $this->ColDB->db_get_col_by_id($cid);
			$cid = $col['id'];
			if ($n) show_msg('GOTO', array('url'=>"/modelsCol/list/id/$cid", 'msg'=>'上传成功.'));
	        else show_msg('GOTO', array('url'=>"/modelsCol/list/id/$cid", 'msg'=>'上传失败.'));
	        return;
		}

		$this->view->assign('cid', $cid);
		$this->view->display('header.html');
		$this->view->display('models/upload.html');
		$this->view->display('footer.html');
	}

    public function on_downfile()
    {
        $file = base64_decode($_GET['file']);
        $fname = basename($file);

        @header("Content-Type: text/html; charset=gbk");
		@header("Content-Type:application/x-msdownload");
		@header("Content-Disposition:".(strstr($_SERVER[TTP_USER_AGENT],"MSIE")?"":"attachment;")."filename=$fname");
        $s = file_get_contents($file);
        $s = iconv('utf-8', 'gbk', $s);
		echo $s;
    }

    // 修改状态
    public function on_updstatus()
    {
        $cid = $_GET['cid'];
        $mid = $_GET['mid'];
        $id = $_GET['id'];
        $status = $_GET['status'];

        $model = $this->ModelsDB->db_get_one($mid);

        $res = $this->ModelsDB->db_upd_status($model['name'], $id, $status);

		if ($res)
		{
			show_msg('GOTO', array('url'=>'/modelsCol/list?id='.$cid, 'msg'=>'操作成功.'));
		}
        else show_msg('GOTO', array('url'=>'/modelsCol/list?id='.$cid, 'msg'=>'操作失败.'));

    }

    // 测试
    public function on_test()
    {
		$objPHPExcel = $this->excel;
		$activeSheet = $objPHPExcel->getActiveSheet();

		//设置格式
		$activeSheetStyle = $activeSheet->getStyle('C2');
		$activeSheetStyle->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$activeSheet->setCellValueExplicit('C2', 'C2单元格的值 ', PHPExcel_Cell_DataType::TYPE_STRING);

		//选择下拉列表框
		$docValidation = $activeSheet->getCell('D2')->getDataValidation();
		$docValidation->setType((string) PHPExcel_Cell_DataValidation::TYPE_LIST);
		$docValidation->setErrorStyle((string) PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
		$docValidation->setOperator((string) PHPExcel_Cell_DataValidation::OPERATOR_NOTEQUAL);
		$docValidation->setAllowBlank(false);
		$docValidation->setShowDropDown(true);
		$docValidation->setShowInputMessage(true);
		$docValidation->setShowErrorMessage(true);
		$docValidation->setErrorTitle('输入的值有误');
		$docValidation->setError('您输入的值不在下拉框列表内.');
		$docValidation->setPromptTitle('下拉选择框');
		$docValidation->setPrompt('请从下拉框中选择您需要的值');
		$docValidation->setFormula1('A,B,C');

		$activeSheet->setTitle('sheet名称');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('d:/ttttttt.xls');
    }

    public function on_set_collector()
    {
        if ($_POST)
		{
            $this->UgcolDB = $this->LoadDB('UgcolDB');
            $cid = $_POST['cid'];
            $this->UgcolDB->db_del_user_from_content($cid);
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
                    $this->UgcolDB->db_add_user_to_content($cid, $user_id, 1,1);
                else
                    $this->UgcolDB->db_add_user_to_content($cid, $user_id, 0,1);
    		}
    		$msg = msg('更新采集内容执行人关系成功！');
            foreach ($user_ids_arr2 as $user_id)
    		{
    		    if (!in_array($user_id, $user_ids_arr))
                    $this->UgcolDB->db_add_user_to_content($cid, $user_id,1, 0);
    		}
    		$msg .= msg('更新采集内容分享人关系成功！');

    		$url = "/modelscol/list?id=$cid";
    		$this->show_msg_ext('GOTO', array('url'=>$url, 'msg'=>$msg));
        } else {
            $cid = $_GET['cid'];
            $this->ColDB = $this->LoadDB('ColDB');
            $col = $this->ColDB->db_get_col_by_id($cid);
            $this->UgcolDB = $this->LoadDB('UgcolDB');

            //初始化已分配的权限名称和权限id
        	$option_str_unusedr = "";
            $option_str_usedr = "";
    		$nbused = $this->UgcolDB->db_get_rusers_notin_content( $cid, $_SESSION['AdminUgId'] );
    		if (!empty($nbused))
    		{
    			foreach ( $nbused as $v)
    			{
    				$option_str_unusedr .= "<option value='$v[user_id]'>$v[name]</option>";
    			}
    		}
    		//已分配
    		$used = $this->UgcolDB->db_get_rusers_in_content($cid);
    		if (!empty($used))
    		{
    			foreach ($used as $v)
    			{
    				$option_str_usedr .= "<option value='$v[user_id]'>$v[name]</option>";
    			}
    		}
            $option_str_unusedw = "";
            $option_str_usedw = "";
    		$nbused = $this->UgcolDB->db_get_wusers_notin_content( $cid, $_SESSION['AdminUgId'] );
    		if (!empty($nbused))
    		{
    			foreach ( $nbused as $v)
    			{
    				$option_str_unusedw .= "<option value='$v[user_id]'>$v[name]</option>";
    			}
    		}
    		//已分配
    		$used = $this->UgcolDB->db_get_wusers_in_content($cid);
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
            $this->view->assign('col', $col);

            $this->view->display('header.html');
        	$this->view->display('modelscol/setcollector.html');
        	$this->view->display('footer.html');
        }
    }
}
?>