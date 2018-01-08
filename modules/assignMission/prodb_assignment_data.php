<?php
include_once "../../include/config.php";
include_once "../../include/adp_core_function.php";
?>
<?php
session_start();

$ind_node='';
$target_data ='';
$insert_mark=false;
$user_id = $_SESSION['user_id'];

if(isset($_SESSION['user_id'])){
	$user_data = $dbh->prepare("SELECT info.user_id, info.uname,info.organization_id, org.name org_name , info.city_code, city.city_name, info.grade, info.class, s.access_level, acc.access_title
				FROM user_info info, user_status s, user_access acc, city , organization org
				where info.city_code = city.city_code and info.organization_id = org.organization_id and info.user_id = s.user_id and s.access_level = acc.access_level and info.user_id = :user_id");
	$user_data->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_STR);
	$user_data->execute();
	$userinfo = $user_data->fetch();
	$level = $userinfo["access_level"];
	$org = $userinfo["organization_id"];
	$grade = $userinfo["grade"];
	$class = $userinfo["class"];
}

if($_POST["mission_type"] == '3'){
	$concept_query = $dbh->prepare("SELECT cs_id FROM `concept_info` WHERE unit = :unit AND subject_id = :subject_id");
	$concept_query->bindValue(':unit', '99', PDO::PARAM_STR);
	$concept_query->bindValue(':subject_id', $_POST["subject_id"], PDO::PARAM_STR);
	$concept_query->execute();
	$concept_data = $concept_query->fetch();
	$full_cs = $concept_data["cs_id"];
}

if($_POST["type"] == '2' && isset($_POST["yearnm"])){
	$resultdata2 = array();
	$mission_str2 = "SELECT DISTINCT organization_id, grade, class FROM seme_teacher_subject WHERE teacher_id = :teacher_id AND seme_year_seme = :seme ORDER BY grade,class ASC";
	$mission_data2 = $dbh->prepare($mission_str2);
	$mission_data2->bindValue(':seme', $_POST["yearnm"], PDO::PARAM_STR);
	$mission_data2->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
	$mission_data2->execute();
	$mission_count =$mission_data2->rowCount();

	$class_nm="SELECT organization_id, grade,class FROM user_info WHERE user_id = :teacher_id";
	$class_data = $dbh->prepare($class_nm);
	$class_data->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
	$class_data->execute();

	$i=0;
	if($mission_count==0){
		while ($row3 =$class_data->fetch(\PDO::FETCH_ASSOC)){
			if($row3["class"]<10)
			{
				$row3["class"]="0".$row3["class"];
			}
			// 		$resultdata2[$i] = $row2["organization_id"]."-".$row2["grade"].$row2["class"];
			$resultdata2[$i] = $row3;
		}
	}else{
		while ($row2 = $mission_data2->fetch(\PDO::FETCH_ASSOC)){
			if($row2["class"]<10)
			{
				$row2["class"]="0".$row2["class"];
			}
			// 		$resultdata2[$i] = $row2["organization_id"]."-".$row2["grade"].$row2["class"];
			$resultdata2[$i] = $row2;
			$i++;
		}
	}
	echo  json_encode($resultdata2);
}

if($_POST["type"] == '1' && isset($_POST["classnm"])){
	$resultData = array();
	if($_POST["classnm"] !='all'){
	//班級任務
		$mission_str = "SELECT a.mission_sn, a.mission_nm, a.target_type, a.node, a.target_id, a.mission_type
			FROM mission_info as a
			WHERE unable=1 AND end_mk='N' AND target_id = :target_id AND teacher_id =:teacher_id AND semester = :yearnm 
			ORDER BY a.mission_sn DESC";
		$mission_data = $dbh->prepare($mission_str);
		$mission_data->bindValue(':target_id', $_POST["classnm"], PDO::PARAM_STR);
		$mission_data->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
		$mission_data->bindValue(':yearnm', $_POST["yearnm"], PDO::PARAM_INT);
		$mission_data->execute();
	}else{
		$mission_str = "SELECT  a.mission_sn, a.mission_nm, a.target_type, a.node, a.target_id, a.mission_type
			FROM mission_info as a
			WHERE unable=1 AND end_mk='N' AND teacher_id =:teacher_id AND target_type ='C' AND semester = :yearnm  
			ORDER BY a.mission_sn DESC";
		$mission_data = $dbh->prepare($mission_str);
		$mission_data->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
		$mission_data->bindValue(':yearnm', $_POST["yearnm"], PDO::PARAM_INT);
		$mission_data->execute();
	}
	//$sub_mission = $mission_data->fetchAll(\PDO::FETCH_ASSOC);
	//個人任務
	$mission_str2 = "SELECT  a.mission_sn, a.mission_nm, a.target_type, a.node, a.target_id, a.mission_type
			FROM mission_info as a 
			WHERE unable=1 AND end_mk='N' AND teacher_id =:teacher_id AND target_type ='I' AND semester = :yearnm   
			ORDER BY a.mission_sn DESC";
	$mission_data2 = $dbh->prepare($mission_str2);
	$mission_data2->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
	$mission_data2->bindValue(':yearnm', $_POST["yearnm"], PDO::PARAM_INT);
	$mission_data2->execute();
	//$sub_mission2 = $mission_data2->fetchAll(\PDO::FETCH_ASSOC);
	$i=0;
	while ($row = $mission_data->fetch(\PDO::FETCH_ASSOC)){
		$resultData[$i] = $row;
		$i++;
	}

	while ($row = $mission_data2->fetch()){
		$resultData[$i] = $row;
		$i++;
	}

	echo  json_encode($resultData);
}

$topic_count=0;
if(isset($_POST["mission_nm"])&&isset($_POST["target_id"])){
	$user_id = $_SESSION['user_id'];
	$time = date ("YmdHis");
	$co = count($_POST["node"]);
	if (count($_POST["node"]) >1){
		foreach ($_POST["node"] as $key=> $value){
			$co2 = _SPLIT_SYMBOL;
			$ind_node = $ind_node.$value._SPLIT_SYMBOL;
		}
	}else $ind_node = $_POST["node"][0];

	$count3 = count($_POST["endYear"]);
	if($count3 >0){
		if (count($_POST["endYear"]) >1){
			foreach ($_POST["endYear"] as $key=> $value){
				$co2 = _SPLIT_SYMBOL;
				$endYear_node = $endYear_node.$value._SPLIT_SYMBOL;
			}
		}else $endYear_node = $_POST["endYear"][0];
	}else $endYear_node='';


	if (count($_POST["data_type"]) >0){
		foreach ($_POST["data_type"] as $key=> $value){
			if($value =='ques'){
				$insert_mark = true;
			}
		}
	}


// 	if($_POST["target_id"][0] =="patch"){
// 		$_POST["type"]=2;//patch
// 		array_splice($_POST["target_id"],0, 1);
// 	}else{
	 	if($_POST["mission_class"] =='I'){
			$stu_co = count($_POST["target_id"]);
			if (count($_POST["target_id"]) >0){
				foreach ($_POST["target_id"] as $key2=> $value2){
					$stu_co2 = _SPLIT_SYMBOL;
					$target_student = $target_student.$value2._SPLIT_SYMBOL;
				}
			}else $target_student = $_POST["target_id"][0];
			$target_data = $target_student;
		}else if ($_POST["mission_class"] =='B'){
			$_POST["type"]=2;//patch
		}else $target_data = $_POST["target_id"];
// 	}


	$topic_num=0;
	//單元題數計算 BlueS 20170623
	if ($_POST["mission_type"]=="0"){
		$topic=$dbh->prepare("SELECT count(*) num FROM `concept_item` where exam_paper_id =:exam_paper_id");
		$topic->bindValue(':exam_paper_id', $ind_node, PDO::PARAM_STR);
		$topic->execute();
		$topic_count=$topic->fetch();
		$topic_num=$topic_count["num"];
	}elseif ($_POST["mission_type"]=="3")//縱貫題數計算 BlueS 20170628
	{
		$bNodeAry = explode( _SPLIT_SYMBOL ,$ind_node);
		if(empty($bNodeAry[count($bNodeAry)]) && count($bNodeAry)>1 ){
			unset($bNodeAry[count($bNodeAry)-1]);
		}
		for( $i=0;$i<=100;$i++ ){
			if( $bNodeAry[$i]==null ){
				unset( $bNodeAry[$i] );
				break;
			}

			if($_POST["endYear"][$i]!=0){
 				$tmp_nodeYear = sNode2bNode( $bNodeAry[$i],$_POST["subject_id"] );
				$nodeYear = $tmp_nodeYear[0];
				if( $_POST["endYear"][$i] > $nodeYear ){
					unset($bNodeAry[$i]);
					continue;
				}
			}

			$sql='
	      SELECT indicate_low
	      FROM indicateTest
	      WHERE indicate="'.$bNodeAry[$i].'"
	    ';
			$re=$dbh->query($sql);
			$data=$re->fetch();
			$tmp = explode( _SPLIT_SYMBOL, $data[indicate_low] );

			foreach( $tmp as $val ){
				if( in_array( $val,$bNodeAry ) ) continue;
				if( $val==null ) continue;
				$bNodeAry[] = $val;
			}
		}
	foreach($bNodeAry as $key=>$value)
	{
		$sql3='SELECT count(*) sum FROM concept_item
			WHERE indicator like "'.$value.'%" AND double_item = 0 AND paper_vol=1
	      AND (cs_id LIKE "023%" OR cs_id LIKE "021%" OR cs_id LIKE "020%")';
		$re3=$dbh->query($sql3);
		$sum=$re3->fetch();
		$topic_num=$topic_num+$sum["sum"];
	}
	}

	if(empty($_POST["run_examtype"])){
		$exam_type = '0';
	}else $exam_type = $_POST["run_examtype"];

	if ($_POST["type"] =='1'){

		$result = $dbh->prepare("INSERT INTO mission_info (mission_nm, target_id, subject_id, node, date, semester, teacher_id, target_type, mission_type, create_date, start_time, endYear, topic_num, exam_type)
			VALUES (:mission_nm, :target_id, :subject_id, :node, :date, :semester, :teacher_id, :target_type, :mission_type, :create_date, :Sdate, :endYear, :topic_num, :exam_type)");//Sdate開始時間 BlueS 2017.07.06
		$result->bindValue(':mission_nm', $_POST["mission_nm"], PDO::PARAM_STR);
		$result->bindValue(':target_id', $target_data, PDO::PARAM_STR);
		$result->bindValue(':subject_id', $_POST["subject_id"], PDO::PARAM_INT);
		$result->bindValue(':node', $ind_node, PDO::PARAM_STR);
		$result->bindValue(':date', $_POST["enddate"], PDO::PARAM_STR);
		$result->bindValue(':Sdate', $_POST["strdate"], PDO::PARAM_STR);
		$result->bindValue(':semester', $_POST["semester"], PDO::PARAM_STR);
		$result->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
		$result->bindValue(':target_type', $_POST["mission_class"], PDO::PARAM_STR);
		$result->bindValue(':mission_type', $_POST["mission_type"], PDO::PARAM_STR);
		$result->bindValue(':create_date', $time, PDO::PARAM_STR);
		$result->bindValue(':endYear', $endYear_node, PDO::PARAM_STR);
		$result->bindValue(':topic_num', $topic_num, PDO::PARAM_INT);
		$result->bindValue(':exam_type', $exam_type, PDO::PARAM_INT);
		$result->execute();
		//$insert_mark = true;

	}else if ($_POST["type"] =='2'){//patch BlueS 20170712
		foreach ($_POST["target_id"] as $key=>$value){
			$result = $dbh->prepare("INSERT INTO mission_info (mission_nm, target_id, subject_id, node, date, semester, teacher_id, target_type, mission_type, create_date, start_time, endYear, topic_num, exam_type)
			VALUES (:mission_nm, :target_id, :subject_id, :node, :date, :semester, :teacher_id, :target_type, :mission_type, :create_date, :Sdate, :endYear, :topic_num, :exam_type)");//Sdate開始時間 BlueS 2017.07.06
			$result->bindValue(':mission_nm', $_POST["mission_nm"], PDO::PARAM_STR);
			$result->bindValue(':target_id', $value, PDO::PARAM_STR);
			$result->bindValue(':subject_id', $_POST["subject_id"], PDO::PARAM_INT);
			$result->bindValue(':node', $ind_node, PDO::PARAM_STR);
			$result->bindValue(':date', $_POST["enddate"], PDO::PARAM_STR);
			$result->bindValue(':Sdate', $_POST["strdate"], PDO::PARAM_STR);
			$result->bindValue(':semester', $_POST["semester"], PDO::PARAM_STR);
			$result->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
			$result->bindValue(':target_type', "C", PDO::PARAM_STR);
			$result->bindValue(':mission_type', $_POST["mission_type"], PDO::PARAM_STR);
			$result->bindValue(':create_date', $time, PDO::PARAM_STR);
			$result->bindValue(':endYear', $endYear_node, PDO::PARAM_STR);
			$result->bindValue(':topic_num', $topic_num, PDO::PARAM_INT);
			$result->bindValue(':exam_type', $exam_type, PDO::PARAM_INT);
			$result->execute();
		}
	
	}
	if($insert_mark){//學習問卷
		$result = $dbh->prepare("INSERT INTO mission_info (mission_nm, target_id, subject_id, node, date, semester, teacher_id, target_type, mission_type, create_date, start_time)
			VALUES (:mission_nm, :target_id, :subject_id, :node, :date, :semester, :teacher_id, :target_type, :mission_type, :create_date, :Sdate)");

		$mis_nm = $_POST["mission_nm"].'--學習問卷';
		$result->bindValue(':mission_nm', $mis_nm, PDO::PARAM_STR);
		$result->bindValue(':target_id', $target_data, PDO::PARAM_STR);
		$result->bindValue(':subject_id', $_POST["subject_id"], PDO::PARAM_INT);
		$result->bindValue(':node', 'viewform', PDO::PARAM_STR);
		$result->bindValue(':date', $_POST["enddate"], PDO::PARAM_STR);
		$result->bindValue(':Sdate', $_POST["strdate"], PDO::PARAM_STR);
		$result->bindValue(':semester', $_POST["semester"], PDO::PARAM_STR);
		$result->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
		$result->bindValue(':target_type', $_POST["mission_class"], PDO::PARAM_STR);
		$result->bindValue(':mission_type', '2', PDO::PARAM_STR);
		$result->bindValue(':create_date', $time, PDO::PARAM_STR);
		$result->execute();
	}
	$html = <<<EOF
	insert success...
EOF;

	echo $html;


	$dbh = NULL;
}
$nowseme=getYearSeme();
if($_POST["type"] == '4'){
	$seme_teacher_data = $dbh->prepare("SELECT organization_id,grade,class FROM seme_teacher_subject where teacher_id=:user_id and seme_teacher_subject.seme_year_seme=:nowseme ORDER BY grade,class ASC");
	$seme_teacher_data->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_STR);
	$seme_teacher_data->bindValue(':nowseme', $nowseme, PDO::PARAM_STR);
	$seme_teacher_data->execute();

	$user_info_class = $dbh->prepare("SELECT organization_id,grade,class FROM user_info where user_id=:user_id");
	$user_info_class->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_STR);
	$user_info_class->execute();
	$teacher_class = $user_info_class->fetch(\PDO::FETCH_ASSOC);
	$classData[0]= $teacher_class;
	$i=1;
	while ($seme_teacher = $seme_teacher_data->fetch(\PDO::FETCH_ASSOC)){
		if($seme_teacher["grade"]==$teacher_class["grade"] && $seme_teacher["class"]==$teacher_class["class"]){
			continue;
		}else{
			$classData[$i] = $seme_teacher;
			$i++;
		}
	}
	$i=0;
	foreach($classData as $key => $value){
		$seme_student_data = $dbh->prepare("SELECT a.user_id,a.uname,b.grade,b.class FROM user_info a,seme_student b where b.organization_id=:organization_id and b.seme_year_seme=:nowseme and b.grade=:grade and b.class=:class and a.user_id=b.stud_id ORDER BY `a`.`user_id` ASC");
		$seme_student_data->bindValue(':organization_id', $value["organization_id"], PDO::PARAM_STR);
		$seme_student_data->bindValue(':nowseme', $nowseme, PDO::PARAM_STR);
		$seme_student_data->bindValue(':grade', $value["grade"], PDO::PARAM_STR);
		$seme_student_data->bindValue(':class', $value["class"], PDO::PARAM_STR);
		$seme_student_data->execute();
// 		$student_class = $seme_student_data->fetchAll(\PDO::FETCH_ASSOC);
		while ($student_class = $seme_student_data->fetch(\PDO::FETCH_ASSOC)){
			$returnData[$i] = $student_class;
			$i++;
		}
	}
// 	$seme_student_data = $dbh->prepare("SELECT a.user_id,a.uname,b.grade,b.class FROM user_info a,seme_student b where b.organization_id=:organization_id and b.seme_year_seme=:nowseme and b.grade=:grade and b.class=:class and a.user_id=b.stud_id");
// 	$seme_student_data->bindValue(':organization_id', "190041", PDO::PARAM_STR);
// 	$seme_student_data->bindValue(':nowseme', "1061", PDO::PARAM_STR);
// 	$seme_student_data->bindValue(':grade', "6", PDO::PARAM_STR);
// 	$seme_student_data->bindValue(':class', "9", PDO::PARAM_STR);
// 	$seme_student_data->execute();
// 	$student_class = $seme_student_data->fetchAll(\PDO::FETCH_ASSOC);
// 	$i=0;
// 	while ($student_class = $seme_student_data->fetch(\PDO::FETCH_ASSOC)){
// 		$returnData[$i] = $student_class;
// 		$i++;
// 	}
	echo json_encode($returnData, JSON_UNESCAPED_UNICODE);
}

// 171026，KP，新增學生小組功能
if($_POST["type"] == '5') {
		$all_stud_id = $_POST["user_id"];
		$organization_id = $_POST["organization_id"];
		$group_name = $_POST["group_name"];
		$user_count = count($user_id);
		$time = date('YmdHis');
		$teacher_id = $_SESSION['user_id'];

		// 171101，搜尋資料庫中總共有幾組小組，取出最大group_id。
		// **171102，修改group_id欄位為Primary Key和A.I，因此不需要在做計算。
		// $find_group = $dbh->prepare("SELECT DISTINCT group_id FROM `seme_group` ORDER BY group_id DESC LIMIT 1");
		// $find_group->execute();
		// $group_data = $find_group->fetch();
		// $group_id_now = $group_data["group_id"];
		// //後面要新增小組的新的group_id
		// $group_id_next = $group_id_now + 1;

		//寫入seme_group資料表
		$add_seme_group_sql = "INSERT INTO seme_group(group_nm, teacher_id, create_date) VALUES (:group_name,:teacher_id,:create_date)";
		$add_seme_group = $dbh->prepare($add_seme_group_sql);
		$add_seme_group->bindValue(':group_name',$group_name,PDO::PARAM_STR);
		$add_seme_group->bindValue(':teacher_id',$teacher_id,PDO::PARAM_STR);
		$add_seme_group->bindValue(':create_date',$time,PDO::PARAM_STR);
		$add_seme_group->execute();

		//寫入seme_group資料表後，要馬上撈出group_id，寫入user_group的group_id欄位。
		$find_group_id_sql = "SELECT * FROM seme_group ORDER BY group_id DESC LIMIT 1";
		$find_group_id = $dbh->prepare($find_group_id_sql);
		$find_group_id->execute();
		$group_data = $find_group_id->fetch();
		$group_id_now = $group_data["group_id"];

		//寫入user_group資料表
		foreach ($all_stud_id as $key => $value) {
			$stud_id = $value;

			//尋找該名學生是否已經有加入小組?
			// $findkey = $dbh->prepare("SELECT * FROM user_group WHERE user_id = :stud_id AND organization_id = :org_id");
			// $findkey->bindValue(':stud_id', $stud_id, PDO::PARAM_STR);
			// $findkey->bindValue(':org_id', $organization_id, PDO::PARAM_STR);
			// $key = $findkey->fetch();
			// $count = $findkey->rowCount();


			// if($count==0){ //如果小組資料，則新增小組。
				$add_user_group_sql = "INSERT INTO user_group(organization_id, user_id, group_id,create_date, update_date) VALUES (:org_id,:stud_id,:group_id,:create_date,:update_date)";

				$add_user_group = $dbh->prepare($add_user_group_sql);
				$add_user_group->bindValue(':org_id',$organization_id,PDO::PARAM_STR);
				$add_user_group->bindValue(':stud_id',$stud_id,PDO::PARAM_STR);
				$add_user_group->bindValue(':group_id',$group_id_now,PDO::PARAM_INT);
				$add_user_group->bindValue(':create_date',$time,PDO::PARAM_STR);
				$add_user_group->bindValue(':update_date',$time,PDO::PARAM_STR);
				$add_user_group->execute();
		// }else { //如果之前已編過小組，則更新該學生的小組資料
		// 		$update_user_group_sql = "UPDATE user_group SET group_id=:group_id,update_date=:update_date WHERE user_id=:stud_id";

		// 		$update_user_group = $dbh->prepare($update_user_group_sql);
		// 		$update_user_group->bindValue(':stud_id',$stud_id,PDO::PARAM_STR);
		// 		$update_user_group->bindValue(':group_id',$group_id_next,PDO::PARAM_INT);
		// 		$update_user_group->bindValue(':update_date',$time,PDO::PARAM_STR);
		// 		$update_user_group->execute();
		// }

	}

	foreach ($all_stud_id as $key => $value) {
		$stud_id = $value;
		//check 學生小組資料是否成功寫入資料庫。
		$check_group_sql = "SELECT * FROM user_group,seme_group WHERE user_group.group_id = seme_group.group_id AND user_id = :stud_id AND user_group.group_id = :group_id";
		$check_group = $dbh->prepare($check_group_sql);
		$check_group->bindValue(':stud_id',$stud_id);
		$check_group->bindValue(':group_id',$group_id_now);
		$check_group->execute();
		$check_group_data = $check_group->fetch();
		$check_group_count = $check_group->rowCount();
		if($check_group_count!=0) {
			$status = '新增小組成功！';
		}else {
			$status = '新增失敗！請稍後在嘗試一次。';
		}
	}

	echo $status;
}

// 171026，新增老師給個別學生獎勵的功能
if($_POST["type"] == '6') {
	if($_POST["user_id"]!=''){
		$good_user_id = $_POST["user_id"];
		$coin_value = $_POST["coin_value"];
		$coin_type = $_POST["coin_type"];
		$memo = '達成【'.$coin_type.'】而獲得代幣'.$coin_value.'個';
		$pro_no = $_POST["pro_no"];
		// tab的id當作參數
		$pro_no_id = '#parentHorizontalTab1';
		$time = date('YmdHis');

		foreach ($good_user_id as $key => $value) {
			$stud_id = $value;
			$user_id_temp = split('-', $stud_id);
			$organization_id = $user_id_temp[0];

			//171011，先抓取現在角色的代幣數(role_info.coin)
			//和可以得到的代幣數(role_reward_way.reward_coin_num)
			$reward_coin = "SELECT coin FROM role_info WHERE stud_id = :user_id";
			$coin_num = $dbh->prepare($reward_coin);
			$coin_num->bindValue(':user_id',$stud_id,PDO::PARAM_STR);
			$coin_num->execute();
			$role_cu = $coin_num->rowCount();

			//存取回答者現在的代幣數
			while($row=$coin_num->fetch(PDO::FETCH_ASSOC)){
				$role_coin = $row['coin'];
				$sn = $row['sn'];
			}
			
			if($role_cu == 0){
				$result = $dbh->prepare("INSERT INTO role_info (organization_id, stud_id, coin,
				body_weight, body_height, health_points, create_time)
				VALUES (:org_id, :stud_id, :coin, :body_weight, :body_height, :health_points, :create_time)");
				$result->bindValue(':org_id', $organization_id, PDO::PARAM_STR);
				$result->bindValue(':stud_id', $stud_id, PDO::PARAM_STR);
				$result->bindValue(':coin', $coin_value, PDO::PARAM_STR);
				$result->bindValue(':body_weight', 0, PDO::PARAM_STR);
				$result->bindValue(':body_height', 0, PDO::PARAM_STR);
				$result->bindValue(':health_points', 0, PDO::PARAM_STR);
				$result->bindValue(':create_time', $time, PDO::PARAM_STR);
						
				$result->execute();
					
			}else{
				//更新回答者的coin數量
				$update_coin = "UPDATE role_info SET coin = :coin_num WHERE stud_id = :user_id";
				$give_coin = $dbh->prepare($update_coin);
				$coin_num = $role_coin + $coin_value;
				$give_coin->bindValue(':coin_num',$coin_num,PDO::PARAM_INT);
				$give_coin->bindValue(':user_id',$stud_id,PDO::PARAM_STR);
				$give_coin->execute();
			}
			
			//date、org_id、user_id、pro_no、type、action、memo
			$add_user_history_sql = "INSERT INTO user_history(date, organization_id, user_id,pro_no, type,action,memo) VALUES (:gain_date, :org_id, :user_id, :pro_no, :type, :action, :memo)";
			$add_user_history = $dbh->prepare($add_user_history_sql);
			$add_user_history->bindValue(':gain_date',$time,PDO::PARAM_STR);
			$add_user_history->bindValue(':org_id',$organization_id,PDO::PARAM_STR);
			$add_user_history->bindValue(':user_id',$stud_id,PDO::PARAM_STR);
			$add_user_history->bindValue(':pro_no',$pro_no,PDO::PARAM_STR);
			$add_user_history->bindValue(':type','gain_t',PDO::PARAM_STR);
			$add_user_history->bindValue(':action',$pro_no_id,PDO::PARAM_STR);
			$add_user_history->bindValue(':memo',$memo,PDO::PARAM_STR);
			$add_user_history->execute();
		}

	}
	echo $memo;
}

if($_POST["type"] == '7') {
	if($_POST["user_id"]!=''){
		$user_id = $_POST["user_id"];
		$user_id_temp = split('-', $user_id);
		$stud_id = $user_id_temp[1];
		$new_group_id = $_POST["new_group_id"];
		$time = date('YmdHis');

		$update_group_id_sql = "UPDATE user_group SET  group_id = :new_group_id,update_date = :update_date WHERE user_id = :stud_id";
		$update_group_id = $dbh->prepare($update_group_id_sql);
		$update_group_id->bindValue(':new_group_id',$new_group_id);
		$update_group_id->bindValue(':stud_id',$user_id);
		$update_group_id->bindValue(':update_date',$time,PDO::PARAM_STR);
		$update_group_id->execute();

	}

	echo $stud_id;
}

if($_POST["type"] == '8') {
	if($_POST["user_id"]!=''){
		$user_id = $_POST["user_id"];
		$user_id_temp = split('-', $user_id);
		$stud_id = $user_id_temp[1];
		$group_id = $_POST['group_id'];

		$update_group_id_sql = "DELETE FROM user_group WHERE user_id = :user_id AND group_id = :group_id";
		$update_group_id = $dbh->prepare($update_group_id_sql);
		$update_group_id->bindValue(':group_id',$group_id);
		$update_group_id->bindValue(':user_id',$user_id);
		$update_group_id->execute();

	}

	echo $stud_id;
}

if($_POST["type"] == '9') {
	if($_POST["user_id"]!=''){
		$all_stud_id = $_POST["user_id"];
		$user_id_temp = split('-', $user_id);
		$organization_id = $user_id_temp[0];
		$group_id = $_POST['group_id'];
		$time = date('YmdHis');

		//寫入user_group資料表
		foreach ($all_stud_id as $key => $value) {
			$stud_id = $value;
			//$teacher_id = $user_id;

			//尋找該名學生是否已經有加入小組?
			// $findkey = $dbh->prepare("SELECT * FROM user_group WHERE user_id = :stud_id AND organization_id = :org_id");
			// $findkey->bindValue(':stud_id', $stud_id, PDO::PARAM_STR);
			// $findkey->bindValue(':org_id', $organization_id, PDO::PARAM_STR);
			// $key = $findkey->fetch();
			// $count = $findkey->rowCount();


			// if($count==0){ //如果小組資料，則新增小組。
				$add_user_group_sql = "INSERT INTO user_group(organization_id, user_id, group_id,create_date, update_date) VALUES (:org_id,:stud_id,:group_id,:create_date,:update_date)";

				$add_user_group = $dbh->prepare($add_user_group_sql);
				$add_user_group->bindValue(':org_id',$organization_id,PDO::PARAM_STR);
				$add_user_group->bindValue(':stud_id',$stud_id,PDO::PARAM_STR);
				$add_user_group->bindValue(':group_id',$group_id,PDO::PARAM_INT);
				$add_user_group->bindValue(':create_date',$time,PDO::PARAM_STR);
				$add_user_group->bindValue(':update_date',$time,PDO::PARAM_STR);
				$add_user_group->execute();

	}

	//echo $stud_id;
	}
}
?>