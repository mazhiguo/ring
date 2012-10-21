<? if(!defined('IN_OA')) exit('Access Denied'); ?>

<? include template('header'); ?>
<form action="demo_setting.php" method="POST">
<table align="center" cellspacing="1" cellpadding="5">
<tr>
<th colspan="2" align="center">URL设置</th>
</tr>
<tr>
<td>gkmsapi_url:</td>
<td><input type="text" name="gkmsapi" value="<?=$gkmsapi_url?>" size="40" /></td>
</tr>
<tr>
<td>ctt_url:</td>
<td><input type="text" name="ctt_url" value="<?=$ctt_url?>" size="40" /></td>
</tr>
<tr>
<td>ctt_ico:</td>
<td><input type="text" name="ctt_ico" value="<?=$ctt_ico?>" size="40" /></td>
</tr>
<tr>
<td colspan="2" align="center"> 
<input type="submit" name="setting_url" value="保存URL设置" onclick="javascript:window.confirm('请正确填写url信息，否则将导致不正常！确定吗？')" />
<input type="submit" name="setting_getuser" value="获得所有用户信息" onclick="javascript:window.confirm('将从ms取回所有用户信息，本地已保存用户信息将全部被覆盖！确定吗？'); " />
</td>
</tr>
</table>
<? include template('footer'); ?>


