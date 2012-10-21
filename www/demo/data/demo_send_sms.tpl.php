<? if(!defined('IN_OA')) exit('Access Denied'); ?>

<? include template('header'); ?>
<script>
function changeSelect(selected, unselected) {
var selected = document.getElementById(selected);
var unselected = document.getElementById(unselected);
selected.style.display='';
unselected.disabled = true;
selected.disabled = false;
unselected.style.display='none'; 
}
</script>
<? if($send_sms_state =='view') { ?>
<form action="demo_send_sms.php" method="POST">
<table align="center" cellspacing="1" cellpadding="5">
<tr><th colspan="2">发送SMS消息</th></tr>
<tr>
<td width="30%">发送人:</td>
<td align="left">
<input type="radio" id="radio1" name="radio1" value="account" onclick="changeSelect('select1', 'select2')" style="border:none;"/>：account &nbsp;&nbsp;
<input type="radio" id="radio2" name="radio1" value="gid" onclick="changeSelect('select2', 'select1')" style="border:none" />：gid &nbsp;&nbsp;
<select id="select1" name="select1">
<? if(is_array($user_info)) { foreach($user_info as $v) { ?>
<option value=<?=$v['account']?>><?=$v['account']?></option>
<? } } ?>
</select>
<select id="select2" name="select1" style="display: none">
<? if(is_array($user_info)) { foreach($user_info as $v) { ?>
<option value=<?=$v['gid']?>><?=$v['gid']?></option>
<? } } ?>
</select>
</td>
</tr>
<tr>
<td width="30%">接收人:</td>
<td align="left">
<input type="radio" id="radio3" name="radio3" value="account" onclick="changeSelect('select3', 'select4')" style="border:none;"/>：account &nbsp;&nbsp;
<input type="radio" id="radio4" name="radio3" value="gid"  onclick="changeSelect('select4', 'select3')" style="border:none" />：gid &nbsp;&nbsp;
<select id="select3" name="select3">
<? if(is_array($user_info)) { foreach($user_info as $v) { ?>
<option value=<?=$v['account']?>><?=$v['account']?></option>
<? } } ?>
</select>
<select id="select4" name="select3" style="display: none">
<? if(is_array($user_info)) { foreach($user_info as $v) { ?>
<option value=<?=$v['gid']?>><?=$v['gid']?></option>
<? } } ?>
</select>
</td>
</tr>
<tr>
<td>
手机:
</td>
<td align="left">
<input type="text" name="mobile_phone" />
</td>
</tr>
<tr>
<td colspan="2" align="left">
内容:
</td>
</tr>
<tr>
<td colspan="2" align="center">
<textarea cols="70" rows="10" name="sms_content"></textarea>
</td>
</tr>
<tr><td colspan="2" align="center"><input type="submit" name="send_sms_submit" value="提交"/></td></tr>
</table>
</form>
<? } else { if($code=='') { ?>
<table align="center" cellspacing="1" cellpadding="5">
<tr><td>无法连接至服务器或XML解析出错!</td></tr>
</table>
<? } else { ?>
<table align="center" cellspacing="1" cellpadding="5">
<tr><th align="center">发送XML信息</th></tr>
<tr><td align="center"><textarea cols="64" rows="15"><?=$xml_view?></textarea></td></tr>
</table>
<table align="center" cellspacing="1" cellpadding="5">
<tr><th align="center">返回XML信息</th></tr>
<tr><td align="center"><textarea cols="64" rows="15"><?=$data_xml_view?></textarea></td></tr>
</table>
<? } } include template('footer'); ?>


