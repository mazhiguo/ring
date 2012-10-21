<?php
require_once "socket.php";

/**
 * wrapper class for GK-Express Monitor service
 */
Class CGKMonService
{
	var $_server;
	var $_port;
	var $debug = false;

	function CGKMonService($server="127.0.0.1", $port=8899)
	{
		$this->_server = $server;
		$this->_port = $port;
	}

	function request( &$request, &$result )
	{
		//echo "----------------------------------------------------------->>request begin\r\n";
		//echo "$request\r\n";
		//echo "----------------------------------------------------------->>request end\r\n";

		$sock = new CTCPSocket(false);
		if ( !$sock->connect($this->_server, $this->_port) ) return false;
		$sock->set_recv_timeout(10*1000);
		if ( !$sock->write_str($request) ) return false;
		$ret=$sock->read_str($result);
		//echo "----------------------------------------------------------->>response begin\r\n";
		//echo "$result\r\n";
		//echo "----------------------------------------------------------->>response end\r\n";
		return $ret;
	}

	function excute( $cmd, &$data)
	{
		$request = '<request method="cmd" service="'.$cmd.'"/>';
		return $this->request($request, $data);
	}

	function writeini($files)
	{
		$request = '';
		$request .= '<request method="writeini">';
		$request .= '<files>';
		foreach ($files as $key => $val)
		{
			$request .= "<file name='$key'>";
			foreach ($val as $item)
				$request .= "<item section='$item[section]' key='$item[key]' value='". base64_encode($item[value]) ."'/>";
			$request .= '</file>';
		}
		$request .= '</files>';
		$request .= '</request>';
		if (!$this->request($request, $res)) return false;
		return (preg_match('/Ok/', $res)) ? true : false;
	}

	function getini($fn, $section, $key, &$value)
	{
		$request = '<request method="getini">';
		$request .= '<file name="'.$fn.'" section="'.$section.'" key="'.$key.'"></file>';
		$request .= '</request>';
		if ($this->request($request, $res))
		{
			if ($res)
			{
				$xml = new SimpleXMLElement($res);
				if ($xml)
				{
					$code = $xml->result->attributes()->code;
					if ($code >= 0) $value = $xml->detail;
				}
			}
		}
		// TODO::解析xml $res, 并获得值
		// <response><result  code="0">eeee</result><detail></detail></response>
	}

	function savetofile( &$tofn, &$content )
	{
		$request = '<request method="savetofile">';
		$request .= '<file name="'.$tofn.'" contents="'. base64_encode($content) .'"></file>';
		$request .= '</request>';
		if ( !$this->request($request, $res) )
		{
			return false;
		}
		if ( preg_match('/success/', $res) ) return true;
		return false;
	}

	function getfile($fn, $offset, $length, &$content)
	{
		$request = '<request method="getfile" >';
		$request .= '<file name="'.$fn.'" offset="'.$offset.'" length="'.$length.'" >';
		$request .= '</file>';
		$request .= '</request>';
		$res = '';
		$content = "";

		if (!$this->request($request, $res))
		{
			return false;
		}
		try {
			$oxml = new SimpleXMLElement($res);
		} catch (Exception $e) {
			if ($e) {
				return false;
			}
		}
		$code_attr = $oxml->result->attributes();
		$code = $code_attr->code.'';
		if ($code < 0)
		{
			return false;
		}
		$content = base64_decode($oxml->detail.'');

		return true;
	}

	function getfilesize($id, &$fn, &$size)
	{
		$request = '<request method="getfilesize" >';
		$request .= '<file name="'.$fn.'">';
		$request .= '</file>';
		$request .= '</request>';
		//$size = 0;

		if (!$this->request($request, $res))
		{
			return false;
		}
		try {
			$oxml = new SimpleXMLElement($res);
		} catch (Exception $e) {
			if ($e) {
				echo "Response xml is not correct!";
				print_r($e->getMessage()."\r\n");
				return false;
			}
		}
		$code_attr = $oxml->result->attributes();
		$code = $code_attr->code.'';
		if ($code < 0)
		{
			return false;
		}
		$size = (int)($oxml->result.'');
		return true;
		// <response><result  code="0">eeee</result><detail></detail></response>
	}

	function service( $method, $service, &$data)
	{
		$request = '<request method="'.$method.'" service="'.$service.'"/>';
		$a = $this->request($request, $data);
		return $a;
	}

	function start_service($service)
	{
		if ( !$this->service('start', $service, $res) ) return false;
		return preg_match('/\s*success\s*/', $res);
	}

	function stop_service($service)
	{
		if ( !$this->service('stop', $service, $res) ) return false;
		return preg_match('/\s*success\s*/', $res);
	}

	//在linux下 服务启动成功的标志是runing 或 pid
	function status_service($service)
	{
		if ( !$this->service('status', $service, $res) ) return false;
		if (preg_match('/WIN/', PHP_OS))
		{
			return preg_match('/\s*running\s*/', $res);
		}
		else
		{
			if (!preg_match('/\s*running\s*/', $res))
			{
				return preg_match('/pid/', $res);
			}
			return true;
		}
	}


	//---------------------------------------------->>
	// for service gkexpsvr
	//---------------------------------------------->>

	function start_gkexpsvr()
	{
		return $this->start_service('gkexpsvr');
	}

	function stop_gkexpsvr()
	{
		return $this->stop_service('gkexpsvr');
	}

	function status_gkexpsvr()
	{
		return $this->status_service('gkexpsvr');
	}
}
?>