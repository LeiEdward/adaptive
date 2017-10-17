<?php

<<<<<<< HEAD
	require_once 'config.php';	

// $Id: adp_API.php $
// ¥»ÀÉ¬°®Ö¤ß¨ç¦¡®w¤W¼hÀÉ¡A
// ­Y«D¥²­n¡A½Ð¥Ñ¬ÛÃö®Ö¤ß¨ç¦¡®w¥h·s¼W­×§ïºûÅ@¡C
=======
	require_once 'config.php';

// $Id: adp_API.php $
// ï¿½ï¿½ï¿½É¬ï¿½ï¿½Ö¤ß¨ç¦¡ï¿½wï¿½Wï¿½hï¿½É¡A
// ï¿½Yï¿½Dï¿½ï¿½ï¿½nï¿½Aï¿½Ð¥Ñ¬ï¿½ï¿½ï¿½ï¿½Ö¤ß¨ç¦¡ï¿½wï¿½hï¿½sï¿½Wï¿½×§ï¿½ï¿½ï¿½ï¿½@ï¿½C
>>>>>>> master

	//excel
	require_once 'read_excel.inc.php';

<<<<<<< HEAD
	// ®É¶¡¬ÛÃö

//	require_once( "sfs_core_time.php" );  

	// ¨t²Î¬É­±/themes
	require_once( "adp_core_html.php" );
	
	// ®Ö¤ß¨ç¼Æ
	require_once( "adp_core_function.php" );
	require_once( "adp_core_class.php" );
	require_once( 'read_excel.inc.php' );
	

	// ¼Ò²Õ¬ÛÃö
//	require_once( "sfs_core_module.php" );
	// ¨ú±o¾Ç®Õ¤º¬ÛÃö¸ê®Æ
//	require_once( "sfs_core_schooldata.php" );
	// ¨t²Î¿ï¶µ¤å¦r
//	require_once( "sfs_core_systext.php" );
	// ¨ú±o¸ô®|¨ç¼Æ
//	require_once( "adp_core_path.php" );
	// ¨t²Î¿ï³æ¬ÛÃö
//	require_once( "adp_core_menu.php" );
	// °O¿ýÀÉ¬ÛÃö
//	require_once( "sfs_core_log.php" );
	// °T®§¬ÛÃö
//	require_once( "sfs_core_msg.php" );
	// sql ¸ê®Æ®w¬ÛÃö
//	require_once( "sfs_core_sql.php" );
	// ª©¥»¸ê°T ¬ÛÃö
//	require_once( "sfs_core_version.php" );

/*
	// ¨t²ÎÀô¹ÒÅÜ¼Æ
=======
	// ï¿½É¶ï¿½ï¿½ï¿½ï¿½ï¿½

//	require_once( "sfs_core_time.php" );

	// ï¿½tï¿½Î¬É­ï¿½/themes
	require_once( "adp_core_html.php" );

	// ï¿½Ö¤ß¨ï¿½ï¿½ï¿½
	require_once( "adp_core_function.php" );
	require_once( "adp_core_class.php" );
	require_once( 'read_excel.inc.php' );


	// ï¿½Ò²Õ¬ï¿½ï¿½ï¿½
//	require_once( "sfs_core_module.php" );
	// ï¿½ï¿½ï¿½oï¿½Ç®Õ¤ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½
//	require_once( "sfs_core_schooldata.php" );
	// ï¿½tï¿½Î¿ï¶µï¿½ï¿½ï¿½r
//	require_once( "sfs_core_systext.php" );
	// ï¿½ï¿½ï¿½oï¿½ï¿½ï¿½|ï¿½ï¿½ï¿½ï¿½
//	require_once( "adp_core_path.php" );
	// ï¿½tï¿½Î¿ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½
//	require_once( "adp_core_menu.php" );
	// ï¿½Oï¿½ï¿½ï¿½É¬ï¿½ï¿½ï¿½
//	require_once( "sfs_core_log.php" );
	// ï¿½Tï¿½ï¿½ï¿½ï¿½ï¿½ï¿½
//	require_once( "sfs_core_msg.php" );
	// sql ï¿½ï¿½ï¿½Æ®wï¿½ï¿½ï¿½ï¿½
//	require_once( "sfs_core_sql.php" );
	// ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½T ï¿½ï¿½ï¿½ï¿½
//	require_once( "sfs_core_version.php" );

/*
	// ï¿½tï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½Ü¼ï¿½
>>>>>>> master

	$HTTP_REFERER = $_SERVER['HTTP_REFERER'];
	$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
//	$THEME_FILE = "$SFS_PATH/themes/$SFS_THEME/$SFS_THEME";
//	$THEME_URL = "$SFS_PATH_HTML"."themes/$SFS_THEME";
<<<<<<< HEAD
	

	// ©w¸q¿ù»~«¬ºA
=======


	// ï¿½wï¿½qï¿½ï¿½ï¿½~ï¿½ï¿½ï¿½A
>>>>>>> master
	define("FATAL", E_USER_ERROR);
	define("ERROR", E_USER_WARNING);
	define("WARNING", E_USER_NOTICE);

	// set the error reporting level for this script
	error_reporting(FATAL | ERROR | WARNING);

<<<<<<< HEAD
	
	// ¸ü¤J¬É­±¼Ò²Õ
	sfs_themes();

	//®É¶¡³]©w¤Î¨ç¼Æ
	set_now_niceDate();

	// ÀË¬d register_globals ¬O§_¥´¶}
	//check_phpini_register_globals();
	
	//³]©wsmartyª«¥ó
=======

	// ï¿½ï¿½ï¿½Jï¿½É­ï¿½ï¿½Ò²ï¿½
	sfs_themes();

	//ï¿½É¶ï¿½ï¿½]ï¿½wï¿½Î¨ï¿½ï¿½ï¿½
	set_now_niceDate();

	// ï¿½Ë¬d register_globals ï¿½Oï¿½_ï¿½ï¿½ï¿½}
	//check_phpini_register_globals();

	//ï¿½]ï¿½wsmartyï¿½ï¿½ï¿½ï¿½
>>>>>>> master
	require_once ("libs/Smarty.class.php");
	$smarty = new Smarty;
	$smarty->compile_check = true;
	$smarty->debugging = false;
	set_upload_path("templates_c");
	$smarty->compile_dir=$UPLOAD_PATH."templates_c";

<<<<<<< HEAD
	//©w¸qsmarty¨Ï¥Îªºtag
	$smarty->left_delimiter="{{";
	$smarty->right_delimiter="}}";

	//¥Ø«eªºµ{¦¡¦W
=======
	//ï¿½wï¿½qsmartyï¿½Ï¥Îªï¿½tag
	$smarty->left_delimiter="{{";
	$smarty->right_delimiter="}}";

	//ï¿½Ø«eï¿½ï¿½ï¿½{ï¿½ï¿½ï¿½W
>>>>>>> master
	$scripts=explode("/",$_SERVER['PHP_SELF']);
	$smarty->assign("CURR_SCRIPT",array_pop($scripts));
*/
?>
