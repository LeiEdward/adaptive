<?php
//本檔記錄可變動之變數
$MySite='aialtest';
//session_name($MySite.date("d"));
if(!session_id()){  //若session_id()為空值，則啟動session
	session_start();
}
//定義Linux中，PEAR所在
@ini_set('include_path' , '.:/opt/lampp/htdocs/'.$MySite.'/classes/PEAR:/opt/lampp/htdocs/'.$MySite.'/classes:/opt/lampp/htdocs/'.$MySite.'/include');

@ini_set('display_errors', 'On');
@ini_set('display_startup_errors', 1);
@ini_set('log_errors', 1);
@ini_set('error_reporting','E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE & ~E_WARNING');
@ini_set('output_buffering','1');

//資料庫設定
$dbtype='mysql';
$hostspec='localhost';
$dbuser=$db_user='bnatadmin';
$dbpass=$db_user_passwd='kbcsqlcat3636';
$database=$db_dbn='kbnattest';  //資料庫名稱
//grant all privileges on kbnat.* to bnatadmin@localhost identified by 'kbcsqlcat3636';

