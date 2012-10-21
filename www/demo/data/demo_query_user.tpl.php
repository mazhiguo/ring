<? if(!defined('IN_OA')) exit('Access Denied'); ?>

<? include template('header'); if($query_user_state) { ?>
<form action="demo_query_user.php" method="POST">
<table align="center" cellspacing="1" cellpadding="5">
<tr><th colspan="2" align="center">用户查询</th></tr>
<tr align="center">
<td>
account:
</td>
<td>
<select name="user_account">
<? if(is_array($user_info)) { foreach($user_info as $v) { ?>
<option value=<?=$v['account']?>><?=$v['account']?></option>
<? } } ?>
</select>
</td>
</tr>
<tr align="center">
<td colspan="2">
<input type="submit" name="query_user_submit" value="提交" />
</td>
</tr>
</table>
</form>
<? } else { if($code=='') { ?>
<table align="center" cellspacing="1" cellpadding="5">
<tr><td>error!</td></tr>
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


