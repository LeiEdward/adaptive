
<script type="text/javascript">
$(document).ready(function(){	
    		$("td[id*=all_]").on( "click", function(){
        		
    	 		var id_split = $(this).attr("id").split("_");
    	 		
    	  		var user_id = id_split[1];
    	  		var btnS = $(this).html().split(" ");
    	  		console.log(btnS);
    	  		//alert( [nodes,$(this).html()] );
    	  		if( btnS[0]==="＋" ){
    	    		$("tr[class*=sub_"+user_id+"]").show();
    	    		$(this).html("－ "+btnS[1]);
    	  		}else{
    	    		$("tr[class*=sub_"+user_id+"]").hide();
    	    		$(this).html("＋ "+btnS[1]);
    	  		}
    			} );

		// 170908，滑鼠移到頭像去，會出現tooltips。
		$("vbox-overlay.vbox-container.vbox-content.vbox-inline.figlio.datatable.datatable-l.role").tipsy({html: true});
	});
</script>

<?php
require_once "HTML/QuickForm.php";
require_once "adp_API.php";
require_once "read_excel.inc.php";

ini_set('display_errors', 1);
$module_name="ExamResult";

if(!$auth->checkAuth()){
	require_once "feet.php";
	die();
}
echo "<center>";
//EXAM_RESULT_table_header();
//-- 顯示主畫面

$user_id_t = $_SESSION['user_id'];

if($_REQUEST['set_opt']==''){
	$_REQUEST['set_opt']="classExamResults";  //預設值
}
// if($_REQUEST['output_excel']==1){
//   make_excel($_REQUEST['class_ep']);  
// }
listCLASSandCS($_REQUEST['set_opt']);

// 	print_r($ind_row);
//debugBAI( __LINE__, __FILE__, $user_data );
function listCLASSandCS($set_opt){
	global $dbh, $module_name, $user_data;
	$Nowseme=getYearSeme();
	$_SESSION[userData]=$user_data;
	$form = new HTML_QuickForm('frmadduser','post',$_SERVER['PHP_SELF']);
	if( $_SESSION[userData]->access_level==21 ){
		//看該教師是否有再其他班級授課
		$sql_sems='
			SELECT *
			FROM seme_teacher_subject
			WHERE teacher_id = "'.$user_data->user_id.'"
		';
		//debugBAI( __LINE__,__FILE__, $sql_sems, 'print_r' );
		$re_sems=$dbh->query($sql_sems);
		while( $data_sems=$re_sems->fetch() ){
			$sql_rule[] = $data_sems[seme_year_seme].$data_sems[organization_id].':'.$data_sems[grade].'-'.$data_sems["class"];
		}
		if( count($sql_rule)>0){
			$sql = '
    		select distinct b.seme_year_seme seme, a.city_code, a.organization_id, b.grade, b.class
    		from user_info a,seme_teacher_subject b
    		where a.user_id = "'.$user_data->user_id.'" AND a.user_id = b.teacher_id
    		ORDER BY seme DESC
  			';
			$sql_now='Select * From user_info where user_id ="'.$user_data->user_id.'"';
			$user_now =$dbh->query($sql_now);
			$user_nowa=$user_now->fetch();
			// 			$user_now->execute();
			$user_class=$Nowseme.$user_nowa['organization_id'].':'.$user_nowa['grade'].'-'.$user_nowa['class'];
			if (! in_array($user_class, $sql_rule)){
				$se=$Nowseme;
				$cc=$user_nowa['city_code'];
				$oi=$user_nowa['organization_id'];
				$gr=$user_nowa['grade'];
				$cl=$user_nowa['class'];
				$gc=$gr.'@XX@'.$cl;
				$select1[$se]=Semester_id2FullName($se);
				$select2[$se][$cc]=id2city($cc);
				$select3[$se][$cc][$oi]=id2org($oi);
				$select4[$se][$cc][$oi][$gc]="$gr 年 $cl 班";
			}
				
		}else{
			$sql = '
    		select distinct city_code, organization_id, grade, class
    		from user_info
			WHERE organization_id="'.$user_data->organization_id.'"
	    	AND grade="'.$user_data->grade.'"
	    	AND class="'.$user_data->class_name.'"
    		ORDER BY city_code
  			';
		}
	}else {
		$sql = '
    	select distinct city_code, organization_id, grade, class
    	from user_info
    	ORDER BY city_code
  		';
	}
	$result =$dbh->query($sql);
	while ($row=$result->fetch()){
		if($row['seme']==''){
			$se=$Nowseme;
		}else{
			$se=$row['seme'];
		}
		$cc=$row['city_code'];
		$oi=$row['organization_id'];
		$gr=$row['grade'];
		$cl=$row['class'];
		$gc=$gr.'@XX@'.$cl;
		$select1[$se]=Semester_id2FullName($se);
		$select2[$se][$cc]=id2city($cc);
		$select3[$se][$cc][$oi]=id2org($oi);
		$select4[$se][$cc][$oi][$gc]="$gr 年$cl 班";
	}
	//-- 顯示選單
  echo('
	 <div class="content2-Box">
    	<div class="path">目前位置：班級成績</div>
       <div class="main-box">
            <div class="left-box discuss-select">
				<a href="modules.php?op=modload&name=ExamResult&file=classErrorStatistics&set_opt=classErrorStatistics" class="btn02">班級學習狀態</a>
        		<a href="modules.php?op=modload&name=ExamResult&file=classesInfo_teacher" class="btn02">班級節點狀態</a>       
				<a href="modules.php?op=modload&name=ExamResult&file=classReports" class="btn02">學生診斷報告</a>
				<a href="modules.php?op=modload&name=learn_video&file=video_report_list" class="btn02">影片瀏覽報告</a>
            </div>
            <div class="right-box">
            	<div class="title01">班級學習狀態</div>
  		 		<font class="color-blue"> <i class="fa fa-square"></i>原查詢功能 已移至 任務進度 依任務進行查詢 「班級學習狀態」。</font>
            	<div class="class-list2 test-search table_scroll">
  ');
//                 if( !is_array( $select5 ) ) echo('<h3>無作答紀錄</h3>');
//                 else{
                  $optionEvent = ' class="input-normal" ';
                  $sel =& $form->addElement('hierselect', 'class_ep', '', $optionEvent);
                	// And add the selection options
                	$sel->setOptions(array($select1, $select2, $select3, $select4));
                	$form->addElement('hidden','op','modload');
                	$form->addElement('hidden','name',$module_name);
                	$form->addElement('hidden','file','classErrorStatistics');
                	$form->addElement('hidden','opt',$set_opt);
                	$btnEvent = 'style="display: block; margin: 0 auto;" class="btn04"';
                  $form->addElement('submit','btnSubmit','送出', $btnEvent);
                	$form->display();
//                 }

  echo('      </div>');
              if($_REQUEST['opt']=='classErrorStatistics')
            	 classErrorStatistics($_REQUEST['class_ep']);
  echo('
            </div>
       </div>
    </div>
  ');



	//echo '如果CSV檔案採「UTF-8」編碼，請使用【<a href="http://210.240.187.23/bnat/oo3_990410.exe">OpenOffice</a>】開啟檔案';
}
$bNodeTmpAry = array();

// print_r($ind_row);

function classErrorStatistics($class_ep){
	
	global $dbh;
	$seme=$class_ep[0];
	$city=$class_ep[1];
	$organization=$class_ep[2];
	$school_name=id2city($city).'-'.id2org($organization);
	$grade_class = $class_ep[3];
	$gc=explode("@XX@",$grade_class);
	$grade=$gc[0];
	$class=$gc[1];
	$sql='
				SELECT *
				FROM seme_student a, user_status b
				WHERE a.organization_id="'.$organization.'" AND a.grade="'.$grade.'" AND a.class="'.$class.'" AND a.seme_year_seme="'.$seme.'"  AND a.stud_id=b.user_id 
				ORDER BY seme_year_seme, grade, class
			';
	$re = $dbh->query($sql);
	$classInfo =$re->rowCount();
	
	$ind_row=array();
	$indicate=$dbh->query("SELECT indicate_name, indicate_id FROM `map_node`");
	while($indicate_row=$indicate->fetch() ){
		$ind_row[$indicate_row[indicate_id]]=$indicate_row[indicate_name];
	
	}
	
	if($classInfo>0){
	while($data=$re->fetch()){
		$grade = $data[grade];
		$classes = $data['class'];
		$user=$data[user_id];
		$sub_ary = array();
		$node_g=array();

		$sql_nodeStatus='
				SELECT *
				FROM map_node_student_status , map_info
				WHERE map_node_student_status.user_id="'.$user.'"  AND map_node_student_status.map_sn=map_info.map_sn
			';
		
	$re_nodeStatus=$dbh->query( $sql_nodeStatus );
	$allnode_pass=0;
	$Allnode_num=0;
	$allnode_nopass=0;
	$pass_node='';
	
	while($data_nodeStatus = $re_nodeStatus->fetch()){
		
		$subject=$data_nodeStatus[subject_id];
		
		$bNodeS = unserialize( $data_nodeStatus[bNodes_Status] );
		$sNodeS = unserialize( $data_nodeStatus[sNodes_Status_FR] );

		if( !is_array($sNodeS) ){
			debugBAI(__LINE__,__FILE__, 'sNodes is null. '.$user);
			continue;
		}
		foreach ($sNodeS as $key =>$value){
			$bNodeAry = sNode2bNode($key,$subject);
			$Node_grade= $bNodeAry[0];
			if(isset($value['status:'])){
				if( $value['status:']==1 ){
					$Allnode_num++;
					$allnode_pass++;
					$user_node[$user][$subject][pass]++;
					$user_node[$user][$subject][all_node]++;
					if(substr($pass_node[$user][$subject][$bNodeAry[0]], -1)=='<br>'){
						$pass_node[$user][$subject][$bNodeAry[0]].='<div class="tooltip">'.$key.'<span class="tooltiptext" style=" padding-left: 5px; padding-right: 5px;">'.$ind_row["$key"].'</span></div>,';
					}elseif(substr($pass_node[$user][$subject][$bNodeAry[0]], -1)==','){
						$pass_node[$user][$subject][$bNodeAry[0]].='&nbsp;<div class="tooltip">'.$key.'<span class="tooltiptext" style=" padding-left: 5px; padding-right: 5px;">'.$ind_row["$key"].'</span></div><br>';
					}else{
						$pass_node[$user][$subject][$bNodeAry[0]].='<div class="tooltip">'.$key.'<span class="tooltiptext" style=" padding-left: 5px; padding-right: 5px;">'.$ind_row["$key"].'</span></div>,';
					}
					
					if( !in_array( $subject, $sub_ary ) ){
						$sub_ary[]=$subject;
					}
					if($Node_grade!=''){
						$node_g_keys=$node_g[$subject];
						if( ($node_g_keys==null) ||  !in_array( $Node_grade, $node_g[$subject] ) ){
							$node_g[$subject][]=$Node_grade;
						}
					}
				}elseif($value['status:']==0){
					$Allnode_num++;
					$allnode_nopass++;
					$user_node[$user][$subject][nopass]++;
					$user_node[$user][$subject][all_node]++;
					if(substr($nopass_node[$user][$subject][$bNodeAry[0]], -1)=='<br>'){
						$nopass_node[$user][$subject][$bNodeAry[0]].='<div class="tooltip">'.$key.'<span class="tooltiptext" style=" padding-left: 5px; padding-right: 5px;">'.$ind_row["$key"].'</span></div>,';
					}elseif(substr($nopass_node[$user][$subject][$bNodeAry[0]], -1)==','){
						$nopass_node[$user][$subject][$bNodeAry[0]].='&nbsp;<div class="tooltip">'.$key.'<span class="tooltiptext" style=" padding-left: 5px; padding-right: 5px;">'.$ind_row["$key"].'</span></div><br>';
					}else{
						$nopass_node[$user][$subject][$bNodeAry[0]].='<div class="tooltip">'.$key.'<span class="tooltiptext" style=" padding-left: 5px; padding-right: 5px;">'.$ind_row["$key"].'</span></div>,';
					}
					if( !in_array( $subject, $sub_ary ) ){
						$sub_ary[]=$subject;
					}
					if($Node_grade!=''){
						$node_g_keys=$node_g[$subject];
						if(($node_g_keys==null) || !in_array( $Node_grade, $node_g[$subject] ) ){
							$node_g[$subject][]=$Node_grade;
						}
					}
				}
			}
		}
	}
	if($Allnode_num==0) $Allnode_num=1;
	$passPer = round(($allnode_pass/$Allnode_num) * 100);
	$noPassPer = round(($allnode_nopass/$Allnode_num) * 100);
	

	$nodeHtml[$user]='
			<tr>
				<td> '.id2uname($user).'
				<td id= "all_'.$user.'" style="text-align: center; cursor: pointer;">＋ 全科目
				<td> <div class="progress progress-success" style="margin-bottom: 0px; height: 36px; "><div class="bar"  style="width:'.$passPer.'%; background-image: linear-gradient(to bottom, #00AA00, #46dd46);"><span style="color: rgba(93, 93, 93, 0.97);font-size: 17px;">('.$passPer.'%)</span>
					</div>
					</div>
		
		';

	foreach($sub_ary as $subject ){
		$sub_nm=sub_name($subject);
		$sub_nopassper[$subject]=round(($user_node[$user][$subject][nopass]/$user_node[$user][$subject][all_node]) * 100);
		$sub_passper[$subject]=round(($user_node[$user][$subject][pass]/$user_node[$user][$subject][all_node]) * 100);
		$tmp3[]='';
		$tmp3[]= '
		'.id2uname($user).'&nbsp;&nbsp;&nbsp;'.$sub_nm;
		$tmp3[]='<table class="datatable datatable-l">';
		$tmp3[]='<tr>';
		$tmp3[]='<th  style="width:20%;">年級  <th  style="width:40%;">精熟節點<th  style="width:40%;">未精熟節點' ;
		$row=array();
		foreach($node_g[$subject] as $grade ){
			$row[]=$grade;
		}
		arsort($row);
		foreach($row as $grade ){
			$tmp3[].='<tr>
					<td> '.$grade.'
					<td style="text-align: left;">'.$pass_node[$user][$subject][$grade].'
					<td style="text-align: left;">'.$nopass_node[$user][$subject][$grade].'
							
							
							';
		}
		$tmp3[]='</table>';
		$nodeHtml[$user].='
					<tr class="gray sub_'.$user.$subject.'" style="display:none;" >
 							    <td>
				                <td style="text-align: center;"> '.$sub_nm.'
				                <td> <div class="progress progress-info" style="margin-bottom: 0px; height: 36px;">
				<div class="bar" data-title="tips_html" style="width:'.$sub_passper[$subject].'%; background-image: linear-gradient(to bottom, #09c, #4ec5ed);">
							<a class="venoboxinline"  data-title="節點狀態"  data-gall="gall-frame2" data-type="inline"  href="#inline-content'.$user.$subject.'"> 
							<span style="color: rgba(93, 93, 93, 0.97);font-size: 17px;">'.$user_node[$user][$subject][pass].'('.$sub_passper[$subject].'%) </span></div>
				            </a></div></div>
							<div id="inline-content'.$user.$subject.'" class="personal-inline">'.implode('',$tmp3).'</div>
				            ';

// 		}
			unset($tmp3);
			unset($row);
	}
	
// 	unset($sub_ary);
	
	}
	ksort($nodeHtml);
	$reportHtml[]='<br><div>';
  $reportHtml[]='<div class="table_scroll">';
	$reportHtml[]='
		<h3>
			全班人數:'.$classInfo.' 人
		</h3>
	';
  $reportHtml[]='<table class="datatable datatable-l">';
  //$reportHtml[]='<td>';
  //$reportHtml[]='<td>學校：'.$userData->organization_name.' <td>班級：'.$userData->cht_class.' <td>總人數:'.$useData[stuNum].' <td> ';
  $reportHtml[]='<tr>';
  $reportHtml[]='<th  style="width:20%;">姓名  <th  style="width:30%;">科目<th  style="width:50%;">學習情形：通過節點數(節點通過率%)' ;//通過人數(通過人數/施測人數)
  $reportHtml[]=implode('',$nodeHtml);
  $reportHtml[]='</table>';
  $reportHtml[]='</div></div>';
	echo implode('',$reportHtml);
	}else echo "此班級無學生！";
}


function sub_name($sub) {
	global $dbh;
	$sql = "SELECT map_name FROM `map_info` where subject_id='$sub' AND display=1";
	//$data =& $dbh->getOne($sql);
	$data = $dbh->query($sql);
	$row = $data->fetch();
	//echo "<br>".$row['name']."<br>";
	return $row['map_name'];
}
?>


    
<style>
.tooltip {
    position: relative;
    display: inline-block;
/*     border-bottom: 1px dotted black; */
}

.tooltip .tooltiptext {
    visibility: hidden;
    width: 320px;
    background-color: #555;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 5px 0;
    position: absolute;
    z-index: 1;
    bottom: 125%;
    left: 50%;
    margin-left: -160px;
    opacity: 0;
    transition: opacity 1s;
}

.tooltip .tooltiptext::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #555 transparent transparent transparent;
}

.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}
</style>
