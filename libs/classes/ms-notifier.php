<?php

class CMSNotifier
{
	var $uuid;
	var $_server;
	var $_port;
	var $_result;
	function CMSNotifier($server="127.0.0.1", $port=10020)
	{
		$this->_server = $server;
		$this->_port = $port;
	}
	
	function request(&$request, &$result)
	{
		$sock = new CTCPSocket();
		if ( !$sock->connect($this->_server, $this->_port) ) return false;
		if ( !$sock->write_str($request) ) return false;
		return $sock->read($result);
	}
	
	function notify(&$request)
	{
		if ( !$this->request($request, $info) ) return false;
		$info = iconv('gb2312', 'UTF-8', $info);
		$this->_result = $info;

		$response = new SimpleXMLElement($info);
		$attr = $response->attributes();
		$id=$attr['id'];
		$info= $response->result;
		$code_info = $info->attributes();
		$code = $code_info['code'];
		if( $code == 0 ||  $code == 1000 ) return 0;
		return $code;
	}

	// 清除服务器缓存
	function ClearCacheNotice()
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .= "<request id='92' msid='$this->uuid'><message>";
		$request .= "</message></request>";
		return $this->notify($request);		
	}
	
	// 批量导入，批量更新消息，同步用户，组织，通讯录
	function SyncAllNotice()
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .= "<request id='92' msid='$this->uuid'><message>";
		$request .= "</message></request>";		
		return $this->notify($request);		
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
	 	return $this->notify($request);
	}
	
	// 发送组织结构变更消息
	function UgNotice()
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .= "<request id='11' msid='$this->uuid'>\r\n";
		$request .= "</request>";
		return $this->notify($request);
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
		return $this->notify($request);	
	}

	// 发送通讯录接受者消息
	function ContactsReceiversNotice($msg)
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .="<request id='43' msid='$this->uuid'>\r\n";
		$request .= $msg;
		$request .= "</request>";
		return $this->notify($request);
	}
	
	/**
	 * 发送系统消息通知
	 * $buddylist =  <receiver gid=" " zoneid=" " gs=" "/> 
	 */
	function SendMsgNotice($buddylist, $msg, $corp_id='')
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .= "<request id='71' msid='$this->uuid'>\r\n";
		$request .= "<message type='sys'>\r\n";
		$request .= "	<sender gid='' zoneid='5000'/>\r\n";
		if ($corp_id != '')
		$request .= "	<receivers ug_id='$corp_id'>\r\n";	
		else
		$request .= "	<receivers>\r\n";
		$request .= $buddylist;
		$request .= "	</receivers>\r\n";
		$request .= "	<body>\r\n";
		$request .= $msg;
		$request .= "	</body>\r\n";
		$request .= "	<htmlbody>\r\n";
		$request .= $msg;
		$request .= "	</htmlbody>";
		$request .= "</message>\r\n";
		$request .= "</request>";
		return $this->notify($request);
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
		return $this->notify($request);
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
		return $this->notify($request);
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
		return $this->notify($request);
	}
	
	function ChannelNotice($ug_id)
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .="<request id='31' msid='$this->uuid'><message><ug ug_id='$ug_id'/></message></request>";
		return $this->notify($request);
	}

	function ParaNotice($ug_id)
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .="<request id='41' msid='$this->uuid'><message><ug ug_id='$ug_id'/></message></request>";
		return $this->notify($request);
	}
	
	function SynchNotice($buddylist)
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .="<request id='51' msid='$this->uuid'><message>";
		$request .=$buddylist;
		$request .="</message></request>";		
		return $this->notify($request);
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
	  return $this->notify($request);
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
		return $this->notify($request);
	}
	 
	function CheckPassport($gid, $passport)
	{
		$request = "<?xml version='1.0' encoding='UTF-8' ?>\r\n";
		$request .="<request id='73' msid='$this->uuid'>";
		$request .="<message><user GID='$gid'>";
		$request .="<passport>".$passport."</passport>";
		$request .="</user></message></request>";
		return $this->notify($request);
	}
}
?>