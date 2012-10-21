<? if(!defined('IN_OA')) exit('Access Denied'); ?>

<? include template('header'); if($update_user_state) { ?>
<form action="demo_update_user.php" method="POST">
<table align="center" cellspacing="1" cellpadding="5">
<tr><th colspan="2">修改用户</th></tr>
<tr>
<td>帐户(非空唯一不能修改):</td>
<td>
<select name="user_account">
<? if(is_array($user_info)) { foreach($user_info as $v) { ?>
<option value=<?=$v['account']?>><?=$v['account']?></option>
<? } } ?>
</select>
</td>
</tr>
<tr><td>用户名(非空可修改):</td><td><input type="text" name="user_name" value="" /></td></tr>
<tr><td>显示名称(非空可修改):</td><td><input type="text" name="display_name" value="" /></td></tr>
<tr><td>密码(非空可修改):</td><td><input type="password" name="user_pw" value="" /></td></tr>
<tr><td colspan="2" align="center"><input type="submit" name="update_user_submit" value="提交"/></td></tr>
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


