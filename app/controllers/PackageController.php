<?php
class PackageController extends DefaultController
{

	/**
	 * 发送系统消息
	 *
	 */
	public function on_package()
	{
		if ($_POST)
		{
            $this->PackageDB = $this->LoadDB('PackageDB');
            $pack['name'] = $_POST['name'];
            $pack['tmpl_id'] = $_POST['tmpl_id'];
            $t = $this->PackageDB->db_get_one($pack['tmpl_id']);
            $tname = $t['name'];
            if (!empty($_POST['valuelist']))
			{
				$cids = str_replace(':', ',', $_POST['valuelist']);
                $cids = rtrim($cids, ",");
			}
            
            $A = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

    		$objPHPExcel = $this->excel;
    		$activeSheet = $objPHPExcel->getActiveSheet();
            
            // 设置表头
            $activeSheetStyle = $activeSheet->getStyle('A1');
            $activeSheetStyle->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $activeSheet->setCellValueExplicit('A1', "汇总名称：$pack[name]", PHPExcel_Cell_DataType::TYPE_STRING);
            $activeSheetStyle = $activeSheet->getStyle('A2');
            $activeSheetStyle->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $activeSheet->setCellValueExplicit('A2', "模板标示：$tname", PHPExcel_Cell_DataType::TYPE_STRING);

            $fields = $this->PackageDB->db_get_all_fields($pack['tmpl_id']);
            $data = $this->PackageDB->db_get_datalist_all($tname, $cids);
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

            $activeSheet->setTitle($pack['name']);
    		foreach($A as $i)
    			$activeSheet->getColumnDimension($i)->setAutoSize(true);
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

    		// 保存到文件
    	 	//$objWriter->save(WWWPATH.'download/'.$cname.'-'.time().'.xls');
    	 	$filename = WWWPATH."package/$pack[name]_".time().'.xls';
            $filepath = iconv("utf-8", "gb2312", $filename);
    	 	$objWriter->save($filepath);
            
            $pack['datacount'] = count($data);
            $pack['filepath'] = $filename;
            $pack['create_account'] = $_SESSION['AdminAccount'];
            // 入库
            if ($this->PackageDB->db_add_pack($pack) <= 0)
			{
                $this->show_msg_ext('BACK', array('msg'=>'增加新活动失败！'));
                exit;
			}
			else
			{
				$this->show_msg_ext('GOTO', array('url'=>'/package/package/', 'msg'=>'汇总成功.'));
			}
   
			$this->_writeLog("消息汇总成功！");
		}
		else
		{
            $this->PackageDB = $this->LoadDB('PackageDB');
            $tmpls = $this->PackageDB->db_get_all_tmpls();
            $option_str = '<select name="tmpl_id" id="tmpl_id" class="intext" ONCHANGE="onSelectChg();">';
            foreach ($tmpls as $v)
            {
                $option_str .= "<option value=\"$v[id]\">【$v[name]】</option>\n";
            }
    		$option_str .= '</select>';
		
            $this->view->assign('option_str',$option_str);
			$this->view->display('header.html');
			$this->view->display('package/package.html');
			$this->view->display('footer.html');
		}
	}
    
    /**
	 * 某个采集活动下的采集内容
	 */
	public function request_col_contents()
	{
		$this->PackageDB = $this->LoadDB('PackageDB');
		$str = "var arr = new Array();";
        $tmpl_id =$_GET['tmpl_id']; 
		$col_id = $_GET['col_id'];

		$cols_id_arr = array();

        if (isset($_GET['branch']) && $_GET['branch']== '1')
		{
			$child_cols = $this->GetPosterityCol($col_id, '1');
			$cols_id_arr = get_id_from_array($child_cols, "id");
			$cols_id_arr[] = $col_id;
		}
		else
		{
			$cols_id_arr[] = $col_id;
		}
		$cols_ids = implode(",", $cols_id_arr);

		$contents_arr = $this->PackageDB->db_get_col_contents($cols_ids, $tmpl_id);
		if (!empty($contents_arr))
		{
			$i = 0;
			foreach ($contents_arr as $content)
			{
				$str .= "arr[$i] = ['$content[name]', '$content[id]'];";
				$i++;
			}
		}
		// 如果是单位 特殊处理:显示增加未分配部门人员
		// 1.get ug 2.check ug is corp 3.get users in corp without  dept;
		echo $str;
	}

    // 已打包汇总的数据列表
    public function on_list()
	{
		$this->PackageDB = $this->LoadDB('PackageDB');

		$total_recode = $this->PackageDB->db_count_pack();
		$limit = !empty( $_SESSION['package_limit']) ? $_SESSION['package_limit'] : 10;
		$total_pages = ceil($total_recode/$limit);
		$curr_page = isset($_GET['page']) ? max(intval($_GET['page']), 1) : 1;
		$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
		$offset = ($curr_page-1)*$limit;
		$in_url = "/package/list/";
		$out_url = $this->Page($total_recode, $limit, $curr_page, $in_url);

		$option_str = make_option_string($limit);
		$pack_arr = $this->PackageDB->db_get_pack($limit, $offset);
		$this->view->assign('option_str',$option_str);
		$this->view->assign('pages',$out_url);
		$this->view->assign('pack_list',$pack_arr);
		$this->view->display('header.html');
		$this->view->display('package/list.html');
		$this->view->display('footer.html');
	}
    
    public function on_set_per_page()
	{
		$name = $_GET['name'];
		$num = $_GET['num'];
		$_SESSION[$name] = $num;
		js_reload();
	}
    
    /**
	 * 数据下载
	 */
	public function on_download()
	{
		$pack_id = $_GET['pack_id'];
        $this->PackageDB = $this->LoadDB('PackageDB');
        $pack = $this->PackageDB->db_get_pack_by_id($pack_id);
        header('Content-Description: File Transfer');
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download; charset=gbk");
		header('Content-Disposition:inline;filename="'.$pack[name].'_'.time().'.xls"');
		header("Content-Transfer-Encoding: binary");
	    header('Expires: 0');
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Pragma: no-cache");
		ob_clean();
	    flush();
        $filepath = iconv("utf-8", "gb2312", $pack['filepath']);
	    readfile($filepath);
	}

}
?>