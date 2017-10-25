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

.accordionPart > section {border:solid 1px #e3e3e3;}
/*.accordionPart > section:nth-child(odd) {background: linear-gradient(#bfddbb,#FFF);}*/
/*.accordionPart > section:hover {background: linear-gradient(#bfddbb,#FFF);}*/
.accordionPart > section.grid-item {width:390px;float:left;}
.accordionPart > section.open {width:100%;float:left;}
.accordionPart > section > div.qa_title {cursor: pointer;}
.accordionPart > section > div.qa_content {display:none;}
.accordionPart > section > div.qa_title.open {}
.accordionPart > section > div.qa_content.open {display:inherit !important;background-color:#f6f7f9;}

.qa_title > ul > li {position:relative;overflow:hidden;}
.qa_title > ul > li > .name {color:rgb(0, 0, 255);}
.qa_title > ul > li > .info {position:absolute;right:25px;font-size:14px;height: 25px;}
.qa_title > ul > li > .time {padding:0px 4px;font-size:14px;}
.qa_title > ul > li > .text {padding-left:1em;}
/*.qa_title > ul > li > .qaimg {width:600px;}*/

.qa_content > ul > li > .name {color:rgb(0, 0, 255);}
.qa_content > ul > li > .time {font-size:14px;color:rgb(157, 157, 157);}
.stamp {height:320px;}
.stamp-button {font-size:25px;cursor:pointer;text-decoration-line:underline;}
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
		// 當點到標題時，若答案是隱藏時則顯示它；反之則隱藏
		// $('.accordionPart > section > div.qa_title, #qaimg').hover(
		// 	function() {
		// 		// $(this).addClass('qa_title_on');
		// },
		// 	function() {
		// 		// $(this).removeClass('qa_title_on');
		// }).click(
		// 	function(e) {
		// 		if ($(e.target).closest('li').hasClass('open')) {
		// 			$(e.target).closest('li').removeClass('open');
		// 		}
		// 		else {
		// 			$(e.target).closest('li').addClass('open');
		// 		}
		// 		$(this).next('div.qa_content').slideToggle();
		// }).siblings('div.qa_content').hide();

		$(document).ready(function() {
			$.LoadingOverlay("hide");

			// Picture Zoom
			// $('.qaimg').elevateZoom({scrollZoom: true, tint:false, tintColour:'#F90', tintOpacity:0.5});

			// CKEDITOR
			CKEDITOR.replace('main_editor');

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
			  columnWidth: 390,
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

			$grid.on('click','.grid-item', function(e) {
				if ($(e.target).parent().parent().parent().next('div.qa_content').hasClass('qa_content')) {
					$(e.target).parent().parent().parent().toggleClass('open');
					$(e.target).parent().parent().parent().next('div.qa_content').toggleClass('open');
				}
				else {
					$(e.target).parent().parent().parent().children('.qa_title').toggleClass('open');
					$(e.target).parent().parent().parent().children('.qa_content').toggleClass('open');
				}
				$(this).toggleClass('open');
			  $grid.masonry('reloadItems');
			  $grid.masonry('layout');
			});
			$grid.masonry();
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
      <!-- <div class="left-box">
        <from>
          <label><input type="text" placeholder="搜尋"/></label>
        </from>
      </div> -->
 			<div class="right-box" style="width:100%;padding:0px 4px;">
        <span class="stamp-button">我要發表</span>
        <article class="main_content">
          <div class="accordionPart grid">
						<section class="stamp">
							<from>
								<textarea id="main_editor" name="main_editor" rows="10"></textarea>
								<button class="btn04" style="float:right;">發布</button>
							</from>
						</section>
						<section class="grid-item">
							<div class="qa_title">
								<ul>
									<li><span class="name">數學老師</span><span class="time">2017-10-23 14:00</span><span class="info">留言數(2)</span></li>
									<li><span class="text">
										老師覺得你接受知識的能力還是挺好的。可是你有時候管不住自己，課上總愛做小動作。字嘛，有些馬虎，這種學習態度可要不得呀！老師相信你一定會改掉的，是嗎？利用假期好好練字哦！
										你平時不愛秀出自己，習慣了默默無聞，但對班級卻很關心。不過，你的字卻讓老師感到頭疼，每次都要「猜猜猜」，希望你能充分認識到這一點，把字寫得端端正正。
										真誠地希望你能在學習上激活所有的腦細胞，我們一起努力，你準備好了嗎？</span>
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
									<li><span class="name">校長</span><span class="time">2017-10-23 14:00</span><span class="info">留言數(2)</span></li>
									<li><span class="text">
										你尊敬老師，團結同學，是老師的得力助手。令老師感到欣慰的是，你上課精彩的發言，總能搏得同學的讚賞。
										不過，令人遺憾的是你的工作能力有待提高。老師真誠地希望你在工作上要向她人學習，做一名人見人愛的好學生。</span>
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
									<li><span class="name">實驗6年9班老師</span><span class="time">2017-10-23 14:00</span><span class="info">留言數(2)</span></li>
									<li><span class="text">學期成績</span></li>
									<li><img class="qaimg" src="./include/srcoe.jpg" data-zoom-image="./include/srcoe.jpg"/></li>
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
