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
</head>
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
									<li>提問</li>
									<li>回答</li>
									<li><a href="modules.php?op=modload&name=message&file=showMessage">即時訊息</a></li>
									<li><a href="modules.php?op=modload&name=message&file=communication">親師溝通</li> 
								</ul>
								<footer>
									<a href="#" class="button alt small icon fa-info-circle"> more</a>
								</footer>
							</section>
							<br/>
					</div>
				</div>
				<div class="9u 12u$(medium) important(medium)">
					<div id="content">
						<section>
							<h3>最新訊息(0)</h3>
							<input type="text"/> <input type="button" value="搜尋"/>
							<p>message...<a href="#" class="button icon fa-arrow-circle-right"> detail</a></p>
							<div>
								<table><tr><td width="200">which</td><td width="400">content</td><td>date</td></tr></table>
							</div>
							<footer>
								<a href="#" class="button icon fa-info-circle"> more message</a>
							</footer>
						</section>
						<!-- Content -->
						<article>
							<h3>回覆</h3>
							<table>
								<tr><td><textarea style="width:450px;height:100px;"></textarea></td></tr>
								<tr><td><input type="button" value="回覆"/></td></tr>
							</table>
						</article>
					</div>
				</div>

			</div>
		</div>
	</div>
</body>
</html>
