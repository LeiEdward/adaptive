<?php
include_once "include/config.php";
include_once 'include/adp_core_function.php';

if (!isset($_SESSION)) {
	session_start();
}

?>
<!-- <!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Assign Mission(任務指派)</title> -->
<style>
	.scrollit {
    	overflow:scroll;
    	height:200px;
	}
</style>

<script type="text/javascript">

function ouputFile(str,url) { 
	if (str == "") {
	   //document.getElementById("showQuiz").innerHTML = "";
	   return;
	}
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
	   xmlhttp = new XMLHttpRequest();
	} else {// code for IE6, IE5
	   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) { //傳回值是固定寫法
		   //document.getElementById("showQuiz").innerHTML = xmlhttp.responseText; //[最後]把select出來的資料 傳回前面指定的html位置
		   //alert(xmlhttp.responseText);
			console.log(xmlhttp.responseText);
			
		}else console.log('error');
	}
	xmlhttp.open("GET", "modules/learn_video/prodb_mission_record.php?indicator=" + str, true);
	xmlhttp.send();
}

function ouputShowLine(str,url){
	var a = document.getElementById('video_report'); //or grab it by tagname etc
	a.href = url;
	//ouputFile(str,url);
	//window.open(url, 'external', 'left=180,top=50');
	console.log('ouputShowLine');
}

$(function() {

	$("#year_search").click(function(){
		var yearnm = $("#year_search").val();
		
		$.ajax({
			type : "POST" ,
			url : "modules/assignMission/prodb_assignment_data.php" ,
			data :{type: '2', yearnm: yearnm },
			dataType:'JSON',
			success: function(returndata2){
				var i = 0;
	        	  $("#miss_search").empty();
	        	  $("#miss_search").append("<option value=\"all\">全部</option>");
	        	  $.each(returndata2, function() {
	        		  /*$("#miss_search").append("<option label=\"\" value=\"" + returndata2[i].organization_id + "-" +  returndata2[i].grade + returndata2[i].class +
	    	        	"\">" + returndata2[i].grade + "年" + returndata2[i].class + "班" + "</option>"); */	
	        		  $("#miss_search").append($('<option>', {value:returndata2[i].organization_id + "-" +  returndata2[i].grade + returndata2[i].class}).text(returndata2[i].grade + "年" + returndata2[i].class + "班"));
	        		  i++;			
	        	  });
			},
			error: function(xhr, ajaxOptions, thrownError){
				alert(xhr.status);//0: 请求未初始化
				alert(thrownError);
			}
		});
			
	});	

	
	$("#miss_search").click(function(){
		var classnm = $("#miss_search").val();
		var yearnm = $("#year_search").val();
		console.log(classnm);
		$.ajax({
			type : "POST" ,
			url : "modules/assignMission/prodb_assignment_data.php" ,
			data :{type: '1', classnm: classnm, yearnm: yearnm },
			dataType:'JSON',
			success: function(returndata){
				console.log(returndata);
				console.log(classnm);
				console.log(yearnm);
				var i = 0;
	        	  $("#miss_select").empty();
	        	  $("#miss_select").append("<option value=\" \">請選擇</option>");
	        	  $.each(returndata, function() {
	        		  /*$("#miss_select").append("<option label=\"\" value=\"" + returndata[i].mission_sn + 
	    	        	"\">" + returndata[i].mission_nm + "</option>");*/	
	        		  $("#miss_select").append($('<option>', {value:returndata[i].mission_sn}).text(returndata[i].mission_nm));
	        		  i++;			
	        	  });
			},
			error: function(xhr, ajaxOptions, thrownError){
				alert(xhr.status);//0: 请求未初始化
				alert(thrownError);
			}
		});
			
	});	
});
</script>
</head>
<?php 
$NowSeme = getYearSeme();

$indicator = array();
if($user_data->class_name < 9){
	$class_no = '0'.$user_data->class_name;
}else $class_no = $user_data->class_name;

$target_class = $user_data->organization_id.'-'.$user_data->grade.$class_no;
$class_nm = $user_data->grade.'年'.$user_data->class_name.'班';

$date = date ("Y-m-d");
$user_id = $_SESSION['user_id'];
$this_seme = getYearSeme();
$this_Year = getNowSemeYear();
$date_start = date("Y-m-d",mktime(23,59,59,date("m")-2,date("d"),date("Y")));
$date_end = date("Y-m-d",mktime(23,59,59,date("m"),date("d"),date("Y")+1));

if(isset($_REQUEST["search_start"])&&isset($_REQUEST["search_end"])){
	$search_start = $_REQUEST["search_start"];
	$search_end = $_REQUEST["search_end"];
}else{
	$search_start = $date_start;
	$search_end = $date_end;
}

//教師有教學的學期
$seme=$dbh->prepare("SELECT DISTINCT seme_year_seme FROM `seme_teacher_subject`
		where teacher_id=:teacher_id
		ORDER BY `seme_year_seme` desc");
$seme->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
$seme->execute();
$teach_seme=$seme->fetchAll(PDO::FETCH_COLUMN);
$teach_count =$seme->rowCount();

//教師教授班級
$teach_class = $dbh->prepare("SELECT DISTINCT teacher_id, grade, class FROM seme_teacher_subject a
		WHERE organization_id = :organization_id AND teacher_id = :teacher_id AND seme_year_seme = :seme");
$teach_class->bindValue(':organization_id', $user_data->organization_id, PDO::PARAM_INT);
$teach_class->bindValue(':teacher_id', $user_id, PDO::PARAM_INT);
$teach_class->bindValue(':seme', $this_seme, PDO::PARAM_INT);
$teach_class->execute();
$teach_sub = $teach_class->fetchAll(\PDO::FETCH_ASSOC);
$class_count = $teach_class->rowCount();

//班級人數(已經沒有在用)
$class_num = $dbh->prepare("SELECT COUNT(*) class_num FROM user_info a, user_status b
		WHERE a.user_id = b.user_id and b.access_level IN ('1','9','8','4') and organization_id = :organization_id AND grade = :grade AND class = :class");
$class_num->bindValue(':organization_id', $user_data->organization_id, PDO::PARAM_INT);
$class_num->bindValue(':grade', $user_data->grade, PDO::PARAM_INT);
$class_num->bindValue(':class', $user_data->class_name, PDO::PARAM_INT);
$class_num->execute();
$sub_classnum = $class_num->fetch();

//任務查詢
$mission_str = "SELECT DISTINCT a.mission_sn, a.mission_nm, a.target_type, a.node, b.nodes_pass, b.all_pass, a.target_id, a.mission_type, a.semester, a.subject_id, a.exam_type  
		FROM mission_info as a left JOIN mission_result as b on (a.mission_sn = b.mission_sn)
		WHERE unable=1 AND end_mk='N' AND teacher_id = :teacher_id "; //, b.user_id

if(isset($_REQUEST["search_end"]) && isset($_REQUEST["search_start"])){
	$mission_str = $mission_str." AND (date =:nodate OR (date >= :start AND date <= :end))";//87
}
$mission_str = $mission_str." ORDER BY a.mission_sn DESC";
$mission_data = $dbh->prepare($mission_str);
$mission_data->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);


if(isset($_REQUEST["search_end"]) && isset($_REQUEST["search_start"])){
	$mission_data->bindValue(':nodate', '0000-00-00', PDO::PARAM_STR);
	$mission_data->bindValue(':start', $_REQUEST["search_start"].' 00:00:00', PDO::PARAM_STR);
	$mission_data->bindValue(':end', $_REQUEST["search_end"].' 00:00:00', PDO::PARAM_STR);
}

$mission_data->execute();
$sub_mission = $mission_data->fetchAll(\PDO::FETCH_ASSOC);
//選擇任務後，再進行任務進度查詢
if(isset($_REQUEST["miss_select"])&&($_REQUEST["miss_select"] !='')){
	
	$mission_result = array();
	
	$class_count2 = update_data($dbh, $_REQUEST["miss_select"]);
	$unfinish = finish_count($dbh, $_REQUEST["miss_select"]);
	$mission_result[$_REQUEST["miss_select"]]["finish"] = $unfinish;
	foreach ($sub_mission as $key=>$value){
		if($value[mission_sn] == $_REQUEST["miss_select"]){
			$data = $sub_mission[$key];
			break;
		}
	}
	if($data["target_type"] =='C'){
		$finish = $class_count2;//$sub_classnum["class_num"];
		$mission_result[$_REQUEST["miss_select"]]["total"] = $finish;
		$target_arr = explode('-', $data["target_id"]);
		$class = (int)substr($target_arr[1], -2);
		$grade = substr($target_arr[1], 0, -2);
		$tclass = $grade."X@@X".$class;
		$mission_cht=$grade."年".$class."班";
		$seme=$data["semester"];  
	}else {
		$target_arr = explode(_SPLIT_SYMBOL, $data["target_id"]);
		$stuname = studentname($dbh,implode(",",$target_arr));
		
		/*if(empty($target_arr[count($target_arr)]) && count($target_arr)>1 ){
			unset($target_arr[count($target_arr)-1]);
		}*/
		if(end($target_arr)==''){ array_pop($target_arr);}
		$finish = count($target_arr);
		$mission_result[$_REQUEST["miss_select"]]["total"] = $finish;
		$tclass = $tclass_p;
		$mission_cht=implode(",",$stuname);
		$seme=$data["semester"];
		//echo 'total:'.$finish.',unfinish:'.$unfinish;
	}
		
	$tmp_arr = explode(_SPLIT_SYMBOL, $data["node"]);
	if($data["mission_type"] != '0'){
		foreach ($tmp_arr as $tmp_key=>$tmp_value){
			$tmp_arr[$tmp_key] = substr($tmp_value,0,6);
			
		}
		$bnode = implode(_SPLIT_SYMBOL,$tmp_arr);
	}else{
		$bnode='';
		foreach ($tmp_arr as $tmp_key=>$tmp_value){
			$tmp_exam = exam_ind($dbh, $tmp_value);
			$tmp_bnode = implode(_SPLIT_SYMBOL,$tmp_exam);
			if($tmp_key >1){
				$bnode = $tmp_bnode._SPLIT_SYMBOL.$bnode;
			}else $bnode = $tmp_bnode.$bnode;
				
		}
	
	}
		
	switch ($data["mission_type"])
	{
		case 0:
			$sn_type=0;
			$ep_id = $data["node"];
			$missiontype_cht="單元式診斷";
			break;
		case 1:
			$sn_type=1;
			$missiontype_cht="知識結構學習";
			break;
		case 2:
			$sn_type=2;
			$missiontype_cht="學習問卷";
			break;
		case 3:
			$sn_type=3;
			if($data["exam_type"]==0) $missiontype_cht="縱貫式診斷 (診斷方式：全測)";
			if($data["exam_type"]==1) $missiontype_cht="縱貫式診斷 (診斷方式：適性省題)";
			if($data['subject_id'] == 2){
				$ep_id = '02602189901';
			}elseif($data['subject_id'] == 1){
				$ep_id = '02601189901';
			}elseif($data['subject_id'] == 3){
				$ep_id = '02603189901';
			}
			break;
	}
	
	
	$report_url = "modules.php?op=modload&name=learn_video&file=reportVideo_reviewRecord&screen=frame&findnode=$bnode&teach_class=$tclass&seme=$seme";
	$diss_url = "modules.php?op=modload&name=learn_video&file=videoDiscuss&screen=frame&findnode=$bnode";
	$report2_url = "modules/assignMission/assign_report.php?sn=$_REQUEST[miss_select]&seme=$seme";
	$report3_url = "modules.php?op=modload&name=assignMission&file=mission_classStatistics&screen=frame&sn=$_REQUEST[miss_select]&class_ep=$ep_id";
	$mission_result[$_REQUEST["miss_select"]]["bnode"] = $bnode;
	$mission_result[$_REQUEST["miss_select"]]["report"] = $report_url;
	$mission_result[$_REQUEST["miss_select"]]["diss"] = $diss_url;
	$mission_result[$_REQUEST["miss_select"]]["report2"] = $report2_url;
	$mission_result[$_REQUEST["miss_select"]]["report3"] = $report3_url;
	$mission_result[$_REQUEST["miss_select"]]["unfinish"] = $finish-$unfinish;
	$mission_result[$_REQUEST["miss_select"]]["mission_cht"] = $mission_cht;
	$mission_result[$_REQUEST["miss_select"]]["missiontype_cht"] = $missiontype_cht;
	$mission_result[$_REQUEST["miss_select"]]["mission_type"] = $sn_type;
		
}

function exam_ind($dbh2, $ep_id){
	$ind_data = $dbh2->prepare("SELECT DISTINCT substr(indicator, 1,6) indicator 
			FROM `concept_item` WHERE exam_paper_id = :ep_id");
	$ind_data->bindValue(':ep_id', $ep_id, PDO::PARAM_STR);
	$ind_data->execute();
	$sub_inddata = array();
	while ($d = $ind_data->fetch()) {
		array_push($sub_inddata, $d['indicator']);
	}
	return $sub_inddata;
}

//已完成人數
function finish_count($dbh2, $ms_sn){
	$finish_num = 0;
	$finish_data = $dbh2->prepare("SELECT COUNT(*) num FROM mission_result WHERE mission_sn = :ms_sn AND all_pass = :all_pass");
	$finish_data->bindValue(':ms_sn', $ms_sn, PDO::PARAM_INT);
	$finish_data->bindValue(':all_pass', '1', PDO::PARAM_INT);
	$finish_data->execute();
	$sub_finish = $finish_data->fetch(\PDO::FETCH_ASSOC);
	return $sub_finish["num"];
}

function update_finish($dbh2, $user, $ind){
	$ind = $ind.'%';
	$finish_data2 = $dbh2->prepare("SELECT a.user_id, b.indicator, MAX(finish_rate) rate FROM `video_review_record` a, video_concept_item b 
			WHERE a.video_item_sn = b.video_item_sn AND a.user_id = :user_id AND b.indicator like :indicator
			GROUP BY a.user_id, b.indicator HAVING MAX(finish_rate) = 100");
	$finish_data2->bindValue(':user_id', $user, PDO::PARAM_INT);
	$finish_data2->bindValue(':indicator', $ind, PDO::PARAM_INT);
	$finish_data2->execute();
	$sub_finish2 = $finish_data2->fetchAll(\PDO::FETCH_ASSOC);
	if(count($sub_finish2) > 0){
		return true;
	}else return false;
}

function studentname($dbh2,$userArr)//學生名稱 Blue0523
{
	$name=$dbh2->prepare("SELECT uname FROM `user_info` WHERE FIND_IN_SET(user_id, :user)");
	$name->bindValue(':user', $userArr, PDO::PARAM_STR);
	$name->execute();
	$student_name=$name->fetchAll(PDO::FETCH_COLUMN);
	
	return $student_name;
}

function update_finish2($dbh2, $user, $cs_id, $vol){
	$finish_data2 = $dbh2->prepare("SELECT * FROM `exam_record` WHERE user_id = :user_id AND cs_id = :cs_id AND paper_vol = :vol");
	$finish_data2->bindValue(':user_id', $user, PDO::PARAM_INT);
	$finish_data2->bindValue(':cs_id', $cs_id, PDO::PARAM_STR);
	$finish_data2->bindValue(':vol', $vol, PDO::PARAM_INT);
	$finish_data2->execute();
	$sub_finish2 = $finish_data2->fetchAll(\PDO::FETCH_ASSOC);
	if(count($sub_finish2) > 0){
		return true;
	}else return false;
}

function update_finish3($dbh2, $user, $ms_sn){
	$finish_data2 = $dbh2->prepare("SELECT a.user_id, a.cs_id FROM `exam_record_indicate` a 
			WHERE a.user_id = :user_id AND a.result_sn = :result_sn ");
	$finish_data2->bindValue(':user_id', $user, PDO::PARAM_STR);
	$finish_data2->bindValue(':result_sn', $ms_sn, PDO::PARAM_STR);
	//$finish_data2->bindValue(':vol', (int)$vol, PDO::PARAM_INT);
	$finish_data2->execute();
	$sub_finish2 = $finish_data2->fetchAll(\PDO::FETCH_ASSOC);
	if(count($sub_finish2) > 0){
		return true;
	}else return false;
}

function update_data($dbh2, $ms_sn){
	global $tclass_p;
	$pass = false;
	$finish_data3 = $dbh2->prepare("SELECT a.node, a.target_id, a.target_type, a.mission_type, a.semester FROM mission_info a
			WHERE mission_sn = :ms_sn");
	$finish_data3->bindValue(':ms_sn', $ms_sn, PDO::PARAM_INT);
	$finish_data3->execute();
	$sub_finish3 = $finish_data3->fetch(\PDO::FETCH_ASSOC);
	
	$node_arr = explode(_SPLIT_SYMBOL, $sub_finish3['node']);
	//echo print_r($node_arr);
	if(empty($node_arr[count($node_arr)]) && count($node_arr)>1 ){
		unset($node_arr[count($node_arr)-1]);
	}
	//print_r($node_arr);
	$query_str="SELECT a.stud_id as user_id, a.grade, a.class FROM seme_student a, user_status b
		WHERE a.stud_id = b.user_id and b.access_level IN ('1','9','8','4') "; //user_info
	if($sub_finish3["target_type"] =='C'){
		$target = explode('-',$sub_finish3["target_id"]);
		$query_str = $query_str.' AND organization_id = :organization_id AND seme_year_seme=:seme AND grade = :grade AND class = :class';
	}else{
		$target_arr = explode(_SPLIT_SYMBOL, $sub_finish3["target_id"]);
		
		/*if(empty($target_arr[count($target_arr)]) && count($target_arr)>1 ){
			unset($target_arr[count($target_arr)-1]);
		}*/
		if(end($target_arr)==''){  array_pop($target_arr);}
		
		$target = implode(",",$target_arr);
		$query_str = $query_str.' AND FIND_IN_SET(a.stud_id, :target)';
	}
	//echo substr($target[1], 0, -2);
	/*$class_num = $dbh2->prepare("SELECT a.user_id FROM user_info a, user_status b
		WHERE a.user_id = b.user_id and b.access_level IN ('1','9','8','4') 
			and organization_id = :organization_id 
			AND grade = :grade AND class = :class");*/
	$class_num = $dbh2->prepare($query_str);
	if($sub_finish3["target_type"] =='C'){
		$class_num->bindValue(':organization_id', $target[0], PDO::PARAM_STR);
		$class_num->bindValue(':seme', $sub_finish3["semester"], PDO::PARAM_INT);
		$class_num->bindValue(':grade', substr($target[1], 0, -2), PDO::PARAM_STR);
		$class_num->bindValue(':class', substr($target[1],-2), PDO::PARAM_INT);
	}else{
		$class_num->bindValue(':target', $target, PDO::PARAM_STR);
	}
	
	$class_num->execute();
	$sub_classnum = $class_num->fetchAll(\PDO::FETCH_ASSOC);
	$count_c = $class_num->rowCount();
	//print_r($sub_classnum);
	
	foreach ($sub_classnum as $key => $value){
		foreach ($node_arr as $key2 => $value2){
			if($sub_finish3["mission_type"] =='1'){
				$pass = update_finish($dbh2, $value["user_id"], $value2);
			}else if($sub_finish3["mission_type"] =='0'){
				$tmp_cs = substr($value2,0,-2);
				$tmp_vol = substr($value2,-1);
				$pass = update_finish2($dbh2, $value["user_id"], $tmp_cs, $tmp_vol);
			}else if($sub_finish3["mission_type"] =='3'){
				$pass = update_finish3($dbh2, $value["user_id"], $ms_sn);
			}
			//echo 'pass:'.$pass;
			if($pass) break;
		}
		$tclass_p = $value["grade"]."X@@X".$value["class"];
		if($pass){
			$time = date ("YmdHis");
	
			$result_query = $dbh2->prepare("SELECT * FROM mission_result a
				WHERE a.user_id = :user_id and mission_sn = :mission_sn");
			$result_query->bindValue(':user_id', $value["user_id"], PDO::PARAM_STR);
			$result_query->bindValue(':mission_sn', $ms_sn, PDO::PARAM_STR);
			$result_query->execute();
			$sub_result = $result_query->fetchAll(\PDO::FETCH_ASSOC);
	
			if(count($sub_result) == 0){
				$result = $dbh2->prepare("INSERT INTO mission_result (`user_id`, `mission_sn`, `nodes_pass`, `all_pass`, `update_date`)
					VALUES (:user_id, :mission_sn, :nodes_pass, :all_pass, :update_date)");
				$result->bindValue(':user_id', $value["user_id"], PDO::PARAM_STR);
				$result->bindValue(':mission_sn', $ms_sn, PDO::PARAM_STR);
				$result->bindValue(':nodes_pass', $sub_finish3['node'], PDO::PARAM_STR);
				$result->bindValue(':all_pass', '1', PDO::PARAM_STR);
				$result->bindValue(':update_date', $time, PDO::PARAM_STR);
				$result->execute();
			}
		}
	}
	return $count_c;
}



?>
<body>
<!-- <div id="result"></div> 
<div id="content" class="content-Box">-->
	<div class="content2-Box">
    	<div class="path">目前位置：任務指派</div>
       <div class="main-box">
       		<div class="left-box discuss-select">
       		<?php 
       			$query_url = "modules.php?op=modload&name=assignMission&file=mission_check";
       			$assign_url = "modules.php?op=modload&name=assignMission&file=assignment";
       			$modify_url = "modules.php?op=modload&name=assignMission&file=mission_modify";
       		?>
               <a href="<?php echo $assign_url;?>" class="btn02">指派任務</a>
               <a href="<?php echo $query_url;?>" class="btn02 current">任務進度</a>
               <a href="<?php echo $modify_url;?>" class="btn02">任務維護</a>
            </div>
			<div class="right-box">
            	<div class="title01">任務進度</div>
            	<div class="class-list2 test-search">
            		<form action="modules.php?op=modload&name=assignMission&file=mission_check" name="QueryForm" method="post">
            		
            		<div class="width-50" style="width:23%; text-align:left;">
                       	 學期：<br>
                        <select id="year_search" name="year_search">
                        
                        <option value="<?php echo $this_seme;?>"><?php echo Semester_id2FullName($this_seme);?></option>
						<?php 
						if($teach_count == 0){
							$tmp1 = ($this_Year-1)."1";
							$tmp2 = ($this_Year-1)."2";
							echo "<option value=".($this_Year-1).'1'.">".Semester_id2FullName($tmp1)."</option>";
							echo "<option value=".($this_Year-1).'2'.">".Semester_id2FullName($tmp2)."</option>";
						}
                        foreach ($teach_seme as $key=>$value){
							if($value != $NowSeme){
								if($_POST["year_search"] == $value){
									$tmp_select= "selected=\"selected\"";
								}else $tmp_select ='';
								?>
								<option value="<?php echo $value;?>" <?php echo $tmp_select;?>><?php echo Semester_id2FullName($value);?></option>
						<?php }}?>
                        </select>
            		</div>
            		
            		<div class="width-50" style="text-align:left; width:25%;">
                       	 班級：<br>
                        <select id="miss_search" name="miss_search">
                        <?php 
                        if($class_count > 0){
                        	echo "<option value='all'>全部</option>";
                        }
                        foreach ($teach_sub as $tclass=>$tvalue){
	                		if($tvalue["class"] < 9){
	                			$tmp_class = '0'.$tvalue["class"];
	                		}else $tmp_class = $tvalue["class"];
	                		
	                		$class = $user_data->organization_id.'-'.$tvalue["grade"].$tmp_class;
	                		if($_POST["miss_search"] == $class){
	                			$tmp_select= "selected=\"selected\"";
	                		}else $tmp_select ='';
	                	?>
	                	<option value="<?php echo $class;?>" <?php echo $tmp_select;?>><?php echo $tvalue["grade"].'年'.$tvalue["class"].'班';?></option>
	                	<?php }
	                		if($class_count == 0){
	                		?>
	                		<option value="<?php echo $target_class;?>"><?php echo $class_nm;?></option>
	                	<?php }?>
                        </select>
            		</div>
            		<div class="width-50" style="text-align:left;">
                       	 時間：<br>
                        <input type="date" placeholder="yyyy/mm" style="width:200px" id="search_start" name="search_start" value="<?php echo $search_start;?>">～
                        <input type="date" placeholder="yyyy/mm" style="width:200px" id="search_end" name="search_end" value="<?php echo $search_end;?>">
            		</div>
            		<div align="center">
            			<!-- <input type="submit" value="查詢"  class="btn04" style="display:inline-block;" /> <a href="#" class="btn04" style="display:inline-block;">更新</a> -->
            		</div>
            		<!-- </form> -->
						<div class="result-box" style="text-align:left;">
							<!-- <form action="modules.php?op=modload&name=assignMission&file=mission_check" name="QueryForm" method="post"> -->
                        	任務：<br>
	                        <select id="miss_select" name="miss_select" onclick="">
	                        	<option value="">請選擇</option>
	                        <?php
	                        	
								foreach ($sub_mission as $key=>$value){	
									$sel_str = '';
									if(isset($_REQUEST["miss_select"]) && $value[mission_sn] == $_REQUEST["miss_select"]){
										$sel_str = 'selected=\"selected\"';
									}
									//$mission_result["mission_sn"] = $value["mission_sn"];
									echo "<option value=\"$value[mission_sn]\" $sel_str> $value[mission_nm] </option>";
									
								}?>
	                        </select>
	                        <input type="hidden" id="target_type" name="target_type" >
	                        <div align="center">
	                        	<input type="submit" value="送出"  class="btn04" style="display:inline-block;" />
	                        </div>
	                        </form>
	                        
	                        <div style="text-align:left;"><?php echo "任務類別：".$mission_result[$_REQUEST["miss_select"]]["missiontype_cht"]."<br>";?>
	                        <?php  if(isset($_REQUEST["miss_select"])&&($_REQUEST["miss_select"] !='')){ 
	                        		echo "指派對象：".$mission_result[$_REQUEST["miss_select"]]["mission_cht"];
	                        }?></div><!--  增加任務類別 指派對象 BlueS 20170622-->
	                        <div class="work-search-box">
	                        <?php if(isset($_REQUEST["miss_select"])&&($_REQUEST["miss_select"] !='')){?>
	                            <div class="work-search-nember">
	                            	<div class="work-search-nember-top"><?php echo $mission_result[$_REQUEST["miss_select"]]["unfinish"];//$finish-$unfinish;?><br><small>未完成人數</small></div>
	                            	<div class="work-search-nember-bottom"><?php echo $mission_result[$_REQUEST["miss_select"]]["total"];//$finish;?><br><small>全班人數</small></div>
	                            </div>
	                            <?php if($mission_result[$_REQUEST["miss_select"]]["mission_type"] == 0 || $mission_result[$_REQUEST["miss_select"]]["mission_type"] == 3){?>
	                            <div class="work-search-nember-btn">
	                            	<a class="venoboxframe vbox-item"  data-title="班級學習狀態" data-gall="gall-frame2" data-type="iframe" href="<?php echo $mission_result[$_REQUEST["miss_select"]]["report3"];?>" >
		                            <i class="fa fa-bar-chart-o"></i><br>班級學習狀態</a>
	                            </div>
	                            <?php }?>
	                            <div class="work-search-nember-btn">
	                            	<a class="venoboxframe vbox-item"  data-title="影片瀏覽報告" data-gall="gall-frame2" data-type="iframe" id="video_report" onclick="ouputShowLine('<?php echo $mission_result[$_REQUEST["miss_select"]]["bnode"];?>','<?php echo $mission_result[$_REQUEST["miss_select"]]["report"];?>')" >
		                            <i class="fa fa-bar-chart-o"></i><br>影片瀏覽報告</a>
	                            </div>
	                            <div class="work-search-nember-btn">
		                            <a class="venoboxframe vbox-item"  data-title="個人進度" data-gall="gall-frame2" data-type="iframe" href="<?php echo $mission_result[$_REQUEST["miss_select"]]["report2"];// $report2_url;?>">
		                            <i class="fa fa-child"></i><br>個人進度</a>
	                            </div>
	                            <?php 
	                            
	                            if($mission_result[$_REQUEST["miss_select"]]["mission_type"] != 0 && $mission_result[$_REQUEST["miss_select"]]["mission_type"] != 3){?>
	                            <div class="work-search-nember-btn">
	                            	<a class="venoboxframe vbox-item"  data-title="查看提問" data-gall="gall-frame2" data-type="iframe" href="<?php echo $mission_result[$_REQUEST["miss_select"]]["diss"];// $diss_url;?>">
		                            <i class="fa fa-bar-chart-o"></i><br>查看提問</a>
	                            </div>
	                            <?php }?>
	                        <?php }else {
	                        	echo '請選取欲查詢的任務(使用上方的下拉選單)，謝謝！';
	                        }?>
	                        </div>
                    	</div>
						
							
							
					</div>
				</div>
				
			</div>
		</div>
<!-- 	<div class="bottom-pto"><img src="images/content-bg.png"></div>
	</div>
</body>
</html>	 -->