<? if(!defined('IN_OA')) exit('Access Denied'); ?>

<? include template('header'); if($update_ug_state) { ?>
<form action="demo_update_ug.php" method="POST">
<table align="center" cellspacing="1" cellpadding="5">
<tr><th colspan="2" align="center">修改组织</th></tr>
<tr>
<td>组织编号(非空唯一不能修改):</td>
<td>
<select name="ug_code">
<? if(is_array($ug_info)) { foreach($ug_info as $v) { ?>
<option value=<?=$v['code']?>><?=$v['code']?></option>
<? } } ?>
</select>
</td>
</tr>
<tr><td>组织名称(非空可修改):</td><td><input type="text" name="ug_name" value="" /></td></tr>
<tr><td>父组织编号(非空可修改):</td><td><input type="text" name="ug_parrent_code" value="" /></td></tr>
<tr><td>组织标识(非空可修改):</td><td><input type="text" name="ug_sign" value="" /></td></tr>
<tr><td>兄弟组织编号(非空可修改):</td><td><input type="text" name="ug_location" value="" /></td></tr>
<tr><td colspan="2" align="center"><input type="submit" name="update_ug_submit" value="提交"/></td></tr>
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


