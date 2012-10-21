<?php
class Cnotice
{
	var $uuid;
	var $socket;

	function Cnotice()
	{
		$this->uuid = $this->uuid();
	}

	function Write( &$request)
	{
		$len = strlen($request);
		$binarydata = pack("L", $len);
		if ($this->socket ) @fclose($this->socket);
		$this->socket = @fsockopen(MS_SERVER, MS_SERVER_PORT, $errno, $errstr, 1);
		if (!$this->socket) return -10001; // could not conect to server
		@fwrite($this->socket, $binarydata, 4);
		return @fwrite($this->socket, $request, $len);
	}

	function Read(&$result)
	{
		$result='';
		if (!$this->socket){
			$result = 'Can not connect to server '. MS_SERVER.':'.MS_SERVER_PORT;
			return 0;
		}
		$len = 0;
		while (true){
		    $tmp = @fread($this->socket, 4096);
		    $n = strlen($tmp);
		    if ( $n == 0 ) break;
		    $len += $n;
		    $result .= $tmp;
		}
		@fclose($this->socket);
		$this->socket = 0;
		return $len;
	}

	function doResponse(&$info)
	{
		$this->Read($info);
		$info = iconv('gb2312', 'UTF-8', $info);
		$response = new SimpleXMLElement($info);
		$attr = $response->attributes();
		$id=$attr['id'];
		$info= $response->result;
		$code_info = $info->attributes();
		$code = $code_info['code'];
		if( $code == 0 ||  $code == 1000 ) return 0;
		return $code;
	}

	function uuid()
	{
		return md5(getmypid().uniqid(rand()).$_SERVER['SERVER_NAME']);
	}

	// 清除服务器缓存
	function ClearCacheNotice()
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .= "<request id='92' msid='$this->uuid'><message>";
		$request .= "</message></request>";
		return $this->Write($request);
	}

	// 批量导入，批量更新消息，同步用户，组织，通讯录
	function SyncAllNotice()
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .= "<request id='92' msid='$this->uuid'><message>";
		$request .= "</message></request>";
		return $this->Write($request);
	}

	// 发送申请GID消息
	function GidNotice($para_arr, $num)
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
  		$request .= "<request id='81'  msid='$this->uuid'>\r\n";
		$request .= "<message>\r\n";
		$request .= "	<scope>$num</scope>\r\n";
		$request .= "	<msid>$para_arr[msid]</msid>\r\n";
		$request .= "	<zoneid>$para_arr[mszoneid]</zoneid>\r\n";
		$request .= "	<gs>$para_arr[gsid]</gs>\r\n";
		$request .= "	<oemid>500</oemid>\r\n";
		$request .= "</message>\r\n";
		$request .= "</request>";
	 	return $this->Write($request);
	}

	// 发送组织结构变更消息
	function UgNotice($ug_id=null)
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		/*$request .= "<request id='11' msid='$this->uuid' ug_id='$ug_id'>\r\n";
		$request .= "</request>";*/
		$request .= "<request id='11' msid='$this->uuid'>\r\n";
		$request .= "<message><ug ug_id=''/></message>\r\n";
		$request .= "</request>";
		return $this->Write($request);
	}

	// 发送通讯录变更消息
	function ContactsNotice($contacts, $ug_id)
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .= "<request id='42' msid='$this->uuid'>\r\n";
		$request .= "<message>\r\n";
		$request .= "	<contacts ug_id='$ug_id'>\r\n";
		if (is_array($contacts))
			foreach ($contacts as $v)
			$request .= "		<contact id='$v'/>\r\n";
		else
			$request .= "		<contact id='$contacts'/>";
		$request .= "	</contacts>\r\n";
		$request .= "</message>\r\n";
		$request .= "</request>";
		return $this->Write($request);
	}

	// 发送通讯录接受者消息
	function ContactsReceiversNotice($msg)
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .="<request id='43' msid='$this->uuid'>\r\n";
		$request .= $msg;
		$request .= "</request>";
		return $this->Write($request);
	}

	// 发送通讯录接受者消息
	// <conf value='' />
	function ContactsConfChange($msg)
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .="<request id='44' msid='$this->uuid'>\r\n";
		$request .= $msg;
		$request .= "</request>";
		return $this->Write($request);
	}

	/**
	 * 发送系统消息通知
	 * $buddylist =  <receiver gid=" " zoneid=" " gs=" "/>
	 * type = sys | OAsys
	 */
	function SendMsgNotice($type='sys', $buddylist, $body, $htmlbody, $corp_id='')
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .= "<request id='71' msid='$this->uuid'>\r\n";
		$request .= "<message type='$type'>\r\n";
		$request .= "	<sender gid='' zoneid='5000'/>\r\n";
		if ($corp_id != '')
		$request .= "	<receivers ug_id='$corp_id'>\r\n";
		else
		$request .= "	<receivers>\r\n";
		$request .= $buddylist;
		$request .= "	</receivers>\r\n";
		$request .= "	<body>\r\n";
		$request .= strip_tags(htmlspecialchars_decode($body));
		$request .= "	</body>\r\n";
		$request .= "	<htmlbody>\r\n";
		$request .= $htmlbody;
		$request .= "	</htmlbody>";
		$request .= "</message>\r\n";
		$request .= "</request>";
		return $this->write($request);
	}

	/**
	 * 发送关键字消息
	 * $buddylist =  <receiver gid=" " zoneid=" " gs=" "/>

		<dataex id="1" subtype="im" msid="" creator="1000">BASE64 MSG </dataex>

		<message type="sys" subtype="dictionary" id="" from="1000" to="1000" ver="2.0"><nickname>Admin</nickname><newtime>1215678136</newtime>
		<htmlbody>

		</htmlbody><body></body></message>
	 */
	function SendDataEx($msg, $buddylist='', $corp_id='')
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .= "<request id='70' msid='$this->uuid'>\r\n";
		$request .= "<message type='dataex' >\r\n";
		if ($corp_id != '')
		$request .= "	<receivers ug_id='$corp_id'>\r\n";
		else
		$request .= "	<receivers>\r\n";
		$request .= $buddylist;
		$request .= "	</receivers>\r\n";
		$request .= "	<body>\r\n";
		$request .= $msg;
		$request .= "	</body>\r\n";
		$request .= "</message>\r\n";
		$request .= "</request>";
		return $this->write($request);
	}

	// 发送用户权限变更消息
	function PrivNotice($buddylist)
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .= "<request id='21' msid='$this->uuid'>\r\n";
		$request .= "<message>\r\n";
		$request .= $buddylist;
		$request .= "</message>\r\n";
		$request .= "</request>";
		return $this->Write($request);
	}

	function PrivNoticeByUg($ugid, $buddylist='')
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .= "<request id='21' msid='$this->uuid'>\r\n";
		$request .= "<message>\r\n";
		$request .= "	<ug ug_id='$ugid'/>\r\n";
		$request .= $buddylist;
		$request .= "</message>\r\n";
		$reuqest .= "</request>";
		return $this->Write($request);
	}

	function ChannelNotice($ug_id)
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .="<request id='31' msid='$this->uuid'><message><ug ug_id='$ug_id'/></message></request>";
		return $this->Write($request);
	}

	function ParaNotice($ug_id)
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .="<request id='41' msid='$this->uuid'><message><ug ug_id='$ug_id'/></message></request>";
		return $this->Write($request);
	}

	function SynchNotice($buddylist)
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .="<request id='51' msid='$this->uuid'><message>";
		$request .=$buddylist;
		$request .="</message></request>";
		return $this->Write($request);
	}

	/**
	 * 发送组长通知消息
	 * 消息格式：
		<message>
			<ug ug_id='$ug_id'/>
			<tribe id='' action=''>
				<buddylist>
					<buddy gid= zoneid= gs= />
					<buddy gid= zoneid= gs= />
				</buddylist>
			</tribe>
		</message>
	 */
	 function TribeNotice($buddylist)
	 {
	  $request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
	  $request .="<request id='61' msid='$this->uuid'>";
	  $request .= $buddylist;
	  $request .="</request>";
	  return $this->Write($request);
	 }

	 /**
	  * 发送短信
	  *
	  */
	function SmsMsg($sender, $receivers_mobile, $body, $time)
	{
		$count = count($receivers_mobile);
		$request = "<?xml version='1.0' encoding='UTF-8' ?>";
		$request = "<request id='72' msid='$this->msid'>";
		$request .= "<message>";
		$request .= "<sender gid='$sender[lgid]'>";
		$request .= "<ReceiverList size='$count'>";
		foreach ($receivers_mobile as $mobile)
		{
			$request .=  "<Receiver mobile='$mobile' />";
		}
		$request .= "</ReceiverList><smsbody>";
		$request .= $body;
		$request .= "</smsbody><time>";
		$request .= $time;
		$request .= "</time></message></request>";
		return $this->Write($request);
	}

	function CheckPassport($gid, $passport)
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .="<request id='73' msid='$this->uuid'>";
		$request .="<message><user GID='$gid'>";
		$request .="<passport>".$passport."</passport>";
		$request .="</user></message></request>";
		return $this->Write($request);
	}
}
?>