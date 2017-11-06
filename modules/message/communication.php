<?php


?>
<!DOCTYPE HTML>
<html>
<style>
  ul, li {margin:0;padding: 0;list-style: none;}
  .main_content {height:700px;width:100%;overflow:hidden;overflow-y:auto;}

  .main_content::-webkit-scrollbar-track {-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);border-radius: 10px;background-color: #F5F5F5;}
  .main_content::-webkit-scrollbar {width: 10px;background-color: #F5F5F5;}
  .main_content::-webkit-scrollbar-thumb {border-radius: 10px;-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);background-color: #555;}
  .filebox::-webkit-scrollbar-track {-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);border-radius: 10px;background-color: #F5F5F5;}
  .filebox::-webkit-scrollbar {height:10px; width: 10px;background-color: #F5F5F5;}
  .filebox::-webkit-scrollbar-thumb {border-radius: 10px;-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);background-color: #555;}

  /* 留言浮動框 */
  .accordionPart > section {border:solid 1px #E3E3E3;}
  .accordionPart > section.grid-item {width:320px;float:left;}
  .accordionPart > section.open {width:100%;float:left;}
  .accordionPart > section.open > div.qa_content {display:inherit !important;background-color:#f6f7f9;}
  .accordionPart > section > div.qa_title {cursor: pointer;}
  .accordionPart > section > div.qa_content {display:none;}

  /* 留言標題 */
  .qa_title > ul > li {position:relative;overflow:hidden;}
  .qa_title > ul > li > .name {color:rgb(0, 0, 255);}
  .qa_title > ul > li > .time {padding:0px 4px;font-size:14px;}
  .qa_title > ul > li > .text {padding-left:1em;}
  .qa_title > ul > li > .info {display:inline-block;height:25px;float:right;font-size:14px;margin-right:1em;}
  .qa_title > ul > li > .attachedfile {display:flex;height:25px;font-size:14px;vertical-align:middle;}
  .qa_title > ul > li > .attachedfile > .ico {display:inline-block;height:25px;width:25px;margin:0px 5px;background-size:80%;background-position:center;background-repeat:no-repeat;background-image: url("./images/toolbar/file.png");}
  .qa_title > ul > li > .attachedfile > .filename {flex:80;display:flex;justify-content:stretch;overflow:hidden;white-space: nowrap;}
  .qa_title > ul > li > .attachedfile > .filename > span {margin-right:4px;overflow:hidden;text-overflow:ellipsis;text-decoration:underline;}
  .qa_title > ul > li > .attachedfile > .filename > span:hover {color:rgb(0, 0, 255);}

  /* 留言回覆 */
  .qa_content > ul > li > .name {color:rgb(0, 0, 255);}
  .qa_content > ul > li > .time {font-size:14px;color:rgb(157, 157, 157);}

  /* 留言給家長 */
  .stamp {display:block;max-width:1150px;margin-bottom:4px;overflow-y:auto;background-color:#F8F8F8;}
  .stamp > textarea {resize:none;}
  .stamp-button {cursor:pointer;margin:0px;margin-bottom:4px;}

  /* 留言功能 */
  .toolbar {display:table;}
  .toolbar > li {position:relative;display:table-cell;vertical-align: middle;height:40px;cursor:pointer;}
  .toolbar > li:nth-of-type(1) {cursor:text;}
  .toolbar > li::after {content:"|";display:block;position:absolute;top:2px;right:-3px;color:#BCBCBC;}
  .toolbar > li:nth-of-type(1)::after ,.toolbar > li:nth-of-type(2)::after, .toolbar > li:last-child::after {display:none;}
  .toolbar > li > i {position:relative;display:block;height:25px;width:25px;overflow:hidden;margin:0px 5px;background-size:80%;background-position:center;background-repeat:no-repeat;}
  .toolbar > li > i > input {position:absolute;top:0px;left:0px;opacity:0;max-width:25px;}
  .toolbar > li > i:hover {background-color:#E7E7E7;border:1px solid #999;}
  .toolbar > li > i.pic {background-image: url("./images/toolbar/picture.png");}
  .toolbar > li > i.file {opacity:0.6;background-image: url("./images/toolbar/addfile.png");}
  .toolbar > li.messageto > select {height:28px;margin-top:5px;}

  /* 留言上傳檔案 */
  .filebox {display:block;overflow:hidden;overflow-x:auto;white-space:nowrap;height:150px;height:110px;}
  .filebox > li {position:relative;display:inline-block;vertical-align:top;width:150px;margin-right:4px;cursor:pointer;}
  .filebox > li.fileupload > div {position:relative;}
  .filebox > li.fileupload > div > img {position:absolute;top:36px;left:45px;opacity: 0.2;}
  .filebox > li.fileupload > div > span {display:block;position:absolute;top:50px;width:150px;overflow:hidden;text-overflow:ellipsis;}
  .filebox > .delete::after {content:"";position:absolute;top:0px;right:0px;width:10px;height:10px;background-image: url("./images/toolbar/del.png");background-size:100%;background-position:center;background-repeat:no-repeat;}

  /* 其他 */
  .messagetoico::after {content:"▸";color:#000;}

  @media screen and (min-width: 500px) {
    .accordionPart > section.grid-item {width:380px;float:left;}
    .accordionPart > section.open {width:100%;float:left;}
  }
</style>
<script type="text/javascript" src="https://unpkg.com/vue"></script>
<script type="text/javascript" src="https://unpkg.com/vue-router/dist/vue-router.js"></script>
<!-- masonry -->
<script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
<!-- Loading套件 -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@1.5.4/src/loadingoverlay.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@1.5.4/extras/loadingoverlay_progress/loadingoverlay_progress.min.js"></script>
<!-- Zoom -->
<!-- <script type="text/javascript" src="./include/libsrc/jquery.elevatezoom.js"></script> -->
<script>
  var oUplod = {};
  var iFileSizeLimit = 3072000; // 3M (1M = 1024000)
  var vPassType = ['png','jpg','jpeg','bmp','gif','doc','docx','xls','xlsx','ppt','pptx','txt'];

  $(function() {
		$.LoadingOverlay('show');

    $('#sumbit_btn').click(function() {
      var fileupload = $('#uplodefile').prop('files')[0];
      var form_data = new FormData();
      form_data.append('import_file', fileupload);
      // console.log(fileupload, form_data);

      $.ajax({
          url: "./modules/message/uploadfile.php",
          method: "POST",
          data: form_data,
          processData: false,
          contentType: false,
          success: function (response) {
              console.log(111,response);
          },
          error: function (jqXHR, textStatus, errorMessage) {
              console.log(errorMessage);
          }
      });
    });

		$(document).ready(function() {
			// masonry
			var $grid = $('.grid').masonry({
			  itemSelector: '.grid-item',
			  columnWidth: $('#grid-item').width(),
				gutter: 5
			});

			var $stamp = $grid.find('.stamp');
			$stamp.hide();
			var isStamped = false;
			$('.stamp-button').on( 'click', function() {

				// stamp or unstamp element
			  if ( isStamped ) {
					$stamp.hide();
			    $grid.masonry( 'unstamp', $stamp );
			  } else {
					$stamp.show();
			    $grid.masonry( 'stamp', $stamp );
			  }
			  // trigger layout
			  $grid.masonry('layout');
			  // set flag
			  isStamped = !isStamped;
			});

      // 新增 圖片/檔案
      $('.toolbar > li > i > input').click(function (e) {
        oUplod = e.target;
      });

      $('body').on('change', '.toolbar > li > i > input', function (e) {
        if (this.files && this.files[0]) {
          oUplod.filename = this.files[0].name;
          oUplod.filetype = this.files[0].type;
          oUplod.filesize = this.files[0].size;

          if (iFileSizeLimit < oUplod.filesize) {
            alert('您的檔案過大，檔案大小限制為3M');
            return;
          }
          if (1 < oUplod.filename.split('.').length-1) {
            alert('您的檔案名稱不能含有特殊字元 .');
            return;
          }
          if (-1 == $.inArray(oUplod.filename.split('.').pop(), vPassType)) {
            alert('不接受此格式檔案!');
            return;
          }

          var reader = new FileReader();
          reader.onload = function (e) {
            $('.filebox').height('150px');
            if (0 <= oUplod.filetype.indexOf('image')) {
              $('.filebox').append('<li class="imgupload delete"><img src="' + e.target.result + '" /></li>');
            }
            else {
              $('.filebox').append('<li class="fileupload delete"><div><img src="./images/toolbar/file.png" /><span>' + oUplod.filename + '</span></div></li>');
            }
            $grid.masonry('reloadItems');
            $grid.masonry('layout');
          }
          reader.readAsDataURL(this.files[0]);
        }
      })

      // 刪除 圖片/檔案
      $('body').on('click', '.filebox > li', function (e) {
        var sX = $(this).position().left;
        var sY = $(this).position().top;
        if (158 <= (e.pageX - sX) && 250 <= (e.pageY - sY) && $(e.target).is('li')) {
          e.target.remove();

          // 如果沒有檔案, 畫面縮小
          if (0 === $('.filebox').children().length) {
            $('.filebox').height('0px');
            $grid.masonry('reloadItems');
            $grid.masonry('layout');
          }
        }
      });

      // textarea 自動高
      $('textarea.auto-height').css('overflow', 'hidden').bind('keydown keyup', function() {
          $(this).height('0px').height($(this).prop('scrollHeight') + 'px');
          $('#stamp').height($(this).height());
          $grid.masonry('reloadItems');
          $grid.masonry('layout');
      }).keydown();

      // 區塊收合
			$grid.on('click','.grid-item', function(e) {
        if ($(e.target).is('input') || $(e.target).closest('div').hasClass('qa_content') || $(e.target).parent().hasClass('filename')) {
          return;
        }

				$(this).toggleClass('open');
			  $grid.masonry('reloadItems');
			  $grid.masonry('layout');

        var ranId = Math.random();
        $(e.target).attr('id', ranId);
        window.location.href = '#' + ranId;
			});

			$grid.masonry();
      $.LoadingOverlay('hide');
  });
});
</script>
  <div class="content2-Box">
	  <div class="path">目前位置：親師互動</div>
      <div class="choice-box">
        <div class="choice-title">選單</div>
          <ul class="choice work-cholic">
        	  <li><a href="" class="current"><i class="fa fa-caret-right"></i>親師互動</a></li>
          </ul>
   		 </div>
 			<div class="right-box" style="width:100%;margin:0px auto;">
        <button class="btn06 stamp-button" style="width:120px;">留言給家長</button>
        <article class="main_content">
          <div class="accordionPart grid">
						<section class="stamp">
              <ul class="toolbar">
                <li>留言<span class="messagetoico"></span></li>
                <li class="messageto">
                  <select>
                    <option value="">全部家長</option>
                    <option value="">小明家長</option>
                    <option value="">小美家長</option>
                  <select>
                </li>
                <!-- <li><i class="pic" title="上傳圖片"><input id="uplodeimg" accept="image/*" type="file" value="" /></i></li> -->
                <li><i class="file" title="上傳圖片/檔案"><input id="uplodefile" type="file" value="" /></i></li>
              </ul>
							<textarea id="edit_text" name="edit_text" class="auto-height"></textarea>
              <ul class="filebox">
                <li class="imgupload delete"><img src="./include/srcoe.jpg" /></li>
                <li class="fileupload delete">
                  <div>
                    <img src="./images/toolbar/file.png" />
                    <span>10月期中考.xsl</span>
                  </div>
                </li>
              </ul>
							<button id="sumbit_btn" name="sumbit_btn" class="btn04" style="float:right;margin:0px;">確認</button>
						</section>
						<section class="grid-item">
							<div class="qa_title">
								<ul>
									<li>
                    <span class="name">我<span class="messagetoico"></span>小明家長</span><span class="time">2017-10-23 14:00</span>
                  </li>
									<li>
                    <span class="text">
										老師覺得你接受知識的能力還是挺好的。可是你有時候管不住自己，課上總愛做小動作。字嘛，有些馬虎，這種學習態度可要不得呀！老師相信你一定會改掉的，是嗎？利用假期好好練字哦！
										你平時不愛秀出自己，習慣了默默無聞，但對班級卻很關心。不過，你的字卻讓老師感到頭疼，每次都要「猜猜猜」，希望你能充分認識到這一點，把字寫得端端正正。
										真誠地希望你能在學習上激活所有的腦細胞，我們一起努力，你準備好了嗎？
                    </span>
									</li>
                  <li>
                    <span class="attachedfile">
                      <i class="ico"></i>
                      <span class="filename">
                        <span>filenanme</span>
                        <span>filenanme</span>
                        <span>filenanme</span>
                      </span>
                      <span class="info">留言數(2)</span>
                    </span>
                  </li>
								</ul>
							</div>
							<div class="qa_content">
								<ul>
									<li>
										<span class="name">王先生</span>
										<span class="text">感謝老師教導有方</span>
										<span class="time">2017-10-25 09:06</span>
									</li>
									<li>
										<span class="name">實驗6年9班老師</span>
										<span class="text">是學生自己用功的</span>
										<span class="time">2017-10-25 09:06</span>
									</li>
								</ul>
								<div><input type="text" placeholder="留言‧‧‧‧‧" /></div>
							</div>
						</section>
						<section class="grid-item">
							<div class="qa_title">
								<ul>
									<li><span class="name">小明家長<span class="messagetoico"></span>我</span><span class="time">2017-10-23 14:00</span></li>
									<li>
                    <span class="text">
										你尊敬老師，團結同學，是老師的得力助手。令老師感到欣慰的是，你上課精彩的發言，總能搏得同學的讚賞。
										不過，令人遺憾的是你的工作能力有待提高。老師真誠地希望你在工作上要向她人學習，做一名人見人愛的好學生。
                    </span>
									</li>
									<li>
                    <span class="attachedfile">
                      <i class="ico"></i>
                      <span class="filename">
                        <span>filenanme</span>
                        <span>filenanme</span>
                        <span>filenanme</span>
                      </span>
                      <span class="info">留言數(2)</span>
                    </span>
                  </li>
								</ul>
							</div>
							<div class="qa_content">
								<ul>
									<li>
										<span class="name">6年9班老師</span>
										<span class="text">感謝老師教導有方</span>
										<span class="time">2017-10-25 09:06</span>
									</li>
									<li>
										<span class="name">實驗6年9班老師</span>
										<span class="text">是學生自己用功的</span>
										<span class="time">2017-10-25 09:06</span>
									</li>
								</ul>
								<div><input type="text" placeholder="留言‧‧‧‧‧" /></div>
							</div>
						</section>
						<section class="grid-item">
							<div class="qa_title">
								<ul>
									<li><span class="name">小明家長<span class="messagetoico"></span>我</span><span class="time">2017-10-23 14:00</span></li>
									<li><span class="text">學期成績</span></li>
									<li>
                    <img class="qaimg" src="./include/srcoe.jpg" />
                  </li>
                  <li>
                    <span class="attachedfile">
                      <i class="ico"></i>
                      <span class="filename">
                        <span>filenanme</span>
                        <span>filenanme</span>
                        <span>filenanme</span>
                      </span>
                      <span class="info">留言數(2)</span>
                    </span>
                  </li>
								</ul>
							</div>
							<div class="qa_content">
								<ul>
									<li>
										<span class="name">王先生</span>
										<span class="text">感謝老師教導有方</span>
										<span class="time">2017-10-25 09:06</span>
									</li>
									<li>
										<span class="name">實驗6年9班老師</span>
										<span class="text">是學生自己用功的</span>
										<span class="time">2017-10-25 09:06</span>
									</li>
								</ul>
								<div><input type="text" placeholder="留言‧‧‧‧‧" /></div>
							</div>
						</section>
            <section class="grid-item">
							<div class="qa_title">
								<ul>
									<li><span class="name">小明家長<span class="messagetoico"></span>我</span><span class="time">2017-10-23 14:00</span></li>
									<li>
                    <span class="text">
										你尊敬老師，團結同學，是老師的得力助手。令老師感到欣慰的是，你上課精彩的發言，總能搏得同學的讚賞。
										不過，令人遺憾的是你的工作能力有待提高。老師真誠地希望你在工作上要向她人學習，做一名人見人愛的好學生。
                    </span>
									</li>
                  <li>
                    <span class="attachedfile">
                      <i class="ico"></i>
                      <span class="filename">
                        <span>filenanme</span>
                        <span>filenanme</span>
                        <span>filenanme</span>
                      </span>
                      <span class="info">留言數(2)</span>
                    </span>
                  </li>
								</ul>
							</div>
							<div class="qa_content">
								<ul>
									<li>
										<span class="name">王先生</span>
										<span class="text">感謝老師教導有方</span>
										<span class="time">2017-10-25 09:06</span>
									</li>
									<li>
										<span class="name">實驗6年9班老師</span>
										<span class="text">是學生自己用功的</span>
										<span class="time">2017-10-25 09:06</span>
									</li>
								</ul>
								<div><input type="text" placeholder="留言‧‧‧‧‧" /></div>
							</div>
						</section>
            <section class="grid-item">
							<div class="qa_title">
								<ul>
									<li><span class="name">小明家長<span class="messagetoico"></span>我</span><span class="time">2017-10-23 14:00</span></li>
									<li>
                    <span class="text">
										你尊敬老師，團結同學，是老師的得力助手。令老師感到欣慰的是，你上課精彩的發言，總能搏得同學的讚賞。
										不過，令人遺憾的是你的工作能力有待提高。老師真誠地希望你在工作上要向她人學習，做一名人見人愛的好學生。
                    </span>
									</li>
                  <li>
                    <span class="attachedfile">
                      <i class="ico"></i>
                      <span class="filename">
                        <span>filenanme</span>
                        <span>filenanme</span>
                        <span>filenanme</span>
                      </span>
                      <span class="info">留言數(2)</span>
                    </span>
                  </li>
								</ul>
							</div>
							<div class="qa_content">
								<ul>
									<li>
										<span class="name">王先生</span>
										<span class="text">感謝老師教導有方</span>
										<span class="time">2017-10-25 09:06</span>
									</li>
									<li>
										<span class="name">實驗6年9班老師</span>
										<span class="text">是學生自己用功的</span>
										<span class="time">2017-10-25 09:06</span>
									</li>
								</ul>
								<div><input type="text" placeholder="留言‧‧‧‧‧" /></div>
							</div>
						</section>
          </article>
        </div>
	   </div>
	 </div>
</html>
