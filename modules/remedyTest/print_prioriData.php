<?php
require_once "include/config.php";
require_once "include/adp_API.php";

if (!isset($_SESSION)) {
	session_start();
}


function findIndData($ind_str, $str_map){
	global $dbh;

	$ind_info = $dbh->prepare("SELECT substring(indicate_id,1,6) as bNode, indicate_id as sNode, indicate_name, map_sn
			FROM map_node WHERE FIND_IN_SET(substring(indicate_id,1,6), :indicate)
			AND FIND_IN_SET(map_sn, :map_sn) AND class ='sNodes' ORDER BY indicate_id");
	$ind_info->bindValue(':indicate', $ind_str, PDO::PARAM_STR);
	$ind_info->bindValue(':map_sn', $str_map, PDO::PARAM_STR);
	$ind_info->execute();
	$sub_info = $ind_info->fetchAll(\PDO::FETCH_ASSOC);
	return $sub_info;
}

function prioriExamResult($user_id,$cp_id,$exam_sn){
	global $dbh;

	//require_once "indicateAdaptiveTestStructure.php";

	if(empty($cp_id)){
		die('本功能暫時停止服務。');
	}else{
		$str_query="SELECT * FROM `concept_priori` WHERE cp_id = :cp_id";
		$query =  $dbh->prepare($str_query);
		$query->bindValue(':cp_id', $cp_id, PDO::PARAM_STR);
		$query->execute();
		$exam_tmp = $query->fetch();
		$count_data = $query->rowCount();

		$str_query2="SELECT * FROM `map_info` WHERE subject_id =:sub_id AND display =1";
		$query2 =  $dbh->prepare($str_query2);
		$query2->bindValue(':sub_id', $exam_tmp["subject_id"], PDO::PARAM_STR);
		$query2->execute();
		$map_data = $query2->fetchAll(\PDO::FETCH_COLUMN, 0);
		$count2_data = $query2->rowCount();

	}
	unset($indicateTest);
	$map_sn= implode(",",$map_data);; //星空圖列表
	$subject= $exam_tmp["subject_id"]; //科目
	//$bNode_order=$indicateTest->showBNodeOrder();
	//$sNode_order=array_reverse($indicateTest->showSNodeOrder()); //小節點出題順序(相反)
	$report_title = $exam_tmp["concept"]; //測驗名稱
	$report_range = $exam_tmp["exam_range"]; //測驗日期
	$indicate = $exam_tmp["indicator_item"]; //能力指標
	$priori_ind = $exam_tmp["indicator_priori"]; //學習內容指標
	$bNode_order = explode(_SPLIT_SYMBOL, $indicate);
	$priori_order = explode(_SPLIT_SYMBOL, $priori_ind);
	$tmp_ind = str_replace(_SPLIT_SYMBOL,',',$indicate);
	//大節點對應的小節點
	$sNode_order = findIndData($tmp_ind, $map_sn);

	//報表顯示的節點列表
	foreach($sNode_order as $skey=>$svalue){
		$tmp=sNode2bNode($svalue["sNode"],$subject);
		$show_node_array[$svalue["bNode"]][]=$svalue["sNode"];
		//echo $svalue["bNode"].','.$svalue["sNode"].'<br>';
	}

	//匯入的測驗資料
	if($exam_sn==0){
		$sql='SELECT * FROM exam_record_priori WHERE user_id=:user_id AND cp_id=:cp_id ORDER BY exam_sn DESC LIMIT 1';
		$result=$dbh->prepare($sql);
		$result->bindParam(':user_id',$user_id, PDO::PARAM_STR);
		$result->bindParam(':cp_id',$cp_id, PDO::PARAM_STR);
	}else{
		$sql='SELECT * FROM exam_record_priori WHERE user_id=:user_id AND cp_id=:cp_id AND exam_sn=:exam_sn';
		$result=$dbh->prepare($sql);
		$result->bindParam(':user_id',$user_id, PDO::PARAM_STR);
		$result->bindParam(':cp_id',$cp_id, PDO::PARAM_STR);
		$result->bindParam(':exam_sn',$exam_sn, PDO::PARAM_INT);
	}
	$result->execute();
	$count=$result->rowCount();
	if($count<1){die('<br><br>沒有做答記錄!<br><br>');}
	$row = $result->fetch(PDO::FETCH_ASSOC);
	$exam_sn=$row[exam_sn];
	$examDate = $row[date]; //匯入日期
	$answer = $row[priori_remedy_rate]; //學生的作答結果
	$priori_status_array = explode(_SPLIT_SYMBOL, $answer);

	$sql2='SELECT * FROM map_node_student_status
      WHERE user_id =:user_id AND FIND_IN_SET(map_sn, :map_sn) '; //AND map_sn =:map_sn
	$result2 = $dbh->prepare($sql2);
	$result2->bindParam(':user_id',$user_id, PDO::PARAM_STR);
	$result2->bindParam(':map_sn',$map_sn, PDO::PARAM_INT);
	$result2->execute();
	//$row2 = $result2->fetchAll(PDO::FETCH_ASSOC);

	$sNodeStatus =''; $bNodeStatus='';
	while ($row2=$result2->fetch(PDO::FETCH_ASSOC)){ //多筆處理
		$sNodeStatus = unserialize($row2["sNodes_Status_FR"]);
		$bNodeStatus = unserialize($row2["bNodes_Status"]);

	}
	//因材網目前的狀態--大節點
	foreach( $bNodeStatus as $key=>$ary ){
		if( $ary==null ) continue;
		$node = $key;
		//echo $key.':'.$ary['bstatus:'].'<br>';
		$status = $ary['bstatus:'];
		$ep_node2[] = $node;
		$bNode_status_array[$node] = $status;
		if( $status == 1 ){
			$_SESSION[stuNodeStatus2][sNode][$node]=1;
		}else if( $status == 0 ){
			$_SESSION[stuNodeStatus2][sNode][$node]=0;
		}
	}
	//因材網目前的狀態--小節點
	foreach( $sNodeStatus as $key=>$ary ){
		if( $ary==null ) continue;
		$node = $key;
		$status = $ary['status:'];
		$ep_node[] = $node;
		$sNode_status_array[$key] = $status;
		if( $status == 1 ){
			$_SESSION[stuNodeStatus][sNode][$node]=1;
		}else{
			$_SESSION[stuNodeStatus][sNode][$node]=0;
		}
	}

	$ep_node_str=implode("','",$ep_node);
	//撈出該受試者是否看過影片
	$sql_mov="SELECT MAX(finish_rate) , indicator
	FROM video_concept_item a, video_review_record b
	WHERE a.video_item_sn = b.video_item_sn
	AND a.indicator IN ('{$ep_node_str}')
	AND b.user_id = :user_id
	GROUP BY indicator
	";
	//echo $sql_mov;
	$result_mov=$dbh->prepare($sql_mov);
	$result_mov->bindParam(':user_id',$user_id, PDO::PARAM_STR);
	$result_mov->execute();
	while ($row_mov=$result_mov->fetch(PDO::FETCH_ASSOC)){
		$mov_finish_rate[$row_mov['indicator']]=$row_mov['MAX(finish_rate)'];
	}
	//print_r($mov_finish_rate);
	//die();
	foreach ($bNode_order as $bkey=>$bNode){
		if($show_node_array[$bNode]==''){
			continue;
		}
		if(array_key_exists($bNode,$bNode_status_array)){
			$bNode_status = $bNode_status_array[$bNode]; //因材網狀態：0, 1, -1
		}else $bNode_status = -1;
		$priori_status = $priori_status_array[$bkey]; //科技化評量的狀態：O X 三角

		if($bNode_status==1){
			$_SESSION[stuNodeStatus2][sNode][$bNode]=1;
			$bNode_statusHtml='<img src=".\images\start\p5-4-03.png">';
		}else if($bNode_status==0){
			$_SESSION[stuNodeStatus2][sNode][$bNode]=0;
			$bNode_statusHtml='<img src=".\images\start\p5-4-02.png">';
		}else{
			$bNode_statusHtml='<img src=".\images\start\p5-4-01.png">';
		}
		//從大節點找小節點
		foreach($show_node_array[$bNode] as $sNode){
			/*if($sNode_status_array[$sNode]==''){
				continue;
			}*/
			$status=$sNode_status_array[$sNode];
			$data_mov = $mov_finish_rate[$sNode];
			echo $data_mov;
			//為了概念試題顯示的函數用
			$loop++;
			if($status==1){
				$_SESSION[stuNodeStatus][sNode][$sNode]=1;
				$statusHtml='<a class="dialogify" ><img src=".\images\icon\O.png"></a>';
			}else{
				$_SESSION[stuNodeStatus][sNode][$sNode]=0;
				$statusHtml='
              <a class="dialogify" ><img src=".\images\icon\X.png"></a>
            ';
			}
			$nodeLowNode=$sNode._SPLIT_SYMBOL.implode(_SPLIT_SYMBOL,$ep_node);
			$nodeName = sn2name( $sNode );
			$nodeFile = chkNodeFileExist( $sNode, $bNode, $subject );
			if( $nodeFile[CR] ) $CRHtml=' <img src="./images/icon/cr.png" > ';
			else $CRHtml=' <img src="./images/icon/cr-no.png" > ';
			if( $nodeFile[teach] ) $teachHtml ='<a href="modules.php?op=modload&name=assignMission&file=ks_viewskill&ind='.$sNode.'&mid='.$nodeFile[mapping_sn].'#parentHorizontalTab3" target="_blank"><img src="./images/icon/Interactivity.png" ></a>';//$teachHtml=' <a href="./modules/New_CR/'.$bNode.'/index.html"><img src="./images/icon/Interactivity.png" ></a> ';
			else $teachHtml=' <img src="./images/icon/Interactivity-no.png" > ';
			if( $nodeFile[DA] ) $DAHtml='';
			else $DAHtml='';
			if( $nodeFile[video] ) $videoHtml='<a href="modules.php?op=modload&name=assignMission&file=ks_viewskill&ind='.$sNode.'&mid='.$nodeFile[mapping_sn].'#parentHorizontalTab1" target="_blank"><img src="./images/icon/mov.png" style="width:40px"></a>';
			else $videoHtml='<img src="./images/icon/mov-no.png" style="width:40px">';
			if( $data_mov>0 && $data_mov<100 ) $videoHtml.='<br>已觀看'.$data_mov.'%';
			else if($data_mov==100 ) $videoHtml.='<br>觀看完畢';
			if( $nodeFile[prac] ) $pracHtml='<a href="modules.php?op=modload&name=assignMission&file=ks_viewskill&ind='.$sNode.'&mid='.$nodeFile[mapping_sn].'#parentHorizontalTab2" target="_blank"><img src="./images/icon/item.png" ></a>';
			else $pracHtml='<img src="./images/icon/item-no.png" >';

			//echo 'sNode:'.$sNode.'<br>';
			$tmpHtml[$bNode][$sNode]='
            <tr>
            <td> <a class="show" title="'.$nodeName.'" href="modules/D3/app/index_view.php?aa='.base64_encode($_SESSION[user_id]).'&map_sn='.$map_sn.'&find_nodes='.$nodeLowNode.'" target="_blank">'.$sNode.'</a></td>
            <td> '.$statusHtml.'</td>
            <td> '.$videoHtml.'</td>
            <td> '.$pracHtml.'</td>
            <td> <a href="#" class="btn02">全測</a> <br> <a href="#" class="btn03">適性</a> </td>
            <td> <select><option>請選擇</option> </select></td></tr>
          ';

		}
		//print_r($tmpHtml);
		$sNode_count=count($show_node_array[$bNode]);
		if(empty($tmpHtml3[$bNode])){
			foreach($tmpHtml[$bNode] as $ary2){
				//print_r($ary2);
				if(!empty($ary2)) $tmpHtml3[$bNode].= $ary2; //implode('',$ary2)
			}
		}
		$tmpHtml2[$bNode]='<tr> <td rowspan="'.($sNode_count+1).'" >'.$bNode.'</td>'.
				'<td rowspan="'.($sNode_count+1).'" >'.$priori_status.'</td>'.
				'<td rowspan="'.($sNode_count+1).'" >'.$bNode_statusHtml.'</td>'.$tmpHtml3[$bNode];
		//print_r($tmpHtml2);
	}

	$reportHtml = '
      <div class="content2-Box">
        <div class="main-box">';
	if($exam_sn==0){$reportHtml.='<div class="right-box">';}
	$reportHtml.='<div class="title01">
			  <span>'.$report_title.'</span><br>
              <span class="color-green">測驗班級：'.$_SESSION[user_data]->cht_class.'</span><br>
              <span class="color-green">目前狀態：全部'.'</span>
            </div>
            <div class="table_scroll">
            <table class="datatable">
              <tr>
                <th>能力指標</th>
                <th>科技化<br>評量結果</th>
            	<th>因材網<br>指標狀態</th>
                <th>因材網子節點</th>
                <th>節點狀態</th>
                <th>影片</th>
                <th>練習題</th>
              	<th>進階診斷</th>
              	<th>診斷報告</th>
              </tr>
                '.implode('',$tmpHtml2).'
            </table>
            </div>'; //
	if($exam_sn==0){$reportHtml.='</div>';}
	$reportHtml.='</div>
      </div>
    ';

	unset($_SESSION['indicateInfo']);
	return $reportHtml;
}

if (isset($_GET['showperson']) && 'Y' === $_GET['showperson']) {
	$user_id = $_GET['studentid'];
	$cp_id = $_GET['cp_id'];
	$exam_sn = $_GET['exam_sn'];
	
	echo prioriExamResult($user_id,$cp_id,$exam_sn);
}
