<?php
include_once "include/config.php";

if (!isset($_SESSION)) {
	session_start();
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title></title>
<!-- <script type="text/javascript" src="http://code.jquery.com/jquery-1.6.2.min.js"></script> -->
<script type="text/javascript" src="include/sticky/sticky.full.js"></script>
<link rel="stylesheet" href="include/sticky/sticky.full.css" type="text/css" />
<script type="text/javascript">
function btnDivShow_onclick() {
	//var btn=window.document.forms(0).btnDivShow;
	var State=div1.style.display;
	if(State=='none')
	{
		div1.style.display='';
		//btn.value='隱藏內容';
	}
	else
	{
		div1.style.display='none';
		//btn.value='顯示內容';
	}
}

$(document).ready(function() {
	 $("#showMsgBox").click(function(){
		 $.sticky('The page has loaded!');
	 });
});

</script>

</head>
<?php 
$user_id = $_SESSION['user_id'];
$top_num = 5;
$limit_num = 20;
$end_d = date("Y-m-d",mktime(23,59,59,date("m"),date("d")-date("w")+1-7,date("Y")));

$search_count = $dbh->prepare("SELECT count(*) num FROM `message_master` a  WHERE a.touser_id =:user_id");
$search_count->bindValue(':user_id', $user_id, PDO::PARAM_STR);
$search_count->execute();
$row_count = $search_count->fetch();

$search_count2 = $dbh->prepare("SELECT count(*) num FROM `message_master` a  WHERE a.touser_id =:user_id and read_mk =:read_mk");
$search_count2->bindValue(':user_id', $user_id, PDO::PARAM_STR);
$search_count2->bindValue(':read_mk', '0', PDO::PARAM_INT);
$search_count2->execute();
$row_count_unread = $search_count2->fetch();

if($row_count["num"] > $top_num) { //大於5筆
	
	$search = $dbh->prepare("SELECT a.msg_sn, a.touser_id, date_format(a.create_time,'%Y-%m-%d') create_time, a.msg_content
			FROM `message_master` a  WHERE a.touser_id =:user_id ORDER by create_time DESC LIMIT 0,5");
	$search->bindValue(':user_id', $user_id, PDO::PARAM_STR);
	$search->execute();
	$row_data_top = $search->fetchAll(\PDO::FETCH_ASSOC);
	
	if($row_count > $top_num+1){
		$search_all = $dbh->prepare("SELECT a.msg_sn, a.touser_id, date_format(a.create_time,'%Y-%m-%d') create_time, a.msg_content 
				FROM `message_master` a  WHERE a.touser_id =:user_id ORDER by create_time DESC LIMIT 6,20");
		$search_all->bindValue(':user_id', $user_id, PDO::PARAM_STR);
		$search_all->execute();
		$row_data_all = $search_all->fetchAll(\PDO::FETCH_ASSOC);
	}

}else{ //小於5筆
	$search_all = $dbh->prepare("SELECT a.msg_sn, a.touser_id, date_format(a.create_time,'%Y-%m-%d') create_time, a.msg_content
		FROM `message_master` a  WHERE a.touser_id =:user_id ORDER by create_time");
	$search_all->bindValue(':user_id', $user_id, PDO::PARAM_STR);
	$search_all->execute();
	$row_data_top = $search_all->fetchAll(\PDO::FETCH_ASSOC);
}

?>
<body>
	<!-- Main -->
	<div id="main-wrapper">
		<div class="container">
			<div class="row 200%">
				<div class="2u 12u$(medium)">
					<div id="sidebar">
						<h2><?php echo $user_data->uname;?></h2>

					<!-- Sidebar -->
						<section>
							<h3>個人討論</h3>
							<ul class="style2">
								<li>即時訊息</li> 
								<li><a href="modules.php?op=modload&name=message&file=parentTalk">親師溝通</<a></li>
							</ul>
							<footer>
								<a href="#" class="button alt small icon fa-info-circle"> more</a>
							</footer>
						</section>
						<br/>
					</div>
				</div>
				<div class="9u 12u$(medium) important(medium)">
					<div id="content" style="border:5px #FFCC66 solid;border-radius:10px;width:100%;background-color:#FFFFCC;padding:10px;margin:10px;">
						<section>
							<table><tr><td style="width:520px;"><h3>您有未讀訊息(<?php echo $row_count_unread["num"];?>)</h3></td><td><input type="text"/> <input type="button" value="搜尋"/></td></tr></table>
							<p></p>
							<table style="border:2px #ffdd70 solid;padding:5px;" cellpadding='5';>
								<tr style="text-align:center;" bgcolor="#ffe89d"><td  style="width:650px;">訊息內容</td><td>日期</td></tr>
							<?php 
								foreach ($row_data_top as $key=>$value){
									echo "<tr><td>$value[msg_content]</td><td>$value[create_time]</td></tr>";
								}
							?>
							</table>
							<!-- <p>message...<a href="#" class="button icon fa-arrow-circle-right"> detail</a></p> -->
							<footer>
								<a href="#" class="button icon fa-info-circle" onclick="return btnDivShow_onclick()"> more message</a>
								<div id=div1 style="display:none">
								<table style="border:2px #ffdd70 solid;padding:5px;" cellpadding='5';>
								<tr style="text-align:center;" bgcolor="#ffe89d"><td width="650px">訊息內容</td><td>日期</td></tr>
								
								<?php 
								if(count($row_data_all) >0){
									foreach ($row_data_all as $key=>$value){
										echo "<tr><td>$value[msg_content]</td><td>$value[create_time]</td></tr>";
									}
								}
								
								?>
								
								</table></div>
							</footer>
							
						</section>
					</div>
				</div>
				
			</div>
		</div>
	</div>
</body>
</html>				