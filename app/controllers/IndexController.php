<?php
class IndexController extends DefaultController
{
	function __construct()
	{
		parent::__construct();
		$this->IndexDB = $this->LoadDB('IndexDB');
	}
	public function on_index()
	{
		$this->view->display('index.htm');
	}

	public function on_menu()
	{ 
        
		if (ISWIN) $this->view->assign("os", "WIN");
		$this->view->assign('switch', $this->tribe_switch());
        $this->view->assign('privs', $_SESSION['AdminPrivs']);
		$this->view->display('menu.html');
	}

	public function on_guide()
	{
		$this->view->assign('adminadminid' ,$_SESSION['AdminAdminId']);
		$this->view->assign('adminaccount' ,$_SESSION['AdminAccount']);

		$this->view->display('header.html');
		$this->view->display('guide.html');
		$this->view->display('footer.html');
	}

    public function on_title()
    {
		$this->view->assign('adminadminid' ,$_SESSION['AdminAdminId']);
        $this->view->assign('adminaccount' ,$_SESSION['AdminAccount']);
 		$this->view->assign('lastlogin' ,$_SESSION['lastlogin']);
        
        $this->view->display('header.html');
		$this->view->display('title.html');
		$this->view->display('footer.html');
       
    }
}
?>

