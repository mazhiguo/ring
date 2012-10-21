<?php
class TreeController extends DefaultController 
{	
    /**
	 * 显示采集活动树
	 */	
	public function printCollectionTree()
	{
		header("Content-type:text/xml"); 		
		$this->TreeDB = $this->LoadDB('TreeDB');
		$treeid = $_GET['id'];
        
		$treexml = "<tree id='$treeid'>";
        $col_id = $treeid == 'root' ? '1' : $treeid;//ajax请求一次根节点和第一级子节点
       		
		//file_put_contents("c:\\a.txt", "treeid:$treeid\r\n", FILE_APPEND);
		// 第一次加载显示ROOT
		if ($treeid == 'root')
		{
            $collection = array();
            $collection = $this->TreeDB->db_get_col($col_id);
			
			$treexml .= "<item child='1' open='1' checked='' select='1' id='$collection[id]' 
							text='$collection[name]' im0='root.gif' im1='root.gif' im2='root.gif'>
							<userdata name='ug_name'>$collection[name]</userdata>
							<userdata name='parent_id'>$collection[parent_id]</userdata>";
		}

		// 加载采集活动
		$chilDepts = array();
        if (isset($_GET['tree_from']) && $_GET['tree_from']=='ug')
        {
            $childDepts = $this->TreeDB->db_get_childugcols($col_id, $_SESSION['AdminUgId']);  
        }else
        {
            $childDepts = $this->TreeDB->db_get_childcols($col_id);            
        }

		// sub item xml data
		foreach ($childDepts as $dept)
		{
            $select = '';
            if (isset($_GET['tree_from']) && $_GET['tree_from']=='ug')
            {
                $select = $dept['id']==$_SESSION['current_ugcol_id'] ? '1' : '';
            }else if (isset($_GET['tree_from']) && $_GET['tree_from']=='col')
            {
                $select = $dept['id']==$_SESSION['current_col_id'] ? '1' : '';                
            }
			
			$treexml .= "<item child='0' id='$dept[id]' select='$select' open='1' checked='' text='$dept[name]'>
							<userdata name='ug_name'>$dept[name]</userdata>
							<userdata name='parent_id'>$dept[parent_id]</userdata>
						</item>";
		}
		
		//第一次加载树结束item标签
		if ($treeid == 'root') $treexml .= "</item>";	
	 	$treexml .= "</tree>";
	 	//file_put_contents("c:\\a.txt", "treexml:$treexml\r\n", FILE_APPEND);
	 	// 调用ob_gzip 方法 来实现树的快速输出
		ob_start('ob_gzip');
		echo $treexml;
		//输出压缩成果
		ob_end_flush();
	}
    
    /**
	 * 显示组织机构树
	 */	
	public function printUgTree()
	{
		header("Content-type:text/xml"); 		
		$this->TreeDB = $this->LoadDB('TreeDB');
		$treeid = $_GET['id'];

		$ug_id = $treeid == 'root' ? '0' : $treeid;//ajax请求一次根节点和第一级子节点
        
        // 根据树的用途获取已选中的节点
        $ug_ids_arr = array();// 控制树节点展开的数组
        if (isset($_GET['col_id']) && $_GET['col_id'] != '')
        {
            $tmp_arr = $this->TreeDB->db_get_ugs_in_content($_GET['col_id']);
            foreach ($tmp_arr as $v) $ug_ids_arr[] = $v['ug_id'];
        }elseif (isset($_GET['role_id']) && $_GET['role_id'] != '')
        {
            $tmp_arr = $this->TreeDB->db_get_ugs_in_role($_GET['role_id']);
            foreach ($tmp_arr as $v) $ug_ids_arr[] = $v['ug_id'];
        }
        if (isset($_GET['tree_from']) && $_GET['tree_from']=='user')
        {
            $ug_ids_arr[] = $_SESSION['active_ug_id'];
        }elseif (isset($_GET['tree_from']) && $_GET['tree_from']=='ug')
        {
            $ug_ids_arr[] = $_SESSION['current_ug_id'];
        }
        //print_r($ug_ids_arr);
        
		$treexml = "<tree id='$treeid'>";
        if ($treeid == 'root')
		{
			$corp = $this->TreeDB->db_get_ug($ug_id);
            $checked = in_array($ug_id, $ug_ids_arr) ? 'checked' : '';
			$treexml .= "<item child='1' open='1' checked='$checked' select='1' id='$corp[ug_id]' 
							text='$corp[name]' im0='root.gif' im1='root.gif' im2='root.gif'>
							<userdata name='ug_name'>$corp[name]</userdata>
							<userdata name='parent_id'>$corp[parent_id]</userdata>";
		}
        
        // 加载下级部门
		$chilDepts = array();
		$childDepts = $this->TreeDB->db_get_childdepts($ug_id);
        
        // sub item xml data
		foreach ($childDepts as $dept)
		{
			$child = $dept['child_id'] != '' ? '1' : '0';
			$checked = in_array($dept['ug_id'], $ug_ids_arr) ? 'checked' : '';
            if (isset($_GET['tree_from']) && $_GET['tree_from']=='user')
                $select = $dept['ug_id'] == $_SESSION['active_ug_id'] ? '1' : '';
            elseif (isset($_GET['tree_from']) && $_GET['tree_from']=='ug')
                $select = $dept['ug_id'] == $_SESSION['current_ug_id'] ? '1' : '';
            else
                $select = '';
			//节点是否再次请求展开,缺省不展开，编辑用户和编辑通讯录展开
			$open = $this->ChildrenHasChecked($ug_ids_arr, $dept['ug_id']) ? '1' : '';
			$treexml .= "<item child='$child' id='$dept[ug_id]' select='$select' open='$open' checked='$checked' text='$dept[name]'>
							<userdata name='ug_name'>$dept[name]</userdata>
							<userdata name='parent_id'>$dept[parent_id]</userdata>
						</item>";
		}
		
		//第一次加载树结束item标签
		if ($treeid == 'root') $treexml .= "</item>";	
	 	$treexml .= "</tree>";
	 	//file_put_contents("c:\\a.txt", "treexml:$treexml\r\n", FILE_APPEND);
	 	// 调用ob_gzip 方法 来实现树的快速输出
		ob_start('ob_gzip');
		echo $treexml;
		//输出压缩成果
		ob_end_flush();
	}
    
	/**
	 * 系统内公用动态数
	 * 1. 用户管理，部门管理target.location.href树
	 * 2. 增加用户，编辑用户 checkbox树
	 * 		2.1 增加用户，默认展开一级
	 * 		2.2 编辑用户，循环请求展开有选中的节点
	 * 3. 通讯录增加，编辑 checkbox树
	 * 		3.1 增加用户，默认展开一级
	 * 		3.2 编辑用户，循环请求展开有选中的节点
	 * 4. 组织结构选人公用onclick事件树
	 * add by zhangqitao @2008-04-16
	 */	
	public function printCommonTree()
	{
		header("Content-type:text/xml"); 		
		$this->TreeDB = $this->LoadDB('TreeDB');
		$treeid = $_GET['id'];
		
		$ug_id = $treeid == 'root' ? $_SESSION['current_corp_id'] : $treeid;//ajax请求一次根节点和第一级子节点
		if ($treeid == 'root')
		{
			// 切换管理单位
			if (isset($_GET['corp_id']) && $_GET['corp_id'] != $_SESSION['current_corp_id'])
				$ug_id = $_GET['corp_id'];
			else
				$ug_id = $_SESSION['current_corp_id'];
		}
		else
		{
			$ug_id = $treeid;
		}
		$treexml = "";
		$treexml .= "<tree id='$treeid'>";
		
		// 从配置文件中取相应配置，看是否需要取子节点时是否包含单位
		$cfg = _parse_ini_file(APPPATH.'ms-web.ini', true);
		//file_put_contents("c:\\a.txt", "treeid:$treeid\r\n", FILE_APPEND);
		// 第一次加载显示ROOT
		if ($treeid == 'root')
		{
			// 控制根节点。
			if (isset($_GET['view_sub_corps']))
			{
				if ($cfg['tree_root'][$_GET['view_sub_corps']] == '2') $ug_id = '0';					
			}
			
			$corp = $this->TreeDB->db_get_ug($ug_id);
			$treexml .= "<item child='1' open='1' checked='check' select='1' id='$corp[ug_id]' 
							text='$corp[name]' im0='root.gif' im1='root.gif' im2='root.gif'>
							<userdata name='ug_name'>$corp[name]</userdata>
							<userdata name='parent_id'>$corp[parent_id]</userdata>";
		}
		
		// 是否取子单位
		$view_sub_corps = isset($_GET['view_sub_corps']) && $cfg['tree_root'][$_GET['view_sub_corps']] != 1 ? true : false;
		
		// 加载下级部门
		$chilDepts = array();
		$childDepts = $this->TreeDB->db_get_childdepts($ug_id, $view_sub_corps);
		$depts_id_arr = array();
		
		if (isset($_GET['tree_no_session'])) $_SESSION['ug_id'] = '';
		//file_put_contents("d:\\tree.log", print_r($_GET, true)."\r\n".print_r($_POST, true), FILE_APPEND);
		// for add suer, default active dept
		if (isset($_GET['dept_id']))
		{
			if ($_GET['dept_id'] != $_SESSION['current_corp_id'])
				$depts_id_arr[] = $_GET['dept_id'];
		}		
		// for upd user , get user's depts
		else if (isset($_GET['user_id']))
		{
			$this->UserDB = $this->LoadDB('UserDB');
			$tmp_arr = $this->UserDB->db_get_user_depts($_GET['user_id']);
			foreach ($tmp_arr as $v) $depts_id_arr[] = $v['ug_id'];
		}
		
		// for contacts upd , get contacts depts
		else if (isset($_GET['contacts_id']))
		{	
			$this->ContactsDB = $this->LoadDB('ContactsDB');
			$depts = $this->ContactsDB->db_get_sub_contacts($_GET['contacts_id']);
			$depts_id_arr = get_id_from_array($depts, 'ug_id');
			if (!is_array($depts_id_arr)) $depts_id_arr = array();
		}
		
		// for set role to depts
		else if (isset($_GET['role_id']))
		{
			$this->RoleDB = $this->LoadDB('RoleDB');
			$depts = $this->RoleDB->db_get_setted_role_depts($_GET['role_id']);
			$depts_id_arr = get_id_from_array($depts, 'ug_id');
			if (!is_array($depts_id_arr)) $depts_id_arr = array();
		}
		
		/**
		 * @author : liqiongtao
		 */
		else if (isset($_GET['tree_from']) || isset($_SESSION['ug_id']))
		{
			if (isset($_GET['tree_from']) && $_GET['tree_from'] == 'ug') $curr_ug_id = $_SESSION['current_ug_id'];
			else if (isset($_GET['tree_from']) && $_GET['tree_from'] == 'user') $curr_ug_id = $_SESSION['active_ug_id'];
			else $curr_ug_id = $_SESSION['ug_id'];
			$_SESSION['ug_id'] = $curr_ug_id;
			if ($_SESSION['ug_id'] != "") $depts_id_arr[] = $_SESSION['ug_id'];
		}
		
		// sub item xml data
		foreach ($childDepts as $dept)
		{
			$child = $dept['child_id'] != '' ? '1' : '0';
			$checked = in_array($dept['ug_id'], $depts_id_arr) ? 'checked' : '';
			$select = in_array($dept['ug_id'], $depts_id_arr) ? '1' : '';
			// 如果是默认通讯录， 就选择所有的部门。
			//if (isset($_GET['contacts_id']) && $_GET['contacts_id']=='0') $checked = 'checked';
			//节点是否再次请求展开,缺省不展开，编辑用户和编辑通讯录展开
			$open = (isset($_GET['tree_no_session']) || isset($_SESSION['ug_id']) || isset($_GET['tree_from']) || isset($_GET['user_id']) || isset($_GET['contacts_id']) || isset($_GET['dept_id']) || isset($_GET['role_id'])) && 
					$this->ChildrenHasChecked($depts_id_arr, $dept['ug_id'])
					? '1' : '';
			$treexml .= "<item child='$child' id='$dept[ug_id]' select='$select' open='$open' checked='$checked' text='$dept[name]'>
							<userdata name='ug_name'>$dept[name]</userdata>
							<userdata name='parent_id'>$dept[parent_id]</userdata>
						</item>";
		}
		
		//第一次加载树结束item标签
		if ($treeid == 'root') $treexml .= "</item>";	
	 	$treexml .= "</tree>";
	 	file_put_contents("c:\\a.txt", "treexml:$treexml\r\n", FILE_APPEND);
	 	// 调用ob_gzip 方法 来实现树的快速输出
		ob_start('ob_gzip');
		echo $treexml;
		//输出压缩成果
		ob_end_flush();
	}
	
	//这是ob_gzip压缩机 为了实现将内容压缩输出
	function ob_gzip($content)
	{   
	    if(!headers_sent() && extension_loaded("zlib") && strstr($_SERVER["HTTP_ACCEPT_ENCODING"],"gzip"))
	    {
	        $content = gzencode($content, 9);
	       
	        header("Content-Encoding: gzip");
	        header("Vary: Accept-Encoding");
	        header("Content-Length: ".strlen($content));
	    } 
	    return $content;
	}
	
	/**
	 * check ug's Posterity , if it's one of Posterity be checked, return true, and tree will open this item
	 *
	 * @param array $depts_id_arr
	 * @param string $ug_id
	 * @return boolean
	 * add by zhangqitao @2008-04-17
	 */
	public function ChildrenHasChecked($depts_id_arr=array(), $ug_id)
	{
		if (empty($depts_id_arr)) return false;
		$ugsPosterity = array();
		$ugsPosterity = $this->GetPosterity($ug_id, '1'); 
		if (empty($ugsPosterity)) return true;
		$flag = false;
		foreach ($ugsPosterity as $val)
		{
			if (in_array($val['ug_id'], $depts_id_arr))
			{
				$flag = true;
				break;
			}
		}
		return $flag;
	}
	
	/**
	 * print ug && ug's user tree
	 *
	 * add by zhangqitao,modify @2008-04-17
	 */	
	public function printUserUgTree()
	{
		header("Content-type:text/xml"); 
		$this->MonmsgDB = $this->LoadDB('MonmsgDB');
		$treeid = $_GET['id'];
		$ug_id = $treeid == 'root' ? $_SESSION['current_corp_id'] : $treeid;//ajax请求一次根节点和第一级子节点
		$treexml = "";
		$treexml .= "<tree id='$treeid'>";
		
		// 第一次加载显示ROOT
		if ($treeid == 'root') 
			$treexml .= "<item child='1' open='1' checked='check' select='1' id='$_SESSION[current_corp_id]' 
						text='$_SESSION[current_corp_name]' im0='root.gif' im1='root.gif' im2='root.gif'>
						<userdata name='root'>root</userdata>";
			
		// 当前ug_id
		if ($treeid == 'root') $ug_id = $_SESSION['current_corp_id'];
		else $ug_id = $treeid;
		
		// 加载有GID且活动的用户
		$users = $ug_id == $_SESSION['current_corp_id'] ?  $this->MonmsgDB->db_get_corp_activeusers($ug_id) : $this->MonmsgDB->db_get_dept_activeusers($ug_id);
		foreach ($users as $user)
		{
			$user_id = $user['user_id']."_".get_rnd_str(5);
			$treexml .= "<item child='0' id='$user_id' text='$user[name]' im0='online.gif'>
						<userdata name='gid'>$user[gid]</userdata>
						</item>";
		}
		// 加载下级部门
		$childDepts = $this->MonmsgDB->db_get_childdepts($ug_id);
		foreach ($childDepts as $dept)
		{
			$deptusers = 0;
			$deptusers = $this->MonmsgDB->db_count_dept_users($dept['ug_id']);
			$child = ($dept['child_id'] != '' || $deptusers) ? '1' : '0';
			$treexml .= "<item child='$child' id='$dept[ug_id]' text='$dept[name]'></item>";
		}
		
		// 第一次加载树结束item标签
		if ($treeid == 'root') $treexml .= "</item>";	
	 	$treexml .= "</tree>";
		echo $treexml;			
	}
	/**
	 * 单位登陆选择树
	 *
	 * add by zhangqitao @2008-04-25
	 */
	public function printSelectCorpTree()
	{
		header("Content-type:text/xml"); 
		$this->AdminDB = $this->LoadDB('AdminDB');
		$admin = $this->AdminDB->db_get_admin_by_account($_SESSION['AdminAccount']);
		$corp_ids_arr = str_explode_deep($admin['attach_ug_id']);
		$this->TreeDB = $this->LoadDB('TreeDB');
		$treeid = $_GET['id'];
		if ($treeid == 'root') $ug_id = $admin['ug_id'];
		else $ug_id = $treeid;
		
		$treexml = "";
		$treexml .= "<tree id='$treeid'>";
		
		// 第一次加载显示ROOT
		$childCorps = $this->TreeDB->db_get_childcorps($ug_id);
		if ($treeid == 'root')
		{
			$root_ug = $this->TreeDB->db_get_ug($ug_id);
			$treexml .= "<item child='1' open='1' checked='check' select='1' id='$ug_id' 
							text='$root_ug[name]' im0='root.gif' im1='root.gif' im2='root.gif'>";
			$treexml .= "<userdata name='permission'>true</userdata>";
		}
		
		// sub item xml data
		foreach ($childCorps as $corp)
		{
			$sub_child_corps = array();
			$sub_child_corps = $this->TreeDB->db_get_childcorps($corp['ug_id']);
			$child = !empty($sub_child_corps) ? '1' : '0';
			$open = $child == '1' ? '1' : '';
			
			//能管理的单位
			if (in_array($corp['ug_id'], $corp_ids_arr)) 
			{
				$treexml .= "<item child='$child' id='$corp[ug_id]' open='$open' text='$corp[name]'>";
				$treexml .= "<userdata name='permission'>true</userdata>";
				$treexml .= "</item>";			
			}
			
			// 不能管理的单位
			else 
			{
				$treexml .= "<item child='$child' id='$corp[ug_id]' open='$open'  text='$corp[name][无权限管理]'>";
				$treexml .= "<userdata name='permission'>false</userdata>";
				$treexml .= "</item>";
			}
		}
		
		// 第一次加载树结束item标签
		if ($treeid == 'root') $treexml .= "</item>";	
	 	$treexml .= "</tree>";
		echo $treexml;
	}
	
	
	/**
	 * print ug && ug's user tree
	 *
	 * add by zhangqitao,modify @2009-02-25
	 */	
	public function printUserUgTreeEx()
	{
		header("Content-type:text/xml"); 
		$this->MonmsgDB = $this->LoadDB('MonmsgDB');
		$treeid = $_GET['id'];
		$ug_id = $treeid == 'root' ? $_SESSION['current_corp_id'] : $treeid;//ajax请求一次根节点和第一级子节点
		$treexml = "";
		$treexml .= "<tree id='$treeid'>";
		
		// 第一次加载显示ROOT
		if ($treeid == 'root') 
			$treexml .= "<item child='1' open='1' checked='check' select='1' id='$_SESSION[current_corp_id]' 
						text='$_SESSION[current_corp_name]' im0='root.gif' im1='root.gif' im2='root.gif'>
						<userdata name='root'>root</userdata>";
			
		// 当前ug_id
		if ($treeid == 'root') $ug_id = $_SESSION['current_corp_id'];
		else $ug_id = $treeid;
		
		// 加载有GID且活动的用户
		$users = $ug_id == $_SESSION['current_corp_id'] ?  $this->MonmsgDB->db_get_corp_activeusers($ug_id) : $this->MonmsgDB->db_get_dept_activeusers($ug_id);
		foreach ($users as $user)
		{
			$user_id = $user['user_id']."_".get_rnd_str(5);
			$treexml .= "<item child='0' id='$user_id' text='$user[name]' im0='online.gif'>
						<userdata name='gid'>$user[gid]</userdata>
						</item>";
		}
		// 加载下级部门
		$childDepts = $this->MonmsgDB->db_get_childdepts($ug_id);
		foreach ($childDepts as $dept)
		{
			$deptusers = 0;
			$deptusers = $this->MonmsgDB->db_count_dept_users($dept['ug_id']);
			$child = ($dept['child_id'] != '' || $deptusers) ? '1' : '0';
			$treexml .= "<item child='$child' id='$dept[ug_id]' text='$dept[name]'></item>";
		}
		
		// 第一次加载树结束item标签
		if ($treeid == 'root') $treexml .= "</item>";	
	 	$treexml .= "</tree>";
		echo $treexml;			
	}	
	
	public function on_showall()
	{		
		$this->view->display("header.html");
		$this->view->display("tree/showall.html");
		$this->view->display("footer.html");
	}
}	 