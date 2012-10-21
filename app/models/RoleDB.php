<?php
class RoleDB
{
	public $db;

	public function db_add_role($role)
	{ 
		$sql = "INSERT INTO roleinfo SET role_id='$role[role_id]', name='$role[name]',
				creator_account='$role[creator_account]', creator_time=now(), mender_account='',
				mender_time=now(), remark='$role[remark]'";
		return $this->db->exec($sql);
	}	

	public function db_upd_role($role)
	{
		$sql = "UPDATE roleinfo SET name='$role[name]', mender_account='$role[mender_account]',
				mender_time=now(), remark='$role[remark]' WHERE role_id='$role[role_id]'";
		return $this->db->exec($sql);
	}

	public function db_check_name($name)
	{
		$sql = "SELECT count(*) FROM roleinfo WHERE name='$name'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_get_unused_priv_in_role($role_id)
	{
		$sql = "SELECT * FROM privinfo WHERE priv_id NOT IN (SELECT priv_id FROM re_role_priv WHERE role_id='$role_id')";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_used_priv_in_role($role_id)
	{
		$sql = "SELECT * FROM privinfo WHERE priv_id IN (SELECT priv_id FROM re_role_priv WHERE role_id='$role_id')";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);		
	}

	public function db_count_corp_roles()
	{
		$sql = "SELECT count(*) FROM roleinfo";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_get_corp_roles($limit=10, $offset=10 )
	{
		$sql = "SELECT * FROM roleinfo ORDER BY name asc";
		$sql .= $this->db->limit($limit,$offset);	
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
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

	public function db_del_priv_in_role($role_id)
	{
		$sql ="DELETE FROM re_role_priv WHERE role_id='$role_id'";
		return $this->db->exec($sql);
	}

	public function db_get_role_users($role_id)
	{
    	$sql = "SELECT user_id FROM re_user_role WHERE role_id='$role_id'";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}	

	public function db_get_role_dept_users($role_id)
	{
		$sql = "SELECT b.user_id FROM re_ug_role a
					LEFT JOIN re_user_ug b ON a.ug_id=b.ug_id
					WHERE a.role_id='$role_id'";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_upd_re_user_ver($name, $gid)
	{
		$sql = "update re_user_ver set mender_time=now() where name='$name' and gid='$gid'";
		return $this->db->exec($sql);
	}	

	public function db_search_count($content)
	{
		$sql = "SELECT count(*) FROM roleinfo WHERE name LIKE '%$content%' 
									OR creator_account LIKE '%$content%' 
									OR remark LIKE '%$content%'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_search($content, $limit=10, $offset=0 )
	{
		$sql = "SELECT * FROM roleinfo WHERE name LIKE '%$content%' 
									OR creator_account LIKE '%$content%' 
									OR remark LIKE '%$content%'";
		$sql.= $this->db->limit($limit, $offset);
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_del_role_by_id($role_id)
	{
		$n = 0;
		$tables = array('re_ug_role','re_user_role', 'roleinfo', 're_role_priv');
		foreach($tables as $table)
		{
			$sql = "DELETE FROM $table WHERE role_id='$role_id'";
			$n += $this->db->exec($sql);
		}
		return $n;
	}

	public function db_get_setted_role_users($role_id)
	{
		$sql = "SELECT b.* FROM re_user_role a
					LEFT JOIN userinfo b ON a.user_id=b.user_id
					WHERE a.role_id='$role_id'";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_unsetted_role_users($role_id, $ug_id)
	{
		$sql = "SELECT a.*,b.gid FROM userinfo a
				LEFT JOIN re_gid_user b ON a.user_id=b.user_id
				WHERE a.state != '".FILTER_DUMMY_STATE."' and a.user_id NOT IN(SELECT user_id FROM re_user_role WHERE role_id='$role_id') AND a.ug_id='$ug_id' AND b.gid IS NOT NULL";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);		
	}
    
    
   	public function db_get_dept_users($ug_ids)
	{
		$sql = "SELECT * FROM userinfo WHERE ug_id in ($ug_ids) order by name asc";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	
	public function db_upd_re_user_ver_by_gid($gid)
	{
		$sql = "update re_user_ver set mender_time=now() where gid='$gid'";
		return $this->db->exec($sql);
	}	

	public function db_get_all_roles($ug_id)
	{
		$sql = "SELECT * FROM roleinfo WHERE ug_id='$ug_id'";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);	
	}	

	public function db_get_role($role_id)
	{
		$sql = "SELECT * FROM roleinfo WHERE role_id='$role_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	public function db_get_all_priv()
	{
		$sql = "SELECT * FROM privinfo";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_setted_role_depts($role_id)
	{
		$sql = "SELECT a.role_id, b.* FROM re_ug_role a 
				LEFT JOIN uginfo b ON a.ug_id=b.ug_id
				WHERE a.role_id='$role_id'  ";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);	
	}

	public function db_get_unseted_role_depts($dept_ids)
	{
		$sql = "SELECT name,ug_id FROM uginfo WHERE ug_id IN($dept_ids)";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_set_role_dept($role_id, $ug_id)
	{
		$sql = "INSERT INTO re_ug_role SET role_id='$role_id',ug_id='$ug_id'";
		return $this->db->exec($sql);
	}

	public function db_del_role_depts($role_id)
	{
		$sql = "DELETE FROM re_ug_role WHERE role_id='$role_id'";
		return $this->db->exec($sql);
	}

	public function db_set_role_user($role_id, $user_id)
	{
		$sql = "INSERT INTO re_user_role SET user_id='$user_id', role_id='$role_id'";
		return $this->db->exec($sql);
	}

	public function db_del_role_users($role_id)
	{
		$sql = "DELETE FROM re_user_role WHERE role_id='$role_id'";
		return $this->db->exec($sql);
	}

	public function db_get_setted_roles_user($user_id)
	{
		$sql = "SELECT * FROM roleinfo WHERE role_id IN(SELECT role_id FROM re_user_role WHERE user_id='$user_id')";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_unsetted_roles_user($user_id)
	{
		$sql = "SELECT * FROM roleinfo WHERE role_id NOT IN(SELECT role_id FROM re_user_role WHERE user_id='$user_id')";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_del_roles_user($user_id)
	{
		$sql = "DELETE FROM re_user_role WHERE user_id='$user_id'";
		return $this->db->exec($sql);
	}

	public function db_get_setted_roles_dept($ug_id)
	{
		$sql = "SELECT * FROM roleinfo WHERE role_id IN(SELECT role_id FROM re_ug_role WHERE ug_id='$ug_id')";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);	
	}
	
	public function db_get_unsetted_roles_dept($dept_id)
	{
		$sql = "SELECT * FROM roleinfo WHERE role_id NOT IN(SELECT role_id FROM re_ug_role WHERE ug_id='$dept_id')";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);	
	}

	public function db_del_roles_dept($ug_id)
	{
		$sql = "DELETE FROM re_ug_role WHERE ug_id='$ug_id'";
		return $this->db->exec($sql);
	}

	public function db_get_dept_gid_users($ug_id)
	{
		$sql = "SELECT c.* FROM re_user_ug a
					LEFT JOIN re_gid_user b ON a.user_id=b.user_id
					LEFT JOIN gidinfo c ON b.gid=c.gid
					WHERE a.ug_id='$ug_id' 	AND c.gid IS NOT NULL";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);	
	}

	public function db_upd_dept_priv_time($ug_id)
	{
		$sql = "UPDATE userinfo u, re_gid_user g, re_user_ver v set v.mender_time=now()
					WHERE u.ug_id='$ug_id' AND u.user_id=g.user_id AND g.gid=v.gid AND g.zoneid= v.zoneid and v.name='priv'";
		return $this->db->exec($sql);
	}
}	
?>