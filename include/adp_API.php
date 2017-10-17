<?php

	require_once 'config.php';

// $Id: adp_API.php $
// ���ɬ��֤ߨ禡�w�W�h�ɡA
// �Y�D���n�A�ХѬ����֤ߨ禡�w�h�s�W�ק����@�C

	//excel
	require_once 'read_excel.inc.php';

	// �ɶ�����

//	require_once( "sfs_core_time.php" );

	// �t�άɭ�/themes
	require_once( "adp_core_html.php" );

	// �֤ߨ���
	require_once( "adp_core_function.php" );
	require_once( "adp_core_class.php" );
	require_once( 'read_excel.inc.php' );


	// �Ҳլ���
//	require_once( "sfs_core_module.php" );
	// ���o�Ǯդ���������
//	require_once( "sfs_core_schooldata.php" );
	// �t�οﶵ���r
//	require_once( "sfs_core_systext.php" );
	// ���o���|����
//	require_once( "adp_core_path.php" );
	// �t�ο�������
//	require_once( "adp_core_menu.php" );
	// �O���ɬ���
//	require_once( "sfs_core_log.php" );
	// �T������
//	require_once( "sfs_core_msg.php" );
	// sql ���Ʈw����
//	require_once( "sfs_core_sql.php" );
	// �������T ����
//	require_once( "sfs_core_version.php" );

/*
	// �t�������ܼ�

	$HTTP_REFERER = $_SERVER['HTTP_REFERER'];
	$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
//	$THEME_FILE = "$SFS_PATH/themes/$SFS_THEME/$SFS_THEME";
//	$THEME_URL = "$SFS_PATH_HTML"."themes/$SFS_THEME";


	// �w�q���~���A
	define("FATAL", E_USER_ERROR);
	define("ERROR", E_USER_WARNING);
	define("WARNING", E_USER_NOTICE);

	// set the error reporting level for this script
	error_reporting(FATAL | ERROR | WARNING);


	// ���J�ɭ��Ҳ�
	sfs_themes();

	//�ɶ��]�w�Ψ���
	set_now_niceDate();

	// �ˬd register_globals �O�_���}
	//check_phpini_register_globals();

	//�]�wsmarty����
	require_once ("libs/Smarty.class.php");
	$smarty = new Smarty;
	$smarty->compile_check = true;
	$smarty->debugging = false;
	set_upload_path("templates_c");
	$smarty->compile_dir=$UPLOAD_PATH."templates_c";

	//�w�qsmarty�ϥΪ�tag
	$smarty->left_delimiter="{{";
	$smarty->right_delimiter="}}";

	//�ثe���{���W
	$scripts=explode("/",$_SERVER['PHP_SELF']);
	$smarty->assign("CURR_SCRIPT",array_pop($scripts));
*/
?>
