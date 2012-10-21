<? if(!defined('IN_OA')) exit('Access Denied'); ?>

<? include template('header'); if($status == 'error') { ?>
<table align="center" cellspacing="1" cellpadding="5">
<tr><th>无效参数或无法与MS建立连接</th></tr>
<tr><td>正确的链接格式:demo_check_passport.php?GID=1000000&passport=AAAAAA</td></tr>

</table>
<? } elseif($status == 'right_argu' ) { ?>
<form action="demo_check_passport.php?GID=<?=$GID?>&passport=<?=$passport?>" method='POST'>
<table align="center" cellspacing="1" cellpadding="5">
<tr>
<th colspan="2">登录信息为：</th>
</tr>
<tr>
<td width="40%">用户gid:</td>
<td><input type="text" name="GID" value="<?=$GID?>"/></td>
</tr>
<tr>
<td>用户passport:</td>
<td><input type="text" name="passport" value="<?=$passport?>"></td>
</tr>	
<tr align="center">
<td colspan="2">
<input type="submit" name="check_passport_submit" value="确定登录验证吗?" />
</td>
</tr>
</table>
</form>
<? } elseif($status == 'result_view') { if($code == '') { ?>
<table align="center" cellspacing="1" cellpadding="5">
<tr><td>error!</td></tr>
</table>
<? } else { ?>
<table align="center" cellspacing="1" cellpadding="5">
<tr><th align="center">返回XML信息</th></tr>
<tr><td align="center"><textarea cols="64" rows="15"><?=$data_xml_view?></textarea></td></tr>
</table>
<table align="center" cellspacing="1" cellpadding="5">
<tr align="center"><th colspan="2">返回信息</th></tr>
<tr><td>code:</td><td><?=$code?></td></tr>
<tr><td>result:</td><td><?=$result?></td></tr>
</table>
<? } } include template('footer'); ?>


