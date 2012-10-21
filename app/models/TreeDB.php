<?php
class TreeDB
{
	public $db;

    public function db_get_col($col_id)
	{
		$sql = "select * from colinfo where id=$col_id";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}
    

    public function db_get_childcols($col_id)
	{
		$sql = "select * from colinfo where parent_id='$col_id' and (sign=1 or sign=2)";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}
    
    public function db_get_childcols_ug($col_id, $ug_id)
    {
        $sql = "select * from colinfo c inner join re_ug_col r 
                on c.id=r.col_id where c.parent_id='$col_id' 
                and (c.sign=1 or c.sign=2) and r.ug_id='$ug_id'";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function db_get_childugcols($col_id, $ug_id)
	{
		$sql = "select * from colinfo where parent_id='$col_id' and ug_id='$ug_id' and sign=3";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_get_ug($ug_id)
	{
		$sql = "select * from uginfo where ug_id=$ug_id";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	public function db_get_childdepts($id)
	{
		$sql = "select a.ug_id,a.parent_id,a.name,a.sign,b.ug_id as child_id from uginfo a
				left join uginfo b on b.parent_id=a.ug_id
				where a.parent_id='$id' and a.sign='1'
                group by a.ug_id order by a.sign desc, a.location asc, a.name asc";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

    public function db_get_ugs_in_content($id)
    {
        $sql = "SELECT ug_id FROM re_ug_col WHERE col_id=$id";
        $query = $this->db->query($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function db_get_ugs_in_role($id)
    {
        $sql = "SELECT ug_id FROM re_ug_role WHERE role_id='$id'";
        $query = $this->db->query($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }





	// 取单位下未分配部门得用户
	public function db_get_users_not_in_dept($ug_id)
	{
		$sql = "
			select a.*,c.* from userinfo a
			inner join re_gid_user b on a.user_id=b.user_id
			inner join gidinfo c on b.gid=c.gid
			where a.state != '2' and a.user_id not in(select user_id from re_user_ug) and a.ug_id='$ug_id'
		";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	// 取部门下用户
	public function db_get_deptusers($ug_id)
	{
		$sql = "
			select b.*,d.*   from re_user_ug a
			inner join userinfo b on a.user_id=b.user_id
			inner join re_gid_user c on b.user_id=c.user_id
			inner join gidinfo d on c.gid = d.gid
			where a.ug_id='$ug_id' and b.state != '2'

		";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	// 取子组织
	public function db_get_childugs($ug_id)
	{
		$sql = "select a.*,b.ug_id as child_id from uginfo a
				left join uginfo b on b.parent_id=a.ug_id
				where a.parent_id='$ug_id' group by ug_id order by location,code";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}
}