<?php
include_once "include/config.php";
include_once 'include/adp_core_function.php';

if (!isset($_SESSION)) {
	session_start();
}

if ($user_data->class_name < 10) {
	$class_no = '0'.$user_data->class_name;
} else { 
	$class_no = $user_data->class_name;
}

if ($_POST['seme'] != '') { // 學年度加學期 例:1061
	$seme = $_POST['seme'];
} else {
	$seme = getYearSeme();
}
//  $seme = getYearSeme();
	
	$user_id = $_SESSION['user_id'];
	//echo '$user_id-->'.$user_id;
 	$target_class = $user_data->organization_id.'-'.$user_data->grade.$class_no; // 例:190041-717
	//echo '$target_class-->'.$target_class; 
	$class_nm = $user_data->grade.'年'.$user_data->class_name.'班'; // 例:7年17班
	$NowYear = getNowSemeYear(); // 學年度 例:106
	$NowSeme = getYearSeme();
	$seme_nm = Semester_id2FullName($NowSeme);
	$date = date ("Y-m-d");
	$neardate = date("Y-m-d",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y")));
	$nodate2= date ("Y-m-d H:i:s");
		
if($_POST["seme"] != '' &&  $_POST["seme"] != $NowSeme){
	$semeclass = $dbh->prepare("SELECT grade, class
			FROM seme_student WHERE stud_id = :user_id and seme_year_seme = :seme ");
	$semeclass->bindValue(':user_id', $user_id, PDO::PARAM_STR);
	$semeclass->bindValue(':seme', $_POST["seme"], PDO::PARAM_STR);
	$semeclass->execute();
	$seme_nodedata = $semeclass->fetch(\PDO::FETCH_ASSOC);

	if($seme_nodedata["class"]<10){
		$seme_nodedata["class"]="0".$seme_nodedata["class"];
	}
	
	$target_class = $user_data->organization_id.'-'.$seme_nodedata["grade"].$seme_nodedata["class"];
	$class_nm=$seme_nodedata["grade"].'年'.$seme_nodedata["class"].'班';
	$seme_nm = Semester_id2FullName($seme);
}

// 	$target_class=$user_data->organization_id.'-'.$sub_nodedata["grade"].$sub_nodedata["class"];
// 	$class_nm='$sub_nodedata["grade"].$sub_nodedata["class"]';
// 	$seme=$_POST["seme"];
// }

//選出學年度學期 2017/8/8 by corn
$chseme = $dbh->prepare("SELECT DISTINCT seme_year_seme seme FROM seme_student 
		order by seme desc");
$chseme->execute();
$chseme_data = $chseme->fetchAll(\PDO::FETCH_ASSOC);


// $mission_now = "SELECT a.mission_nm, a.target_type, a.create_date, a.date, a.node, a.mission_sn, a.subject_id, a.semester, a.mission_type, a.endYear, a.teacher_id
// 				FROM mission_info as a ";//, b.user_id, b.nodes_pass, b.all_pass
// if($_POST["dowh_val"]='1'){
// 	$mission_now =$mission_now. "left JOIN (SELECT * FROM mission_result WHERE user_id = :user_id  and user_id NOT IN (:user_id)) as b on (a.mission_sn = b.mission_sn) ";
// }
// $mission_now =$mission_now. "WHERE (a.target_id LIKE :target_id OR a.target_id LIKE :target_id2) AND a.semester like :seme AND unable=1";
// if($_POST["dowh_val"]='1'){
// 	$mission_now =$mission_now. "and NOT EXISTS (SELECT user_id FROM mission_result where user_id ='190041-s61605' and a.mission_sn=mission_sn)";
// }

//171204，新增分頁功能，每一頁限制資料數量5筆
$default_page =5;
if(isset($_POST['page'])=='') {
	$_POST['page'] = 1; //預設為page1
}
if(isset($_POST["btn02"])) $_SESSION['page_num']=0;

if ($_POST["dowh_val"]=="1") { // 下拉式  select val = 1 (進行中)
	$mission_now ="SELECT a.mission_nm, a.target_type, a.create_date, a.date, a.node, a.mission_sn, a.subject_id, a.semester, a.mission_type, a.endYear, a.teacher_id, a.exam_type, ifnull(b.history_mk,'N') history_mk 
					FROM mission_info as a LEFT JOIN (SELECT * FROM mission_result WHERE user_id = :user_id ) as b on (a.mission_sn = b.mission_sn)
					WHERE (a.target_id = :target_id OR a.target_id LIKE :target_id2) AND a.semester = :seme AND unable=1 AND (a.date=:nodate or a.date>:nodate2)
					AND NOT EXISTS (SELECT * FROM mission_result where user_id =:user_id2 and mission_sn=a.mission_sn) ";//and NOT EXISTS (SELECT user_id FROM mission_result where user_id =:user_id and a.mission_sn=mission_result.mission_sn) ORDER BY a.mission_sn DESC

	if($_SESSION['page_num']==0) {
		$mission_row = $mission_now." ORDER BY a.mission_sn DESC";
		$mission_data = $dbh->prepare($mission_row); 
	}else{
		$mission_now = $mission_now." ORDER BY a.mission_sn DESC LIMIT ".(($_POST['page']-1)* $default_page).",$default_page";
		$mission_data = $dbh->prepare($mission_now); 
	}
	
	$mission_data->bindValue(':target_id', $target_class, PDO::PARAM_STR);
	$mission_data->bindValue(':target_id2', '%'.$user_id.'%', PDO::PARAM_STR);
	$mission_data->bindValue(':nodate', '0000-00-00', PDO::PARAM_STR);
	$mission_data->bindValue(':nodate2',$nodate2, PDO::PARAM_STR);
	$mission_data->bindValue(':user_id2', $user_id, PDO::PARAM_STR);
	$mission_data->bindValue(':user_id', $user_id, PDO::PARAM_STR);
	$mission_data->bindValue(':seme', $seme, PDO::PARAM_STR);

} elseif ($_POST["dowh_val"]=="2"){ // 下拉式  select val = 2 (過期)
	$mission_now ="SELECT a.mission_nm, a.target_type, a.create_date, a.date, a.node, a.mission_sn, a.subject_id, a.semester, a.mission_type, a.endYear, a.teacher_id, a.exam_type, ifnull(b.history_mk,'N') history_mk
					FROM mission_info as a LEFT JOIN (SELECT * FROM mission_result WHERE user_id = :user_id) as b on (a.mission_sn = b.mission_sn)
					WHERE NOT a.date=:nodate AND (a.target_id = :target_id OR a.target_id LIKE :target_id2) AND a.semester = :seme AND a.unable=1 AND a.date<:nodate2 ";//ORDER BY a.mission_sn DESC

	if($_SESSION['page_num']==0) {
		$mission_row = $mission_now." ORDER BY a.mission_sn DESC";
		$mission_data = $dbh->prepare($mission_row);
	}else{
		$mission_now = $mission_now." ORDER BY a.mission_sn DESC LIMIT ".(($_POST['page']-1)* $default_page ).",$default_page";
		$mission_data = $dbh->prepare($mission_now);
	}
	
	$mission_data->bindValue(':target_id', $target_class, PDO::PARAM_STR);
	$mission_data->bindValue(':target_id2', '%'.$user_id.'%', PDO::PARAM_STR);
	$mission_data->bindValue(':user_id', $user_id, PDO::PARAM_STR);
	$mission_data->bindValue(':seme', $seme, PDO::PARAM_STR);
	$mission_data->bindValue(':nodate', '0000-00-00', PDO::PARAM_STR);
	$mission_data->bindValue(':nodate2',$nodate2, PDO::PARAM_STR);
	
} elseif ($_POST["dowh_val"]=="3"){ // 下拉式  select val = 3 (已完成)
	$mission_now ="SELECT a.mission_nm, a.target_type, a.create_date, a.date, a.node, a.mission_sn, a.subject_id, a.semester, a.mission_type, a.endYear, a.teacher_id, a.exam_type, ifnull(b.history_mk,'N') history_mk
					FROM mission_info as a LEFT JOIN (SELECT * FROM mission_result WHERE user_id = :user_id) as b on (a.mission_sn = b.mission_sn)
					WHERE (a.target_id = :target_id OR a.target_id LIKE :target_id2) AND a.semester = :seme AND a.unable=1 AND b.all_pass = '1' ";//and NOT EXISTS (SELECT user_id FROM mission_result where user_id =:user_id and a.mission_sn=mission_result.mission_sn) ORDER BY a.mission_sn DESC
	
	if($_SESSION['page_num']==0) {
		$mission_row = $mission_now." ORDER BY a.mission_sn DESC";
		$mission_data = $dbh->prepare($mission_row);
	}else{
		$mission_now = $mission_now." ORDER BY a.mission_sn DESC LIMIT ".(($_POST['page']-1)* $default_page).", $default_page";
		$mission_data = $dbh->prepare($mission_now);
	}
	
	$mission_data->bindValue(':target_id', $target_class, PDO::PARAM_STR);
	$mission_data->bindValue(':target_id2', '%'.$user_id.'%', PDO::PARAM_STR);
	$mission_data->bindValue(':user_id', $user_id, PDO::PARAM_STR);
	$mission_data->bindValue(':seme', $seme, PDO::PARAM_STR);
	
} else { //下拉式  select val = 4 (所有任務)
	$mission_now ="SELECT a.mission_nm, a.target_type, a.create_date, a.date, a.node, a.mission_sn, a.subject_id, a.semester, a.mission_type, a.endYear, a.teacher_id, a.exam_type, ifnull(b.history_mk,'N') history_mk
					FROM mission_info as a LEFT JOIN (SELECT * FROM mission_result WHERE user_id = :user_id ) as b on (a.mission_sn = b.mission_sn)
					WHERE (a.target_id = :target_id OR a.target_id LIKE :target_id2) AND a.semester like :seme AND unable=1 ";//ORDER BY a.mission_sn DESC
	
	if($_SESSION['page_num']==0) {
		$mission_row = $mission_now." ORDER BY a.mission_sn DESC";
		$mission_data = $dbh->prepare($mission_row);
	}else{
		$mission_now = $mission_now." ORDER BY a.mission_sn DESC LIMIT ".(($_POST['page']-1)* $default_page).",$default_page";
		$mission_data = $dbh->prepare($mission_now);
	}

	$mission_data->bindValue(':target_id', $target_class, PDO::PARAM_STR);
	$mission_data->bindValue(':target_id2', '%'.$user_id.'%', PDO::PARAM_STR);
	//$mission_data->bindValue(':nodate', '0000-00-00', PDO::PARAM_STR);
	//$mission_data->bindValue(':nodate2',$nodate2, PDO::PARAM_STR);
	$mission_data->bindValue(':user_id', $user_id, PDO::PARAM_STR);
	$mission_data->bindValue(':seme', $seme, PDO::PARAM_STR);
	
}

$mission_data->execute();

//171201，mission_row，撈出全部的資料，作為計算分頁頁數的分母。
if($_SESSION['page_num']==0 || isset($_POST["btn02"])) {
  //171201，要計算分頁的頁數，以任務的數量，每5 $default_page筆為一頁。
  $sub_mission_count = $mission_data->rowcount();
  $page_num = ceil($sub_mission_count/$default_page); //ceil，無條件進位
  $_SESSION['page_num'] = $page_num;
  $_SESSION['mission_count'] = $sub_mission_count;
  $_SESSION['mis_dowh_val'] = $_POST["dowh_val"];
  //echo $_POST["dowh_val"].', count page....'.$_SESSION['page_num'].'<br> mission count...'.$sub_mission_count;
  
}else {
  $page_num = $_SESSION['page_num'];
  if(isset($_SESSION['mis_dowh_val'])) $_POST["dowh_val"] = $_SESSION['mis_dowh_val'];
}
//echo 'dowh_val:'.$_POST["dowh_val"].', show session:'.$_SESSION['page_num'];
$sub_mission = $mission_data->fetchAll(\PDO::FETCH_ASSOC);

// echo 'target_class:'.$target_class.'--user_id:'.$user_id.'--seme:'.$seme;
//任務節點對應的影片查詢
function nodevideo($dbh2, $str_node, $user_id){
	$cn=0;
	$node_data = $dbh2->prepare("SELECT a.video_item_sn, indicator, video_src, video_nm, IFNULL(b.ask_sn,0) ask_sn , IFNULL(c.note_sn,0) note_sn
			FROM video_concept_item a left join  (SELECT video_item_sn, max(ask_sn) ask_sn FROM video_noteask WHERE user_id =:user_id GROUP BY video_item_sn) b on a.video_item_sn= b.video_item_sn
			 left join ( SELECT video_item_sn, max(video_note_sn) note_sn FROM video_note  WHERE user_id =:user_id2 GROUP BY video_item_sn) c  on a.video_item_sn= c.video_item_sn
			WHERE indicator like :ind_str AND end_mk = :end_mk ");
	$node_data->bindValue(':user_id', $user_id, PDO::PARAM_STR);
	$node_data->bindValue(':user_id2', $user_id, PDO::PARAM_STR);
	$node_data->bindValue(':ind_str', $str_node.'%', PDO::PARAM_STR);
	$node_data->bindValue(':end_mk', 'N', PDO::PARAM_STR);
	$node_data->execute();
	$sub_nodedata = $node_data->fetchAll(\PDO::FETCH_ASSOC);
	if(count($sub_nodedata) >0 ) $cn= count($sub_nodedata);
	return $sub_nodedata;
}

function nodeprac($dbh2, $str_node){
	$cn=0;//刪除$node_data 之 select nodes_uuid 項目  BlueS 20170602
	$node_data = $dbh2->prepare("SELECT  DISTINCT indicator, public  FROM prac_questions
			WHERE indicator like :ind_str ");
	$node_data->bindValue(':ind_str', $str_node.'%', PDO::PARAM_STR);
	$node_data->execute();
	$sub_nodedata = $node_data->fetchAll(\PDO::FETCH_ASSOC);
	if(count($sub_nodedata) >0 ) $cn= count($sub_nodedata);
	return $sub_nodedata;
}

function missionPublisher($dbh2, $subject, $user_data){
	$sems = getYearSeme();
	//班級版本 seme_teacher_subject ->seme_publisher AND class = :class
	$class_pub_data = $dbh2->prepare("SELECT * FROM seme_publisher
		WHERE organization_id = :organization_id AND grade = :grade
		 AND subject_id = :subject_id AND sems = :seme");
	$class_pub_data->bindValue(':organization_id', $user_data->organization_id, PDO::PARAM_INT);
	$class_pub_data->bindValue(':grade', $user_data->grade, PDO::PARAM_INT);
	//$class_pub_data->bindValue(':class', $user_data->class_name, PDO::PARAM_INT);
	$class_pub_data->bindValue(':subject_id', $subject, PDO::PARAM_INT);
	$class_pub_data->bindValue(':seme', $sems, PDO::PARAM_INT);
	$class_pub_data->execute();
	$sub_class_pub = $class_pub_data->fetch(\PDO::FETCH_ASSOC);
	return $sub_class_pub["publisher_id"];
}

function  exam_EPnm($dbh2, $ep_id){
	$concept_data5 = $dbh2->prepare("SELECT DISTINCT a.cs_id, a.concept, b.exam_paper_id, b.paper_vol
			FROM concept_item b, concept_info a WHERE a.cs_id = b.cs_id
			AND b.exam_paper_id = :exam_paper_id ORDER BY exam_paper_id");
	$concept_data5->bindValue(':exam_paper_id', $ep_id, PDO::PARAM_STR);
	$concept_data5->execute();
}

function EP_update_finish($dbh2, $user, $ep){
	$cs_id = substr($ep, 0,9);
	$vol = substr($ep,-2);
	$finish_data2 = $dbh2->prepare("SELECT a.user_id, b.cs_id FROM `exam_record` a, concept_info b
			WHERE a.cs_id = b.cs_id AND a.user_id = :user_id AND a.cs_id = :cs_id AND a.paper_vol = :vol
			GROUP BY a.user_id, b.cs_id");
	$finish_data2->bindValue(':user_id', $user, PDO::PARAM_STR);
	$finish_data2->bindValue(':cs_id', $cs_id, PDO::PARAM_STR);
	$finish_data2->bindValue(':vol', (int)$vol, PDO::PARAM_INT);
	$finish_data2->execute();
	$sub_finish2 = $finish_data2->fetchAll(\PDO::FETCH_ASSOC);
	if(count($sub_finish2) > 0){
		return true;
	}else return false;
}

function EP2_update_finish($dbh2, $user, $sn){ //$ind
	$finish_data2 = $dbh2->prepare("SELECT a.user_id, a.cs_id FROM `exam_record_indicate` a
			WHERE a.user_id = :user_id AND  a.result_sn = :result_sn 
			GROUP BY a.user_id, a.cs_id"); //a.skill_remedy_rate_s LIKE :indicator
	$finish_data2->bindValue(':user_id', $user, PDO::PARAM_STR);
	$finish_data2->bindValue(':result_sn', $sn, PDO::PARAM_STR);
	//$finish_data2->bindValue(':indicator', '%'.$ind.'%', PDO::PARAM_STR);
	//$finish_data2->bindValue(':vol', (int)$vol, PDO::PARAM_INT);
	$finish_data2->execute();
	$sub_finish2 = $finish_data2->fetchAll(\PDO::FETCH_ASSOC);
	if(count($sub_finish2) > 0){
		return true;
	}else return false;
}

function update_finish($dbh2, $user, $ind){
	$finish_data2 = $dbh2->prepare("SELECT a.user_id, b.indicator, MAX(finish_rate) rate FROM `video_review_record` a, video_concept_item b
			WHERE a.video_item_sn = b.video_item_sn AND a.user_id = :user_id AND b.indicator like :indicator
			GROUP BY a.user_id, b.indicator HAVING MAX(finish_rate) = 100");
	$finish_data2->bindValue(':user_id', $user, PDO::PARAM_INT);
	$finish_data2->bindValue(':indicator', $ind.'%', PDO::PARAM_INT);
	$finish_data2->execute();
	$sub_finish2 = $finish_data2->fetchAll(\PDO::FETCH_ASSOC);
	if(count($sub_finish2) > 0){
		return true;
	}else return false;
}

function prac_update_finish($dbh2,$user,$ind){
	$prac_finish_data=$dbh2->prepare("SELECT org_res, binary_res FROM prac_answer where user_id=:user_id AND indicator like :indicator");
	$prac_finish_data->bindValue(':user_id',$user,PDO::PARAM_INT);
	$prac_finish_data->bindValue(':indicator',$ind.'%',PDO::PARAM_INT);
	$prac_finish_data->execute();
	$prac_finish=$prac_finish_data->fetchAll(\PDO::FETCH_ASSOC);
	$prac_finish_status= 0;
	foreach ($prac_finish as $key=>$value){
		$prac_bin_tmp=explode(_SPLIT_SYMBOL,$value["binary_res"]);
		$prac_org_tmp=explode(_SPLIT_SYMBOL,$value["org_res"]);
		//echo array_sum($prac_bin_tmp).','.count($prac_bin_tmp).',';
		//if(array_sum($prac_bin_tmp)/count($prac_bin_tmp)==1){
		if(count($prac_org_tmp) == count($prac_bin_tmp)){
			$prac_finish_status= 1;
		}else{
			$prac_finish_status= 0;
		}
	}
	return $prac_finish_status;
}

function findMappingData($dblink, $ind, $sub, $pub){
	$sems = getYearSeme();
	$temp_ind = substr($ind, 0, 6);
	$ind_data = $dblink->prepare("SELECT * FROM publisher_mapping c
			where c.subject_id = :subject and c.publisher_id = :publisher
			 and indicator = :indicator
			ORDER BY sems, unit LIMIT 0, 1");//and c.sems = :sems
	$ind_data->bindValue(':subject', $sub, PDO::PARAM_INT);
	$ind_data->bindValue(':publisher', $pub, PDO::PARAM_INT);
	//$ind_data->bindValue(':sems', $sems, PDO::PARAM_INT);
	$ind_data->bindValue(':indicator', $temp_ind, PDO::PARAM_STR);
	$ind_data->execute();
	$sub_ind = $ind_data->fetch(\PDO::FETCH_ASSOC);
	return $sub_ind;
}

//科目圖層
function findMapNO($dblink, $sub){
	$map_data = $dblink->prepare("SELECT * FROM map_info WHERE subject_id = :subject and display = :display ORDER BY map_sn DESC LIMIT 0,1"); //  AND　
	$map_data->bindValue(':display', '1', PDO::PARAM_INT);
	$map_data->bindValue(':subject', $sub, PDO::PARAM_INT);
	$map_data->execute();
	$map_row = $map_data->fetch(\PDO::FETCH_ASSOC);
	return $map_row;
}

function teachername($dbh2,$userid)
{
	$name=$dbh2->prepare("SELECT uname FROM `user_info` where user_id = :user_id");
	$name->bindValue(':user_id', $userid, PDO::PARAM_INT);
	$name->execute();
	$teacher_name=$name->fetch(\PDO::FETCH_ASSOC);
	return $teacher_name["uname"];
}

?>
<script>
//170829，分頁的頁數，作為參數傳回來。
function load_mission() {
  //var page = "page"+id;
  var form_page = document.getElementById('page_count');
  form_page.submit();
}

function history(type, link, memo) {
	//var link = $(".challenge").attr('href');
	//var type = 'challenge';
	console.log('into user_history:'+link);
	//window.event.returnValue=false;
	$.ajax({
		url: 'modules/role/pro_user_history.php',
          type : "POST" ,
          data: {'type':type, 'link':link, 'memo':memo},
          dataType:'JSON',
          success: function (returndata) {
        	  console.log(returndata);
        	  var jwu = JSON.parse(JSON.stringify(returndata));
        	  if(jwu.db_code !='00000'){
	        	  alert(jwu.db_txt);  
        	  }     	  
          },
          error: function(xhr, ajaxOptions, thrownError){
				alert(xhr.status);
				alert(thrownError);
		  }
	});
}
</script>
<div id="content" class="content-Box">
	<div class="content2-Box">
    	<div class="path">目前位置：我的任務</div>
       <div class="main-box">
            <div class="left-box">
            <form name="QueryForm" action="modules.php?op=modload&name=assignMission&file=mission_action" method="post">
				我的任務<input type="hidden" id="userId" value="<?php echo $user_id?>" /><br>
				<?php //echo $_POST["dowh_val"]?>
            	<select id="seme" name="seme" >
	    			<option value="">請選擇(學期)</option>
	    			<?php foreach ($chseme_data as $key=>$value){
	    				if($_POST['seme'] == $value["seme"] && $_POST["btn02"]!=''){
	    					$att = "selected=\"selected\"";
	    				}else $att = '';?>
	    			<option value="<?php echo $value["seme"];?>"<?php echo $att;?>><?php echo Semester_id2FullName($value["seme"]);?></option>
	    			<?php }?>
	    		</select>
               	<select id ="dowh_val" name="dowh_val">
               		<option value="4"<?php if($_POST['dowh_val'] == "4" ){ echo "selected=\"selected\"";}?>>所有任務</option>
                   	<option value="1"<?php if($_POST['dowh_val'] == "1" ){ echo "selected=\"selected\"";}?>>進行中</option>
               		<option value="2"<?php if($_POST['dowh_val'] == "2" ){ echo "selected=\"selected\"";}?>>過期</option>
              		<option value="3"<?php if($_POST['dowh_val'] == "3" ){ echo "selected=\"selected\"";}?>>已完成</option>
               	</select>
               	<div align="center">
	           		<input type="submit" name="btn02" value="查看"  class="btn02" style="display:inline-block;" />
	        	</div>
            
            </form>
            </div>
            <div class="right-box">
            	<div class="title01">今天<?php echo $date;?></div>
            	<div class="table_scroll">
            	<?php echo $seme_nm." ".$class_nm;  ?>
            	<div align="right"><font class="color-blue">(註：僅顯示本學年度的任務)</font></div>
               	<table class="datatable" data-sortable > <!-- 加排序功能、串&result_sn= -->
                	<thead>
                	<tr>
                		<th>類型</th>
                		<th data-sorted="true" data-sorted-direction="descending">指派日期</th>
                      	<th width="200px">任務名稱</th> 
                      	<th>指派教師</th>
                      	<th>任務進度</th> 
                      	<th>完成期限</th> 
                      	<th>任務內容</th>
                    </tr>
                    </thead>
                    <?php 
      					foreach ($sub_mission as $key=>$value){
      						if( $key > $default_page-1){
      							break;
      						}
      						$data = missionPublisher($dbh, $value["subject_id"], $user_data);//班級的版本
      						$mission_type = $value["mission_type"];
      										
      						$node_arr = explode(_SPLIT_SYMBOL, $value["node"]);
      						if(empty($node_arr[count($node_arr)]) && count($node_arr)>1 ){
      							unset($node_arr[count($node_arr)-1]);
      							//echo 'last:'.$node_arr[count($node_arr)].','.count($node_arr);
      						}
      						$all_finish = count($node_arr);
      						$finish_num =0;
      						foreach ($node_arr as $nodekey=>$nodevalue){
      							if($value[mission_type] =='1'){
	      							$ind_status = update_finish($dbh, $user_id, $nodevalue);
	      							$ind_status2 = prac_update_finish($dbh, $user_id, $nodevalue);
	      							if($ind_status){
	      								$finish_num = $finish_num + 0.5;
	      							}
	      							if($ind_status2){
	      								$finish_num = $finish_num + 0.5;
	      							}
      							}else{
      								$ep_status = EP_update_finish($dbh, $user_id, $nodevalue);
      								if($ep_status) $finish_num++;
      								$ep_status2 = EP2_update_finish($dbh, $user_id, $value["mission_sn"]); //$nodevalue
      								if($ep_status2) $finish_num++;
      							}
      						}
      						$finish_rate =round(($finish_num/$all_finish)*100);
      						if($value[mission_type] =='0'){
      							$miss_type='<img src="images/m3-icon.png">';
      						}elseif($value[mission_type] =='1'){
      							$miss_type='<img src="images/m2-icon.png">';
      						}elseif($value[mission_type] =='3'){
      							$miss_type='<img src="images/m3-icon.png">';
      						}elseif($value[mission_type] =='2'){
      							$miss_type='<i class="fa fa-bar-chart" aria-hidden="true"></i>';
      						}
      						$teachername=teachername($dbh,$value[teacher_id])
                    ?>
                    <tr><td><?php echo $miss_type;?></td><td><?php echo substr($value[create_date], 0, 10);?></td>
                    	<td>
                    	<?php if($value[mission_type] =='2'){
      							$miss_type='學習問卷';
      							$feedback_url = 'https://docs.google.com/forms/d/e/1FAIpQLSc_h6uOjckcLA4OUsbWeRizlQkLFWYB336QX-cb_jusfU-eGg/viewform?entry.1398247146='.$user_data->city_name.'&entry.516685473='.$user_data->organization_name.'&entry.2027549775='.$user_data->grade.'&entry.835231682='.$user_data->class_name.'&entry.1006864608='.$user_data->uname.'&entry.1845044628='.$value["mission_sn"];
      						?>
                        <a class="venoboxframe vbox-item"  data-title="<?php echo $value[mission_nm];?>" data-gall="gall-frame2" data-type="iframe" href="<?php echo $feedback_url;?>"><?php echo $value[mission_nm];?></a>
                        <?php }else{?>
                    	<a class="venoboxinline"  data-title="<?php echo $value[mission_nm];?>" data-gall="gall-frame2" data-type="inline" href="#inline-content<?php echo $key;?>"><?php echo $value[mission_nm];?></a>
                    	<?php }?>
                    	</td><td><?php echo $teachername." 老師";?></td>
                    	<td><?php echo $finish_rate.'% ('.$finish_num.'/'.$all_finish.')'; 
	                    	if($finish_num == $all_finish && $value["history_mk"]=='N'){ //判斷是否寫入過
	                    		echo "<script  type='text/javascript'>
	                    		var link = 'modules.php?op=modload&name=assignMission&file=mission_action&sn=$value[mission_sn]';
	                    		history('finish', link,'完成挑戰任務 $value[mission_nm]');
	                    		</script>";//執行 update_finish
	                    	}
                    		?>
                    	</td>
                    	<td>
                    	<?php if(substr($value["date"], 0, 10)  == '0000-00-00'){
                            echo '無期限';
                          }else echo substr($value["date"], 0, 10);?></td>
                        <td>
                        <?php if($value[mission_type] =='2'){
      							$miss_type='學習問卷';
      							$feedback_url = 'https://docs.google.com/forms/d/e/1FAIpQLSc_h6uOjckcLA4OUsbWeRizlQkLFWYB336QX-cb_jusfU-eGg/viewform?entry.1398247146='.$user_data->city_name.'&entry.516685473='.$user_data->organization_name.'&entry.2027549775='.$user_data->grade.'&entry.835231682='.$user_data->class_name.'&entry.1006864608='.$user_data->uname;
      						?>
                        <a class="venoboxframe vbox-item"  data-title="<?php echo $value[mission_nm];?>" data-gall="gall-frame2" data-type="iframe" href="<?php echo $feedback_url;?>"><i class="fa fa-edit"></i></a>
                        <?php }else{?>
                        <a class="venoboxinline"  data-title="<?php echo $value[mission_nm];?>" data-gall="gall-frame2" data-type="inline" href="#inline-content<?php echo $key;?>"><i class="fa fa-edit"></i></a>
                        <?php }?>
                        </td>
                    </tr>  
                    <?php }?>                      
                	</table> 
               </div>
               <div>
               <!-- 171201，load_mission將select的值，用past的方式傳送出去 -->
               <form id=page_count action="modules.php?op=modload&name=assignMission&file=mission_action" method="post">
                <div><font class="color-blue">當前頁面：
                <select  class="input-normal" name="page" onchange="load_mission();">
                    <!--<li><a href="#">«</a></li>-->
                  <?php
                        for($i=1;$i<=$page_num;$i++) {

                          //echo '<option value="'.$i'"><a onclick="load_mission('.$i.');">'.$i.'</a><input name="page" type=hidden value="'.$i.'"></li>';
                          if($_POST["page"] == $i){
                            $selected = "selected=\"selected\"";
                          }else {
                            $selected = '';
                          }
                          echo '<option value="'.$i.'"'.$selected.'>第'.$i.'頁</option>';
                        }
                  ?><!-- end of for loop -->
                    <!--<li><a href="#">»</a></li>-->
                </select>
                  <?php echo '總共'.$_SESSION['mission_count'].'筆任務' ?>
                  </font>
                </div>
               </form>
               </div>
               <?php
                foreach ($sub_mission as $key=>$value){
                	if( $key > $default_page-1){
                		break;
                	}
                    $data = missionPublisher($dbh, $value["subject_id"], $user_data);//班級的版本
                    $mission_type = $value["mission_type"];
                    $m_sn = $value["mission_sn"];
                    $m_endday = $value["date"];
					$node_arr = explode(_SPLIT_SYMBOL, $value["node"]);
					$endYear_arr = explode(_SPLIT_SYMBOL, $value["endYear"]);
					if(empty($node_arr[count($node_arr)]) && count($node_arr)>1 ){
						unset($node_arr[count($node_arr)-1]);
						//echo 'last:'.$node_arr[count($node_arr)].','.count($node_arr);
					}
					if(empty($node_arr[count($endYear_arr)]) && count($endYear_arr)>1 ){
						unset($node_arr[count($endYear_arr)-1]);
					}
					if(($m_endday != '0000-00-00') && ($date > $m_endday)) $mend_mark = true;
					else $mend_mark = false;
                  
                    echo '<div id="inline-content'.$key.'" class="personal-inline">';
                    if($mission_type == '3'){
                  		echo '<div>
                          	<table class="datatable personal-table">
                                <tr>
                                  <th>縱貫式診斷</th><th colspan="2">狀態</th>
                                </tr>';
                    }
                    if($mission_type == '0'){
                  	  echo '<div>
                          	<table class="datatable personal-table">
                                <tr>
                                  <th>單元式診斷</th><th>狀態</th>
                                </tr>';
                    }

                  foreach ($node_arr as $nodekey=>$nodevalue){
                  	
                  	$map_no = findMapNO($dbh,$value["subject_id"]);
							$str_node = ''.$nodevalue.'';
							$str_endYear = $endYear_arr[$nodekey];
							
							$start_url  = "modules/D3/app/index_view.php?aa=".base64_encode($_SESSION[user_id])."";
							$course_url = "modules.php?op=modload&name=assignMission&file=ks_viewunit&subject=".$value["subject_id"]."&publisher=".$data."&indicator=".substr($nodevalue, 0, -4)."&seme=".$seme;
							$start_urlfind = '&map_sn='.$map_no["map_sn"].'&find_nodes='.$nodevalue;
							$exam_url="modules.php?op=modload&name=BayesianTest&file=AdaptiveTestInit&type=mission&ep_id=".$nodevalue;
							$mid = findMappingData($dbh, $nodevalue, $value["subject_id"], $data);
                    if($mission_type == '1'){
                      //影片部分
                      $node_array = nodevideo($dbh, $str_node, $user_id);
                      if(count($node_array) >0){
                        echo '<div class="title01">'.$nodevalue.'<a href="'.$start_url.$start_urlfind.'"><img src="images/start_list.jpg" style="vertical-align:middle;"></a> <a href="'.$course_url.'"><img src="images/course_list.png" style="vertical-align:middle;"></a></div>
                                <div class="personal-box">
                                  <table class="datatable personal-table">
                                    <tr>
                                      <th>學習影片</th>
                                      <th>狀態</th>
                                    </tr>';
                        foreach ($node_array as $node=>$ind){
  												$finish_icon = '';
  												$note_icon = '';
  												$ind_status = update_finish($dbh, $user_id, $ind["indicator"]);
  						  $ind_mov_url='modules.php?op=modload&name=assignMission&file=ks_viewskill&ind='.$ind["indicator"].'&mid='.$mid["mapping_sn"].'#parentHorizontalTab1';
                          //$ind_mov_url='modules/learn_video/video_learn_list.php?indicator='.$ind[indicator];
													if($ind_status){
														$finish_icon ='<img src="img/do.png" alt="function btn" height="30" width="30">';//'(已完成)';
													}else $finish_icon ='<img src="img/undo.png" alt="function btn" height="30" width="30">';
													if($ind["note_sn"] >0){
														//$note_icon = '<input id="show_note" name="note_now" type="image" src="img/note_data.png" alt="function btn" height="50" width="50" videoSn="'.$ind["video_item_sn"].'">';
														$note_icon = '<img src="img/note_data.png" alt="function btn" height="30" width="30">';
													}
                          echo '<tr>
                                  <td><a href="'.$ind_mov_url.'" onclick="history(\'challenge\', this.getAttribute(\'href\'), \'開始挑戰'.$value[mission_nm].'\')">'.$ind[video_nm].'</a></td>
                                  <td>'.$finish_icon.$note_icon.'</td>
                                </tr>
                          ';
                        }
                        echo '</table></div>';
                      }else{
                      		echo '<div class="title01">'.$nodevalue.'</div>';
                      		echo '<div class="personal-box">
                                  <table class="datatable personal-table">';
                      		echo '<tr><th>學習影片</th>
                                      <th>狀態</th></tr>';
                      		echo '<tr><td colspan="2">暫無影片資料</td></tr>';
                      		echo '</table></div>';
                      }                      
                      
                      //練習題部分
                      $node_array2 = nodeprac($dbh, $str_node);
                      if(count($node_array2)>0){
                        echo '
                          <div class="personal-box">
                          	<table class="datatable personal-table">
                                <tr>
                                  <th>練習題</th> 
                                  <th>狀態</th>  
                                </tr>
                        ';
                        foreach ($node_array2 as $node=>$uuid){
                          $prac_finish_icon="";
                          $ind_prac_status=prac_update_finish($dbh, $user_id, $uuid["indicator"]);
                          if($ind_prac_status){
                            $prac_finish_icon ='<img src="img/do.png" alt="function btn" height="30" width="30">';//'(已完成)';
                          }else $prac_finish_icon ='<img src="img/undo.png" alt="function btn" height="30" width="30">';
                          $prac_url='modules.php?op=modload&name=assignMission&file=ks_viewskill&ind='.$uuid["indicator"].'&mid='.$mid["mapping_sn"].'#parentHorizontalTab2';
                          //$prac_url = "modules/Practice/practice.php?indicator=".$uuid["indicator"];
                          
                          echo '
                            <tr>
                              <td><a href="'.$prac_url.'"  onclick="history(\'challenge\', this.getAttribute(\'href\'), \'開始挑戰'.$value[mission_nm].'\')">練習題('.$uuid["indicator"].')</a></td>
                              <td>'.$prac_finish_icon.'</td>
                            </tr>
                          ';
                        }
                        echo '</table></div>';
                      }else{
                      		echo '<div class="personal-box">
                                  <table class="datatable personal-table">';
                      		echo '<tr><th>練習題</th>
                                      <th>狀態</th></tr>';
                      		echo '<tr><td colspan="2">暫無練習題資料</td></tr>';
                      		echo '</table></div>';
                      }  
                    }else if($mission_type == '3'){
                    	if($str_node != ''){
                    		$exam_finish_icon="";
                    		$exam_status=EP2_update_finish($dbh, $user_id, $value["mission_sn"]); //$nodevalue
                    		if($exam_status){
                    			$exam_finish_icon ='<img src="img/do.png" alt="function btn" height="30" width="30">';//'(已完成)';
                    		}else $exam_finish_icon ='<img src="img/undo.png" alt="function btn" height="30" width="30">';
                    		if($str_endYear !=''){
                    			$exam_url2="modules.php?op=modload&name=indicateTest&file=indicateTest&subject=".$value["subject_id"]."&testIndicate=".$nodevalue."&result_sn=".$m_sn."&endYear=".$str_endYear;
                    			//$exam_url2new="modules.php?op=modload&name=indicateTest&file=indicateTest&subject=".$value["subject_id"]."&testIndicate=".$value["node"]."&result_sn=".$m_sn."&endYear=".$str_endYear."&exam_type=".$value["exam_type"];       // 正式區專用
                    			//$exam_url2new="modules.php?op=modload&name=indicateTest&file=indicateAdaptiveTest&subject=".$value["subject_id"]."&testIndicate=".$value["node"]."&result_sn=".$m_sn."&endYear=".$str_endYear."&exam_type=".$value["exam_type"];測試區專用
                    		  $exam_url2new="modules.php?op=modload&name=indicateTest&file=indicateAdaptiveTest&mission_sn=".$m_sn;
                        }else $exam_url2="modules.php?op=modload&name=indicateTest&file=indicateTest&subject=".$value["subject_id"]."&testIndicate=".$nodevalue."&result_sn=".$m_sn;
                    		$onclick = 'onclick="history(\'challenge\', this.getAttribute(\'href\'), \'開始挑戰縱貫測驗\')"';
                    		
                    		if($nodekey ==0){
                    			echo '<tr>
	                              <td>診斷概念('.$nodevalue.')</td>
	    						  <td rowspan="'.count($node_arr).'"><a href="'.$exam_url2new.'" '.$onclick.'>開始測驗</a></td><td>'.$exam_finish_icon.'</td>
	                            </tr>';
                    		}else{
                    			echo '<tr>
	                              <td>診斷概念('.$nodevalue.')</td>
	    						  <td>'.$exam_finish_icon.'</td>
	                            </tr>';
                    		}
                    		/*echo '<tr>
                              <td><a href="'.$exam_url2.'">診斷概念('.$nodevalue.')</a></td>
    						  <td>'.$exam_finish_icon.'</td>
                            </tr>';*/
                    	}
                    }else{
                    	$exam_finish_icon2="";
                    	$exam_status2=EP_update_finish($dbh, $user_id, $nodevalue);
                    	if($exam_status2){
                    		$exam_finish_icon2 ='<img src="img/do.png" alt="function btn" height="30" width="30">';//'(已完成)';
                    	}else $exam_finish_icon2 ='<img src="img/undo.png" alt="function btn" height="30" width="30">';

                    	$cs_id = substr($nodevalue, 0, -2);
                    	$cs_title = CSid2FullName($cs_id);
                    	//日期判斷：$mend_mark
                    	if($mend_mark) {
                    		$onclick = 'onclick="message()"'; 
                    		$exam_url = 'javascript: return false;';
                    	}else{
                    		$onclick = 'onclick="history(\'challenge\', this.getAttribute(\'href\'), \'開始挑戰單元測驗\')"';
                    	}
                      	echo '<tr>
                      		<td><a href="'.$exam_url.'" '.$onclick.'>'.$cs_title.'</a></td>
                      		<td>'.$exam_finish_icon2.'</td>
                      	</tr>';
                    }

                                  
                  }
                  if(($mission_type == '3')||($mission_type == '0')){
                  	echo '</table></div>';
                  }
                  echo '</div>';
                }
               ?>
            </div>
       </div>
    </div>
    </div>
<div id="gotop"><a class="fa fa-chevron-up"></a></div>

<script type="text/javascript" src="scripts/customer.js"></script>
<script>
function message(){
	swal('系統訊息','該測驗任務已到期！');
}

</script>

<script> 
$(document).ready(function(){
    $(".idx-main").hover(function(){
  		$(".idx-main").toggleClass("open-close");
	});
});
</script>

<script type="text/javascript">
	    $(document).ready(function() {
	        //Horizontal Tab
	        $('#parentHorizontalTab').easyResponsiveTabs({
	            type: 'default', //Types: default, vertical, accordion
	            width: 'auto', //auto or any width like 600px
	            fit: true, // 100% fit in a container
	            tabidentify: 'hor_1', // The tab groups identifier
	            activate: function(event) { // Callback function if tab is switched
	                var $tab = $(this);
	                var $info = $('#nested-tabInfo');
	                var $name = $('span', $info);
	                $name.text($tab.text());
	                $info.show();
	            }
	        });
	    });
	</script>

<script type="text/javascript" src="scripts/jsManage.js"></script>
<!-- </body>
</html> -->