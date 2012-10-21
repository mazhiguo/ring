<?php
class LogController extends DefaultController 
{
	public function on_ls()
	{
		$this->LogDB = $this->LoadDB('LogDB');
		$total_recode = $this->LogDB->count_corp_logs($_SESSION['current_corp_id']);
		$limit = !empty( $_SESSION['log_limit']) ? $_SESSION['log_limit'] : 10;	
		$total_pages = ceil($total_recode/$limit);
		$curr_page = max(intval($_GET['page']), 1);
		$curr_page = $curr_page > $total_pages ? $total_pages : $curr_page;
		$offset = ($curr_page-1)*$limit;
		$in_url = "/log/ls/";
		$out_url = $this->Page($total_recode, $limit, $curr_page, $in_url);
		$option_str = make_option_string($limit);
		$logls = $this->LogDB->get_logs($_SESSION['current_corp_id'], $limit, $offset);
		
		$this->view->assign('is_admin', $this->IsAdmin());
		$this->view->assign('option_str', $option_str);
		$this->view->assign('pages', $out_url);						
		$this->view->assign('logls', $logls);
		$this->view->display('header.html');	
		$this->view->display('log/ls.html');	
		$this->view->display('footer.html');	
	}	
	
	public function on_del()
	{
		$this->LogDB = $this->LoadDB('LogDB');
		$days_num = intval($_POST['days_num']);
		$n = $this->LogDB->del_logs($days_num, $_SESSION['current_corp_id']);
		if ($n>0) 
		{	
			$this->_writeLog("批量删除[$n]条管理日志");
			$this->show_msg_ext('GOTO', array('url'=>'/log/ls', 'msg'=>"成功清除{$days_num}天前{$n}条日志！"));
		}
		else 
		{
			$this->show_msg_ext('GOTO', array('url'=>'/log/ls', 'msg'=>"{$days_num}天前的日志记录数为0！"));
		}
	}
}
?>