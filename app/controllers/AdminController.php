<?php
class AdminController extends DefaultController 
{		
	
	/**
	 * 登录部分
	 */
	public function on_login()
	{	
		$this->AdminDB = $this->LoadDB('AdminDB');
		
		// 如果登录过，没有退出或是没有关闭浏览器，进行自动登录处理	
		if (!empty($_SESSION['AdminAccount']) && !empty($_SESSION['AdminPwd'])) 
		{
			$this->show_msg_ext('GOTO', array('url'=>'/admin/select_corp', 'timeout'=>'0'));
			exit;
		}
		
		// 管理员新登录
		if (isset($_POST['account']) && isset($_POST['password'])) 
		{
			$account = $_POST['account'];
			$password = strtoupper(md5($_POST['password']));
			$admin = $this->AdminDB->db_get_admin_by_account($account); //$admin,返回结果	
			if (empty($admin['account'])) 
			{
				$this->show_msg_ext('GOTO',array('url'=>'/admin/login/', 'msg'=>'管理员不存在！'));
				exit;
			} 
			if (checkpass($password, $admin['pwd'])) 
			{
				$this->show_msg_ext('GOTO',array('url'=>'/admin/login/', 'msg'=>"密码不正确！"));
				exit;
			}
			
            $privs = $this->AdminDB->db_get_privs_by_userid($admin['user_id']);
			// 记录管理员的id,account,password
			$_SESSION['AdminAdminId'] = $admin['user_id'];
			$_SESSION['AdminAccount'] = $account;
			$_SESSION['AdminPwd'] = $password;
            $_SESSION['AdminUgId'] = $admin['ug_id'];
            $_SESSION['AdminPrivs'] = get_id_from_array($privs, 'priv_id');
            $tmparray = $this->AdminDB->db_get_dataprivs_by_userid($admin['user_id']);
            $dataprivs = array();
            foreach ($tmparray as $v)
            {
                $dataprivs[$v['col_id']] = $v;
            } 
            $_SESSION['AdminDataPrivs'] = $dataprivs;
            $_SESSION['lastlogin'] = $admin['lastlogin'];
			$_SESSION['login_time'] = date("Y-m-d H:i:s");
            $this->AdminDB->db_upd_admin_login_time($admin['user_id'], $_SESSION['login_time']);
			$this->show_msg_ext('GOTO',array('url'=>'/', 'msg'=>'登录成功！', 'timeout'=>3000));
		} 
		// 显示登录界面
		else 
		{
			$this->view->display('header.html');
			$this->view->display('admin/login.html');
			$this->view->display('footer.html');
		}
	}
	

	/**
	 *  重置密码
	 */
	public function on_reset_pwd()
	{
		$this->AdminDB = $this->LoadDB('AdminDB');
		if ($_POST)
		{
			$admin_id = $_POST['admin_id'];
			$account = $_POST['account'];
            $pwdtmp = strtoupper(md5($_POST['password']));	
            $pwd = getpass($pwdtmp);		
            if ($_POST['password'] != $_POST['repassword'])
			{
				$this->show_msg_ext('BACK',array('msg'=>"两次密码输入不一致！"));
				exit;
			}

			if ($this->AdminDB->db_reset_pwd($admin_id, $pwd) > 0)
			{
				$this->_writeLog("重置管理员[$account]密码！");
				js_close();
			}
			else
			{
			 	js_alert('密码没有改变！');
			 	js_close();
			}
		}
		else 
		{
			$admin_id = $_GET['admin_id'];
			$admin = $this->AdminDB->db_get_admin_by_admin_id($admin_id);
			$this->view->assign('admin', $admin);
			$this->view->display('header.html');
			$this->view->display('admin/reset_pwd.html');
			$this->view->display('footer.html');
		}
	}
	

	public function on_logout()
	{
		$this->_writeLog("管理员[$_SESSION[AdminAccount]]正常退出！");
		unset($_SESSION);
		session_destroy();
		$this->show_msg_ext('GOTO', array('msg'=>'退出成功！', 'url'=>'/admin/login'));
	}
	
	/**
	 * 新增加管理员--ajax 检查帐号是否存在
	 */
	public function check_account()
	{  
		$this->AdminDB = $this->LoadDB('AdminDB');
		$account = $_REQUEST['account'];
		if ($this->AdminDB->db_check_admin($account) > 0)
			echo "帐号[{$account}]已存在，请重新输入帐号！";
	}
	/**
	 * 登录前account ajax检查
	 */
	public function check_login_account()
	{
		$this->AdminDB = $this->LoadDB('AdminDB');
		$account = $_REQUEST['account'];
		if ($this->AdminDB->db_check_admin($account) == 0)
			echo "该帐户[$account]不存在，请重新输入帐号！<img src='/images/check_error.gif'/>";
        else
            echo " ";		
	}
}
?>