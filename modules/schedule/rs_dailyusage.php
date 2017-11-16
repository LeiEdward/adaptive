<?php
require_once('./include/config.php');
require_once('./include/adp_API.php');
require_once('./include/ref_cityarea.php');

$sSemeYear = getYearSeme();

createReport_dailyusage($sSemeYear);

function createReport_dailyusage($sSemeYear) {
  global $dbh, $ref_cityarea;

   $oReport = $dbh->prepare("SELECT organization_id FROM report_dailyusage WHERE datetime_log LIKE '".date('Y-m-d')."%'") ;
   $oReport->execute();
   $iReprotCount = $oReport->rowCount();
   if (0 < $iReprotCount) {
     file_put_contents(realpath('./data/').'/rs_dailyusage.log', date('Y-m-d H:i:s').' : 今天已經執行過報表'.PHP_EOL, FILE_APPEND);
     return;
   }

file_put_contents(realpath('./data/').'/rs_dailyusage.log', date('Y-m-d H:i:s').' : 正在查詢影片瀏覽人數...'.PHP_EOL, FILE_APPEND);
  // 影片瀏覽人數
  $sSQLVideo = 'SELECT organization.organization_id, city.city_name, organization.name, SUBSTR(organization.address,2,3) postcode, COUNT(video_review_record.user_id) num
    FROM user_info,	(SELECT * FROM video_review_record GROUP BY user_id) video_review_record, organization, city ,user_status
  	WHERE user_info.used = 1
    AND organization.organization_id = user_info.organization_id
    AND city.city_code = user_info.city_code
  	AND user_info.user_id = video_review_record.user_id
    AND user_status.user_id = user_info.user_id
    AND user_status.access_level = 1
    GROUP BY organization.organization_id';
  $oVideo = $dbh->prepare($sSQLVideo);
  $oVideo->execute();
  $vVideoData= $oVideo->fetchAll(\PDO::FETCH_ASSOC);

file_put_contents(realpath('./data/').'/rs_dailyusage.log', date('Y-m-d H:i:s').' : 正在查詢影片瀏覽時間...'.PHP_EOL, FILE_APPEND);
  // 影片瀏覽時間
  $sSQLSpentTime='SELECT organization.organization_id, city.city_name, organization.name, SUBSTR(organization.address,2,3) postcode, SUM(total_time) total_sec
    FROM user_info,	(SELECT * FROM video_review_record GROUP BY user_id) video_review_record, organization, city ,user_status
  	WHERE user_info.used =1
    AND organization.organization_id = user_info.organization_id
    AND city.city_code = user_info.city_code
  	AND user_info.user_id = video_review_record.user_id
    AND user_status.user_id = user_info.user_id
    AND user_status.access_level = 1
    GROUP BY organization.organization_id';
  $oSpentTime = $dbh->prepare($sSQLSpentTime);
  $oSpentTime->execute();
  $vSpentTimeData = $oSpentTime->fetchAll(\PDO::FETCH_ASSOC);

file_put_contents(realpath('./data/').'/rs_dailyusage.log', date('Y-m-d H:i:s').' : 正在查詢練習題人數...'.PHP_EOL, FILE_APPEND);
  //練習題人數 5
  $sSQLPrac = 'SELECT organization.organization_id, city.city_name, organization.name, SUBSTR(organization.address,2,3) postcode, COUNT(DISTINCT prac_answer.user_id) num
    FROM user_info, prac_answer, organization, city
  	WHERE user_info.used =1
    AND organization.organization_id = user_info.organization_id
    AND city.city_code = user_info.city_code
  	AND user_info.user_id = prac_answer.user_id
    GROUP BY organization.organization_id';
  $oPrac = $dbh->prepare($sSQLPrac);
  $oPrac->execute();
  $vPracData = $oPrac->fetchAll(\PDO::FETCH_ASSOC);

file_put_contents(realpath('./data/').'/rs_dailyusage.log', date('Y-m-d H:i:s').' : Create View Table...'.PHP_EOL, FILE_APPEND);
  // Create View
  $sSQLExam = 'CREATE VIEW EXAM_VIEW AS
    SELECT organization.organization_id, city.city_name, organization.name, COUNT(DISTINCT exam_record.user_id) num, SUBSTR(organization.address,2,3) postcode
    FROM user_info, exam_record, organization, city
    WHERE user_info.used =1
    AND organization.organization_id = user_info.organization_id
    AND city.city_code = user_info.city_code AND user_info.user_id = exam_record.user_id
    GROUP by organization.organization_id
    UNION
    SELECT organization.organization_id, city.city_name, organization.name, COUNT(DISTINCT exam_record_indicate.user_id) num, SUBSTR(organization.address,2,3) postcode
    FROM user_info, exam_record_indicate, organization, city
    WHERE user_info.used =1
    AND organization.organization_id = user_info.organization_id
    AND city.city_code = user_info.city_code
    AND user_info.user_id = exam_record_indicate.user_id
    GROUP BY organization.organization_id';
  $oExam = $dbh->prepare($sSQLExam);
  $oExam->execute();

file_put_contents(realpath('./data/').'/rs_dailyusage.log', date('Y-m-d H:i:s').' : Query View Table...'.PHP_EOL, FILE_APPEND);
  $sSQLExamView = 'SELECT organization_id, city_name, postcode, name, SUM(num) num FROM EXAM_VIEW GROUP BY organization_id';
  $oExamView = $dbh->prepare($sSQLExamView);
  $oExamView->execute();
  $vExamViewData = $oExamView->fetchAll(\PDO::FETCH_ASSOC);

  $oExamView = $dbh->prepare('DROP VIEW EXAM_VIEW');
  $oExamView -> execute();

file_put_contents(realpath('./data/').'/rs_dailyusage.log', date('Y-m-d H:i:s').' : Data Handling...'.PHP_EOL, FILE_APPEND);
  $vReportData = array();
  // 影片瀏覽人數
  foreach ($vVideoData as $sCol => $tmpVideo) {
    $vReportData[$tmpVideo['organization_id']]['postcode'] = $tmpVideo['postcode'];
  	$vReportData[$tmpVideo['organization_id']]['city_name'] = $tmpVideo['city_name'];
  	$vReportData[$tmpVideo['organization_id']]['schoolname'] = $tmpVideo['name'];
  	$vReportData[$tmpVideo['organization_id']]['video_p'] = $tmpVideo['num'];
  }

  //影片瀏覽時間4
  foreach ($vSpentTimeData as $sCol => $tmpSpentTime) {
    $vReportData[$tmpSpentTime['organization_id']]['postcode'] = $tmpSpentTime['postcode'];
  	$vReportData[$tmpSpentTime['organization_id']]['city_name'] = $tmpSpentTime['city_name'];
  	$vReportData[$tmpSpentTime['organization_id']]['schoolname'] = $tmpSpentTime['name'];
  	$vReportData[$tmpSpentTime['organization_id']]['video_t'] = round($tmpSpentTime['total_sec']/3600,2);
  }

  //練習題測驗人數5
  foreach ($vPracData as $key => $tmpPrac) {
    $vReportData[$tmpPrac['organization_id']]['postcode'] = $tmpPrac['postcode'];
  	$vReportData[$tmpPrac['organization_id']]['city_name'] = $tmpPrac['city_name'];
  	$vReportData[$tmpPrac['organization_id']]['schoolname'] = $tmpPrac['name'];
  	$vReportData[$tmpPrac['organization_id']]['prac'] = $tmpPrac['num'];
  }

  // 測驗總人數
  foreach ($vExamViewData as $sCol => $tmpExam) {
    $vReportData[$tmpExam['organization_id']]['postcode'] = $tmpExam['postcode'];
  	$vReportData[$tmpExam['organization_id']]['city_name'] = $tmpExam['city_name'];
  	$vReportData[$tmpExam['organization_id']]['schoolname'] = $tmpExam['name'];
  	$vReportData[$tmpExam['organization_id']]['exam_total'] = $tmpExam['num'];
  }

file_put_contents(realpath('./data/').'/rs_dailyusage.log', date('Y-m-d H:i:s').' : Query LearningStat...'.PHP_EOL, FILE_APPEND);
  $vReportData = getLearningStat($vReportData, $sSemeYear);

file_put_contents(realpath('./data/').'/rs_dailyusage.log', date('Y-m-d H:i:s').' : Start Insert report_dailyusage 共 '.count($vReportData).' 筆資料...'.PHP_EOL, FILE_APPEND);
  $sSQLUsage = $dbh->prepare("INSERT INTO
    report_dailyusage (sn, organization_id, city_name, postcode, city_area, name, exam_total, video_watching_total, video_spend_time, exercise_total, node, datetime_log)
    VALUES (NULL, :organization_id, :city_name, :postcode,:city_area, :name, :exam_total, :video_watching_total, :video_spend_time, :exercise_total, :node, :datetime_log)");

  foreach ($vReportData as $key=>$value) {
    if (empty($value['city_name']) || empty($value['postcode']) || empty($value['schoolname'])) continue;
    if ('' == $value['exam_total']) $value['exam_total'] = 0;
    if ('' == $value['video_p']) $value['video_p'] = 0;
    if ('' == $value['video_t']) $value['video_t'] = 0;
    if ('' == $value['prac']) $value['prac'] = 0;
    if (!isset($value['node']) || empty($value['node'])) $value['node'] = '';

  	$sSQLUsage->bindValue(':organization_id', $key, PDO::PARAM_STR);
  	$sSQLUsage->bindValue(':city_name', $value['city_name'], PDO::PARAM_STR);
    $sSQLUsage->bindValue(':postcode', $value['postcode'], PDO::PARAM_STR);
  	$sSQLUsage->bindValue(':city_area', $ref_cityarea[$value['postcode']][1], PDO::PARAM_STR);
  	$sSQLUsage->bindValue(':name', $value['schoolname'], PDO::PARAM_STR);
  	$sSQLUsage->bindValue(':exam_total', $value['exam_total'], PDO::PARAM_STR);
  	$sSQLUsage->bindValue(':video_watching_total', $value['video_p'], PDO::PARAM_STR);
  	$sSQLUsage->bindValue(':video_spend_time', $value['video_t'], PDO::PARAM_STR);
  	$sSQLUsage->bindValue(':exercise_total', $value['prac'], PDO::PARAM_STR);
    $sSQLUsage->bindValue(':node', $value['node'], PDO::PARAM_STR);
    $sSQLUsage->bindValue(':datetime_log', date("Y-m-d H:i:s"), PDO::PARAM_STR);
  	$sSQLUsage->execute();
  }
file_put_contents(realpath('./data/').'/rs_dailyusage.log', date('Y-m-d H:i:s').' : Finish report_dailyusage'.PHP_EOL, FILE_APPEND);
  echo 'Finish report_dailyusage';
}

function getLearningStat($vReportData, $sSemeYear) {
	global $dbh;

  // 查出該校的所有學生
  $sql = 'SELECT *
		FROM seme_student a, user_status b
		WHERE a.seme_year_seme = "'.$sSemeYear.'" AND a.stud_id=b.user_id
		ORDER BY seme_year_seme, grade, class';

	$re = $dbh->query($sql);
	$classInfo = $re->rowCount();

	$ind_row=array();
	$indicate=$dbh->query("SELECT indicate_name, indicate_id FROM `map_node`");
	while($indicate_row=$indicate->fetch() ){
		$ind_row[$indicate_row[indicate_id]]=$indicate_row[indicate_name];
	}

	if($classInfo>0) {
  	while($data=$re->fetch()) {
  		$grade = $data[grade];
  		$classes = $data['class'];
  		$user = $data[user_id];
      $sOrganizationID = $data['organization_id'];

  		$sql_nodeStatus = '	SELECT *
				FROM map_node_student_status , map_info
				WHERE map_node_student_status.user_id = "'.$user.'"  AND map_node_student_status.map_sn=map_info.map_sn';

    	$re_nodeStatus=$dbh->query($sql_nodeStatus);
    	while($data_nodeStatus = $re_nodeStatus->fetch()) {
    		$subject = $data_nodeStatus[subject_id];
        $subtName = sub_name($subject);
        // 小節點精熟及完成度
    		$sNodeS = unserialize( $data_nodeStatus[sNodes_Status_FR] );
    		if(!is_array($sNodeS)) {
    			// debugBAI(__LINE__,__FILE__, 'sNodes is null. '.$user);
    			continue;
    		}
    		foreach ($sNodeS as $value) {
          if (!empty($value['status:'])) {
            switch($value['status:']) {
              case 0:
                $vNodeData[$sOrganizationID][$subject][$grade]['name'] = $subtName;
                $vNodeData[$sOrganizationID][$subject][$grade]['nopassnode']++;
                $vNodeData[$sOrganizationID][$subject][$grade]['allnode']++;
                break;
              case 1:
                $vNodeData[$sOrganizationID][$subject][$grade]['name'] = $subtName;
                $vNodeData[$sOrganizationID][$subject][$grade]['passnode']++;
                $vNodeData[$sOrganizationID][$subject][$grade]['allnode']++;
                break;
            }
          }
    		}
    	}
	  }
    foreach ($vNodeData as $sOrganization => $vNodeTmp) {
      $sNodeData = serialize($vNodeData[$sOrganization]);
      $vReportData[$sOrganization]['node'] = $sNodeData;
    }
	}
  return $vReportData;
}

function sub_name($sub) {
  global $dbh;

  $sql = "SELECT map_name FROM `map_info` where subject_id='$sub' AND display=1";
  $data = $dbh->query($sql);
  $row = $data->fetch();
  return $row['map_name'];
}
