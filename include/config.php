<?php
require_once 'config_db.php';  //不可移除

//@ini_set('allow_call_time_pass_reference','On');
date_default_timezone_set('Asia/Taipei');//將時區設為台北標準時間

//定義系統目錄
define('_ADP_PATH' , dirname($_SERVER['SCRIPT_FILENAME'])."/");
define('_ADP_URL' , 'http://'.$_SERVER["SERVER_ADDR"].'/'.$MySite.'/');
define('_WEB_TITLE' , '因材網');
define('_SPLIT_SYMBOL' , '@XX@');
define('_SPLIT_SYMBOL2' , '*%%*');
define('_TEST_ACCOUNT' , 'bnattest');
define('_FRONT_PAGE' , 'index_AIAL2.php');
//認證信
define('_MAIL_ACC', 'ntcujems506');
define('_MAIL_PW', 'ntcu!@#$%kbc');
//define("P_HOME", dirname($_SERVER['SCRIPT_FILENAME']).'/topiclibrary/');
define("P_HOME", dirname($_SERVER['SCRIPT_FILENAME']).'/topiclibrary/'); //007 調整

//班級人數
// define('_MAX_CLASS_S_NUM', 36);  //每班最大學生數
define('_MAX_CLASS_T_NUM', 6);   //每班最大教師數
define('_MAX_CLASS_S_NUM', 'e'._SPLIT_SYMBOL2.'32'._SPLIT_SYMBOL.'j'._SPLIT_SYMBOL2.'36'._SPLIT_SYMBOL.'s'._SPLIT_SYMBOL2.'40'._SPLIT_SYMBOL.'u'._SPLIT_SYMBOL2.'200');//每班最大學生數(e國小,j國中,s高中,u大學)
// define('_MAX_CLASS_S_NUM', 'e:32,j:36,s:40,u:60');//每班最大學生數(e國小,j國中,s高中,u大學)

//密碼長度
define('_MAX_PASSWORD_LENGTH', 15);  //最長密碼字元數
define('_MIN_PASSWORD_LENGTH', 6);   //最短密碼字元數


//系統維護時間
define('_MAINTAIN_TIME_START', '23:59');  //開始時間
define('_MAINTAIN_TIME_STOP', '00:00');  //結束時間
$SiteConfig['setMaintainTime']=1;  //啟用系統維護時間

//學生作品
define('_WORK_PATH' , _ADP_PATH.'data/work/');
define('_WORK_URL' , _ADP_URL.'data/work/');

//上傳檔案目錄
define('_UPLOAD_PATH' , "data/");
define('_ADP_UPLOAD_PATH' , _ADP_PATH."data/");

//上傳影片目錄
define('_ADP_VIDEO_PATH' , "../../data/video_data");

//上傳截圖目錄
define('_ADP_fb_PATH' , _ADP_URL."data/topUPFILE/");

//預設上傳結構概念矩陣檔及試題之目錄
define('_CS_UPLOAD_PATH' , _UPLOAD_PATH."CS_db/");
define('_ADP_CS_UPLOAD_PATH' , _ADP_UPLOAD_PATH."CS_db/");

//預設上傳檔案暫存目錄
define('_ADP_TMP_UPLOAD_PATH' , _ADP_UPLOAD_PATH."tmp/");

//預設題庫網址
//define('_ADP_EXAM_DB_PATH' , _ADP_URL."data/CS_db/");
define('_ADP_EXAM_DB_PATH' , "data/CS_db/");

//模組預設 templates_dir
define("_TEMPLATE_DIR", dirname($_SERVER['SCRIPT_FILENAME'])."/templates/");

//布景主題 theme
define("_THEME", "themes/bnat/");
define("_THEME_CSS", _ADP_URL._THEME."css/front.css");
define("_THEME_IMG", _ADP_URL._THEME."img/");

//系統版本
define("_SYS_VER", "asia");

// 檔案大小
define('FILESIZE_KB', 1024);
define('FILESIZE_MB', 1048576);
define('FILESIZE_GB', 1073741824);
define('FILESIZE_TB', 1099511627776);

//整個網站使用之暫存檔
define('_SITECONFIG_PATH' , _UPLOAD_PATH."SiteConfig/");
$SiteFile['SchoolHierselect']=_SITECONFIG_PATH.'SchoolHierselect.txt';  //階層式"已啟用學校列表HTML"
//$SiteFile['AAA']='AAA.txt';

//身份認證的資料表
$auth_table='user_info';
//消息內容資料表
//$news_table='news';
//登入後允許的閒置時間(秒)
define('_IDLETIME' , '6000');
$idletime = 6000;
//登入後cookie的存活時間(秒)
$expire = 60000;


//PDO 連接方式
$config['db']['dsn']="mysql:host=".$dbhost.";dbname=".$db_dbn.";charset=utf8";
$config['db']['user'] = $dbuser;
$config['db']['password'] = $dbpass;

//$dbconnect = "mysql:host=".$dbhost.";dbname=".$db_dbn;

$dbh = new PDO($config['db']['dsn'],
				$config['db']['user'],
				$config['db']['password'],
				array(
					PDO::ATTR_EMULATE_PREPARES => false,
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
				)
		);

if (!$dbh){
	die('config 無法連資料庫');
}
