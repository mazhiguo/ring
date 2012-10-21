<?php
class LogDB
{
	public $db;
	
	public function writeLog($logaction, $ClientIp, $AdminAccount, $ug_name, $ug_id)
	{
		$sql = "insert into loginfo set `action`='$logaction', ip='$ClientIp', admin_account='$AdminAccount', ug_name='$ug_name', opetime=now(), ug_id='$ug_id'";
		return $this->db->exec($sql);
	}

	public function count_corp_logs($ug_id)
	{
		$sql = "select count(*) from loginfo where ug_id='$ug_id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);	
	}
	
	public function get_logs($ug_id, $limit, $offset)
	{
		$sql = "SELECT * FROM loginfo WHERE ug_id='$ug_id' ORDER BY opetime desc";
		$sql .= $this->db->limit($limit, $offset);	
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function del_logs($days_num, $ug_id)
	{
		$sql = "delete from loginfo where ug_id='$ug_id' and TO_DAYS(now())-TO_DAYS(opetime) >= $days_num";
		return $this->db->exec($sql);
	}
}
?>