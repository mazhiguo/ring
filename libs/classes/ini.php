<?php

//
// logger 
//
function logger($s)
{
	echo $s;
	echo "\r\n";
}

// get/set ini value form file
class IniFile
{
	public $filename;
	public $contents;

	public function IniFile($f=null)
	{
		if ($f)
		{
			$this->filename = $f;
			$this->contents = file_get_contents($f);
		}
	}
	public function contents()
	{
		return $this->contents;
	}
	public function savetofile($f=null)
	{
		$ff = empty($f)?$this->filename:$f;
		//logger(">> now save the configure to file: $ff");
		file_put_contents($ff, $this->contents);
	}
	public function loadfile($f)
	{
		$this->filename = $f;
		//logger(">> now load the configure from file: $f");
		$this->contents = file_get_contents($f);
	}

	public function get($sec, $key, $def=null)
	{
		return $this->__get_section_key($this->contents, $sec, $key, $def);
	}

	public function set($sec, $key, $val)
	{
		$this->contents = $this->__set_section_key($this->contents, $sec, $key, $val);
	}
	
	// Ìæ»»
	public function replace($regexp, $vs)
	{
		$this->contents = preg_replace($regexp, $vs, $this->contents);
	}

	// read ini
	private function __get_section_key($str, $sec, $key, $def=null)
	{
		$arr = array();
		$ps = preg_match("/\S*\[\s*$sec\s*\]\S*/", $str, $arr, PREG_OFFSET_CAPTURE );
		if (!$ps)
		{
			//logger(">> get conf value : [$sec]:$key-->[$def]......[default]" );
			return $def;
		}
	
		// pos of the end of the section line
		$pos_of_section__begin = $arr[0][1]; 
		$pos_of_section_contents__begin = $arr[0][1] + strlen($arr[0][0]); 
		
		// find the next '[', to get the contents between the $sec and the next section
		$pos = strpos($str, "\n[", $pos_of_section_contents__begin) ;
		if ($pos === false)  $pos = strpos($str, "\r[", $pos_of_section_contents__begin);
	
		// get the whole section string
		$sec_str = '';
		if ($pos === false)
		{
			$sec_str = substr($str, $pos_of_section__begin);
		} else {
			$sec_str = substr($str, $pos_of_section__begin, $pos - $pos_of_section__begin);
		}

		// replace the key value
		$arr= array();
		$ps = preg_match("/\S*$key\s*=\s*(.*)\s*\S*/", $sec_str, $arr, PREG_OFFSET_CAPTURE );
		if (!$ps)
		{
			//logger(">> get conf value : [$sec]:$key-->[$def]......[default]" );
			return $def;
		}else
		{
			$v = preg_replace( '/[\r|\n]/', '', $arr[1][0]);
			//logger(">> get conf value : [$sec]:$key-->[$v]......[ok]" );
			return $v;
		}
	}
	
	
	// ÉèÖÃiniµÄÖµ
	private function __set_section_key($str, $sec, $key, $val)
	{
		//logger(">> set conf value : [$sec]:$key-->[$val]" );
		$arr = array();
		$ps = preg_match("/\S*\[\s*$sec\s*\]\S*/", $str, $arr, PREG_OFFSET_CAPTURE );
		if (!$ps)
		{
			return "$str\r\n[$sec]\r\n$key=$val\r\n";
		}
	
		// pos of the end of the section line
		$pos_of_section__begin = $arr[0][1]; 
		$pos_of_section_contents__begin = $arr[0][1] + strlen($arr[0][0]); 
		
		// find the next '[', to get the contents between the $sec and the next section
		$pos = strpos($str, "\n[", $pos_of_section_contents__begin) ;
		if ($pos === false)  $pos = strpos($str, "\r[", $pos_of_section_contents__begin);
	
		// get the whole section string
		$sec_str = '';
		if ($pos === false)
		{
			$sec_str = substr($str, $pos_of_section__begin);
		} else {
			$sec_str = substr($str, $pos_of_section__begin, $pos - $pos_of_section__begin);
		}
		
		// replace the key value

		$arr= array();
		$ps = preg_match("/\S*$key\s*=(.*)\S*/", $sec_str, $arr, PREG_OFFSET_CAPTURE );
		if (!$ps)
		{
			$rn = preg_match("/[\r|\n]+$/", $sec_str)?"":"\r\n";
			$sec_str .= ("$rn$key=$val\r\n");
		}else
		{
			$sec_str = preg_replace("/(\S*)$key\s*=(.*)(\S*)/", "\${1}$key=$val\${3}", $sec_str);
		}

		if ($pos === false) 
			$res = substr_replace($str, $sec_str, $pos_of_section__begin );
		else
			$res = substr_replace($str, $sec_str, $pos_of_section__begin, $pos- $pos_of_section__begin);
		//logger($res);
		return $res;
	}
}

?>
