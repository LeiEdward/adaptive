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

$search_count2 = $dbh->prepare("SELECT count(*) num FROM `message_master` a  WHERE a.touser_id =:user_id and read_mk =:read_mk and delete_flag = '0'");
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

if($op=='modload'){
	if (ereg("\.\.",$name) || ereg("\.\.",$file)) {
		echo "You are so cool";
		die();
	} else {
		$exec_file="modules/".$my_code_path."/".$my_code_filename.".php";
		if(file_exists($exec_file)){
			//檢查程式執行時間
			$code_time_start = microtime(true);
			//執行程式
			include($exec_file);
			$code_time_end = microtime(true);
			$code_spent_time = $code_time_end - $code_time_start;
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
		title: '送給同學一個點數',
		html: '<div style="text-align:left;"><img src="images/role/Girl04.png"  style="float:left;width:40%;height:40%;vertical-align:middle;">'+
			'1.只要看完影片、答對題目可以拿到點數！(點數的計算:第一次為4點，第二次後獎勵折半計算)<br>'+
			'<img src="images/role/coin2.png"><img src="images/role/coin2.png"><img src="images/role/coin2.png"><img src="images/role/coin2.png"><br>'+
			'2.你可以在同學回答自己問題的地方，送同學點數(點數的計算:1點)！<br>'+
			'<img src="images/role/coin2.png"><br>'+
			'3.可以在同學動態中看到同學們的最新挑戰、點數、老師贈送的鼓勵點數'+
			'</div>',
		showCloseButton: false,
		showConfirmButton: true
	});
	</script>
<?php }elseif ($user_data->access_level==21){?>
	<script type="text/javascript">
	var url = 'modules.php?op=modload&';
	swal({
		title: '獎勵制度公告',
		html: '<div style="text-align:left;"><img src="images/role/Man.png"  style="float:left;width:40%;height:40%;vertical-align:middle;">'+
			'1.系統會依同學的學習類型自動給予點數，例如：觀看完影片、答對題目等，點數的計算為第一次為4點，第二次後獎勵折半計算<br>'+
			'<img src="images/role/coin2.png"><img src="images/role/coin2.png"><img src="images/role/coin2.png"><img src="images/role/coin2.png"><br>'+
			'2.教師可以在班級管理的學生管理中依學生表現給予鼓勵的點數(點數的計算:1點)<br>'+
			'<img src="images/role/coin2.png">'+
			'</div>',
		showCloseButton: true,
		showConfirmButton: false
	});
	</script>
<?php
	}
	echo '<div class="content2-Box">
    	<div class="path">目前位置：<a href="modules.php?op=main">首頁</a></div>
		<div style="text-align:center; font-size:24px; color:#1c1c1c; padding-bottom:30px; line-height:150%;">';
	echo '<b>歡迎光臨！您是 '.$user_data->uname.'，身分：'.$user_data->user_level.'。</b>';
	echo '</div>';
	$map_new_url = "'modules/D3/app/index_view.php?aa=".base64_encode($_SESSION['user_id'])."'";
	if($user_data->access_level < 10 || $user_data->access_level==21){  //暫定等級30以下使用此畫面
		echo '
		<script  src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30=" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/start/jquery-ui.css">
		<ul class="idx-menu">
       		<li ><a><div class="idx-menu-data" >知識結構學習<br><div class="btn01" id="menu" style="cursor:pointer;">進入查看</div></div></a></li>
       		<li><a href="modules.php?op=modload&name=BayesianTest&file=index"><div class="idx-menu-data">智慧適性診斷<br><div class="btn01">進入查看</div></div></a></li>
       		<li><a href="'.$CR_url.'"><div class="idx-menu-data">互動式學習<br><div class="btn01">進入查看</div></div></a></li>
       		<li><a href="http://210.240.189.1/CPS/login.php?aa='.$cps_encode.'&stuid='.$_SESSION['user_id'].'" target="_blank" ><div class="idx-menu-data">PISA合作問題解決能力<br><div class="btn01">進入查看</div></div></a></li>
					<li><a href="http://210.240.189.1/GC_assessment/login.php?aa='.$cps_encode.'&stuid='.$_SESSION['user_id'].'" target="_blank" ><div class="idx-menu-data">全球競合力<br><div class="btn01">進入查看</div></div></a></li>
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
	//修改by 彥鈞 106.11.22
	//.$_SERVER["REQUEST_URI"].')-('
	//_POST
	echo '<div class="bottom-pto"><img src="images/content-bg.png" title="('.sprintf("%01.4f",$code_spent_time).')-('.$mem_usage.')"></div>';
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
