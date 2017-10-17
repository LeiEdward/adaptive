<?php

	require_once 'config.php';	

// $Id: adp_API.php $
// 本檔為核心函式庫上層檔，
// 若非必要，請由相關核心函式庫去新增修改維護。

	//excel
	require_once 'read_excel.inc.php';

	// 時間相關

//	require_once( "sfs_core_time.php" );  

	// 系統界面/themes
	require_once( "adp_core_html.php" );
	
	// 核心函數
	require_once( "adp_core_function.php" );
	require_once( "adp_core_class.php" );
	require_once( 'read_excel.inc.php' );
	

	// 模組相關
//	require_once( "sfs_core_module.php" );
	// 取得學校內相關資料
//	require_once( "sfs_core_schooldata.php" );
	// 系統選項文字
//	require_once( "sfs_core_systext.php" );
	// 取得路徑函數
//	require_once( "adp_core_path.php" );
	// 系統選單相關
//	require_once( "adp_core_menu.php" );
	// 記錄檔相關
//	require_once( "sfs_core_log.php" );
	// 訊息相關
//	require_once( "sfs_core_msg.php" );
	// sql 資料庫相關
//	require_once( "sfs_core_sql.php" );
	// 版本資訊 相關
//	require_once( "sfs_core_version.php" );

/*
	// 系統環境變數

	$HTTP_REFERER = $_SERVER['HTTP_REFERER'];
	$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
//	$THEME_FILE = "$SFS_PATH/themes/$SFS_THEME/$SFS_THEME";
//	$THEME_URL = "$SFS_PATH_HTML"."themes/$SFS_THEME";
	

	// 定義錯誤型態
	define("FATAL", E_USER_ERROR);
	define("ERROR", E_USER_WARNING);
	define("WARNING", E_USER_NOTICE);

	// set the error reporting level for this script
	error_reporting(FATAL | ERROR | WARNING);

	
	// 載入界面模組
	sfs_themes();

	//時間設定及函數
	set_now_niceDate();

	// 檢查 register_globals 是否打開
	//check_phpini_register_globals();
	
	//設定smarty物件
	require_once ("libs/Smarty.class.php");
	$smarty = new Smarty;
	$smarty->compile_check = true;
	$smarty->debugging = false;
	set_upload_path("templates_c");
	$smarty->compile_dir=$UPLOAD_PATH."templates_c";

	//定義smarty使用的tag
	$smarty->left_delimiter="{{";
	$smarty->right_delimiter="}}";

	//目前的程式名
	$scripts=explode("/",$_SERVER['PHP_SELF']);
	$smarty->assign("CURR_SCRIPT",array_pop($scripts));
*/
?>
