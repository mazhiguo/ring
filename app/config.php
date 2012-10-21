<?php
// --------- db setting
define('DB_TYPE', 'mysql');
define('DB_NAME', 'coldb');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8');

// --------- template engine setting
define('SMARTY_TMPDIR', WWWPATH.'templates/');
define('SMARTY_TMPDIRC', APPPATH.'data/tplc/');
define('SMARTY_CACHEDIR', APPPATH.'data/cache-www/');
define('SMARTY_DLEFT', '{/');
define('SMARTY_DRIGHT', '/}');

// ---------- ms setting
define('MS_SERVER', '127.0.0.1');
define('MS_SERVER_PORT', 10020);
define('MS_MONITOR', '127.0.0.1');
define('MS_MONITOR_PORT', 8899);

// ------------ ms web port
define('MS_WEB_PORT', 8900);
// ----------- apply license ip&port
define('APPLY_LICENSE_IP', 'gke.dianji.com');
define('APPLY_LICENSE_PORT', 80);

//瀹氫箟铏氭嫙浜虹殑鏍囪瘑 鍦╱serinfo琛╯tate=2
define('FILTER_DUMMY_STATE', 2);

//瀹氫箟鍗忎綔鍖烘湇鍔″櫒鍦板潃鍜岀鍙?
//define('TRIBE_SERVER', '172.16.0.243');
//define('TRIBE_PORT', '8085');
?>