<?php
require_once APPPATH.'chklogin-conf.php';
class DefaultController
{
	public $x = array();

	function __construct()
	{
		$this->CheckLogin();
	}

	public function __call($name, $arguments)
	{
		if ('QueryOne' == $name)
		{
			$query = $this->dbh->query($arguments[0]);
			$result = $query->fetch(PDO::FETCH_ASSOC);
			return is_bool($result) ? array() : $result;
		}
		else if ('QueryAll' == $name)
		{
			$query = $this->dbh->query($arguments[0]);
			$result = $query->fetchAll(PDO::FETCH_ASSOC);
			return is_bool($result) ? array() : $result;
		}
		else if ('DbExec' == $name)
		{
			return $this->dbh->exec($arguments[0]);
		}
		else
		{
			return null;
		}
	}

	public function __get($member)
	{
		if (isset($this->x[$member]))
			return $this->x[$member];

		// load view while necessary
		if ('view' == $member)
			return $this->x[$member] = $this->InitView();
		// load notifier while necessary
		else if ('excel' == $member)
			return $this->x[$member] = $this->InitPhpExcel();
		else if ('notice' == $member)
			return $this->x[$member] = $this->InitNotice();
		// LoadPDO while necessary
		else if ('dbh' == $member)
			return $this->x[$member] = $this->LoadPDO();
		// LoadDB while necessary
		else if (preg_match('/DB$/', $member))
			return $this->x[$member] = $this->LoadDB($member);
		// check online http host
		else if ('chkonline_http_host' == $member)
			return $this->x[$member] = $this->GetChkOnlineHttpHost();
		else
			return null;
	}

    public function __set($nm, $val)
    {
        isset($this->x[$nm]) && $this->x[$nm] = $val;
    }

	public function __isset($nm)
    {
        return isset($this->x[$nm]);
    }

    public function __unset($nm)
    {
        unset($this->x[$nm]);
    }

	protected function CheckLogin()
	{
		if ( empty($_SESSION['AdminAccount']) || empty($_SESSION['AdminPwd']) )
		{
			global $no_checked_arr;
			$curr_class_method1 = $GLOBALS['controller'].':'.$GLOBALS['method'];
			$curr_class_method2 = $GLOBALS['controller'].':on_'.$GLOBALS['method'];
			if (!in_array($curr_class_method1, $no_checked_arr) && !in_array($curr_class_method2, $no_checked_arr))
			{
				js_goto('/admin/login', 'top');
				exit;
			}
		}
	}

	protected function LoadDB($db_name)
	{
		require_once APPPATH.'models/'.$db_name.'.php';
		$ObjDB = new $db_name();
		$ObjDB->db = $this->dbh;
		return $ObjDB;
	}

	protected function LoadPDO()
	{
		require_once LIBPATH.'classes/pdo.php';
		return new Database();
	}

	protected function InitView()
	{
		require_once LIBPATH.'classes/tplengine/Smarty.class.php';
		$view = new Smarty();
		$view->template_dir = SMARTY_TMPDIR;
		$view->compile_dir = SMARTY_TMPDIRC;
		$view->cache_dir = SMARTY_CACHEDIR;
		$view->left_delimiter = SMARTY_DLEFT;
		$view->right_delimiter = SMARTY_DRIGHT;
		$view->caching = false;
		$view->compile_check = true;
		$view->debugging = false;
		return $view;
	}

	protected function Page($total_recode=10, $limit=1, $page=1, $in_url='/',$js_func='', $type=2, $go_links=1, $links_num=5)
	{
		require_once LIBPATH.'classes/page.php';
		$s = '
			$o = new Pagination();
			$o->js_func = $js_func;
			$o->type = $type;
			$o->total_recode = $total_recode;
			$o->limit = $limit;
			$o->page = $page;
			$o->in_url = $in_url;
			$o->view = 1;
			$o->links_num = $links_num;
			$o->go_links = $go_links;
			$o->out_url();
		';
		eval($s);
		return $o->out_url();
	}

	protected function InitPhpExcel()
	{
		require_once LIBPATH.'classes/PHPExcel.php';
		return new PHPExcel();
	}

	protected function InitNotice()
	{
		require_once LIBPATH.'classes/notice.php';
		return new Cnotice();
	}

	protected function InitMonService($host="127.0.0.1", $port=8899)
	{
		// 在require_once时，被加载文件的<? ...? >最后不能有多余的空格或换行，否则在使用ajax获取返回值时会产生多余的空格或换行
		require_once LIBPATH.'classes/mon-service.php';
		return new CGKMonService($host, $port);
	}

	protected function _writeLog($logaction)
	{
		$this->LogDB = $this->LoadDB('LogDB');
		$ClientIp = get_client_ip();
		$AdminAccount =  $_SESSION['AdminAccount'] ;
		$ug_name = '' ;
		$corp_id = $_SESSION['AdminUgId'];
		return $this->LogDB->writeLog($logaction, $ClientIp, $AdminAccount, $ug_name, $corp_id);
	}
    
    public function GetPosterityCol($pid, $sign='1')
	{
		$this->VectorQueryCol($pid, $rs, $sign);
		return $rs;
	}
    
    public function VectorQueryCol($pid=0, &$rs=array(), $sign='1')
	{
		$arr = array();
		$arr = $this->GetChildrenCol($pid, $sign);
		if (!empty($arr))
		{
			foreach ($arr as $v)
			{
				$rs[] = $v;
				$this->VectorQueryCol($v['id'], $rs, $sign);
			}
		}
	}
    
    protected function GetChildrenCol($pid, $sign)
	{
		$sql = "select * from colinfo where parent_id='$pid'";
		if ($sign == '0' || $sign =='1' )
			$sql .= " and sign='$sign'";
		$query = $this->dbh->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function GetPosterity($pid, $sign='1')
	{
		$this->VectorQueryUg($pid, $rs, $sign);
		return $rs;
	}

	public function VectorQueryUg($pid=0, &$rs=array(), $sign='1')
	{
		$arr = array();
		$arr = $this->GetChildren($pid, $sign);
		if (!empty($arr))
		{
			foreach ($arr as $v)
			{
				$rs[] = $v;
				$this->VectorQueryUg($v['ug_id'], $rs, $sign);
			}
		}
	}

	protected function GetChildren($pid, $sign)
	{
		$sql = "select * from uginfo where parent_id='$pid'";
		if ($sign == '0' || $sign =='1' )
			$sql .= " and sign='$sign'";
		$query = $this->dbh->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	protected function GetLicenseinfo()
	{
		$_licenseinfo = $this->SysDB->db_get_licenseinfo();
		$licenseinfo = array();
		foreach ($_licenseinfo as $key => $val)
		{
			$licenseinfo[$val['name']] = $val['value'];
		}
		return $licenseinfo;
	}

	protected function GetGid()
	{
		$licenseinfo = $this->GetLicenseinfo();
		$gids = $licenseinfo['gids'];
		return str_explode_deep($gids, '|');
	}

	public function GetOneUnusedGid()
	{
		$licenseinfo = $this->GetLicenseinfo();
		//if ($licenseinfo['type']==null || $licenseinfo['type']=='' || $licenseinfo['type']!='1')
		//{
			$canbeusednum = $licenseinfo['activenum'];
			$usednum = $this->UserDB->db_count_used_gid();
			//当前已使用的gid数 已经超过 最大激活数
			if ($canbeusednum <= $usednum)
				return array();
			return $this->UserDB->db_get_one_unused_gid();
		//}

	}

	protected function GetParainfo()
	{
		$_parainfo = $this->SysDB->db_get_parainfo();
		$parainfo = array();
		foreach ($_parainfo as $val)
		{
			$parainfo[$val['name']] = array('name'=>$val['name'], 'value'=>$val['value'], 'type'=>$val['type']);
		}
		return $parainfo;
	}

	protected function IsAdmin()
	{
		if ($_SESSION['AdminAccount'] == 'admin')
			return true;
		return false;
	}

	protected function AdminsPriv()
	{
		if (!$this->IsAdmin())
		{
			$this->show_msg_ext('TEXT', array('msg'=>'只有管理员admin才能执行此操作！'));
			exit;
		}
		return true;
	}

	// 判断新旧两版协作区
	public function tribe_switch()
	{
		$cfg = _parse_ini_file(APPPATH.'ms-web.ini', true);
		return $cfg['tribe']['switch'];
	}

	// 判断新版协作区服务器是否能连接成功
	public function check_tribe_connection()
	{
		$cfg = _parse_ini_file(APPPATH.'ms-web.ini', true);
		$host = $cfg['tribeext']['ip'];
		$port = $cfg['tribeext']['port'];
		require_once LIBPATH.'classes/socket.php';
		$fs = new CTCPSocket(false);
		return $fs->connect($host, $port);
	}

	// 消息提醒
	// ctrl = {msg, url, hint, timeout, error_msg}
	// type = BACK, CLOSE, GOTO, TEXT
	public function show_msg_ext($type, $ctrl = array())
	{
		!isset($type) && $type = 'TEXT';
		!isset($ctrl['msg']) && $ctrl['msg'] = '';
		!isset($ctrl['url']) && $ctrl['url'] = '/';
		!isset($ctrl['hint']) && $ctrl['hint'] = '提示';
		!isset($ctrl['timeout']) && $ctrl['timeout'] = 3000;
		!isset($ctrl['error_msg']) && $ctrl['error_msg'] = '';
		$this->view->assign('type', $type);
		$this->view->assign('ctrl', $ctrl);
		$this->view->display("tmpl/show_msg.html");
	}

	/**
	 * 获取 gid 在线状态的 http接口主机
	 */
	public function GetChkOnlineHttpHost()
	{
		$cfg = _parse_ini_file(APPPATH.'ms-web.ini', true);
		return !isset($cfg['chkonline']['http_host']) ? $_SERVER['HTTP_HOST'] : $cfg['chkonline']['http_host'];
	}
}
?>