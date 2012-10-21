<? if(!defined('IN_OA')) exit('Access Denied'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="utf-8">
<head>
<title>demo</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<meta http-equiv="Content-Language" content="utf-8" /> 
<script>
/*function $(id) {
return getElementById(id);
}
function <?=$F?>(obj) {
return document.getElementById(obj).value;
}*/
</script>
<style>
body, ul, dl, dd, p, h1, h2, h3, h4, h5, h6, form, fieldset { margin: 0; padding: 0; };
ul{float:left}
body{font-size:9pt}
table{background:#F1F1F1;border:1px solid #555555;width:55%;margin-top:10px;}
th{background:#D4D0C8;font-weight:800;font-size:10pt;}
td{background:#fff}
input{border:1px solid #555555;size:100}
</style>
</head>
<body>
<div align="center" style="font-size:30pt;color:red;font-weight:800">demo</div>
<hr width="80%"/>



<table align="center" cellpadding="5" cellspacing="1" style="width:800px;border:none">
<tr>
<td align="center" valign="top" width="100">
<div align="center" style="font-size:10pt;color:red;font-weight:800">
<input type="button" value="基本设置" onclick="window.location='demo_setting.php'" /><br /> <br />
<input type="button" value="增加用户" onclick="window.location='demo_add_user.php'"><br /> <br />
<input type="button" value="编辑用户" onclick="window.location='demo_update_user.php'" /><br /> <br />
<input type="button" value="查询用户" onclick="window.location='demo_query_user.php'" /><br /> <br />
<input type="button" value="增加组织" onclick="window.location='demo_add_ug.php'" /><br /> <br />
<input type="button" value="编辑组织" onclick="window.location='demo_update_ug.php'" /><br /> <br />
<input type="button" value="发送I M" onclick="window.location='demo_send_im.php'"><br /> <br />
<input type="button" value="发送SMS" onclick="window.location='demo_send_sms.php'"><br /> <br />
<input type="button" value="C  T  T" onclick="window.location='demo_ctt.php'" /><br /> <br />
<input type="button" value="PASSPORT" onclick="window.location='demo_check_passport.php'" /><br /> <br />
</div>	
</td>
<td align="center" valign="top" >

