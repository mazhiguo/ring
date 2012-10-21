<?php
class AdminDB
{
	public $db;

	public function db_get_admin_by_account($account)
	{
		$sql = "SELECT * FROM userinfo WHERE account='$account'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}
    
    public function db_upd_admin_login_time($user_id, $time)
    {
        $sql = "UPDATE userinfo SET lastlogin='$time' WHERE user_id='$user_id'";

   		return $this->db->exec($sql);
    }

	public function db_check_admin($account)
	{
		$sql = "SELECT count(*) FROM userinfo WHERE account='$account'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_reset_pwd($user_id, $pwd)
	{
		$sql = "UPDATE userinfo SET pwd='$pwd' WHERE user_id='$user_id'";
		return $this->db->exec($sql);
	}	

	public function db_get_admin_by_admin_id($admin_id)
	{
		$sql = "SELECT * FROM userinfo WHERE user_id='$admin_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);		
	}
    
    public function db_get_privs_by_userid($user_id)
    {
        $sql = "SELECT priv_id from re_role_priv r  
                inner join re_user_role u on r.role_id=u.role_id 
                WHERE u.user_id='$user_id'";
        $query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);	
    }
    
    public function db_get_dataprivs_by_userid($user_id)
    {
        $sql = "SELECT * from re_user_col WHERE user_id='$user_id'";
        $query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);	
    }
}; 
?>