<?php
require_once "auth_chk.php";
require_once "include/security_function.php";

if( !isset($auth) || !$auth->checkAuth()){
	if(!$auth->checkAuth()){
		//debug_msg("第".__LINE__."行 auth ", $auth);
		$auth->logout();
		//die();
	}
	//unset($dbh);   //資料庫離線
	//session_destroy();
	//Header("Location: "._FRONT_PAGE);

	$_SESSION['out']['msg']='認證已到期，請重新登入！';
	$_SESSION['out']['next_page']='logout_msg.php';
	Header("Location: ".$_SESSION['out']['next_page']);
	die();
}

if(!isset($_SESSION['user_data'])){
	$_SESSION['user_data']=$user_data;
}
//debug_msg("第".__LINE__."行 user_data ", $user_data);


//防非法存取，$token由 modules.php 設定
$_SESSION['token']=$token = md5(uniqid(rand(), true));

//針對輸入值做檢查
array_walk_recursive($_POST, 'str_security');
array_walk_recursive($_GET, 'str_security');
array_walk_recursive($_REQUEST, 'str_security');

//登出
if ($_GET['act']=='logout' && $auth->checkAuth()) {
	$logouttime=date("Y-m-d, H:i:s");
	//記錄登出時間，計算總停留時間，待改
	//$sql="UPDATE user_status SET stoptimestamp='{$logouttime}' WHERE user_id ='{$_SESSION['_authsession']['username']}'";
	//$result = $dbh->query($sql);
	//debug_msg("第".__LINE__."行 auth ", $auth);
	//die();
	$auth->logout();
	unset($dbh);   //資料庫離線
	session_destroy();
	Header("Location: "._FRONT_PAGE);
    die();
}

$mem_usage=null;

$search_count2 = $dbh->prepare("SELECT count(*) num FROM `message_master` a  WHERE a.touser_id =:user_id and read_mk =:read_mk");
$search_count2->bindValue(':user_id', $user_id, PDO::PARAM_STR);
$search_count2->bindValue(':read_mk', '0', PDO::PARAM_INT);
$search_count2->execute();
$row_count_unread = $search_count2->fetch();
$_SESSION['msg_count'] = $row_count_unread["num"];
if( $_GET[file]=='viewErrorsBN' OR $_REQUEST[screen]=='all' OR $_REQUEST[scr]=='all'){
	;
}else if ($_REQUEST[screen] == 'frame'){
	require_once "head_iframe.php";
}else{
	require_once "head_new.php";
}

echo '<div id="content" class="content-Box">';
//my_filter 在 "include/security_function.php";
$op= isset($_REQUEST['op']) ? my_filter($_REQUEST['op'], "string") : '';
$my_code_path= isset($_REQUEST['name']) ? my_filter($_REQUEST['name'], "string") : '';
$my_code_filename= isset($_REQUEST['file']) ? my_filter($_REQUEST['file'], "string") : '';
user_historyadd($_SERVER['QUERY_STRING'], $my_code_filename, $user_data);

if($op=='modload'){
	if (ereg("\.\.",$name) || ereg("\.\.",$file)) {
		echo "You are so cool";
		die();
	} else {
		$exec_file="modules/".$my_code_path."/".$my_code_filename.".php";
		if(file_exists($exec_file)){
			include($exec_file);
		}else{
			echo "請不要任意輸入網址！！";
		}
	}
}elseif($op=='main'){
	$_SESSION['user_id']=$user_data->user_id;
	//$_SESSION['password']=$user_data->viewpass;
	//學生年級
	$cps_user='cpsedu';
	$cps_pass='cpsedu';
	$autott_user='autotutor';
	$autott_pass='0000';
	$rand_seed=rand(1,9);
	$transfer=create_password($rand_seed);
	$split_str=$rand_seed.'-'.$transfer.$rand_seed.'-';
	$cps_encode=base64_encode($rand_seed.$cps_user.$split_str.$cps_pass.$transfer);
	$rand_seed=rand(1,9);
	$transfer=create_password($rand_seed);
	$split_str=$rand_seed.'-'.$transfer.$rand_seed.'-';
	$autott_encode=base64_encode($rand_seed.$autott_user.$split_str.$autott_pass.$transfer);
	$autott_url="http://210.240.189.1/mathITS/index.php?aa='.$autott_encode.'";
	$CR_url = "modules.php?op=modload&name=assignCR&file=assignCR_list&subject=2";
?>
<script src="scripts/sweetalert2/sweetalert2.min.js"></script>
<link rel="stylesheet" href="scripts/sweetalert2/sweetalert2.min.css">
<?php
if($user_data->access_level < 10){
?>
<script type="text/javascript">
var url = 'modules.php?op=modload&';
swal({
	title: '關於我',
	html: '<div ><img src="images/role/head/head01.png"  style="float:left;width:40%;height:40%;vertical-align:middle;">'+
		'<div class="class-root-box" >我想開始挑戰新的任務  <a class="btn07" href="'+ url +'name=assignMission&file=mission_action">GO！</a></div>'+
		'<div class="class-root-box" >回到挑戰上一次的任務  <a href="'+ url +'name=indicateTest&file=indicateAdaptiveTest&mission_sn=2766" class="btn07">GO！</a></div>'+
		'<div class="class-root-box" >同學有問題，我來回答   <a href="'+ url +'name=learn_video&file=video_askquestion#parentHorizontalTab4" class="btn07">GO！</a></div>'+
		'</div>',
	showCloseButton: true,
	showConfirmButton: false
});

$(function() {
	// 171012，即時訊息移動至class-rank，首頁留連結框。
    //$.unblockUI();
    $.blockUI({
            theme: true,
            title: '<a href="modules.php?op=modload&name=role&file=role_class_rank"><p id="role_head">查看同學動態GO!</p></a>',
            //draggable: true,
            message: ' ', //設定要顯示的div內容
            fadeIn: 700,
            fadeOut: 700,
            // timeout: 2000,  //設定顯示時間，這邊註解掉
            showOverlay: false, //是否讓主畫面變暗
            centerY: false,
            themedCSS: {
                width: '200px',
                top: '18%',
                // bottom: '10px',
                left: '65%',
                // right: '5%', //讓視窗靠左下，因此不設定右邊數值
                border: 'none',
                padding: '5px',
                backgroundColor: '#AFADAD',
                '-webkit-border-radius': '10px',
                '-moz-border-radius': '10px',
                opacity: .8,
                color: 'black',
                cursor: 'default' //原本預設為wait(一直轉圈圈)，改為default。
            }

     });
});

</script>
<?php }elseif ($user_data->access_level==21){?>
<script type="text/javascript">
var url = 'modules.php?op=modload&';
swal({
	title: '選擇要進行的教學模式',
	html: '<div ><img src="images/role/head/head01.png"  style="float:left;width:30%;height:30%;vertical-align:middle;">'+
		'<div class="class-root-box" ><a href="'+ url +'name=assignMission&file=assignment2" class="btn07">翻轉教室 <i class="fa fa-arrow-right"></i></a></div>'+
		'<div class="class-root-box" ><a href="'+ url +'name=assignMission&file=assignment3" class="btn07" >適性診斷與補救教學(縱貫) <i class="fa fa-arrow-right"></i></a></div>'+
		'<div class="class-root-box" ><a href="'+ url +'name=assignMission&file=assignment" class="btn07" >適性診斷與補救教學(單元) <i class="fa fa-arrow-right"></i></a></div>'+
		'</div>',
	showCloseButton: true,
	showConfirmButton: false
});
</script>
<?php }?>
<?php
	echo '<div class="content2-Box">
    	<div class="path">目前位置：<a href="modules.php?op=main">首頁</a></div>
		<div style="text-align:center; font-size:24px; color:#1c1c1c; padding-bottom:30px; line-height:150%;">';
	echo '<b>歡迎光臨！您是 '.$user_data->uname.'，身分：'.$user_data->user_level.'。</b>';
	echo '</div>';
	$map_new_url = "'modules/D3/app/index_view.php?aa=".base64_encode($_SESSION['user_id'])."'";
	if($user_data->access_level < 10 || $user_data->access_level==21){  //暫定等級30以下使用此畫面
		echo '
		<ul class="idx-menu">
       		<li ><a><div class="idx-menu-data" >知識結構學習<br><div class="btn01" id="menu" style="cursor:pointer;">進入查看</div></div></a></li>
       		<li><a href="modules.php?op=modload&name=BayesianTest&file=index"><div class="idx-menu-data">智慧適性診斷<br><div class="btn01">進入查看</div></div></a></li>
       		<li><a href="'.$CR_url.'"><div class="idx-menu-data">互動式學習<br><div class="btn01">進入查看</div></div></a></li>
       		<li><a href="http://210.240.189.1/CPS/login.php?aa='.$cps_encode.'&stuid='.$_SESSION['user_id'].'" target="_blank" ><div class="idx-menu-data">PISA合作問題解決能力<br><div class="btn01">進入查看</div></div></a></li>
       </ul>
    </div>';
    	echo '<div id="dialog-message" title="科目選單" style="display:none;">
				<div>
					<span style="margin-right: 90px;">數學</span>
					<button id="math" class="ui-button ui-corner-all ui-widget" style=" width: 85px; height: 35px; font-size:15pt; padding-top: 4px">前往</button>
				</div>
				<div>
					<span style="margin-right: 90px;">國語</span>
					<button id="chinese" class="ui-button ui-corner-all ui-widget" style=" width: 85px; height: 35px; font-size:15pt; padding-top: 4px">前往</button>
				</div>
				<div>
					<span style="margin-right: 90px;">自然</span>
					<button id="science" class="ui-button ui-corner-all ui-widget" style=" width: 85px; height: 35px; font-size:15pt; padding-top: 4px">前往</button>
				</div>
			</div>';

	}

?>
<script>
	  $("#menu").click(function(){
		 $("#dialog-message").dialog({
			 modal: true,
			 width: 320
		 });
		//   $("#dialog-message").css("height","170px");
		//   $("#dialog-message").css("width","340px");
		//  $("div[role='dialog']").css("top","260px");
	  })
    $("#math").click(function(){
        location.href= "modules.php?op=modload&name=D3&file=chart&subject="+3;
    })
    $("#science").click(function(){
        location.href= "modules.php?op=modload&name=D3&file=chart&subject="+4;
    })

    $("#chinese").click(function(){
        location.href= "modules.php?op=modload&name=D3&file=chart&subject="+10;
    })
</script>
<style>
 #endExam{
 	background: url(images/stop-btn.png) no-repeat;
    width: 180px;
    height: 63px;
    position: absolute;
    bottom: 19px;
    right: 227px;
    border-width: 0px;
 }
</style>

<?php
}else{
	//debug_msg("第".__LINE__."行 _REQUEST ", $_REQUEST);
	die ("抱歉！您的權限不符");
}
$mem_usage=echo_memory_usage();
//debug_msg("第".__LINE__."行 _REQUEST ", $_REQUEST);
if( $_GET['file']=='viewErrorsBN' OR $_REQUEST['screen']=='all'){
	;
}elseif($_REQUEST['screen'] == 'frame'){
	;
}else{
	//修改by 彥鈞 105.10.2
	echo '<div class="bottom-pto"><img src="images/content-bg.png" title="'.$mem_usage.'"></div>';
	echo '</div>';
	require_once "feet_new.php";
}

//隨機取得字串
function create_password($pw_length ='6'){
	$randpwd = '';
	for ($i = 0; $i < $pw_length; $i++)	{
		$randpwd .= chr(mt_rand(33, 126));
	}
	return $randpwd;
}


function user_historyadd($server, $filename, $user_data){
	global $dbh;
	$time = date ("YmdHis");
	if($filename !='') $qs_str = explode($filename, $server);
	if($qs_str[1] != '') $action = $qs_str[1];
	else $action = 'none';

	if($server == 'op=main') $pro_no ='A001';
	else{

		$search = $dbh->prepare("SELECT pro_no FROM program_info WHERE pro_path LIKE :filename");
		$search->bindValue(':filename', '%file='.$filename, PDO::PARAM_STR);
		$search->execute();
		$row_data = $search->fetch();
		$pro_no = $row_data["pro_no"];
	}
	if($pro_no !=''){
		$result = $dbh->prepare("INSERT INTO user_history (date, user_id, action, pro_no, type, organization_id)
			VALUES (:adddate, :user_id, :action, :pro_no, :pro_type, :org_id)");

		$result->bindValue(':adddate', $time, PDO::PARAM_STR);
		$result->bindValue(':user_id', $user_data->user_id, PDO::PARAM_STR);
		$result->bindValue(':action', $action, PDO::PARAM_INT);
		$result->bindValue(':pro_no', $pro_no, PDO::PARAM_STR);
		$result->bindValue(':pro_type', 'login', PDO::PARAM_STR);
		$result->bindValue(':org_id', $user_data->organization_id, PDO::PARAM_STR);
		$result->execute();
	}

}
