<?php
session_start();
include_once "include/config.php";
require_once "include/adp_API.php";

$user_id = $_SESSION['user_id'];
$user_data = new UserData($user_id);

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>因材網</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=yes,maximum-scale=5.0,minimum-scale=1.0">
<link href='favicon.ico' rel='icon' type='image/x-icon'/>
<link rel="stylesheet" href="css/cms-index.css" type="text/css">
<link rel="stylesheet" href="css/cms-header.css" type="text/css">
<link rel="stylesheet" href="css/cms-footer.css" type="text/css">

<script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.9.1.js "></script>
<script src="http://ajax.aspnetcdn.com/ajax/knockout/knockout-3.0.0.js "></script>
<script src="http://html2canvas.hertzen.com/build/html2canvas.js"></script>
<script type="text/javascript" src="scripts/venobox/venobox.min.js"></script>
<script src="scripts/sweetalert.min.js"></script>
<link rel="stylesheet" type="text/css" href="scripts/sweetalert.css">
<style>
    fieldset { width: 400px; height: auto; margin-top: 6px; }
</style>

<script>

function chk(){
	if(typeof($("input[name=chk_fb_sub]:checked").val()) === "undefined" ){
		sweetAlert("填寫不完整", "問題類型未填!", "error");
      return false;
	}
	else if(typeof($("input[name=chk_fb]:checked").val()) === "undefined" ){
		sweetAlert("填寫不完整", "問題狀況未填!", "error");
		return false;
	}else{
		swal({
			title: "感謝您提供寶貴的意見 ！",
			text: "流程正在處理中，請稍候...(請勿重複點選『完成』按鈕)",
			timer: 7000,
			showConfirmButton: false
		});
		return true;
	}
}
$(function() {
    $("#btn05").click(function() { //重新截圖
        console.log('test...');
      html2canvas([document.getElementById('content')], {
        onrendered: function(canvas) {
          var $div = $("fieldset div");
          $div.empty();
          $("<img />", { src: canvas.toDataURL("image/png") }).appendTo($div);
        }
      });
    });
});
$(document).ready(function(){
	$("#btn06").click(function() {
		//console.log('btn06_test...');
		$('.vbox-close, .vbox-overlay').trigger('click');
	});

	/*$('#chk_fb[]').toggle({
		alert($('#chk_fb[]').val());
	});*/
});
</script>
</head>
<body>
<div id="content" class="content-Box">
	<div class="content2-Box">
		<div class="path">目前位置：問題回報</div>
			<div class="main-box">
        <div style="float: right; width: 226px;  background: rgba(91, 220, 180, 0.62);" class="btn07" ><a href="modules.php?op=modload&name=schoolReport&file=my_QAmanager&screen=frame&imgpath=<?php echo $_GET["imgpath"];?>">之前已提出的問題回報</a></div>
				<div class="right-box">
					<form action="modules/assignMission/prodb_feedback_save.php" method="post">

						<div class="title01">步驟一：電子信箱</div>
						<div class="class-list2 test-search">
							<div class="feedback-box">
								<input type="text" id="fb_email" name="fb_email" placeholder="電子信箱"  value="<?php echo $user_data->email;?>" required>
							</div>
						</div>
						<div class="after-20"></div>
						<div class="title01">步驟二：問題描述</div>
							<div class="class-list2">
								問題類型(必選)：
								<input type="radio" id="chk_fb_sub" name="chk_fb_sub" value="1">語文科<!--  required-->
								<input type="radio" id="chk_fb_sub" name="chk_fb_sub" value="2">數學科
								<input type="radio" id="chk_fb_sub" name="chk_fb_sub" value="3">自然科
								<input type="radio" id="chk_fb_sub" name="chk_fb_sub" value="4">系統
								<input type="radio" id="chk_fb_sub" name="chk_fb_sub" value="4">其他
           						<p>問題狀況(必選)：
	          			 		<input type="radio" id="chk_fb" name="chk_fb" value="1"> 影片內容
	           					<input type="radio" id="chk_fb" name="chk_fb" value="2"> 題目
	           					<input type="radio" id="chk_fb" name="chk_fb" value="3"> 網頁
	           					<input type="radio" id="chk_fb" name="chk_fb" value="4"> 連線緩慢
	           					<input type="radio" id="chk_fb" name="chk_fb" value="5"> 系統
	           					<input type="radio" id="chk_fb" name="chk_fb" value="6"> 其他<br></p>
	           					<p>(若問題發生在別人的設備 請告知機器型號！！)</p>
	          				<div class="feedback-box">
	     		          	<textarea rows="4" cols="50" id="fb_text" name="fb_text" placeholder="請把您遇到的問題告訴我們..." required></textarea>
	     			     	</div>
           				</div>
					<div class="after-20"></div>
           			<div class="title01">步驟三：系統畫面</div>
	            	<div class="class-list2">
            			<?php echo $url = $_GET[imageurl];?>
            			<p>目前影片無法被截圖！請在問題描述的欄位告訴我們問題，謝謝！</p>
							<fieldset>
							    <legend>圖檔</legend>
							    <div>
							    	<img src="<?php echo $_GET["imgpath"];?>">
							    	<input type="hidden" id="fb_img_path" name="fb_img_path" value="<?php echo $_GET["imgpath"];?>">
								</div>
							</fieldset>
					</div>
					<div align="center"><input type="submit" onclick="return chk();" id="btn06" class="btn04" value="完成"> <a href="#" class="btn04">取消</a></div>
				</form>
			</div>
		</div>
	</div>
</div>
</body>
</html>
<?php
