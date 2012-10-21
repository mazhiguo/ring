<?php
class ModelsDB
{
	public $db;

    // 创建表
	public function db_create_table($tablename)
	{
		$sql = "
			CREATE TABLE `m_{$tablename}` (
	            `id` int(9) NOT NULL AUTO_INCREMENT,
	            `cid` int(9) NOT NULL,
                `ugid` varchar(32) DEFAULT NULL,
                `creator` varchar(32) DEFAULT NULL,
                `create_time` varchar(32) DEFAULT NULL,
                `status` int(4) DEFAULT 0,
	            PRIMARY KEY (`id`)
	          ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		";
		return $this->db->exec($sql);
	}

	// 修改表结构信息
	public function db_update_tablename($old_tablename, $new_tablename)
	{
		$sql = "rename table `m_{$old_tablename}` to `m_{$new_tablename}`;";
		return $this->db->exec($sql);
	}

	// 删除表
	public function db_delete_table($tablename)
	{
		$sql = "drop table `m_{$tablename}`;";
		return $this->db->exec($sql);
	}

    // 添加字段
    public function db_add_table_field($tablename, $fieldname, $lenght, $defaultv)
    {
		$sql = "alter table `m_{$tablename}` add column `{$fieldname}` varchar({$lenght}) DEFAULT '{$defaultv}' NULL;";
		return $this->db->exec($sql);
    }

    // 修改字段
    public function db_upd_table_field($tablename, $old_fieldname, $new_fieldname, $lenght, $defaultv)
    {
		$sql = "alter table `m_{$tablename}` change `{$old_fieldname}` `{$new_fieldname}` varchar({$lenght}) character set utf8 collate utf8_general_ci default '{$defaultv}' NULL ;";
		return $this->db->exec($sql);
    }

    // 删除字段
    public function db_del_table_field($tablename, $fieldname)
    {
		$sql = "alter table `m_{$tablename}` drop column `{$fieldname}`;";
		return $this->db->exec($sql);
	}

	// 添加模板表数据
	public function db_add_table_data($tname, $fields, $_REQUEST, $data)
	{
		$fs = array();
		foreach($fields as $field)
		{
            $type = $field['type'];
			$name = $field['name'];
            if ($type=='file' && $_FILES[$name]['size']!=0)
            {
                $f_tmp_name = $_FILES[$name]['tmp_name'];
                $f_name = $_FILES[$name]['name'];
                $_f_name = mb_convert_encoding($f_name, "GBK", "UTF-8");
                $s = file_get_contents($f_tmp_name);
                $up_dir = APPPATH.'data/upload/';
                if (!file_exists($up_dir)) mkdir($up_dir, 0777);
                file_put_contents($up_dir.$_f_name, $s);
                $value = $up_dir.$f_name;
            }
			else if ($type == 'checkbox')
            {
                $value = isset($_REQUEST[$name]) ? (is_array($_REQUEST[$name])?implode(',', $_REQUEST[$name]):$_REQUEST[$name]) : '';
            }
            else $value = isset($_REQUEST[$name]) ? $_REQUEST[$name] : '';
			$fs[] = "`{$name}`='$value'";
		}
        foreach($data as $k=>$v)
		{
			$fs[] = "`{$k}`='$v'";
		}
		$sql = "insert into m_{$tname} set ".implode(',',$fs);
		return $this->db->exec($sql);
	}

    // 修改模板数据
    public function db_upd_table_data($tname, $id, $fields, $_REQUEST, $data)
    {

		$fs = array();
		foreach($fields as $field)
		{
            $type = $field['type'];
			$name = $field['name'];
            // 如果有新文件上传
            if ($type=='file')
            {
                if ($_FILES[$name]['size'] != 0)
                {
                    $f_tmp_name = $_FILES[$name]['tmp_name'];
                    $f_name = $_FILES[$name]['name'];
                    $_f_name = mb_convert_encoding($f_name, "GBK", "UTF-8");
                    $s = file_get_contents($f_tmp_name);
                    $up_dir = APPPATH.'data/upload/';
                    if (!file_exists($up_dir)) mkdir($up_dir, 0777);
                    file_put_contents($up_dir.$_f_name, $s);
                    $value = $up_dir.$f_name;
                    $fs[] = "`{$name}`='$value'";
                }
                continue;
            }

            $value = isset($_REQUEST[$name]) ? (is_array($_REQUEST[$name])?implode(',', $_REQUEST[$name]):$_REQUEST[$name]) : '';
            $fs[] = "`{$name}`='$value'";
		}
        foreach($data as $k=>$v)
		{
			$fs[] = "`{$k}`='$v'";
		}
		$sql = "update m_{$tname} set ".implode(',',$fs)." where id='$id'";
		return $this->db->exec($sql)<0 ? false : true;
    }

	// 删除模板数据
	public function db_del_table_data($tname, $id)
	{
		$sql = "delete from m_{$tname} where id='$id'";
		return $this->db->exec($sql);
	}

    // 查询模板数据
    public function db_get_table_data($tname, $id)
    {
		$sql = "SELECT * FROM m_{$tname} where id='$id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
    }

    // 查询模板表数据总数 用于分页
    public function db_get_data_count($tname)
	{
		$sql = "SELECT count(*) FROM m_{$tname}";
		$query = $this->db->query($sql);
		return $query ? $query->fetch(PDO::FETCH_COLUMN, 0) : 0;
	}

    // 查询模板表数据总数 用于分页
    public function db_datalist_count($tname, $cid)
	{
        $account = $_SESSION['AdminAccount'];
        $ug_id = $_SESSION['AdminUgId'];
        $privs = $_SESSION['AdminPrivs'];
        $dataprivs = $_SESSION['AdminDataPrivs'];
        if (in_array(2, $privs) || $dataprivs[$cid]['canread']==1)
        {
            $sql = "SELECT count(*) FROM m_{$tname} where cid='$cid' and ugid='$ug_id'";    
        }else if ($dataprivs[$cid]['canwrite']==1)
        {
            $sql = "SELECT count(*) FROM m_{$tname} where cid='$cid' and ugid='$ug_id' and creator='$account'";
        }else 
        {
            return 0;
        }
		
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

    // 查询模板表数据 用于分页
    public function db_get_datalist($tname, $cid, $limit, $offset)
    {
        $account = $_SESSION['AdminAccount'];
        $ug_id = $_SESSION['AdminUgId'];
        $privs = $_SESSION['AdminPrivs'];
        $dataprivs = $_SESSION['AdminDataPrivs'];
        if (in_array(2, $privs) || $dataprivs[$cid]['canread']==1)
        {
            $sql = "SELECT * FROM m_{$tname} where cid='$cid' and ugid='$ug_id' order by id desc";
        }else if ($dataprivs[$cid]['canwrite']==1)
        {
            $sql = "SELECT * FROM m_{$tname} where cid='$cid' and ugid='$ug_id' and creator='$account' order by id desc";
        }else
        {
            return array();
        }
		$sql .= $this->db->limit($limit, $offset);

		$query = $this->db->query($sql);
		return $query->fetchAll();
    }

    // 查询模板表数据
    public function db_get_datalist_all($tname, $cid)
    {
		$sql = "SELECT * FROM m_{$tname} where cid='$cid' order by id desc";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // 修改模板数据状态
    public function db_upd_status($tname, $id, $status)
    {
		$sql = "update m_{$tname} set status=$status where id='$id'";
		return $this->db->exec($sql);
    }

    // 模板总数 用于分页
    public function db_count($q='')
	{
		$sql = "SELECT count(*) FROM models";
		if (trim($q) != '')
			$sql .= " where `name` like '%$q%' or `desc` like '%$q%'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_COLUMN, 0);
	}

	// 分页显示模板
	public function db_get_all($limit=10, $offset=10, $q='')
	{
		$sql = "SELECT * FROM models";
		if (trim($q) != '')
			$sql .= " where `name` like '%$q%' or `desc` like '%$q%'";
		$sql .= $this->db->limit($limit,$offset);

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

	// 根据name查询模板信息
	public function db_get_one_by_name($name, $id='')
	{
		$sql = "SELECT * FROM models where name='$name' ";
		if (trim($id) != '') $sql .= "and id!='$id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	// 添加模板信息
	public function db_add_model($name, $desc)
	{
		$sql = "insert into models set `name`='$name', `desc`='$desc'";
		return $this->db->exec($sql);
	}

	// 修改模板信息
	public function db_upd_model($id, $name, $desc)
	{
		$sql = "update models set `name`='$name', `desc`='$desc' where id='$id'";
		return $this->db->exec($sql);
	}

	// 删除模板信息
	public function db_del_model($id)
	{
		$sql = "delete from models where id='$id'";
		return $this->db->exec($sql);
	}

	// 根据mid查询所有字段
	public function db_get_all_fields($mid)
	{
		$sql = "SELECT * FROM fields where mid = '$mid'";
		$query = $this->db->query($sql);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	// 根据mid、isshow查询所有字段
	public function db_get_all_fields_by_isshow($mid, $isshow)
	{
		$sql = "SELECT * FROM fields where mid='$mid' and isshow='$isshow'";
		$query = $this->db->query($sql);
		//return $query?$query->fetchAll(PDO::FETCH_ASSOC):array();
        return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	// 查询一个字段信息
	public function db_get_one_field($id)
	{
		$sql = "SELECT * FROM fields where id='$id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	// 根据name查询一个字段信息
	public function db_get_one_field_by_name($mid, $name, $id='')
	{
		$sql = "SELECT * FROM fields where name='$name' and mid='$mid' ";
		if (trim($id) != '') $sql .= "and id!='$id'";
		$query = $this->db->query($sql);
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	// 添加一个字段
	public function db_add_field($mid, $name, $desc, $type, $lenght, $values, $rules, $rulesdesc, $required, $isshow)
	{
		$sql = "insert into fields set `mid`='$mid', `name`='$name', `desc`='$desc', `type`='$type', `lenght`='$lenght', `values`='$values', `rules`='$rules', `rulesdesc`='$rulesdesc', `required`='$required', `isshow`='$isshow'";
		return $this->db->exec($sql);
	}

	// 修改字段
	public function db_upd_field($id, $name, $desc, $type, $lenght, $values, $rules, $rulesdesc, $required, $isshow)
	{
		$sql = "update fields set `name`='$name', `desc`='$desc', `type`='$type', `lenght`='$lenght', `values`='$values', `rules`='$rules', `rulesdesc`='$rulesdesc', `required`='$required', `isshow`='$isshow' where id='$id'";
		return $this->db->exec($sql);
	}

	// 删除字段
	public function db_del_field($id)
	{
		$sql = "delete from fields where id='{$id}';";
		return $this->db->exec($sql);
	}
}
 ?>