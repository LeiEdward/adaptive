<?php
//session start寫在config.php中，預設已啟動
require_once "include/config.php";
$_SESSION['fromAdmin']=0;
//echo session_id();

//170825，Peggy，用session計算在線人數
  function onLineCount($second){
     $qty = 0;
     //讀取sess_開頭的檔案，如有自定session檔前綴字元的，請自行變更
     foreach (glob(session_save_path()."/sess_*") as $sess_file){
        //session異動時間在300秒以內的，就納入在線人數
        if (filemtime($sess_file)+$second >= time()){
         ++$qty;
        }
     }
     return $qty;
  }

?>
<!doctype html>

<!--
===============================================================
檔名：	index_AIAL2.php
功能：	首頁登入
作者：	coway
異動者	日期			異動說明
----------------------------------------------------
coway	20160930	增加帳密錯誤MSG回饋
coway	20161013	更新操作流程-我是老師PPT
coway	20161017	更新操作流程-我是學生PPT
coway	20161228	增加帳密錯誤時停留至登入窗
yen     20160131    增加三層式學校關聯選單
coway	20170213	我是學生及我是老師增加系統操作影片
teresa	20170213	更新最新消息
ccoway	20170303	include feet_new.php
corn    20170323    試題修改alert
===============================================================
-->
<html lang="en">
<head>
<meta charset="UTF-8">
<title>因材網</title>
<meta name="viewport"
	content="width=device-width,initial-scale=1.0,user-scalable=yes,maximum-scale=5.0,minimum-scale=1.0">
<link href='favicon.ico' rel='icon' type='image/x-icon' />
<link rel="stylesheet" href="css/index.css" type="text/css">
<link rel="stylesheet" href="css/cms-header.css" type="text/css">
<link rel="stylesheet" href="css/cms-footer.css" type="text/css">
<link rel="stylesheet" href="css/web_style.css" type="text/css">

<script type='text/javascript' src='scripts/jquery-2.1.0.min.js'></script>

<link rel="stylesheet" href="scripts/venobox/venobox.css"
	type="text/css" media="screen" />
<script type="text/javascript" src="scripts/venobox/venobox.min.js"></script>
<script src="scripts/sweetalert.min.js"></script>
<link rel="stylesheet" type="text/css" href="scripts/sweetalert.css">
<script type="text/javascript">
	 $(document).ready(function(){
		$('.venobox').venobox({
			numeratio: true,
			infinigall: true,
			border: '0px',
		});
		$('.venoboxvid').venobox({
			bgcolor: '#000'
		});
		$('.venoboxframe').venobox({
			border: '6px'
		});
		$('.venoboxinline').venobox({
			framewidth: 'auto',
			frameheight: 'auto',
			border: '10px',
			/*bgcolor: '#000',*/
			titleattr: 'data-title',framewidth: '400px',frameheight: '550px',
		});
		//操作流程BOX
		$('.venoboxinline2').venobox({
			framewidth: 'auto',
			frameheight: 'auto',
			border: '10px',
			/*bgcolor: '#000',*/
			titleattr: 'data-title',framewidth: '100%',frameheight:'800px',
		});
		$('.venoboxajax').venobox({
			border: '30px;',
			frameheight: '220px'
		});

	})

	var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
	function getChromeVersion () {
		var raw = navigator.userAgent.match(/Chrom(e|ium)\/([0-9]+)\./);

		return raw ? parseInt(raw[2], 10) : false;
	}

	var version = getChromeVersion();
	 if(!isChrome){
		 alert('建議您使用 Chrome 瀏覽器，瀏覽影片方能順暢！');
	 }else{
		if(version < 50) alert('建議您更新 Chrome 瀏覽器的版本至最新，謝謝。');
	 }
</script>

</head>

<body>
	<div class="content-Box">
		<div class="idx-left">
			<div class="idx-nav">
				<ul>
					<li class="logo2"><img src="images/logo1.png"></li>
					<li>
						<!-- <a id="aInline" class="btn btn-warning venoboxinline"  data-title=" " data-gall="gall-frame" data-type="inline" href="#inline-content"> -->
						<a class="btn btn-warning venoboxinline" data-title="登入系統"
						data-gall="gall-frame2" data-type="iframe" href="login.php"><img
							src="images/idx-ico-1.png">登入系統</a>
					</li>
					<li><a href="http://adaptive-instruction.weebly.com/"><img
							src="images/idx-ico-2.png">教師適性教學計畫</a></li>
					<li><a class="btn btn-warning venoboxinline" data-title="因材網帳號申請"
						data-gall="gall-frame" data-type="inline"
						href="#inline-content-apply"><img src="images/idx-ico-3.png">因材網帳號申請</a></li>
				</ul>
			</div>
			<div id="dialog-form22" title="login">
				<div id="inline-content-apply" style="display: none;">
					<div
						style="background: #fff; width: 100%; max-width: 400px; margin: 0 auto; height: 100%; padding: 10px;">
						<ul>
							<li><i class="fa fa-user"></i> <a
								href="http://adaptive-instruction.weebly.com/2224026448321782341626657316492970224115343993000335531.html">學校管理帳號申請：管理並開啟教師與學生帳號</a></li>
							<li><i class="fa fa-user"></i> <a href="https://goo.gl/yorvn1">教師個人帳號申請：若學校無申請學校管理帳號，請由此管道申請</a></li>
							<li><i class="fa fa-user"></i> <a href="https://goo.gl/kU3nkL">訪客帳號申請</a></li>
						</ul>
					</div>
				</div>
				<div id="inline-content" style="display: none;">
					<!-- style="display:none;" -->
					<div
						style="background: #fff; width: 100%; max-width: 400px; margin: 0 auto; height: 100%; padding: 10px;">
						<div class="contact-line-100">
							<!-- <div class="contact-title">學校：</div> -->
							<div class="contact-data"></div>
						</div>

					</div>
				</div>
			</div>
			<div style="display: none;"></div>
		</div>


		<div class="idx-right">
			<div class="idx-main">
				<div class="open-arrow">
					<img src="images/more-btn.png">
				</div>
				<div class="news-box">
					<div class="title01">最新消息</div>
					<ul class="news">
						<li><span>2017-06-15</span><a
							href="https://www1.inservice.edu.tw/"
							title="7月17日(三)「因材網」功能操作工作坊報名表">7月17日(三)「因材網」功能操作工作坊報名表</a></li>
						<li><span>2017-06-15</span><a
							href="https://www1.inservice.edu.tw/"
							title="6月21日(三)提升數學學習領域教師適性教學素養與輔助平臺建置計畫第一次工作坊">6月21日(三)提升數學學習領域教師適性教學素養與輔助平臺建置計畫第一次工作坊</a></li>
						<li><span>2017-06-15</span><a
							href="https://www1.inservice.edu.tw/"
							title="6月21日(三)「因材網」功能操作工作坊報名表">6月21日(三)「因材網」功能操作工作坊報名表</a></li>
						<li><span>2017-02-13</span><a
							href="http://adaptive-instruction.weebly.com/"
							title="教學平臺-數學領域推廣中心學校甄選">教學平臺-數學領域推廣中心學校甄選</a></li>
						<!--                	<li><span>2017-02-07</span><a href="#">數學領域國小三年級教學影片更新</a></li>  -->
						<!--                	<li><span>2017-02-06</span><a href="#">自然科學領域-教學影片更新</a></li> -->
						<!--                	<li><span>2017-02-06</span><a href="#">語文領域-教學影片更新</a></li> -->
						<!--                	<li><span>2016-02-27</span><a href="#">教學平臺-語文領域啟用</a></li> -->

					</ul>
				</div>
				<div class="qa-box">
					<div class="title01">常見問題</div>
					<ul class="qa">
						<li><a href="#">Q&A:1.帳號申請流程</a></li>
						<li><a href="#">Q&A:2.老師/學生 功能介紹</a></li>
						<li><a href="#">Q&A:3.訪客帳號使用說明</a></li>
						<li><a href="#">Q&A:4.推廣中心申請方式</a></li>
					</ul>
				</div>
				<div class="video-box">
					<div class="title01">影音媒體</div>
					<div class="video">
						<iframe src="http://www.youtube.com/embed/QzN1WtW7FDU"
							frameborder="0"></iframe>
					</div>
				</div>
			</div>
		</div>
	</div>

	<footer class="content-Box after-0">
		<div class="footer-left">
			<span>關於我們</span>
			<div>
				<a target="_blank" href="systemIntor.php">系統特色</a><a target="_blank"
					href="teamMember.php">研發團隊</a>
			</div>
			<span>操作流程</span>
			<div>
				<a target="_blank" href="systemdoc_info.php">系統操作</a>
			</div>
		</div>
		<div class="footer-right">
			<div class="teacher-logo">
				<img src="images/teacher-logo.png">
			</div>
			<p style="color: blue; size: 20px;"><?php echo '目前在線人數：'.onLineCount(300); ?></p>
			<span>© 2016 國立臺中教育大學</span> <span>♦ 測驗統計與適性學習研究中心</span><br> <span>最佳瀏覽建議：
				Chrome 瀏覽器</span> <span>♦ 最佳解析度：1280x768</span>
		</div>
	</footer>

	<!-- about -->
	<div class="about" id="about">
		<div class="col-md-6 ab-w3-agile-info">
			<div class="ab-w3-agile-info-text">
				<h2 class="title-w3">關於我們</h2>
				<p class="sub-text one">系統特色</p>
				<p>「適性教學」(adaptive instruction)指教學的過程能配合學習者的能力與學習需求，而作因應與導引式調整。
					以提升教師「適性教學及相關數位科技教學」專業素養為主要目標，使教師透過此輔助平臺，適時掌握學生的學習需求，
					權宜的改變教學策略，能有效擬定適當的教學方案，利用各種不同的教學方法，續追蹤且評估學生學習狀況，增益個別的學習效果， 達成教學目標。
					「教師適性教學素養與輔助平臺-因材網」能協助教師有利於進行差異化教學，達成「因材施教」。</p>
				<div class="agileits_w3layouts_more menu__item one">
					<!-- <a href="#" class="menu__link" data-toggle="modal" data-target="#myModal"> -->
					<a class="btn btn-warning venoboxinline"  data-title="系統特色" data-gall="gall-frame" data-type="inline" href="#myModal">
					更多</a>
				</div>
			</div>
			<div class="ab-w3-agile-inner">
				<div class="col-md-6 ab-w3-agile-part">
					<h4>因材網的目前涵蓋領域為數學、自然與國語文，適用對象為一到九年級學童，而內容主要分成四個部分。</h4>
					<p>1.知識結構學習</p>
					<p>2.智慧適性診斷</p>
					<p>3.互動式學習</p>
					<p>4.PISA合作問題解決能力</p>
				</div>
				<div class="col-md-6 ab-w3-agile-part two">
					<h4>1.知識結構學習</h4>
					<p>依據教育部頒布的九年一貫課程綱要進行分析，將能力指標更細分成適合學習的概念節點，建置出代表學習路徑的知識結構，並以每個概念節點作為學習的單位，編製概念教學媒體、診斷試題與互動式教學等。</p>
					<div class="agileits_w3layouts_more menu__item one">
					<a href="#" class="menu__link" data-toggle="modal"
						data-target="#myModal">更多</a>
					</div>
					<h4>2.智慧適性診斷</h4>
					<p>在施行適性學習時，能先透過適性診斷的功能，掌握學習的弱點，能讓學習成效事半功倍。</p>
					<div class="agileits_w3layouts_more menu__item one">
					<a href="#" class="menu__link" data-toggle="modal"
						data-target="#myModal">更多</a>
					</div>
					<h4>3.互動式學習</h4>
					<p>在九年一貫能力指標中，也包含實作型的教學指標，系統也提供互動式的教學元件，能依據學生的操作歷程，適時地給予回饋，而此類工具常被教師在翻轉教室或ICT融入教學中應用。</p>
					<div class="agileits_w3layouts_more menu__item one">
					<a href="#" class="menu__link" data-toggle="modal"
						data-target="#myModal">更多</a>
					</div>
					<h4>4.PISA合作問題解決能力</h4>
					<p>設計不同領域（科學、數學、閱讀、綜合）的合作問題解決單元，讓學生與電腦夥伴進行互動，一起進行合作問題解決，評估學生是否具備與他人合作共同解決問題的能力。</p>
					<div class="agileits_w3layouts_more menu__item one">
					<a href="#" class="menu__link" data-toggle="modal"
						data-target="#myModal">更多</a>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
		<!-- <div class="col-md-6 ab-w3-agile-img">
	     
	  </div> -->

		<div class="clearfix"></div>
	</div>
	<!-- //about -->
	<!-- Modal1 -->
	<div id="myModal" style="display:none;">
	<!-- <div class="modal fade" id="myModal" tabindex="-1" role="dialog"> -->
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<!-- <img src="images/2.jpg" alt=" " class="img-responsive"> -->
					<h5>本適性教學素養輔助平台目標為減輕教師教學負擔，提升教師適性教學素養，精確掌握學生學習需求，擬定適當教學策略，以下為其目的及功能：</h5>
					<p>1.藉由電腦化適性診斷測驗，診斷學生學習成效，立即回饋教師教學成效。</p>
					<p>2.藉由電腦化適性診斷測驗，診斷學生學習成效，達到「因材施測」，提升測驗效率，且能提供跨年級之學習診斷結果。</p>
					<p>3.能自動化提供學生「個別化學習路徑」，達到「因材施教」的效果，輔助教師調整教學方式級策略，提升教師教學效能。</p>
					<p>4.整合「教學媒體」、「診斷測驗」及「互動式教學輔助元件」，適時輔助教師現場教學。</p>
					<p>5.可繼續擴增各學科之教學元件素材，擴大辦理以提供各學科教師進行適性教學。</p>
				</div>
			</div>
		</div>
	</div>
	<!-- //Modal1 -->

	<div class="testmonials" id="monials">
		<div id="particles-js1"></div>
		<div class="client-top">
			<h3 class="title-w3 three"></h3>
			<p class="sub-text">研發團隊</p>
			<div class="slider">
				<div class="callbacks_container">
					<ul class="rslides" id="slider3">
						<li>
							<div class="agileits-clients">

								<div class=" client_agile_info">

									<div class="c-img">
										<h4>
											<i class="fa fa-quote-right"></i>適性教學教材研發基地學校
										</h4>
									</div>
									<p>
										臺北市：市立胡適國小、市立金華國小、市立介壽國中 <br>桃園市：市立大崙國小 ；新竹市：市立龍山國小 <br>臺中市：市立四張犁國小、市立大智國小、市立潭陽國小、市立北屯國小、市立西岐國小、市立德化國小、市立惠文高中
										；彰化縣：縣立田中國小、縣立東山國小、縣立湳雅國小 <br>南投縣：縣立營盤國小、縣立漳興國小、縣立德興國小、縣立溪南國小、縣立社寮國中、縣立集集國中、縣立草屯國中
										；高雄市：市立獅甲國中
									</p>
									<h4>
										<img src="images/m1.jpg" alt="">
									</h4>

								</div>

							</div>
						</li>
						<li>
							<div class="agileits-clients">

								<div class="client_agile_info">

									<div class="c-img">
										<h4>
											<i class="fa fa-quote-right"></i>數學領域適性教學推廣中心學校
										</h4>
									</div>
									<p>
										新竹縣：新竹縣縣立東海國小 <br>臺中市：臺中市市立潭陽國小、臺中市市立四張犁國小、臺中市市立大智國小、臺中市市立惠文高中
										；彰化縣：彰化縣縣立田中國小 <br>南投縣：南投縣縣立社寮國中 ​ ；雲林縣：雲林縣縣立崇德國中
										；臺南市：臺南市市立東陽國小 ；高雄市：高雄市市立河濱國小、高雄市市立興仁國中
									</p>
									<h4>
										<img src="images/m1.jpg" alt="">
									</h4>

								</div>
								<div class="clearfix"></div>
							</div>
						</li>
						<li>
							<div class="agileits-clients">
								<div class=" client_agile_info">

									<div class="c-img">
										<h4>
											<i class="fa fa-quote-right"></i>語文領域適性教學推廣中心學校
										</h4>
									</div>
									<p>
										新竹縣：新竹縣縣立中山國小、新竹縣縣立中正國中 ；臺中市：臺中市市立北屯國小 <br>彰化縣：彰化縣縣立東山國小、彰化縣縣立大興國小、彰化縣縣立社頭國中
										；南投縣：南投縣縣立大成國小、南投縣縣立德興國小、南投縣縣立溪南國小 <br>高雄市：高雄市市立南成國小、高雄市市立國昌國中
									</p>
									<h4>
										<img src="images/m1.jpg" alt="">
									</h4>

								</div>

							</div>
						</li>
						<li>
							<div class="agileits-clients">
								<div class=" client_agile_info">

									<div class="c-img">
										<h4>
											<i class="fa fa-quote-right"></i>自然科學領域適性教學推廣中心學校
										</h4>
									</div>
									<p>
										基隆市：基隆市市立中華國小 ；臺北市：臺北市市立金華國小 ；新竹市：新竹市市立南寮國小 <br>臺中市：臺中市市立德化國小、臺中市市立竹林國小
										；彰化縣：彰化縣縣立聯興國小 ；南投縣：南投縣縣立營盤國小 ；高雄市：高雄市市立佛公國小 <br>屏東縣：屏東縣國立屏東大學附小
										；金門縣：金門縣縣立賢庵國小
									</p>
									<h4>
										<img src="images/m1.jpg" alt="">
									</h4>

								</div>

							</div>
						</li>
						<li>
							<div class="agileits-clients">
								<div class=" client_agile_info">

									<div class="c-img">
										<h4>
											<i class="fa fa-quote-right"></i>數學領域團隊成員
										</h4>
									</div>
									<p>
										郭伯臣 特聘教授兼院長 ；施淑娟 教授兼所長 ；李政軒 助理教授 ；楊智為 助理教授 <br>吳慧珉 副研究員 ；鄭俊彥
										助理 ​ ；洪裕堂 教師 ；陳俊華 教師
									</p>
									<h4>
										<img src="images/m1.jpg" alt="">
									</h4>

								</div>

							</div>
						</li>
						<li>
							<div class="agileits-clients">
								<div class=" client_agile_info">

									<div class="c-img">
										<h4>
											<i class="fa fa-quote-right"></i>語文領域團隊成員
										</h4>
									</div>
									<p>
										楊裕貿 副教授兼系主任 ；許文獻 助理教授 ；蔡喬育 助理教授 <br>郭伯臣 特聘教授兼院長 ；陳靜儀 教師 ；林思妏
										教師
									</p>
									<h4>
										<img src="images/m1.jpg" alt="">
									</h4>

								</div>

							</div>
						</li>
						<li>
							<div class="agileits-clients">
								<div class=" client_agile_info">

									<div class="c-img">
										<h4>
											<i class="fa fa-quote-right"></i>自然科學領域團隊成員
										</h4>
									</div>
									<p>
										吳穎沺 副教授 ；郭伯臣 特聘教授兼院長 ；張正杰 助理教授 <br>林佳慶 助理教授 ；楊宗榮 主任/教師/輔導員
									</p>
									<h4>
										<img src="images/m1.jpg" alt="">
									</h4>

								</div>

							</div>
						</li>
						<li>
							<div class="agileits-clients">
								<div class=" client_agile_info">

									<div class="c-img">
										<h4>
											<i class="fa fa-quote-right"></i>因材網平台建置團隊
										</h4>
									</div>
									<p>
										曾彥鈞 教師 <br>江鴻鈞 教師 ；白宗恩 教師 ；張嫄萱 助理；王明昱 助理<br>施宏毅 助理；劉俊杰 助理；梁依婷
										助理
									</p>
									<h4>
										<img src="images/m1.jpg" alt="">
									</h4>

								</div>

							</div>
						</li>
						<li>
							<div class="agileits-clients">
								<div class=" client_agile_info">

									<div class="c-img">
										<h4>
											<i class="fa fa-quote-right"></i>AutoTutor 建置團隊
										</h4>
									</div>
									<p>
										郭伯臣 教授 ；廖晨惠 教授 ；白鎧誌 助理 ；陳穎相 助理 ；劉音美 教師 <br>陳雯君 教師 ；陳慧芬 教師 ；林育正
										教師 ；林信良 教師 ；陳姿蓉 教師 ；蔣心宜
									</p>
									<h4>
										<img src="images/m1.jpg" alt="">
									</h4>

								</div>

							</div>
						</li>
						<li>
							<div class="agileits-clients">
								<div class=" client_agile_info">

									<div class="c-img">
										<h4>
											<i class="fa fa-quote-right"></i>CPS 建置團隊
										</h4>
									</div>
									<p>
										郭伯臣教授 ；廖晨惠教授 ；施淑娟教授 ；李政軒助理教授 ；白鎧誌助理 ；劉志勇助理 ；白嘉郁教師 <br>李盈嫻教師
										；何美緻教師 ；楊靜怡教師 ；林宜儂教師 ；陳貞妙教師 ；吳惠琴教師 ；劉語文 ；張琇涵
									</p>
									<h4>
										<img src="images/m1.jpg" alt="">
									</h4>

								</div>

							</div>
						</li>
					</ul>
				</div>
			</div>

		</div>
	</div>

	<div id="gotop">
		<a class="fa fa-chevron-up"></a>
	</div>

	<script type="text/javascript" src="scripts/customer.js"></script>
<?php //維護試題排程alert  _MAINTAIN_TIME_START,_MAINTAIN_TIME_STOP
	//date_default_timezone_set('Asia/Taipei');
	//$now = strtotime(now)."<br>";
	$now = strtotime(now);
	$start = date("Y-m-d");
	//$start." 22:00:00";
	//$start." 02:00:00";
	$ts = strtotime($start." "._MAINTAIN_TIME_START);
	$te = strtotime($start." "._MAINTAIN_TIME_STOP);
	$_SESSION['duringMaintainPeriod']=0;
	if($now<=$te || $now>=$ts){
		$_SESSION['duringMaintainPeriod']=1;
		echo "<script>";
		echo "swal('系統維護公告','維護試題時間為"._MAINTAIN_TIME_START."~凌晨"._MAINTAIN_TIME_STOP."')";
		echo "</script>";
	}
?>
	<script>
	//swal("伺服器維修公告","維修時間為106年7月1日(星期六)凌晨12點\n至106年7月3日(星期一)早上9點")
	
	$(document).ready(function(){
	    $(".idx-main").hover(function(){
	  		$(".idx-main").toggleClass("open-close");
		});
	});
	</script>

	<script src="scripts/responsiveslides.min.js"></script>
	<script>
		// You can also use "$(window).load(function() {"
		$(function () {
		  // Slideshow 4
		  $("#slider3").responsiveSlides({
			auto: true,
			pager:false,
			nav:true,
			speed: 500,
			namespace: "callbacks",
			before: function () {
			  $('.events').append("<li>before event fired.</li>");
			},
			after: function () {
			  $('.events').append("<li>after event fired.</li>");
			}
		  });
	
		});
	 </script>

	<script type="text/javascript" src="scripts/jsManage.js"></script>

</body>
</html>