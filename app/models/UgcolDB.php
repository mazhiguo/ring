<?php
class UgcolDB
{
	public $db;
    public function db_get_user_by_ugid($ug_id)
    {
        $sql = "SELECT * FROM userinfo where ug_id='$ug_id'";
        $query = $this->db->query($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function db_get_all_tmpls()
    {
        $sql = "SELECT * FROM models";
        $query = $this->db->query($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

	public function db_get_col_by_id($id)
	{
		$sql = "SELECT * FROM colinfo WHERE id='$id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	public function db_get_col_by_name($name)
	{
		$sql = "SELECT * FROM ugcolinfo WHERE name='$name'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	public function db_count_sub_cols_by_sign($sign=1)
	{
		$sql = "SELECT count(*) FROM ugcolinfo WHERE sign='$sign'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_count_sub_cols_by_sign_status($sign=1, $status=0)
	{
		$sql = "SELECT count(*) FROM ugcolinfo WHERE sign='$sign' and status='$status'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

    public function db_get_all_parent_cols($pid)
    {
        $sql = "SELECT * FROM ugcolinfo where parent_id='$pid'";
        $query = $this->db->query($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function db_get_rusers_in_content($id)
    {
        $sql = "SELECT u.* FROM userinfo u left join 
                re_user_col r on u.user_id=r.user_id 
                WHERE r.col_id=$id and r.canread=1";

        $query = $this->db->query($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function db_get_rusers_notin_content($id, $ug_id)
    {
        $sql = "select a.* from userinfo a left join 
                (select * from re_user_col WHERE col_id=$id and canread=1) b 
                on a.user_id=b.user_id where a.ug_id='$ug_id' and b.col_id is null"; 
        $query = $this->db->query($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function db_get_wusers_in_content($id)
    {
        $sql = "SELECT u.* FROM userinfo u left join 
                re_user_col r on u.user_id=r.user_id 
                WHERE r.col_id=$id and r.canwrite=1";

        $query = $this->db->query($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function db_get_wusers_notin_content($id, $ug_id)
    {
        $sql = "select a.* from userinfo a left join 
                (select * from re_user_col WHERE col_id=$id and canwrite=1) b 
                on a.user_id=b.user_id where a.ug_id='$ug_id' and b.col_id is null"; 
        $query = $this->db->query($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function db_add_content($ug)
    {
        $sql = "INSERT INTO colinfo SET name='$ug[name]', code='$ug[code]', parent_id=$ug[parent_id],
									ug_id='$ug[ug_id]', tmpl_id='$ug[tmpl_id]', create_account='$ug[create_account]',
                                    create_time=now(), remark='$ug[remark]', sign='$ug[sign]',
                                    location='$ug[location]'";
		if ($this->db->exec($sql) <= 0)
        {
            return "0";
        }
        else
        {
            return $this->db->lastInsertId();
        }
     }

    public function db_add_user_to_content($con_id, $user_id, $canr, $canw)
    {
        $sql = "INSERT INTO re_user_col (user_id,col_id,canread,canwrite,status) values ('$user_id',$con_id,$canr,$canw,0)";
        return $this->db->exec($sql);
    }
    public function db_upd_user_to_content($con_id, $user_id, $canr, $canw)
    {
        $sql = "UPDATE re_user_col SET  canread=$canr,canwrite=$canw 
                WHERE user_id=$user_id and col_id=$con_id";
        return $this->db->exec($sql);
    }

    public function db_del_user_from_content($con_id)
    {
        $sql = "DELETE FROM re_user_col WHERE col_id=$con_id";
        return $this->db->exec($sql);
    }

	public function db_upd_ug($ug)
	{
        $sql = "UPDATE ugcolinfo SET name='$ug[name]', code='$ug[code]', remark='$ug[remark]',
                create_time=now() WHERE id=$ug[id] AND sign=1";
		return $this->db->exec($sql);
	}

    public function db_upd_content($content)
    {
        $sql = "UPDATE colinfo SET name='$content[name]', code='$content[code]', remark='$content[remark]',
                create_time=now(), tmpl_id=$content[tmpl_id] WHERE id=$content[id] AND sign=3";


		return $this->db->exec($sql);
    }

	public function db_del_col($ug_id)
	{
		$num = 0;
		$sql = "DELETE FROM colinfo WHERE parent_id='$ug_id'";
		$num += $this->db->exec($sql);
		$sql = "DELETE FROM colinfo WHERE id='$ug_id'";
		$num += $this->db->exec($sql);
		$sql = "DELETE FROM re_user_col WHERE col_id not in (SELECT id FROM colinfo)";
		$num += $this->db->exec($sql);
		return $num;
	}
	public function db_count_sub_cols($id, $ug_id)
	{
		$sql = "SELECT count(*) FROM colinfo WHERE parent_id='$id' and ug_id='$ug_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_get_sub_cols($id, $ug_id, $limit=10, $offset=0)
	{
		$sql = "SELECT c.*,t.name tmpl_name FROM colinfo c left join models t on c.tmpl_id=t.id
                WHERE c.parent_id='$id' and c.ug_id='$ug_id' ORDER BY location ASC";
		$sql .= $this->db->limit($limit, $offset);
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function db_check_code($code)
	{
		$sql = "SELECT count(*) FROM colinfo WHERE code='$code'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_check_expect_self_code($code, $id)
	{
		$sql = "SELECT count(*) FROM colinfo WHERE code='$code' AND id<>'$id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_check_expect_self_name($name, $parent_id, $id)
	{
		$sql = "SELECT count(*) FROM colinfo WHERE name='$name' AND parent_id='$parent_id' AND id<>'$id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_check_name($name, $parent_id)
	{
		$sql = "SELECT count(*) FROM colinfo WHERE name='$name' AND parent_id='$parent_id' and sign=3";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_get_max_location()
	{
		$sql = "SELECT max(location) FROM colinfo";
		$query = $this->db->query($sql);
		$result = $query->fetch(PDO::FETCH_ASSOC);
		return $result['max(location)'];
	}

    public function db_upd_status($id, $status)
    {
        $sql = "UPDATE ugcolinfo SET status='$status' WHERE id=$id";
		return $this->db->exec($sql);
    }
};
?>