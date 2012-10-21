<?php 
define('LDAP_SERVER', '192.168.0.69');
define('LDAP_SERVER_PORT', '389');
define('LDAP_AUTH_USER', 'administrator@ceshi.com');
define('LDAP_AUTH_PWD', 'ZAQ!2wsx');
define('LDAP_BASE_DN', 'OU=点击科技,DC=ceshi,DC=com');
define('LDAP_DN', 'OU=点击科技,DC=ceshi,DC=com');
define('LDAP_FILTER_UG', '(&(objectClass=organizationalUnit))');
define('LDAP_FILTER_USER', '(&(objectClass=organizationalPerson)(objectClass=user))');
define('LDAP_DEFAULT_FILTER_UG', '(&(objectClass=organizationalUnit))');
define('LDAP_DEFAULT_FILTER_USER', '(&(objectClass=organizationalPerson)(objectClass=user))');
?>