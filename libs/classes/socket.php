<?php
Class CMSSocket
{
	var $socket;
	var $server;
	var $port;
	var $connected;
	function __destruct()
	{
		@socket_close($this->socket);
	}

	function CMSSocket($server=MS_SERVER, $port=MS_SERVER_PORT)
	{
		$this->socket= NULL;
		$this->server = $server;
		$this->port = $port;
		if (false == ($this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)))
		{
			echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
			exit;
		}
		$this->connected = @socket_connect($this->socket,$this->server, $this->port);
	}

	function isConnected()
	{
		return $this->connected;
	}

	function can_write($timeout)
	{
		$write = array($this->socket);
		socket_select($read = NULL,$write,$except = NULL,$timeout);
		return $write;
	}

	function Write(&$data)
	{
		$timeout=5;
		if (!($this->can_write($timeout))) return -1;

		// 发送长度
		$len = strlen($data);
		$binarydata = pack("L", $len);
		@socket_write($this->socket, $binarydata, 4);

		// 发送内容
		$length=@socket_write($this->socket, $data, $len);
		if (false == $length)
		{
			echo "socket_write() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
			return -1;
		}
		else return $length;
	}

	function write_int($i)
	{
		$timeout=5;
		if (!($this->can_write($timeout))) return -1;
		$binarydata = pack("L", $i);
		$length=@socket_write($this->socket, $binarydata, 4);
		if (false == $length)
		{
			echo "socket_write() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
			return -1;
		}
		else return $length;
	}

	function can_read($timeout)
	{
		$read = array($this->socket);
		socket_select($read,$write = NULL,$except = NULL,$timeout);
		return $read;
	}

	function Read(&$data)
	{
		$timeout = 5;
    	if (!($this->can_read($timeout))) return -1;
		while ($tmp = socket_read($this->socket, 2048))
			$data .= $tmp;
	    if($data) return 0;
	    else return -1;
	}
}


/**
 * class CTCPSocket
 * simple wrapper for socket read write
 * write by jianhua @ dianji.com
 * @2008-11-10
 */
Class CTCPSocket
{
	var $_host;
	var $_port;
	var $_connected;
	var $_socket_handle;
	var $_debug_log;

	function __construct($debug_log = true)
	{
		$this->_debug_log = $debug_log;
	}
	function __destruct()
	{
		@socket_close($this->_socket_handle);
	}
	function connect($server, $port)
	{
		$this->_host = $server;
		$this->_port = $port;
		$this->_socket_handle = NULL;
		if (false == ($this->_socket_handle = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)))
		{
			$this->errormsg();
			return 0;
		}
		if ($this->_debug_log)
		{
			echo "\r\nnow begin connect to server ... ".$this->_host .":". $this->_port."\r\n";
		}
		if ( !@socket_connect($this->_socket_handle,$this->_host, $this->_port) )
		{
			$this->errormsg();
			return 0;
		}
		return true;
	}

	function set_send_timeout($to)
	{
		if ($this->_socket_handle)
			socket_set_option(
			  $this->_socket_handle,
			  SOL_SOCKET,  // socket level
			  SO_SNDTIMEO, // timeout option
			  array(
				"sec"=>$to, // Timeout in seconds
				"usec"=>0  // I assume timeout in microseconds
				)
			  );
	}

	function set_recv_timeout($to)
	{
		if ($this->_socket_handle)
			socket_set_option(
			  $this->_socket_handle,
			  SOL_SOCKET,  // socket level
			  SO_RCVTIMEO, // timeout option
			  array(
				"sec"=>$to, // Timeout in seconds
				"usec"=>0  // I assume timeout in microseconds
				)
			  );
	}

	function connected()
	{
		return $this->_connected;
	}

	function errormsg()
	{
		echo "socket failed: reason: " . socket_strerror(socket_last_error()) . "\n";
	}

	function is_ready_write($timeout)
	{
		$write = array($this->_socket_handle);
		socket_select($read = NULL,$write,$except = NULL,$timeout);
		return $write;
	}
	function is_ready_read($timeout)
	{
		$read = array($this->_socket_handle);
		socket_select($read,$write = NULL,$except = NULL,$timeout);
		return $read;
	}

	function write(&$data)
	{
		$len = strlen($data);
		$length=@socket_write($this->_socket_handle, $data, $len);
		if (!$length)
		{
			$this->errormsg();
			return -1;
		}
		return $length;
	}

	function write_int($i)
	{
		$binarydata = pack("L", $i);
		$length=@socket_write($this->_socket_handle, $binarydata, 4);
		if (!$length)
		{
			$this->errormsg();
			return 0;
		}
		return $length;
	}

	//
	// 发送数据协议格式
	// 长度	4字节的整数
	// XML数据
	//
	function write_str(&$data)
	{
		$len = strlen($data);
		$binarydata = pack("L", $len);
		$length=@socket_write($this->_socket_handle, $binarydata, 4);
		$length=@socket_write($this->_socket_handle, $data, $len);
		if (!$length)
		{
			$this->errormsg();
			return -1;
		}
		if ($this->_debug_log) {
			$length = strlen($data);
			echo "\r\nrecv: begin---------------------------------------------------------->>".$length."\r\n";
			echo $data;
			echo "\r\nrecv: end---------------------------------------------------------->>".$length."\r\n";
		}
		return $length;
	}

	//
	// 返回数据协议格式
	// 长度
	// XML数据
	//
	// 新版本才支持
	// 服务器根据
	function read_str(&$data)
	{
		$timeout = 5;
    	if (!($this->is_ready_read($timeout))) return -1;
    
	    $tmp = @socket_read($this->_socket_handle, 4);
	    if ($tmp == '')
	    {
	    	return "";
	    }
	    // 先取长度
	    $arr = unpack("L", $tmp);
	    $len=$arr[1];
		while ($tmp = @socket_read($this->_socket_handle, 4096))
		{
			 $data .= $tmp;
			if (strlen($data) >= $len )
			{
				break;
			}
		}
		if (!$data) return 0;
		return strlen($data);
	}

	//
	// 返回数据协议格式
	// 长度
	// XML数据
	//
	// 新版本才支持
	// 服务器根据
	function read_no_used(&$data)
	{
		$timeout = 5;
    	if (!($this->is_ready_read($timeout))) return -1;
		while ($tmp = @socket_read($this->_socket_handle, 4096))
		{
			$data .= $tmp;
			$pos = strpos($data, "\r\n\r\n");
			if ($pos > 0 )
			{
				$slen = substr($data, 0,$pos);		// 截取\r\n\r\n之前的 “长度”
				$xmldatalen = 0 + $slen;			// 转变为整数，就是 后面的xml的长度
				$total = strlen($slen) + 4 + $xmldatalen; // 应该收到的总长度
				if (strlen($data) >= $total )
				{
					$data = substr($data, $pos+4);
					break;
				}
			}
		}
	    if (!$data) return 0;
		$response="\r\n\r\n";
		@socket_write($this->_socket_handle, $response, 4);
	    return strlen($data);
	}

	//
	// 返回原始的数据格式
	//
	function read_raw(&$data)
	{
		$timeout = 5;
    	if (!($this->is_ready_read($timeout))) return -1;
		while ($tmp = @socket_read($this->_socket_handle, 4096))
		{
			$data .= $tmp;
		}
	    if (!$data) return 0;

		if ($this->_debug_log) {
			$length = strlen($data);
			echo "\r\nrecv: begin---------------------------------------------------------->>".$length."\r\n";
			echo $data;
			echo "\r\nrecv: end---------------------------------------------------------->>".$length."\r\n";
		}
	    return strlen($data);
	}
}

?>