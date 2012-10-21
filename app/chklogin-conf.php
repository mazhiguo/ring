<?php
//
// 登陆不需要检查的controller中的method
//
$no_checked_arr = array (
	'AdminController:on_login',
	'AdminController:check_login_account',
	'LdapController:auth',
	'LdapController:validate_user',
	'UserController:dn',
	'ChkonlineController:index',
	'ChkonlineController:dn',
	'ChkonlineController:account',
	'ApiController:test',
	'ApiController:gkmsapitest',
	'ApiController:gkmsapi',
	'LicenseController:autoapplygid',
	'LdapController:task_sync',
	'WebcontactsController:on_index',
	'WebcontactsController:on_search',
	'WebcontactsController:on_find_userinfo_by_id',
	'WebcontactsController:printUserUgTreeEx',
	'PrewarningController:monapi',
	'CttController:index',
	'UserController:dn',
	'UserController:gid',
);
?>