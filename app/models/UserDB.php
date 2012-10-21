<?php
class UserDB
{
	public $db;
    public function db_get_ug_by_ug_id($ug_id)
	{
		$sql = "SELECT * FROM uginfo WHERE ug_id='$ug_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}
    
    public function db_get_all_roles()
    {
        $sql = "SELECT * FROM roleinfo";
        $query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function db_get_ug_role_ids($ug_id)
    {
        $sql = "SELECT role_id FROM re_ug_role WHERE ug_id='$ug_id'";
        $query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
    }

	public function db_add_user($user)
	{
		$sql = "INSERT INTO userinfo SET user_id='$user[user_id]', account='$user[account]', name='$user[name]',
									state='$user[state]', pwd='$user[pwd]',	creator_account='$user[creator_account]',
                                    mender_account='$user[mender_account]',	creator_time=now(), mender_time=now(), 
                                    remark='$user[remark]', sex='$user[sex]', mobile='$user[mobile]', 
                                    office_tel='$user[office_tel]',	position='$user[position]', email='$user[email]',
									location='$user[location]',	ug_id='$user[ug_id]'";
		return $this->db->exec($sql);
	}
    
   	public function db_add_user_role($user_id, $role_id)
	{
		$sql = "INSERT INTO re_user_role SET user_id='$user_id', role_id='$role_id'";
		return $this->db->exec($sql);
	}

    public function db_get_user_role_ids($user_id)
    {
        $sql = "SELECT role_id FROM re_user_role WHERE user_id='$user_id'";
        $query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function db_del_user_role($user_id)
    {
		$sql = "DELETE FROM re_user_role WHERE user_id='$user_id'";
		return $this->db->exec($sql);
    }

	public function db_upd_user($user)
	{
		$sql = "UPDATE userinfo SET account='$user[account]', name='$user[name]', state='$user[state]', 
                                    mender_account='$user[mender_account]',mender_time=now(),
									remark='$user[remark]', sex='$user[sex]',mobile='$user[mobile]',
									office_tel='$user[office_tel]', position='$user[position]', 
									email='$user[email]', ug_id='$user[ug_id]',
									location='$user[location]' WHERE user_id='$user[user_id]'";
		return $this->db->exec($sql);
	}

	public function db_check_user_account($account)
	{
		$sql = "SELECT count(*) FROM userinfo WHERE account='$account'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_search($content)
	{
		$sql = "SELECT * FROM userinfo a
					WHERE (account LIKE '%$content%' OR name LIKE '%$content%' 
							OR creator_account LIKE '%$content%' OR remark LIKE '%$content%'
							OR mobile LIKE '%$content%'	OR office_tel LIKE '%$content%' 
                            OR position LIKE '%$content%' OR email LIKE '%$content%') ";
		$sql .= "ORDER BY location ASC,account ASC";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_del_user($user_id, $msgid=44808775)
	{
		$n = 0;
		$tables = array('re_user_role', 'userinfo');
		foreach ($tables as $table)
		{
			$sql = "DELETE FROM $table WHERE user_id='$user_id'";
			$n += $this->db->exec($sql);
		}
		return $n;
	}








	public function upd_verinfo($name)
	{
		$sql = "UPDATE verinfo SET mender_time=now() WHERE name='$name'";
		return $this->db->exec($sql);
	}
    
	public function db_count_unused_gid()
	{
		$sql = "SELECT count(*) FROM gidinfo WHERE state='0' AND msid IN (SELECT value FROM licenseinfo WHERE name='msid')";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_count_used_gid()
	{
		$sql = "SELECT count(*) FROM gidinfo WHERE state!='0' AND msid IN (SELECT value FROM licenseinfo WHERE name='msid')";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_get_unused_gid($limit=50, $offset=0)
	{
		$sql = "SELECT * FROM gidinfo WHERE state='0' AND msid IN (SELECT value FROM licenseinfo WHERE name='msid') order by gid asc";
		$sql .= $this->db->limit($limit, $offset);
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_one_unused_gid()
	{
		$sql = "SELECT * FROM gidinfo WHERE state='0' LIMIT 1";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	public function db_del_user_depts($user_id)
	{
		$sql = "DELETE FROM re_user_ug WHERE user_id='$user_id'";
		return $this->db->exec($sql);
	}

	public function db_add_user_dept($user_id, $dept_id)
	{
		$sql = "INSERT INTO re_user_ug SET user_id='$user_id', ug_id='$dept_id'";
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

	public function db_get_user_sms($gid)
	{
		$sql = "SELECT * FROM smsgw.tableuser WHERE gid='$gid'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	public function db_get_gid_info($gid)
	{
		$sql = "SELECT * FROM gidinfo WHERE gid='$gid'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
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


    public function db_get_sub_dept_count($dept_id)
	{
		$sql = "SELECT count(*) FROM uginfo WHERE parent_id = '$dept_id'";
		
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}
	public function db_get_user($user_id)
	{
		$sql = "SELECT * FROM userinfo WHERE user_id='$user_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	public function db_get_user_depts($user_id)
	{
		$sql = "SELECT b.* FROM re_user_ug a
				INNER JOIN uginfo b ON a.ug_id=b.ug_id
				WHERE a.user_id='$user_id' ORDER BY b.location";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_check_user_account_expect_self($account, $user_id)
	{
		$sql = "SELECT count(*) FROM userinfo WHERE account='$account' AND user_id<>'$user_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_reset_pwd($user_id, $pwd)
	{
		$sql = "UPDATE userinfo SET pwd='$pwd' WHERE user_id='$user_id'";
		return $this->db->exec($sql);
	}

	public function db_get_useableness_gidnum()
	{
		$sql1 = "SELECT count(*) FROM gidinfo WHERE state = '1' AND msid IN(SELECT value FROM licenseinfo WHERE name='msid')";
		$query1 = $this->db->query($sql1);
		$n1 =  $query1->fetch(PDO::FETCH_COLUMN, 0);
		$sql2 = "SELECT value FROM licenseinfo WHERE name='activenum'";
		$query2 = $this->db->query($sql2);
		$arr = $query2->fetch(PDO::FETCH_BOTH);
		return $arr['value']-$n1;
	}

	public function db_get_all_unused_gid()
	{
		$sql = "SELECT * FROM gidinfo WHERE state='0' AND msid IN (SELECT value FROM licenseinfo WHERE name='msid') ";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_upd_mobile_smsgw($gid, $mobile)
	{
		$sql = "UPDATE smsgw.tableuser SET mobile='$mobile' WHERE gid='$gid'";
		return $this->db->exec($sql);
	}

	public function db_get_max_location()
	{
		$sql = "SELECT max(location) FROM userinfo where state != '".FILTER_DUMMY_STATE."'";
		$query = $this->db->query($sql);
		$arr = $query->fetch(PDO::FETCH_ASSOC);
		return $arr['max(location)'];
	}

	public function db_count_all_users()
	{
		$sql = "SELECT count(*) FROM userinfo";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_get_all_users($limit=10, $offset=0)
	{
		$sql = "SELECT * FROM userinfo ORDER BY location ASC,account ASC";
		$sql .= $this->db->limit($limit, $offset);
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}
    
    public function db_get_havedept_users($ug_id)
	{
		$sql = "SELECT a.* FROM userinfo a WHERE a.ug_id='$ug_id' AND a.state != '0' and a.state != '".FILTER_DUMMY_STATE."' ORDER BY a.location ASC,account ASC";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_corp_active_users()
	{
		$sql = "SELECT * FROM userinfo WHERE state != '0' ORDER BY location ASC,account ASC";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_corp_unactive_users()
	{
		$sql = "SELECT * FROM userinfo WHERE state = '0' ORDER BY location ASC,account ASC";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_nodept_users()
	{
	 	$sql = "SELECT * FROM userinfo WHERE ug_id NOT IN (SELECT ug_id FROM uginfo) ORDER BY location ASC,account ASC";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_dept_all_users($ug_ids_str)
	{
		$sql = "SELECT * FROM userinfo WHERE ug_id IN ($ug_ids_str) ORDER BY location,account ";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_dept_active_users($ug_ids_str)
	{
		$sql = "SELECT * FROM userinfo WHERE ug_id IN ($ug_ids_str) AND state='1' ORDER BY location, account";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_dept_unactive_users($ug_ids_str)
	{
		$sql = "SELECT * FROM userinfo WHERE ug_id IN ($ug_ids_str) AND b.state='0' ORDER BY location, account";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_dept_nogid_users($ug_id, $ug_ids_str)
	{
		$sql = "SELECT a.user_id, b.gid, c.* FROM re_user_ug a
					LEFT JOIN re_gid_user b ON a.user_id=b.user_id
					LEFT JOIN userinfo c ON a.user_id=c.user_id
					WHERE a.ug_id IN($ug_ids_str) AND b.gid IS NULL GROUP BY account ORDER BY location, account";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	*find uid by gid.
	*select user_id from re_gid_user where gid = '';
	*
	*@author liqiongtao
	*/
	public function db_get_user_id_by_gid($gid)
	{
		$sql = "select user_id from re_gid_user where gid = '$gid'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * select name from userinfo
	 * where user_id in (select user_id from re_gid_user where gid = '$gid')
	 *
	 * @author : liqiongtao.
	 */
	public function db_get_username_by_gid($gid)
	{
		$sql = "select name from userinfo where state != '".FILTER_DUMMY_STATE."' and user_id = (select user_id from re_gid_user where gid = '$gid')";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}


	// ----------------------------- for 中国人民银行,相同账号登陆时返回xml
	public function db_get_users_by_account($account)
	{
 		$sql = "select DISTINCT a.*,b.*,c.gid from userinfo a
				inner join re_gid_user c on a.user_id=c.user_id
				inner join re_id_dn b on a.user_id=b.id and type='2'
				where a.state != '".FILTER_DUMMY_STATE."' and a.account='$account'";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_user_by_args($arr = array())
	{
		if (isset($arr['account']))
		{
	 		$sql = "select DISTINCT a.*,c.gid from userinfo a
					inner join re_gid_user c on a.user_id=c.user_id
					where a.state != '".FILTER_DUMMY_STATE."' and a.account='$arr[account]'";
		}
		else if (isset($arr['dn']))
		{
 			$sql = "select DISTINCT a.*,b.*,c.gid from userinfo a
					inner join re_gid_user c on a.user_id=c.user_id
					inner join re_id_dn b on a.user_id=b.id and type='2'
					where a.state != '".FILTER_DUMMY_STATE."' and b.dn='$arr[dn]'";
		}
		else if (isset($arr['user_id']))
		{
	 		$sql = "select DISTINCT a.*,c.gid from userinfo a
					inner join re_gid_user c on a.user_id=c.user_id
					where a.state != '".FILTER_DUMMY_STATE."' and a.user_id='$arr[user_id]'";
		}
		else
		{
			return false;
		}
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}
}
?>