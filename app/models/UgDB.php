<?php
class UgDB
{
	public $db;
	public function db_get_all_depts()
	{
		$sql = "SELECT * FROM uginfo WHERE sign='1' ORDER BY location ASC";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_ug_by_ug_id($ug_id)
	{
		$sql = "SELECT * FROM uginfo WHERE ug_id='$ug_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	public function db_add_ug($ug)
	{
		$sql = "INSERT INTO uginfo SET ug_id='$ug[ug_id]', name='$ug[name]', code='$ug[code]', parent_id='$ug[parent_id]',
									creator_account='$ug[creator_account]', creator_time=now(),
									mender_account='$ug[mender_account]', mender_time=now(),
									remark='$ug[remark]', sign='$ug[sign]', email='$ug[email]',
									location='$ug[location]'";
		return $this->db->exec($sql);
	}

	public function db_upd_ug($ug)
	{
		if ($ug['parent_id'] != '')
		{
			$sql = "UPDATE uginfo SET name='$ug[name]', parent_id='$ug[parent_id]', code='$ug[code]',
					mender_account='$ug[mender_account]',mender_time=now(), remark='$ug[remark]', sign='$ug[sign]',
					email='$ug[email]' WHERE ug_id='$ug[ug_id]'";
		}
		else
		{
			$sql = "UPDATE uginfo SET name='$ug[name]', code='$ug[code]', mender_account='$ug[mender_account]',
					mender_time=now(), remark='$ug[remark]', sign=$ug[sign],email='$ug[email]' WHERE ug_id='$ug[ug_id]'";
		}
		return $this->db->exec($sql);
	}

	public function db_del_dept($ug_id)
	{
		$num = 0;
		$sql = "DELETE FROM re_ug_role WHERE ug_id='$ug_id'";
		$num += $this->db->exec($sql);
		$sql = "UPDATE FROM userinfo SET ug_id='' WHERE ug_id='$ug_id'";
		$num += $this->db->exec($sql);
		$sql = "DELETE FROM uginfo WHERE ug_id='$ug_id'";
		$num += $this->db->exec($sql);
		return $num;
	}






	public function upd_verinfo($name)
	{
		$sql = "update verinfo set mender_time=now() where name='$name'";
		return $this->db->exec($sql);
	}



	public function db_upd_ug_ext($ug)
	{
		$sql = "UPDATE uginfo SET name='$ug[name]', parent_id='$ug[parent_id]', code='$ug[code]',
				mender_account='$ug[mender_account]',mender_time=now(), remark='$ug[remark]', sign='$ug[sign]',
				email='$ug[email]', location='$ug[location]' WHERE ug_id='$ug[ug_id]'";
		return $this->db->exec($sql);
	}

	public function db_del_corp($ug_id)
	{
		$sql = "DELETE FROM channelinfo WHERE ug_id='$ug_id'";
		$num += $this->db->exec($sql);
		$sql = "DELETE FROM verinfo WHERE ug_id='$ug_id'";
		$num += $this->db->exec($sql);
		$sql = "DELETE FROM uginfo WHERE ug_id='$ug_id'";
		$num += $this->db->exec($sql);
		return $num;
	}

	public function db_get_all_ug()
	{
		$sql = "SELECT * FROM uginfo";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_all_corps()
	{
		$sql = "SELECT * FROM uginfo WHERE sign='0' ORDER BY location ASC";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_count_sub_depts($ug_id)
	{
		$sql = "SELECT count(*) FROM uginfo WHERE parent_id='$ug_id' AND sign='1'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_get_sub_depts($ug_id, $limit=10, $offset=0)
	{
		$sql = "SELECT * FROM uginfo WHERE parent_id='$ug_id' AND sign='1' ORDER BY location ASC";
		$sql .= $this->db->limit($limit, $offset);
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_count_sub_corps($ug_id)
	{
		$sql = "SELECT count(*) FROM uginfo WHERE parent_id='$ug_id' AND sign='0'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_get_sub_corps($ug_id, $limit=10, $offset=0)
	{
		$sql = "SELECT * FROM uginfo WHERE parent_id='$ug_id' AND sign='0' ORDER BY location ASC";
		$sql .= $this->db->limit($limit, $offset);
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_check_code($code)
	{
		$sql = "SELECT count(*) FROM uginfo WHERE code='$code'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_check_expect_self_code($code, $ug_id)
	{
		$sql = "SELECT count(*) FROM uginfo WHERE code='$code' AND ug_id<>'$ug_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_check_expect_self_name($name, $parent_id, $ug_id)
	{
		$sql = "SELECT count(*) FROM uginfo WHERE name='$name' AND parent_id='$parent_id' AND ug_id<>'$ug_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_check_name($name, $parent_id)
	{
		$sql = "SELECT count(*) FROM uginfo WHERE name='$name' AND parent_id='$parent_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_check_ug_location($location, $ug_id)
	{
		$sql = "SELECT count(*) FROM uginfo WHERE location=$location and ug_id!='$ug_id';";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_get_max_location()
	{
		$sql = "SELECT max(location) FROM uginfo";
		$query = $this->db->query($sql);
		$result = $query->fetch(PDO::FETCH_ASSOC);
		return $result['max(location)'];
	}

	public function db_get_next_location($action, $parent_id, $location)
	{
		if ($action == 'up')
		{
			$sql = "SELECT ug_id,location FROM uginfo WHERE parent_id = '$parent_id' AND sign='1' AND location < $location ORDER by location DESC LIMIT 1";
		}
		else if($action == 'down')
		{
			$sql = "SELECT ug_id,location FROM uginfo WHERE parent_id = '$parent_id' AND sign='1' AND location > $location ORDER by location ASC LIMIT 1 ";
		}
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	public function db_apply_up_down($ug_id1, $location1, $ug_id2, $location2)
	{
		$sql1 = "UPDATE uginfo SET location = '$location2' WHERE ug_id = '$ug_id1'";
		$sql2 = "UPDATE uginfo SET location = '$location1' WHERE ug_id = '$ug_id2'";
		$n += $this->db->exec($sql1);
		$n += $this->db->exec($sql2);
		return $n;
	}

	public function db_count_dept_users($ug_id)
	{
		$sql = "SELECT count(*) FROM userinfo WHERE ug_id='$ug_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_get_dept_roles($ug_id)
	{
		$sql = "SELECT b.name FROM  re_ug_role a
				LEFT JOIN roleinfo b ON a.role_id=b.role_id
				WHERE a.ug_id='$ug_id' LIMIT 3";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_corp_active_users($ug_id)
	{
	     $sql = "select c.* from userinfo a
	     				inner join re_gid_user b on a.user_id=b.user_id
	     				inner join gidinfo c on b.gid=c.gid
	     				where a.state != '".FILTER_DUMMY_STATE."' and a.ug_id='$ug_id'";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_dept_active_users($ug_id)
	{
	     $sql = "select d.* from re_user_ug a
	     				inner join re_gid_user c on a.user_id=c.user_id
	     				inner join gidinfo d on c.gid=d.gid
	     				where a.ug_id='$ug_id'";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_location_by_ug_id($ug_id)
	{
		$sql = "select location from uginfo where ug_id = '$ug_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

  public function db_get_parent_ug($ug_id)
  {
    $sql= "select * from uginfo where ug_id = (select parent_id from uginfo where ug_id= '$ug_id')";
    $query = $this->db->query($sql);
    return $query->fetch(PDO::FETCH_ASSOC);
  }
};
?>