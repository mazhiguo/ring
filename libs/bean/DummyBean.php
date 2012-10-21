<?
class DummyBean extends DefaultController
{
	function __construct()
	{
		parent::__construct();
	}
	/**
	 * 	添加虚拟人 1.将虚拟人添加到userinfo表 2.修改privinfo表
	 * 	array $user 存放要添加的虚拟人的信息
	 * 	return : 添加成功 返回$n>0 否则返回$n<0
	 */
	public function dummy_add ($user)
	{
		$this->UserDB = $this->LoadDB('UserDB');
		$gid_arr = $this->UserDB->db_get_gid_info($user['gid']);
		$user['creator_account'] = $_SESSION['AdminAccount'];
		$user['gs'] = $gid_arr['gs'];
		$user['zoneid'] = $gid_arr['zoneid'];
		$user['ug_id'] = $_SESSION['current_corp_id'];
		if ($user['name'] != '') $user['display_name'] = $user['name'];
		else $user['name'] = "虚拟人";
		$n = $this->DummyDB->db_add_dummy($user);
		$n += $this->assign_dummy_customer();
		//发送用户权限下推消息
		$n += $this->send_notices($user);
		return $n;
	}

	/**
	 * 删除虚拟人 1.删除userinfo表中的数据 2.修改privinfo表中的数据
	 * 删除虚拟人要发虚拟人同步消息和权限下推消息
	 * return : 返回值大于0 或小于0
	 */
	public function dummy_del ($user_id, $gid)
	{
		$this->UserDB = $this->LoadDB('UserDB');
		$num = $this->UserDB->db_del_user($user_id);
		$num += $this->assign_dummy_customer();
		//发送用户权限下推消息
		$user = $this->UserDB->db_get_gid_info($gid);
		$num += $this->send_notices($user);
		return $num;
	}

	/**
	 * 修改密码 修改userinfo 表
	 * 需要发送客户端同步消息 和 权限下推消息
	 */
	public function dummy_upd_pwd ($pwd, $id, $gid)
	{
		$this->DummyDB = $this->LoadDB('DummyDB');
		$num = $this->DummyDB->db_upd_pwd($pwd, $id);
		//发送用户权限下推消息
		$user = $this->UserDB->db_get_gid_info($gid);
		$num += $this->send_notices($user);
		return $num;
	}

	/**
	 * 修改privinfo表（查询所有的虚拟人，拼接xml）
	 * 1.添加虚拟人时 2.删除虚拟人是
	 * return : 插入表成功时，返回值大于0，否则返回值小于0
	 */
	public function assign_dummy_customer ()
	{
		$this->DummyDB  = $this->LoadDB("DummyDB");
		//修改权限表
		$licenseinfo = $this->GetLicenseinfo();
		$msid = $licenseinfo['msid'];
		$xml = "<param><msid>$msid</msid><vrs>";
		$dummy_list = $this->DummyDB->db_get_all_dummy_info();
		foreach ($dummy_list as $dummy)
		{
			$lgid = makegid($dummy['gid'], $dummy['zoneid']);
			$xml .= " <vr>$lgid</vr>";
		}
		$xml .= "</vrs></param>";
		//插入表privinfo
		$num = $this->DummyDB->db_add_dummy_customer($xml);
		return $num;
	}

	/**
	 * 发送用户同步 和 权限下推消息
	 * 数据库查询取会的是所有用户和虚拟人用户
	 * return : 如果同步成功 则返回值大于0 否则返回值小于0
	 */
	public function send_notices ($user = array())
	{
		//if (!$user) $users = $this->UserDB->db_get_corp_active_users($_SESSION['current_corp_id']);  //有gid并且是活动用户
		//发送用户权限变化消息，先更新时间，再发送消息
		$buddylist = "<buddylist>";
		//foreach ($users as $v)
		//{
			//$this->RoleDB->db_upd_re_user_ver('priv', $v['gid']);
			//$buddylist .= "<buddy gid='$v[gid]' zoneid='$v[zoneid]' gs='$v[gs]'/>";
		//}
		if ($user) $buddylist .= "<buddy gid='$user[gid]' zoneid='$user[zoneid]' gs='$user[gs]'/>";
		$buddylist .= "</buddylist>";
		//file_put_contents("d:\\a.txt", "$buddylist\r\n", FILE_APPEND);
		$this->UserDB->upd_verinfo('ug');
		$this->UserDB->upd_verinfo('clientpara');
		$n = $this->notice->SynchNotice($buddylist);
		$n += $this->notice->ParaNotice($_SESSION['current_corp_id']);
		$n += $this->notice->PrivNotice($buddylist);
		$this->notice->UgNotice($_SESSION['current_corp_id']);

		return $n;
	}

	/**
	 * 分页方法
	 * $current_page : 当前是第几页
	 * $page_rows_num : 每页显示多少条
	 * $total_rows : 总共有多少条
	 * return : 数组 存放了 每页要显示多少页的下拉框数据、当前是第几页、每页显示多少条、总共有多少条、共有多少页
	 */
	public function on_page ($current_page, $page_rows_num, $total_rows)
	{
		$page_info = array();
		$page_info['page_rows'] = array(5, 10, 20, 30, 50);
		if (!isset($current_page) || empty($current_page)) $current_page = 1;
		if (!isset($page_rows_num) || empty($page_rows_num)) $page_rows_num = $page_info['page_rows'][0];
		$page_info['current_page'] = $current_page;			//当前是第几页
		$page_info['page_rows_num'] = $page_rows_num;		//每页显示多少条
		$page_info['total_rows'] = $total_rows; 			//总共有多少条
		//共有多少页
		if ($page_info['total_rows'] % $page_info['page_rows_num'] == 0) $page_info['total_pages'] = $page_info['total_rows'] / $page_info['page_rows_num'];
		else $page_info['total_pages'] = (int)($page_info['total_rows'] / $page_info['page_rows_num']) + 1;
		if ($page_info['total_pages'] == 0) $page_info['total_pages'] = 1;
		return $page_info;
	}
}
?>