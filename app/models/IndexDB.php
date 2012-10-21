<?php
class IndexDB
{
	public $db;

	public function db_count_roles()
	{
		$sql = "SELECT count(*) FROM roleinfo";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function db_count_tribes($ug_id)
	{
		$sql = "SELECT count(*) FROM tribeinfo WHERE ug_id='$ug_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}
	
	public function db_count_channels($ug_id)
	{
		$cfg = _parse_ini_file(APPPATH.'ms-web.ini', true);
		$type = $cfg['tribe']['type']; 
		$url = $cfg['tribe']['url'];
		$condition = " and type != '$type' and url != '$url'";
		
		$sql = "SELECT count(*) FROM channelinfo WHERE ug_id='$ug_id' " . $condition;
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}	
	
	public function db_count_sub_depts($ug_id)
	{
		$sql = "SELECT count(*) FROM uginfo WHERE parent_id='$ug_id' AND sign='1'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}
	
	public function db_count_corp_users($ug_id)
	{
		$sql = "SELECT count(*) FROM userinfo WHERE state != '".FILTER_DUMMY_STATE."' and ug_id='$ug_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}
	
	public function db_count_contacts($ug_id)
	{
		$sql = "SELECT count(*) FROM contactsinfo WHERE is_root=1 AND ug_id='$ug_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}
	
	public function db_get_licenseinfo()
	{
		$sql = "SELECT * FROM licenseinfo";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function db_count_used_gid()
	{
		$sql = "SELECT count(*) FROM gidinfo WHERE state=1";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);	
	}
	
	public function db_count_getted_gid()
	{
		$sql = "SELECT count(*) FROM gidinfo";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);	
	}
}; 
?>