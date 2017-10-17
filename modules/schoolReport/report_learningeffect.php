<?php
include_once('include/config.php');
require_once('feet.php');
include_once('classes/PHPExcel.php');

function classErrorStatistics($class_ep) {
	global $dbh;
  // $class_ep Array ( [0] => 1061 [1] => 19 [2] => 190041 [3] => 6@XX@1 )
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
