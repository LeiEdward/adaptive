<?php
	session_start();

  //包含需求檔案 ------------------------------------------------------------------------
	include("./include/common_lite.php");
	$showButton = false;
  $user_id=$_SESSION['user_id'];

	//宣告變數 ----------------------------------------------------------------------------
	$ODb = new run_db("mysql",3306);      //建立資料庫物件

	//取出單元資料
	$sql_dsc = "select * from `main_data`";//管理員資料
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$title_array[$row['num']] = $row['c_title'];
		switch($row['c_module_type']){
		case "science":
		$module_type_array[$row['num']] = "view_test.php";
		break;
		case "mathematics":
		$module_type_array[$row['num']] = "view_test_m.php";
		break;
		case "read":
		$module_type_array[$row['num']] = "view_test_r.php";
		break;
		}
	}

	$NameArray = array('0'=>'練習題');

	//取出測驗資料
	$sql_dsc = 'select * from opt_record WHERE stuid="'.$user_id.'" order by num desc';
	$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
	while($row = mysql_fetch_array($res)){
		$test_data['num'] = $row['num'];
		$test_data['main_data_num'] = $row['main_data_num'];
		$test_data['test_begin_time'] = $row['test_begin_time'];
		$test_data['up_date'] = $row['up_date'];
		$test_data['power_dsc'] = $row['power_dsc'];
		$DataArray[$row['timelist_num']][] = $test_data;
		if(!isset($testNameArray[$row['timelist_num']])){
		$NameArray[$row['timelist_num']]= $row['timelist_dsc'];
		}
	}

	function getUserName($tableDsc,$key){
		$ODb = new run_db("mysql",3306);      //建立資料庫物件
		$sql_dsc = "select `c_name` from `".$tableDsc."` where `num`='".$key."'";
		$res=$ODb->query($sql_dsc) or die("載入資料出錯，請聯繫管理員。");
		while($row = mysql_fetch_array($res)){
			return $row['c_name'];
		}
		$ODb->close();
		return '';
	}
	$ODb->close();



?>

<!-- <html xmlns="http://www.w3.org/1999/xhtml"> -->
<!-- <head> -->
<!-- <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> -->
<!-- <title>合作問題解決數位學習系統</title> -->
<!--<script src="./modules/ExamResult/CPS/js/jquery-1.10.1.min.js"> </script> -->
<!--<script src="./modules/ExamResult/CPS/js/javascript.js"></script> 頁面收和 -->
<!-- <script src="./modules/ExamResult/CPS/js/jquery-ui.js"></script> -->
<script src="./modules/ExamResult/CPS/js/jquery.colorbox.js"></script>
<!-- <link rel="stylesheet" href="./modules/ExamResult/CPS/css/admin.css" /> -->
<link rel="stylesheet" href="./modules/ExamResult/CPS/css/colorbox.css" />
<!-- <link rel="stylesheet" href="./modules/ExamResult/CPS/css/jquery-ui.css" /> -->
<!-- <link rel="Stylesheet" href="./modules/ExamResult/CPS/css/jquery-ui-1.7.1.custom.css" type="text/css" /> -->
<script language="javascript">
	//顯示能力值
	function show_msg_box(getValue,getValue2){
		$.ajax({
			url: './modules/ExamResult/get_testResultsList.php',
			data: {num:getValue,snum:getValue2,swType:'oneData'},
			type:"POST",
			error: function(xhr) {
				//console.log(xhr);
				alert('Ajax request 發生錯誤');
			},
			success: function(response) {
				$('#show_msg_box').append(response).show();
				$.colorbox({inline:true,href:"#show_msg_box", width:"50%",height:"80%",open:true,onClosed:function(){
				$('#show_msg_box').html('').hide();
				}});
			}
		});
	}

	//顯示總分數
	function show_total_box(getValue,getValue2){
		$.ajax({
			url: './modules/ExamResult/get_testResultsList.php',
			data: {num:getValue,snum:getValue2,swType:'totalData'},
			type:"POST",
			error: function(xhr) {
				//console.log(xhr);
				//alert('Ajax request 發生錯誤');
			},
			success: function(response) {
				$('#show_msg_box').append(response).show();
				$.colorbox({inline:true,href:"#show_msg_box", width:"50%",height:"80%",open:true,onClosed:function(){
				$('#show_msg_box').html('').hide();
				}});
			}
		});
	}

	$(document).ready(function() {
	<?php
		if($_GET['tr']!=''){
		echo "$('#div_area_".$_GET['tr']."').slideDown('fase');";
		}
	?>
	});
</script>
</head>


<div id="wrapper">
<?php
$outNum = 0;
foreach($NameArray as $myKey => $myDSC) {
?>

<!-- 單元 → 試題列表 -->
    <div class="content2-Box" id="div_area_<?php echo $myKey;?>">
    	<div class="path">目前位置：診斷報告(CPS)</div>
			<div class="choice-box">
      <div class="choice-title">診斷</div>
        <ul class="choice work-cholic">
      	  <li><a href="modules.php?op=modload&name=ExamResult&file=classReports"><i class="fa fa-caret-right"></i>診斷報告</a></li>
      		<li><a href="modules.php?op=modload&name=ExamResult&file=record_list" class="current"><i class="fa fa-caret-right"></i>診斷報告(CPS)</a></li>
        </ul>
 		 </div>
    	<div class="main-box">
    	<div class="title01"><?php echo $user_id;?>歷來測驗單元</div>
    	<div class="table_scroll">
        <table class="datatable datatable-l">
            <tr>
				<th width="10%">編號</th>
				<th width="22%">測驗單元名稱</th>
				<th width="20%">能力指標分數</th>
        		<th width="20%">教學影片</th>
            </tr>
			<?php
			if(is_array($DataArray[$myKey])){
				$num =1;
				foreach($DataArray[$myKey] as $key2 => $value2){ ?>
				<tr>
                <td width="10%"><?php echo $num;?></td>
                <td width="22%"><p class="name"><?php echo $title_array[$value2['main_data_num']];?></p></td>
                <td width="20%"><div align="center"><a class="button" onclick="show_msg_box('<?php echo $value2['num'];?>','<?php echo $_GET['s'];?>')">觀看成績</a></div></td>
                <td width="18%">
                <?php
                 switch($value2['main_data_num']){
                       case "66":
                            echo '<a href="https://drive.google.com/file/d/0B1Lin3duEDn5bXVWY3Z2clBGc0E/view" title="觀看教學影片" target="_blank" class="button">觀看</a>';
                       break;
                       case "69":
                            echo '<a href="https://drive.google.com/file/d/0B-LBLzmLJni-Q3VBZUpTdGhTTm8/view" title="觀看教學影片" target="_blank" class="button">觀看</a>';
                       break;
                       case "71":
                            echo '<a href="https://drive.google.com/file/d/0B1Lin3duEDn5ekZ1X1lmQy1fN0U/view" title="觀看教學影片" target="_blank" class="button">觀看</a>';
                       break;
                       case "74":
                            echo '<a href="https://drive.google.com/file/d/0B1Lin3duEDn5RndQQUlmaU1iSjQ/view" title="觀看教學影片" target="_blank" class="button">觀看</a>';
                       break;
                       case "80":
                            echo '<a href="https://drive.google.com/file/d/0B1Lin3duEDn5WXRUaTdmM1JnMXM/view" title="觀看教學影片" target="_blank" class="button">觀看</a>';
                       break;
                       case "86":
                            echo '<a href="https://drive.google.com/file/d/0B1Lin3duEDn5MTZmd29JNjJGLUE/view" title="觀看教學影片" target="_blank" class="button">觀看</a>';
                       break;
                       case "87":
                            echo '<a href="https://drive.google.com/file/d/0B1Lin3duEDn5VTFtYUMwbDhTa28/view" title="觀看教學影片" target="_blank" class="button">觀看</a>';
                       break;
                       case "107":
                            echo '<a href="https://drive.google.com/file/d/0B1Lin3duEDn5QmFIZ0hfWUdHcDg/view" title="觀看教學影片" target="_blank" class="button">觀看</a>';
                       break;
                       case "111":
                            echo '<a href="https://drive.google.com/file/d/0B1Lin3duEDn5WmlOc2RHTGVWRzg/view" title="觀看教學影片" target="_blank" class="button">觀看</a>';
                       break;
                       default:
                       break;
                 }


                ?>
               </td>
          </tr>
			<?php
				$num++;
				}
			}

			?>
        </table>
        </div>
        </div>
    </div>
<!-- 單元 → 試題列表 end -->

<?php
	$outNum++;
}
?>
</div>
<div id="show_msg_box" style="display:none;">

</div>
