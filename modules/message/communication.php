<!DOCTYPE HTML>
<html>
<!-- <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.25.0/codemirror.min.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/froala-editor/2.7.0/css/froala_editor.pkgd.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/froala-editor/2.7.0/css/froala_style.min.css" /> -->
<style>
ul, li {margin:0;padding: 0;list-style: none;}
.main_content {height:480px;max-height:480px;width:calc(100% - 178px);overflow:hidden;overflow-y:scroll;}
#qaContent {width: 500px;}
#qaContent h3 {width:500px;height:22px;text-indent:-9999px;}
#qaContent h3.qa_group_1 {background: url(qa_group_1.gif) no-repeat;}
#qaContent h3.qa_group_2 {background: url(qa_group_2.gif) no-repeat;}
#qaContent ul.accordionPart {margin: 10px 10px 50px 30px;}
#qaContent ul.accordionPart li {border-bottom: solid 1px #e3e3e3;padding-bottom: 12px;margin-top: 12px;}
#qaContent ul.accordionPart li .qa_title {background: url(icon_q_a.gif) no-repeat 0px 3px;padding-left: 28px;color: #1186ec;cursor: pointer;}
#qaContent ul.accordionPart li .qa_title_on {text-decoration: underline;}
#qaContent ul.accordionPart li .qa_content {margin: 6px 0 0;background: url(icon_q_a.gif) no-repeat 0px -24px;padding-left: 28px;color: #666;}
.content_box{
	display: inline-block;
	border: 1px dotted #4F4F4F;
	padding: 10px;
	margin: 5px 5px 5px 0;
	overflow:hidden;
	width:337px;
}
</style>
<script type="text/javascript" src="https://unpkg.com/vue"></script>
<!-- <script type="text/javascript" src="https://masonry.desandro.com/jquery.masonry.min.js"></script> -->
<script src="https://cdn.ckeditor.com/4.7.3/standard/ckeditor.js"></script>
<script>
  $(function(){
  	$('#qaContent ul.accordionPart li div.qa_title').hover(function(){
  		$(this).addClass('qa_title_on');
  	}, function(){
  		$(this).removeClass('qa_title_on');
  	}).click(function(){
  		// 當點到標題時，若答案是隱藏時則顯示它；反之則隱藏
  		$(this).next('div.qa_content').slideToggle();
  	}).siblings('div.qa_content').hide();

  	// $('#_container').imagesLoaded(function () {
    //       $('#_container').masonry({
    //           itemSelector: '.content_box',
    //           columnWidth: 364,
    //           animate:true
    //       });
  	// });
  });
</script>
  <div class="content2-Box">
	  <div class="path">目前位置：親師互動</div>
      <div class="choice-box">
        <div class="choice-title">選單</div>
          <ul class="choice work-cholic">
        	  <li><a href="" class="current"><i class="fa fa-caret-right"></i>親師互動</a></li>
        		<!-- <li><a href=""><i class="fa fa-caret-right"></i>學生管理</a></li> -->
          </ul>
   		 </div>
      <div class="left-box">
        <from>
          <label><input type="text" placeholder="搜尋"/></label>
        </from>
      </div>
 			<div class="right-box">
        <from>
          我要發表
          <!-- <textarea name="editor1"></textarea>
          <script>
            CKEDITOR.replace('editor1');
          </script> -->
        </from>
        <div class="main_content">
          <div id="qaContent">
          	<ul class="accordionPart">
              <li>
          			<div class="qa_title">問題</div>
          			<div class="qa_content">答案</div>
          		</li>
              <li>
          			<div class="qa_title">問題</div>
          			<div class="qa_content">答案</div>
          		</li>
              <li>
                <div class="qa_title">問題</div>
                <div class="qa_content">答案</div>
              </li>
              <li>
                <div class="qa_title">問題</div>
                <div class="qa_content">答案</div>
              </li>
              <li>
                <div class="qa_title">問題</div>
                <div class="qa_content">答案</div>
              </li>
          		<li>
          			<div class="qa_title">問題</div>
          			<div class="qa_content">答案</div>
          		</li>
              <li>
          			<div class="qa_title">問題</div>
          			<div class="qa_content">答案</div>
          		</li>
              <li>
                <div class="qa_title">問題</div>
                <div class="qa_content">答案</div>
              </li>
              <li>
                <div class="qa_title">問題</div>
                <div class="qa_content">答案</div>
              </li>
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
