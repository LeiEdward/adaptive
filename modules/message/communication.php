<?php


?>
<!DOCTYPE HTML>
<html>
<style>
  ul, li {margin:0;padding: 0;list-style: none;}
  .main_content {height:700px;width:100%;overflow:hidden;overflow-y:auto;}
  .main_content::-webkit-scrollbar-track{-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);border-radius: 10px;background-color: #F5F5F5;}
  .main_content::-webkit-scrollbar{width: 12px;background-color: #F5F5F5;}
  .main_content::-webkit-scrollbar-thumb{border-radius: 10px;-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);background-color: #555;}

  .accordionPart > section {border:solid 1px #E3E3E3;}
  .accordionPart > section.grid-item {width:320px;float:left;}
  .accordionPart > section.open {width:100%;float:left;}
  .accordionPart > section.open > div.qa_content {display:inherit !important;background-color:#f6f7f9;}
  .accordionPart > section > div.qa_title {cursor: pointer;}
  .accordionPart > section > div.qa_content {display:none;}

  .qa_title > ul > li {position:relative;overflow:hidden;}
  .qa_title > ul > li > .name {color:rgb(0, 0, 255);}
  .qa_title > ul > li > .info {display:inline-block;height:25px;float:right;font-size:14px;margin-right:1em;}
  .qa_title > ul > li > .time {padding:0px 4px;font-size:14px;}
  .qa_title > ul > li > .text {padding-left:1em;}

  .qa_content > ul > li > .name {color:rgb(0, 0, 255);}
  .qa_content > ul > li > .time {font-size:14px;color:rgb(157, 157, 157);}

  .stamp {display:block;margin-bottom:4px;overflow-y:auto;background-color:#F8F8F8;}
  .stamp > textarea {resize:none;}
  .stamp-button {cursor:pointer;margin:0px;margin-bottom:4px;}

  .toolbar {display:table;}
  .toolbar > li {position:relative;display:table-cell;vertical-align: middle;height:40px;width:25px;cursor:pointer;}
  .toolbar > li::after {content:"|";display:block;position:absolute;top:2px;right:-3px;color:#BCBCBC;}
  .toolbar > li > i {display:block;height:25px;width:25px;margin:0px 5px;background-size:80%;background-position:center;background-repeat:no-repeat;}
  .toolbar > li > i:hover {background-color:#E7E7E7;border:1px solid #999;}
  .toolbar > li > i.pic {background-image: url("./images/toolbar/picture.png");}
  .toolbar > li > i.file {opacity:0.6;background-image: url("./images/toolbar/addfile.png");}

  @media screen and (min-width: 500px) {
    .accordionPart > section.grid-item {width:380px;float:left;}
    .accordionPart > section.open {width:100%;float:left;}
  }
</style>
<script type="text/javascript" src="https://unpkg.com/vue"></script>
<script type="text/javascript" src="https://unpkg.com/vue-router/dist/vue-router.js"></script>
<!-- masonry -->
<script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
<!-- ckeditor -->
<script type="text/javascript" src="./include/ckeditor/ckeditor.js"></script>
<!-- Loading套件 -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@1.5.4/src/loadingoverlay.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@1.5.4/extras/loadingoverlay_progress/loadingoverlay_progress.min.js"></script>
<!-- Zoom -->
<!-- <script type="text/javascript" src="./include/libsrc/jquery.elevatezoom.js"></script> -->
<script>
  $(function() {
		$.LoadingOverlay("show");

    $('#editor_btn').click(function() {
      $.ajax({
        url: 'modules.php?op=modload&name=message&file=uploadfile',
        type: 'POST',
        data: {
          edit_text: $('#edit_text').val()
        },
        error: function() {
          alert('無法下載, 請稍後嘗試!');
        },
        success: function(response) {

        }
      });
    });

		$(document).ready(function() {
			// Picture Zoom
			// $('.qaimg').elevateZoom({scrollZoom: true, tint:false, tintColour:'#F90', tintOpacity:0.5});

			// CKEDITOR
			// CKEDITOR.replace('edit_text');

			// Vue.component('todo-item', {
			//   props: ['todo'],
			//   template: '<li>{{ todo.text }}</li>'
			// })
			// var app7 = new Vue({
			//   el: '#app-7',
			//   data: {
			//     groceryList: [
			//       {id: 0, text: '' },
			//       {id: 1, text: '奶酪' },
			//       {id: 2, text: '随便其他什么人吃的东西' }
			//     ]
			//   }
			// })


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

      // textarea 自動高
      $("textarea.auto-height").css("overflow", "hidden").bind("keydown keyup", function() {
          $(this).height('0px').height($(this).prop("scrollHeight") + 'px');
          $('#stamp').height($(this).height());
          $grid.masonry('reloadItems');
          $grid.masonry('layout');
      }).keydown();

      // 區塊收合
			$grid.on('click','.grid-item', function(e) {
        if ($(e.target).is('input') || $(e.target).closest('div').hasClass('qa_content')) {
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
      $.LoadingOverlay("hide");
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
        <button class="btn04 stamp-button">發布消息</button>
        <article class="main_content">
          <div class="accordionPart grid">
						<section class="stamp">
              <ul class="toolbar">
                <li><i class="pic"></i></li>
                <li><i class="file"></i></li>
              </ul>
							<textarea id="edit_text" name="edit_text" class="auto-height"></textarea>
              <span></span>
							<button id="editor_btn" name="editor_btn" class="btn04" style="float:right;margin:0px;">確認</button>
						</section>
						<section class="grid-item">
							<div class="qa_title">
								<ul>
									<li><span class="name">數學老師</span><span class="time">2017-10-23 14:00</span></li>
									<li>
                    <span class="text">
										老師覺得你接受知識的能力還是挺好的。可是你有時候管不住自己，課上總愛做小動作。字嘛，有些馬虎，這種學習態度可要不得呀！老師相信你一定會改掉的，是嗎？利用假期好好練字哦！
										你平時不愛秀出自己，習慣了默默無聞，但對班級卻很關心。不過，你的字卻讓老師感到頭疼，每次都要「猜猜猜」，希望你能充分認識到這一點，把字寫得端端正正。
										真誠地希望你能在學習上激活所有的腦細胞，我們一起努力，你準備好了嗎？
                    </span>
                    <span class="info">留言數(2)</span>
									</li>
									<!-- <li><img class="qaimg" /></li> -->
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
									<li><span class="name">校長</span><span class="time">2017-10-23 14:00</span></li>
									<li>
                    <span class="text">
										你尊敬老師，團結同學，是老師的得力助手。令老師感到欣慰的是，你上課精彩的發言，總能搏得同學的讚賞。
										不過，令人遺憾的是你的工作能力有待提高。老師真誠地希望你在工作上要向她人學習，做一名人見人愛的好學生。
                    </span>
                    <span class="info">留言數(2)</span>
									</li>
									<!-- <li><img class="qaimg" /></li> -->
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
									<li><span class="name">實驗6年9班老師</span><span class="time">2017-10-23 14:00</span></li>
									<li><span class="text">學期成績</span></li>
									<li>
                    <img class="qaimg" src="./include/srcoe.jpg" data-zoom-image="./include/srcoe.jpg" />
                    <span class="info">留言數(2)</span>
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
									<li><span class="name">校長</span><span class="time">2017-10-23 14:00</span></li>
									<li>
                    <span class="text">
										你尊敬老師，團結同學，是老師的得力助手。令老師感到欣慰的是，你上課精彩的發言，總能搏得同學的讚賞。
										不過，令人遺憾的是你的工作能力有待提高。老師真誠地希望你在工作上要向她人學習，做一名人見人愛的好學生。
                    </span>
                    <span class="info">留言數(2)</span>
									</li>
									<!-- <li><img class="qaimg" /></li> -->
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
									<li><span class="name">校長</span><span class="time">2017-10-23 14:00</span></li>
									<li>
                    <span class="text">
										你尊敬老師，團結同學，是老師的得力助手。令老師感到欣慰的是，你上課精彩的發言，總能搏得同學的讚賞。
										不過，令人遺憾的是你的工作能力有待提高。老師真誠地希望你在工作上要向她人學習，做一名人見人愛的好學生。
                    </span>
                    <span class="info">留言數(2)</span>
									</li>
									<!-- <li><img class="qaimg" /></li> -->
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
