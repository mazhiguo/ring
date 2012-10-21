<?php
class PackageDB
{
	public $db;
    public function db_get_all_tmpls()
    {
        $sql = "SELECT * FROM models";
        $query = $this->db->query($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function db_get_col_contents($cols_ids, $tmpl_id)
	{
		$sql = "SELECT * FROM colinfo WHERE parent_id in ($cols_ids) and sign=2 and tmpl_id=$tmpl_id order by name asc";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}    
    
	public function db_count_pack()
	{
		$sql = "SELECT count(*) FROM packageinfo";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}  
    
    public function db_get_pack($limit=10, $offset=0)
	{
		$sql = "SELECT p.*, m.name as tmpl_name FROM packageinfo p left join models m on p.tmpl_id=m.id ORDER BY p.create_time DESC";
		$sql .= $this->db->limit($limit, $offset);
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}
    
    // 根据mid查询所有字段
	public function db_get_all_fields($mid)
	{
		$sql = "SELECT * FROM fields where mid = '$mid'";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}
    
   	// 根据id查询模板信息
	public function db_get_one($id)
	{
		$sql = "SELECT * FROM models where id='$id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}
    
    // 查询模板表数据
    public function db_get_datalist_all($tname, $cids)
    {
		$sql = "SELECT * FROM m_{$tname} where cid in ($cids) order by id desc";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function db_add_pack($pack)
	{
		$sql = "INSERT INTO packageinfo SET name='$pack[name]', tmpl_id='$pack[tmpl_id]', 
                                    datacount=$pack[datacount],filepath='$pack[filepath]',
									create_account='$pack[create_account]', create_time=now()";
		return $this->db->exec($sql);
	}
    
	public function db_get_pack_by_id($id)
	{
		$sql = "SELECT * FROM packageinfo where pack_id='$id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}

}
?>