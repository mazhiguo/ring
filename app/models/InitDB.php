<?php
class InitDB
{
	public $db;
	
	public function db_get_ug_by_code($code)
	{
		$sql = "select * from uginfo where code='$code'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}
	
	public function db_get_corps_num()
	{
		$sql = "select count(*) from uginfo where sign='0'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}
	
	public function db_check_name_at_one_parent($name, $ug_id)
	{
		$sql = "select count(*) from uginfo where name='$name' and parent_id='$ug_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);		
	}
	
	public function db_add_ug($ug)
	{ 
		$sql = "INSERT INTO uginfo SET ug_id='$ug[ug_id]', name='$ug[name]', code='$ug[code]', parent_id='$ug[parent_id]', 
									creator_account='$ug[creator_account]', creator_time=now(), 
									mender_account='$ug[mender_acccount]', mender_time=now(),
									remark='$ug[remark]', sign='$ug[sign]', email='$ug[email]',
									location='$ug[location]'";
		return $this->db->exec($sql);
	}

	public function db_get_admin_by_account($account)
	{
		$sql = "select * from admininfo where account='$account'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);		
	}
	
	public function db_upd_admin_priv($admin_id, $attach_ug_id)
	{
		$sql = "update admininfo set attach_ug_id='$attach_ug_id' where admin_id='$admin_id'";
		return $this->db->exec($sql);
	}
	
	public function db_add_role($role=array())
	{ 
		$sql = "INSERT INTO roleinfo SET role_id='$role[role_id]', name='$role[name]', parent_id='$role[parent_id]',
				creator_account='$role[creator_account]', creator_time=now(), mender_account='$role[mender_account]',
				mender_time=now(), remark='$role[remark]', location='$role[location]', ug_id='$role[ug_id]'";
		return $this->db->exec($sql);
	}
	
	public function db_get_priv($priv_id)
	{
		$sql = "SELECT * FROM privinfo where priv_id='$priv_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}
		
	public function db_add_priv_to_role($role_id, $priv_arr)
	{
		$sql = "INSERT INTO re_role_priv SET role_id='$role_id', priv_id='$priv_arr[priv_id]', value='1', 
				para_value='$priv_arr[para_value]', type='$priv_arr[type]'";	
		return $this->db->exec($sql);
	}
	
	public function db_add_verinfo($ug_id)
	{
		$sql = "INSERT INTO verinfo SET name='channel', mender_time=now(),ug_id='$ug_id'";
		return $this->db->exec($sql);
	}
	
	public function upd_verinfo($name)
	{
		$sql = "update verinfo set mender_time=now() where name='$name'";
		return $this->db->exec($sql);
	}
	
	public function db_check_account($account)
	{
		$sql = "select count(*) from userinfo where state != '".FILTER_DUMMY_STATE."' and account='$account'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);				
	}
	
	public function db_add_user($user)
	{
		$sql = "INSERT INTO userinfo SET user_id='$user[user_id]', account='$user[account]', name='$user[name]', 
									display_name='$user[display_name]', state='$user[state]', pwd='$user[pwd]', 
									creator_account='$user[creator_account]',mender_account='$user[mender_account]',
									creator_time=now(), mender_time=now(), remark='$user[remark]', sex='$user[sex]',
									birthday='$user[birthday]', mobile='$user[mobile]', office_tel='$user[office_tel]', 
									position='$user[position]', fax='$user[fax]', email='$user[email]', 
									postcode='$user[postcode]', location='$user[location]', 
									address='$user[address]', webaddress='$user[webaddress]',ug_id='$user[ug_id]'";
		return $this->db->exec($sql);
	}
	
	public function db_bind_user_with_gid($user_id, $gid_arr, $creator_account)
	{
		$n = 0;
		$sql = "INSERT INTO re_gid_user SET user_id='$user_id', gid='$gid_arr[gid]', zoneid='$gid_arr[zoneid]', gs='$gid_arr[gs]', creator_account='$creator_account', creator_time=now()";
		$n += $this->db->exec($sql);
		$sql = "UPDATE gidinfo SET state=1 WHERE gid='$gid_arr[gid]' ";
		$n += $this->db->exec($sql);
		return $n;
	}

	public function db_bind_gid_with_mobile($mobile, $gid_arr, $creator_account)
	{
		$n = 0;
		$sql = "DELETE FROM re_gid_mobile WHERE gid='$gid_arr[gid]'";
		$n += $this->db->exec($sql);
		$sql = "INSERT INTO re_gid_mobile SET mobile='$mobile', gid='$gid_arr[gid]', zoneid='$gid_arr[zoneid]', creator_account='$creator_account', creator_time=now()";
		$n += $this->db->exec($sql);
		return $n;
	}

	public function db_add_mobile_smsgw($mobile, $gid_arr)
	{
		$sql = "INSERT INTO smsgw.tableuser SET mobile='$mobile', gid='$gid_arr[gid]', zoneid='$gid_arr[zoneid]', count=0";
		return $this->db->exec($sql);
	}

	public function db_add_default_role_for_user($user_id, $role_id)
	{
		$sql = "INSERT INTO re_user_role SET user_id='$user_id', role_id='$role_id'";
		return $this->db->exec($sql);
	}
	
	public function db_re_user_ver($gid_arr)
	{
		$n = 0;
		$sql = "DELETE FROM re_user_ver WHERE gid='$gid_arr[gid]'";
		$n += $this->db->exec($sql);
		$sql = "INSERT INTO re_user_ver SET gid='$gid_arr[gid]', zoneid='$gid_arr[zoneid]', name='priv', value='', remark='', mender_time=now()";
		$n += $this->db->exec($sql);
		return $n;
	}
	
	public function db_add_user_dept($user_id, $dept_id)
	{
		$sql = "INSERT INTO re_user_ug SET user_id='$user_id', ug_id='$dept_id'";
		return $this->db->exec($sql);	
	}
}
?>