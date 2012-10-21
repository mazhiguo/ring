<?
require_once LIBPATH . 'classes/socket.php';
//目录服务接口
define('PACKET_TYPE_TRIBE_BALANCE', '45');					//企业版申请创建部落
define('BALANCE_SUBTYPE_APPLY_TSANDTID', '12');				//企业版申请创建部落
define('PACKET_TYPE_TRIBE_CONTENT', '48');					//企业版取指定部落的所在服务链接方式、企业版查询指定gid列表中部落信息、企业版查询指定企业的部落gidlist
define('CONTENT_SUBTYPE_GET_TRIBESERVER', '11');			//企业版取指定部落的所在服务链接方式
define('CONTENT_SUBTYPE_GET_TIDLIST_BY_MSGID', '12');		//企业版查询指定gid列表中部落信息
define('CONTENT_SUBTYPE_GET_TRIBEINFO_BYTIDLIST', '13');	//企业版查询指定企业的部落gidlist
//部落管理接口
define('PACKET_TYPE_TRIBE', '44');
define('TRIBE_SUBTYPE_COMMIT_CREATE_V3', '101');			//创建部落
define('TRIBE_SUBTYPE_DISBAND_BYPASSPORT', '102');			//解散部落
define('TRIBE_SUBTYPE_GET_TRIBEINFO_BYPASSPORT', '103');	//获取部落信息
define('TRIBE_SUBTYPE_FREEZE_BYPASSPORT', '104');			//冻结部落
define('TRIBE_SUBTYPE_UNFREEZE_BYPASSPORT', '105');			//解冻部落
//成员管理接口
define('PACKET_TYPE_TRIBE_MEMBER', '47');
define('MEMBER_SUBTYPE_ADD_BYPASSPORT', '61');				//批量增加成员协议
define('MEMBER_SUBTYPE_DELETE_BYPASSPORT', '62');			//批量删除成员协议
define('MEMBER_SUBTYPE_LIST_BYPASSPORT', '63');				//取成员列表接口协议
//日志管理接口
define('PACKET_TYPE_TRIBE_LOGGING', '57');
define('LOGGING_SUBTYPE_GET', '1');							//请求最新的n条工具更新
define('LOGGING_SUBTYPE_DEL', '2');							//删除log记录
//工具类型
define('TRIBE_TOOLTYPE_UNKNOWN', '0');		//不确定类型  成员管理使用
define('TRIBE_TOOLTYPE_MEMBER', '2');		//成员
define('TRIBE_TOOLTYPE_BULLETIN', '3');		//公告
define('TRIBE_TOOLTYPE_CHAT', '4');			//聊天室
define('TRIBE_TOOLTYPE_BBS', '5');			//论坛
define('TRIBE_TOOLTYPE_DOCUMENT', '6');		//文件库
define('TRIBE_TOOLTYPE_LOGGING', '15');		//日志
// 工具类型 toolid
#define TRIBE_TOOLTYPE_UNKNOWN 0
#define TRIBE_TOOLTYPE_TRIBE 1
#define TRIBE_TOOLTYPE_MEMBER 2
#define TRIBE_TOOLTYPE_BULLETIN 3
#define TRIBE_TOOLTYPE_CHAT  4
#define TRIBE_TOOLTYPE_BBS  5
#define TRIBE_TOOLTYPE_DOCUMENT 6
#define TRIBE_TOOLTYPE_LOGGING  15
#define TRIBE_TOOLTYPE_RECOMMEND 8
#define TRIBE_TOOLTYPE_TOPIC     9
#define TRIBE_TOOLTYPE_PHOTO    11

//协作区状态
#define TRIBE_STATUS_DISMISS    0//  解散
#define TRIBE_STATUS_ACTIVE     1//  活动
#define TRIBE_STATUS_MOVEED     2//  被迁移
#define TRIBE_STATUS_LOCKED     3//  被限制。

class TribeBean extends DefaultController
{
	//----------------------------------------目录服务接口
	/**
	 * 企业版申请创建部落
	 * 创建协作区第一步
	 * return : triber_id, passport
	 */
	public function apply_create_tribe()
	{
		$licenseinfo = $this->GetLicenseinfo();
		$data = array(
			'type' => PACKET_TYPE_TRIBE_BALANCE,
			'subtype' => BALANCE_SUBTYPE_APPLY_TSANDTID,
			'data_2' => array('INT64' => '0', 'INT32' => $licenseinfo['msid'])
		);
		$xml = $this->request($data);
		$tribe_info = array();
		if ($xml)
		{
			$tribes = $xml->xpath("//tribe");
			if (count($tribes) > 0)
			{
				$tribe_info['tid'] = strval($tribes[0]->attributes()->id);
				$tribe_info['tpassport'] = strval($tribes[0]->tpassport->attributes()->value);
			}
		}
		return $tribe_info;
	}

	/**
	 * 企业版取指定部落的所在服务链接方式
	 * return : tribe_id, server_id
	 */
	public function tribe_link_info($tribe_id)
	{
		$licenseinfo = $this->GetLicenseinfo();
		$data = array(
			'type' => PACKET_TYPE_TRIBE_CONTENT,
			'subtype' => CONTENT_SUBTYPE_GET_TRIBESERVER,
			'data_2' => array('INT64' => $tribe_id, 'INT32' => $licenseinfo['msid'])
		);
		$xml = $this->request($data);
		$tribe_info = array();
		if ($xml)
		{
			$tribes = $xml->xpath('//tribe');
			$tribe_info['tid'] = strval($tribes[0]->attributes()->id);
			$tribe_info['tdomain'] = strval($tribes[0]->server->attributes()->domain);
		}
		return $tribe_info;
	}

	/**
	 * 企业版查询指定gid列表中部落信息
	 * return : 提取返回信息中的 tid, tribe_sid, name
	 */
	public function get_tribe_info_list_by_gids($tribe_id_list)
	{
		$rd = '<tribelist>';
		if (is_array($tribe_id_list))
		{
			foreach ($tribe_id_list as $tribe_id)
				$rd .= "<tribe tid=\"$tribe_id\"/>";
		}
		else $rd .= "<tribe tid=\"$tribe_id_list\"/>";
		$rd .= '</tribelist>';
		$data = array(
			'type' => PACKET_TYPE_TRIBE_CONTENT,
			'subtype' => CONTENT_SUBTYPE_GET_TRIBEINFO_BYTIDLIST,
			'tribe' => $rd
		);
		$xml = $this->request($data);

		$tribes_info = array();
		if ($xml)
		{
			$tribes = $xml->xpath('//tribelist/tribe');
			foreach($tribes as $tribe)
			{
				$tmp = $tribe->attributes();
				$tribes_info[] = array(
					'tid' => strval($tmp->tid),
					'name' => strval($tmp->name),
					'class' => strval($tmp->class),
					'creator' => strval($tmp->creator),
					'cre_time' => strval($tmp->cre_time),
					'modify_time' => strval($tmp->modify_time),
					'last_login_time' => strval($tmp->last_login_time),
					'login_count' => strval($tmp->login_count),
					'num' => strval($tmp->num),
					'iden' => strval($tmp->iden),
					'header' => strval($tmp->header),
					'tribedesc' => strval($tmp->tribedesc),
					'tsport' => strval($tmp->tsport),
					'agentid' => strval($tmp->agentid),
					'domain' => strval($tmp->tid),
					'rank' => strval($tmp->rank),
					'score' => strval($tmp->score),
					'enabled' => strval($tmp->status)		//协作区的屏蔽状态
				);
			}
		}
		return $tribes_info;
	}

	/**
	 * 企业版查询指定企业的部落gidlist
	 * return : 由协作区id组成的数组列表
	 */
	public function get_tribe_id_list()
	{
		$licenseinfo = $this->GetLicenseinfo();
		$data = array(
			'type' => PACKET_TYPE_TRIBE_CONTENT,
			'subtype' => CONTENT_SUBTYPE_GET_TIDLIST_BY_MSGID,
			'data_2' => array('INT32' => $licenseinfo['msid'])
		);
		$xml = $this->request($data);
		$tribe_id_list = array();
		if ($xml)
		{
			$tribes = $xml->xpath('//tribelist/tribe');
			foreach($tribes as $tribe)
				$tribe_id_list[] = strval($tribe->attributes()->tid);
		}
		return $tribe_id_list;
	}

	//----------------------------------------部落管理接口
	/**
	 * 创建部落
	 * 创建协作区第二步，依赖第一步的tribe_id, passport
	 * return : true false
	 */
	public function create_tribe(&$new_tribe)
	{
		$licenseinfo = $this->GetLicenseinfo();
		$new_tribe_info = $this->apply_create_tribe();
		if (count($new_tribe_info) != 2) return false;
		$new_tribe['tribe_id'] = $new_tribe_info['tid'];
		// 工具类型
		$tool_types = array(
			'tmember' => TRIBE_TOOLTYPE_MEMBER,
			'tbulletin' => TRIBE_TOOLTYPE_BULLETIN,
			'tchat' => TRIBE_TOOLTYPE_CHAT,
			'tbbs' => TRIBE_TOOLTYPE_BBS,
			'tdocument' => TRIBE_TOOLTYPE_DOCUMENT,
			'tlogging' => TRIBE_TOOLTYPE_LOGGING,
		);
		$rd = <<<EOD
<tribe>
<tribeinfo tid="$new_tribe_info[tid]" name="$new_tribe[name]" creator="$licenseinfo[msid]" class="1" iden="$new_tribe[iden]" open="$new_tribe[open]" header="1" desc="$new_tribe[remark]" keyword="$new_tribe[keyword]" province ="0" city ="0" auto="1" />
<chief id="$new_tribe[chief_gid]" gs="1" nick="$new_tribe[nick]" gender="0" memheader="0" />
<vr id="$new_tribe[dummy_id]" gs="1" nick="数据服务器(虚拟人)" gender="0" memheader="0" />
<tools>
<tool id="" itemid="2" type="$tool_types[tmember]" name="成员" status="1" toolserver=""/>
<tool id="" itemid="1" type="$tool_types[tbulletin]" name="公告" status="1" toolserver="">$new_tribe[notice]</tool>
<tool id="" itemid="3" type="$tool_types[tchat]" name="聊天室" status="1" toolserver=""/>
<tool id="" itemid="4" type="$tool_types[tdocument]" name="文件" status="1" toolserver="" tracker="track.lava-lava.com:4333" />
<tool id=" " itemid="6" type="$tool_types[tlogging]" name="日志" status="1" toolserver=" "/>
</tools>
</tribe>
EOD;
		$data = array(
			'type' => PACKET_TYPE_TRIBE,
			'subtype' => TRIBE_SUBTYPE_COMMIT_CREATE_V3,
			'tribe' => $rd,
			'data_1' => $new_tribe_info['tpassport'],
			'data_2' => array('INT64' => $new_tribe_info['tid'], 'INT32' => $licenseinfo['msid'])
		);
		$xml = $this->request($data);
		if ($xml)
			return $tribes = $xml->xpath('//tribe/tribeinfo');
		 else return false;
	}

	/**
	 * 解散部落
	 * return : true false
	 */
	public function del_tribe($tribe_id)
	{
		$rd = "<tribe id='$tribe_id' />";
		$data = array(
			'type' => PACKET_TYPE_TRIBE,
			'subtype' => TRIBE_SUBTYPE_DISBAND_BYPASSPORT,
			'tribe' => $rd,
			'data_2' => array('INT64' => $tribe_id, 'INT32' => '0')
		);
		$xml = $this->request($data);
		if ($xml)
		{
			$res = $xml->xpath('//WorkformResponse');
			$ste = strval($res[0]->attributes()->ste);
			if ($ste == TRIBE_SUBTYPE_DISBAND_BYPASSPORT) return true;
			else return false;
		}
		else return false;
	}

	/**
	 * 获取部落信息
	 * return :
	 */
	public function get_tribe_info($tribe_id)
	{
		$data = array(
			'type' => PACKET_TYPE_TRIBE,
			'subtype' => TRIBE_SUBTYPE_GET_TRIBEINFO_BYPASSPORT,
			'data_2' => array('INT64' => $tribe_id, 'INT32' => '0')
		);
		$xml = $this->request($data);
		if ($xml)
		{
			$tribeinfo = $xml->xpath('//tribe/tribeinfo');
			if (!$tribeinfo) return null;
			$tmp = $tribeinfo[0]->attributes();
			$tools = $xml->xpath('//tools/tool');
			foreach($tools as $tool)
			{
				$type = strval($tool->attributes()->type);
				$id = strval($tool->attributes()->id);
				if ($type == TRIBE_TOOLTYPE_LOGGING) $tool_id_logid = $id;		//uuid 取tool id
				else continue;
			}
			$tribes_info = array(
				'tid' => strval($tmp->tid),
				'name' => strval($tmp->name),
				'class' => strval($tmp->class),
				'creator' => strval($tmp->creator),
				'cre_time' => strval($tmp->cre_time),
				'modify_time' => strval($tmp->modify_time),
				'last_login_time' => strval($tmp->last_login_time),
				'login_count' => strval($tmp->login_count),
				'num' => strval($tmp->num),
				'iden' => strval($tmp->iden),
				'header' => strval($tmp->header),
				'tribedesc' => strval($tmp->tribedesc),
				'tsport' => strval($tmp->tsport),
				'agentid' => strval($tmp->agentid),
				'domain' => strval($tmp->tid),
				'rank' => strval($tmp->rank),
				'score' => strval($tmp->score),
				'dummy_gid' => strval($tmp->dummy_gid),		//虚拟人gid
				'tool_id_logid' => $tool_id_logid			//uuid 取tool id
			);
			return $tribes_info;
		}
		else return null;
	}

	/**
	 * 屏蔽违规部落 和 解除屏蔽违规部落
	 * $id=1 激活协作区 $id=0 冻结协作区
	 * return :
	 */
	public function tribe_enabled($tribe_id, $id)
	{
		$licenseinfo = $this->GetLicenseinfo();
		if ($id == "1") //激活
		{
			$data = array(
				'type' => PACKET_TYPE_TRIBE,
				'subtype' => TRIBE_SUBTYPE_UNFREEZE_BYPASSPORT,
				'data_2' => array('INT64' => $tribe_id, 'INT32' => $licenseinfo['msid'])
			);
		}
		else  //冻结
		{
			$data = array(
				'type' => PACKET_TYPE_TRIBE,
				'subtype' => TRIBE_SUBTYPE_FREEZE_BYPASSPORT,
				'data_2' => array('INT64' => $tribe_id, 'INT32' => $licenseinfo['msid'])
			);
		}
		$xml = $this->request($data);
		if($xml)
		{
			$res = $xml->xpath('//WorkformRequest');
			return true;
		}
		else return false;
	}

	//----------------------------------------成员管理接口
	/**
	 * 批量增加成员
	 * 此处用到的添加的gid为长gid，查询用户的gid是短gid
	 * return : true false
	 */
	public function add_members($tribe_id, $members)
	{
		$licenseinfo = $this->GetLicenseinfo();
		$rd = "<tribe id='$tribe_id' msid='$licenseinfo[msid]'>";
		$rd .= "<member_list>";
		foreach ($members as $member)
		{
			$lgid = $member['lgid'];
			$status = $member['status'];
			parsegid($lgid, $zoneid, $sgid);
			$user = $this->TribeDB->db_get_user_by_gid($sgid);
			$rd .= "<member gid='$lgid' status='$status' card='1' header='0' gs='$user[gs]'>$user[display_name]</member>";
		}
		$rd .= "</member_list>";
		$rd .= "</tribe>";

		$data = array(
			'type' => PACKET_TYPE_TRIBE_MEMBER,
			'subtype' => MEMBER_SUBTYPE_ADD_BYPASSPORT,
			'tribe' => $rd,
			'data_2' => array('INT64' => $tribe_id, 'UUID' => TRIBE_TOOLTYPE_UNKNOWN, 'INT32' => $licenseinfo[msid])
		);
		$xml = $this->request($data);
		if ($xml)
		{
			$res = $xml->xpath('//WorkformResponse');
			$ste = strval($res[0]->attributes()->ste);
			if ($ste == MEMBER_SUBTYPE_ADD_BYPASSPORT) return true;
			else return false;
		}
		return false;
	}

	/**
	 * 批量删除成员
	 * 这里传入的gid为 长gid
	 * return :
	 */
	public function del_members($tribe_id, $members_lgid)
	{
		$licenseinfo = $this->GetLicenseinfo();
		$rd = "<tribe id='$tribe_id' msid='$licenseinfo[msid]'>";
		$rd .= "<member_list>";
		foreach ($members_lgid as $lgid)
		{
			if ($lgid != "") $rd .= "<member gid='$lgid'/>";
		}
		$rd .= "</member_list>";
		$rd .= "</tribe>";

		$data = array(
			'type' => PACKET_TYPE_TRIBE_MEMBER,
			'subtype' => MEMBER_SUBTYPE_DELETE_BYPASSPORT,
			'tribe' => $rd,
			'data_2' => array('INT64' => $tribe_id, 'UUID' => TRIBE_TOOLTYPE_UNKNOWN, 'INT32' => $licenseinfo[msid])
		);
		$xml = $this->request($data);
		if ($xml)
		{
			$res = $xml->xpath('//WorkformResponse');
			$ste = strval($res[0]->attributes()->ste);
			if ($ste == MEMBER_SUBTYPE_DELETE_BYPASSPORT) return true;
			else return false;
		}
		else return false;
	}

	/**
	 * 取成员列表
	 * return : gid status 列表
	 */
	public function get_members_list($tribe_id)
	{
		$licenseinfo = $this->GetLicenseinfo();
		$rd = "<tribe id='$tribe_id' msid='$licenseinfo[msid]'/>";
		$data = array(
			'type' => PACKET_TYPE_TRIBE_MEMBER,
			'subtype' => MEMBER_SUBTYPE_LIST_BYPASSPORT,
			'tribe' => $rd,
			'data_2' => array('INT64' => $tribe_id, 'UUID' => TRIBE_TOOLTYPE_UNKNOWN, 'INT32' => $licenseinfo['msid'])
		);
		$xml = $this->request($data);
		$member_list = array();
		if ($xml)
		{
			foreach ($xml->xpath('//member_list/member') as $val)
			{
				$member_attr = $val->attributes();
				$member_list[] = array(
					'gid' => strval($member_attr->gid),
					'status' => strval($member_attr->status),	//status=1 表示组长 status=3 表示普通成员
					'vr' => strval($member_attr->vr)			//vr=1 表示虚拟人 vr=0 表示非虚拟人
				);
			}
		}
		return $member_list;
	}
	//----------------------------------------日志管理接口
	/**
	 * 请求最新的n条工具更新 (聊天室记录、文件库记录)
	 * tooltype=4 表示群聊 tooltype=6 表示文件库
	 * return :
	 */
	public function get_record_list($tribe_id, $id, $page_size, $start, $orderby)
	{
		//通过 获取部落信息 获取 toolid
		$action = array
		(
			'1' => '添加一个成员',
			'2' => '删除一个成员',
			'3' => '成员权限发生变化',
			'11' => '添加文件',
			'12' => '添加目录',
			'13' => '删除文件',
			'14' => '删除目录',
			'15' => '改变文件或目录的名称',
			'16' => '移动文件',
			'17' => '移动目录',
			'21' => '添加群聊内容',
			'22' => '保存群聊内容到文件',
			'31' => 'bbs发帖',
			'32' => 'bbs删帖',
			'33' => 'bbs置顶',
			'34' => 'bbs加精',
		);
		$tribe_info = $this->get_tribe_info($tribe_id);
		if ($id == '0') $tooltype = TRIBE_TOOLTYPE_CHAT; 		//id=0 : 聊天记录
		else $tooltype = TRIBE_TOOLTYPE_DOCUMENT;				//id=1 : 文件库记录
		$toolid = $tribe_info['tool_id_logid'];
		$rd= "<log><condition tooltype=\"$tooltype\" page_size=\"$page_size\" start=\"$start\" orderby=\"$orderby\"/></log>";
		$data = array(
			'type' => PACKET_TYPE_TRIBE_LOGGING,
			'subtype' => LOGGING_SUBTYPE_GET,
			'tribe' => $rd,
			'data_2' => array('INT64' => $tribe_id, 'UUID' => $toolid)
		);
		$xml = $this->request($data);
		if ($xml)
		{
			$log = $xml->xpath('//log');
			$records = $xml->xpath('//log/record');
			$count = strval($log[0]->attributes()->count);
			$record_list = array();
			foreach($records as $record)
			{
				$att = $record->attributes();
				list($date, $time) = explode(' ', strval($att->dateline));
				$lgid = strval($att->gid);
				parsegid($lgid, $zoneid, $gid);
				$user = $this->TribeDB->db_get_user_by_gid($gid);
				$name = $user['name'];
				$text = $action[strval($att->actionid)].strval($att->text);
				$record_list[] = array(
					'gid' => $gid,
					'date' => $date,
					'time' => $time,
					'name' => $name,
					'text' => $text
				);
			}
			return array($count, $record_list);
		}
		else return null;
	}

	/**
	 * 删除log记录 (聊天室记录、文件库记录)
	 * tooltype=4 表示群聊 tooltype=6 表示文件库
	 * <log><condition searchtype='2' tooltype='4' beforeday='30'/></log> 表示删除群聊记录30天之前的记录
	 * <log><condition searchtype='2' tooltype='4' beforeday='0'/></log> 表示删除群聊记录的所有记录
	 * return :
	 */
	public function del_record($tribe_id, $stype, $id, $dday)
	{
		//通过 获取部落信息 获取 toolid
		$tribe_info = $this->get_tribe_info($tribe_id);
		if ($id == '0') $tooltype = TRIBE_TOOLTYPE_CHAT; 		//id=0 : 聊天记录
		else $tooltype = TRIBE_TOOLTYPE_DOCUMENT;				//id=1 : 文件库记录
		$toolid = $tribe_info['tool_id_logid'];
		$rd = "<log><condition searchtype='$stype' tooltype='$tooltype' beforeday='$dday'/></log>";
		$data = array(
			'type' => PACKET_TYPE_TRIBE_LOGGING,
			'subtype' => LOGGING_SUBTYPE_DEL,
			'tribe' => $rd,
			'data_2' => array('INT64' => $tribe_id, 'UUID' => $toolid)
		);
		$xml = $this->request($data);
		if ($xml) return true;
		else return false;
	}

	//----------------------------------------通用方法
	/**
	 * 发送用户同步 和 权限下推消息
	 * 数据库查询取会的是所有用户和虚拟人用户
	 * return : 如果同步成功 则返回值大于0 否则返回值小于0
	 */
	public function send_notices ()
	{
		$user_list = $this->UserDB->db_get_corp_active_users($_SESSION['current_corp_id']);  //有gid并且是活动用户
		$n = 0;
		if ($user_list && count($user_list) > 0)
		{
			$buddylist = "<buddylist>";
			foreach ($user_list as $user)
				$buddylist .= "<buddy gid='$user[gid]' zoneid='$user[zoneid]' gs='$user[gs]'/>";
			$buddylist .= "</buddylist>";
			$SynchNotice = $this->notice->SynchNotice($buddylist);
			$n += $this->notice->SynchNotice($buddylist) > 0;	 //发送用户信息同步消息成功
			$this->UserDB->upd_verinfo('clientpara');
			$n += $this->notice->ParaNotice();
			$n += $this->notice->PrivNotice($buddylist);
		}
		return $n;
	}

	/**
	 * 1.组合拼接xml包
	 * 2.调用socket 发送包 和 读取返回内容
	 * 3.将返回的内容转换为xml对象
	 * return : xml 对象
	 */
	function request ($data)
	{
		$debug = false;
		$tribe = $this->make_tribe_info($data);
		$str_xml = $this->tribe_xml($tribe);
		ms_web_log("TribeBean-Write:\r\n$str_xml");
		// false : 表示不显示debug信息
		$fs = new CTCPSocket(false);
		if (!$fs->connect($tribe['server'], $tribe['port']))
		{
			$msg = "无法连接至协作区服务器，请检查网络。<br />如果网络正常，请检查协作区目录服务器地址[$tribe[server]:$tribe[port]]配置是否正确。";
			show_msg('TEXT', array('msg'=>$msg));
			exit();
		}
		if (!$fs->write($str_xml)) return false;
		$result = '';
		$xml = null;
		$len = $fs->read_raw($result);
		if ($len)
		{
			ms_web_log("TribeBean-Result:\r\n$result");
			list(, $rs)= explode("<ns:WorkformResult>", $result);
			list($res, )= explode("</ns:WorkformResult>", $rs);
			$xml = simplexml_load_string($res);
		}
		if(!$xml)
		{
			$arr = array('msg'=>'服务器返回数据有误。', 'error_msg'=>"发送的XML:\r\n$str_xml \r\n接受的XML:\r\n$result");
			$this->show_msg_ext('TEXT', $arr);
			exit();
		}
		else return $xml;
	}

	//组合数据 默认组合data_1
	public function make_tribe_info($data)
	{
		$licenseinfo = $this->GetLicenseinfo();
		$tribe = array();
		//组合WorkformRequest 标签里的数据
		$cfg = _parse_ini_file(APPPATH.'ms-web.ini', true);
		if (!empty($data['server'])) $tribe['server'] = $data['server'];
		else $tribe['server'] = $cfg['tribeext']['ip'];
		if (!empty($data['port'])) $tribe['port'] = $data['port'];
		else $tribe['port'] = $cfg['tribeext']['port'];
		$tribe['verid'] = '1.1';
		if (!empty($data['msid'])) $tribe['msid'] = $data['msid'];
		else $tribe['msid'] = $licenseinfo['msid'];
		$tribe['type'] = $data['type'];
		$tribe['subtype'] = $data['subtype'];
		//组合sd data_1 里面的数据
		if (!empty($data['data_1']))
			$data_1 = $data['data_1'];
		else $data_1 = $licenseinfo['passport'];
		$len = strlen($data_1) + 2;
		$tribe['data_1']= array ('SHORT' => $len, 'STRING' => $data_1);
		//组合data_2
		$tribe['data_2'] = $data['data_2'];
		//组合rd 里面的数据
		if (!empty($data['tribe'])) $tribe['tribe'] = $data['tribe'];
		return $tribe;
	}

	//xml 拼接 一个标签内最好不要换行
	function tribe_xml($tribe)
	{
		$body = <<<xml
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:ns="http://www.dianji.com">
<SOAP-ENV:Body SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
<ns:Workform><WorkformRequest VerID="$tribe[verid]" gid="$tribe[msid]" te="$tribe[type]" ste="$tribe[subtype]"></WorkformRequest><workformRecord><sd>
xml;
		if(!empty($tribe['data_1']))
		{
			$body .= "\r\n<node name=\"data1\" itype=\"PASCAL\">";
			foreach($tribe['data_1'] as $itype => $value)
				$body .= "\r\n<node itype=\"$itype\">$value</node>";
			$body .= "\r\n</node>";
		}
		$body .= "\r\n<node name=\"data2\" itype=\"PASCAL\">";
		$len = 2;
		foreach ($tribe['data_2'] as $itype => $value)
		{
			if ($itype == 'INT64') $len += 8;
			else if ($itype == 'INT32') $len += 4;
			else if ($itype == 'SHORT') $len += 2;
			else if ($itype == 'BYTE') $len += 1;
			else if ($itype == 'STRING') $len += strlen($value);
			else if ($itype == 'UUID') $len += 16;
		}
		$body .= "\r\n<node itype=\"SHORT\">$len</node> ";
		foreach($tribe['data_2'] as $itype => $value)
			$body .= "\r\n<node itype=\"$itype\">$value</node>";
		$body .= "\r\n</node>";
		$body .= "\r\n</sd>";
		 if (!empty($tribe['tribe']) && $tribe['tribe'] != "")
		 {
		 	$rd = $this->html_encoding($tribe['tribe']);
		 	$body .= "\r\n<rd><node itype=\"STRING\" act=\"XMLDECODING\">\r\n$rd\r\n</node></rd>";
		 }
		 else $body .= "\r\n<rd></rd>";
		$body .= "\r\n</workformRecord></ns:Workform></SOAP-ENV:Body></SOAP-ENV:Envelope>";
		$header = '';
		$header .= "POST /cgi-bin/webservice/Workform HTTP/1.0\r\n";
		$header .= "HOST: ". $tribe['server'] .":". $tribe['port'] . "\r\n";
		$header .= "Content-Type: text/xml; charset=utf-8\r\n";
		$header .= "Content-Length: ". strlen($body) ."\r\n\r\n";

		$request = $header . $body;
		return $request;
	}

	/**
	 * 将字符串< 或 > 转换成 &lt; 或&gt;
	 * 示例： <node name="data1">data</data>
	 * return ： &lt;node name="data1"&gt;data&lt;/data&gt;
	 */
	function html_encoding($str)
	{
		$arr1 = array("&", "<", ">");
		$arr2 = array("&amp;", "&lt;", "&gt;");
		return str_replace($arr1, $arr2, $str);
	}

	//分页代码 设置分页参数
	public function on_page($current_page, $page_rows_num, $total_rows)
	{
		$page_info = array();
		$page_info['page_rows'] = array(5, 10, 15, 20, 30);
		if (!isset($current_page) || empty($current_page)) $current_page = 1;
		if (!isset($page_rows_num) || empty($page_rows_num)) $page_rows_num = $page_info['page_rows'][0];
		$page_info['current_page'] = $current_page;			//当前是第几页
		$page_info['page_rows_num'] = $page_rows_num;		//每页显示多少条
		$page_info['total_rows'] = $total_rows; 			//总共有多少条
		//共有多少页
		if ($page_info['total_rows'] % $page_info['page_rows_num'] == 0) $page_info['total_pages'] = $page_info['total_rows'] / $page_info['page_rows_num'];
		else $page_info['total_pages'] = (int)($page_info['total_rows'] / $page_info['page_rows_num']) + 1;
		if ($page_info['total_pages'] == 0) $page_info['total_pages'] = 1;
		return $page_info;
	}

	public function add_tribe_default_channel($type, $url, $ug_id)
	{
		$channel['type'] = $type;
		$channel['url'] = $url;
		$channel['channel_id'] = uuid();
		$channel['display_name'] = '协作区';
		$channel['name'] = '协作区';
		$channel['img'] = '{image:png}'.base64_encode(file_get_contents(INSTALLPATH."/scripts/tribe.png")); //$img;
		$channel['creator_account'] = $_SESSION['AdminAccount'];
		$channel['mender_account'] = $_SESSION['AdminAccount'];
		$channel['mender_time'] = strval(date('Y-m-d H:i:s'));
		$channel['remark'] = '协作区';
		$channel['width'] = 24;
		$channel['height'] = 24;
		$channel['ug_id'] = $ug_id;
		$channel['refreshmode'] = 'onactive';
		$n = 0;
		$n += $this->ChannelDB->db_add_channel($channel);
		$n += $this->ChannelDB->upd_channel_verinfo($ug_id);
		return $n;
	}

	// 判断新旧两版协作区，是否要启用协作区
	public function check_tribe_ver()
	{
		$cfg = _parse_ini_file(APPPATH.'ms-web.ini', true);
		$type = $cfg['tribe']['type'];
		$url = $cfg['tribe']['url'];
		$switch = $cfg['tribe']['switch'];
		// 旧版 或者 新版-启用
		$tribe_info = $this->TribeDB->db_select_tribe_status($type, $url);
		if ($switch == "0" || ($switch != "0" && $tribe_info))
		{
			// 删除
			$this->TribeDB->db_del_tribe_status($type, $url);
			// 重新添加
			$corps = $this->UgDB->db_get_all_corps();
			foreach($corps as $corp) $this->add_tribe_default_channel($type, $url, $corp['ug_id']);
		}
	}
}
?>