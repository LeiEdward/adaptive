<!DOCTYPE HTML>
<html>
<script src="https://www.gstatic.com/firebasejs/4.6.0/firebase.js"></script>
<script>
  $(function(){
  	// 幫 div.qa_title 加上 hover 及 click 事件
  	// 同時把兄弟元素 div.qa_content 隱藏起來
  	$('#qaContent ul.accordionPart li div.qa_title').hover(function(){
  		$(this).addClass('qa_title_on');
  	}, function(){
  		$(this).removeClass('qa_title_on');
  	}).click(function(){
  		// 當點到標題時，若答案是隱藏時則顯示它；反之則隱藏
  		$(this).next('div.qa_content').slideToggle();
  	}).siblings('div.qa_content').hide();
  });
</script>
<style>
ul, li {margin:0;padding: 0;list-style: none;}
#qaContent {width: 500px;}
#qaContent h3 {width:500px;height:22px;text-indent:-9999px;}
#qaContent h3.qa_group_1 {background: url(qa_group_1.gif) no-repeat;}
#qaContent h3.qa_group_2 {background: url(qa_group_2.gif) no-repeat;}
#qaContent ul.accordionPart {margin: 10px 10px 50px 30px;}
#qaContent ul.accordionPart li {border-bottom: solid 1px #e3e3e3;padding-bottom: 12px;margin-top: 12px;}
#qaContent ul.accordionPart li .qa_title {background: url(icon_q_a.gif) no-repeat 0px 3px;padding-left: 28px;color: #1186ec;cursor: pointer;}
#qaContent ul.accordionPart li .qa_title_on {text-decoration: underline;}
#qaContent ul.accordionPart li .qa_content {margin: 6px 0 0;background: url(icon_q_a.gif) no-repeat 0px -24px;padding-left: 28px;color: #666;}
</style>
  <div class="content2-Box">
	  <div class="path">目前位置：親師互動</div>
      <div class="choice-box">
        <div class="choice-title">選單</div>
          <ul class="choice work-cholic">
        	  <li><a href="" class="current"><i class="fa fa-caret-right"></i>親師互動</a></li>
        		<!-- <li><a href=""><i class="fa fa-caret-right"></i>學生管理</a></li> -->
          </ul>
   		 </div>
      <!-- <div class="left-box">
        依定區搜尋
      </div> -->
 			<div class="right-box">
        <div id="main_content">
          <from>

          </from>
          <div id="qaContent">
          	<h3 class="qa_group_1">問題分類</h3>
          	<ul class="accordionPart">
          		<li>
          			<div class="qa_title">問題</div>
          			<div class="qa_content">答案</div>
          		</li>
          	</ul>
          </div>
        </div>
      </div>
  </div>
</html>
