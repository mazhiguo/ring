<? if(!defined('IN_OA')) exit('Access Denied'); ?>

<? include template('header'); if($add_ug_state == 'view') { ?>
<form action="demo_add_ug.php" method="POST">
<table align="center" cellspacing="1" cellpadding="5">
<tr><th colspan="2" align="center">增加组织</th></tr>
<tr><td>组织编号(非空唯一不能修改):</td><td><input type="text" name="ug_code" value="" /></td></tr>
<tr><td>组织名称(非空可修改):</td><td><input type="text" name="ug_name" value="" /></td></tr>
<tr><td>父组织编号(非空):</td><td><input type="text" name="parent_code" value="test2" /></td></tr>
<tr><td>组织标识:</td><td><input type="text" name="sign" value="0" /></td></tr>
<tr><td>兄弟组织编号:</td><td><input type="text" name="location" value="" /></td></tr>
<tr><td colspan="2" align="center"><input type="submit" name="add_ug_submit" value="提交"/></td></tr>
</table>
</form>
<? } else { if(code=='') { ?>
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


