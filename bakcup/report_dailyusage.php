<?php
  include_once('include/config.php');
  include_once('classes/PHPExcel.php');
  include_once('classes/PHPExcel/IOFactory.php');
  include_once('include/ref_cityarea.php');

  if (!isset($_SESSION)) {
  	session_start();
  }
  // 以下是把所有資料整理至 report_dailyusage Table中 --------------------------------------------------------------------
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
  $oVideo -> execute();
  $vVideoData= $oVideo->fetchAll(\PDO::FETCH_ASSOC);

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
  $oSpentTime -> execute();
  $vSpentTimeData = $oSpentTime->fetchAll(\PDO::FETCH_ASSOC);

  //練習題人數 5
  $sSQLPrac = 'SELECT organization.organization_id, city.city_name, organization.name, SUBSTR(organization.address,2,3) postcode, COUNT(DISTINCT prac_answer.user_id) num
    FROM user_info, prac_answer, organization, city
  	WHERE user_info.used =1
    AND organization.organization_id = user_info.organization_id
    AND city.city_code = user_info.city_code
  	AND user_info.user_id = prac_answer.user_id
    GROUP BY organization.organization_id';
  // //時間條件式
  // if($_POST["time"]=='search'){
  // 	$sSQLPrac = $sSQLPrac." AND (prac_answer.date >'".$_POST["search_start"]."' AND prac_answer.date<'".$_POST["search_end"]."')";
  // }else{
  // 	$sSQLPrac = $sSQLPrac." AND prac_answer.date >'".$_POST["time"]."'";
  // }
  // $sSQLPrac = $sSQLPrac." GROUP BY organization.organization_id";
  $oPrac = $dbh->prepare($sSQLPrac);
  if($a==0){
  	//時間條件式
  }
  $oPrac->execute();
  $vPracData = $oPrac->fetchAll(\PDO::FETCH_ASSOC);

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
  $oExam -> execute();

  $sSQLExamView = 'SELECT organization_id, city_name, postcode, name, SUM(num) num FROM EXAM_VIEW GROUP BY organization_id';
  $oExamView = $dbh->prepare($sSQLExamView);
  $oExamView -> execute();
  $vExamViewData = $oExamView->fetchAll(\PDO::FETCH_ASSOC);

  $oExamView = $dbh->prepare('DROP VIEW EXAM_VIEW');
  $oExamView -> execute();

  $resultData = array();
  // 影片瀏覽人數
  foreach ($vVideoData as $sCol => $tmpVideo) {
    $resultData[$tmpVideo["organization_id"]]["postcode"] = $tmpVideo["postcode"];
  	$resultData[$tmpVideo["organization_id"]]["city_name"] = $tmpVideo["city_name"];
  	$resultData[$tmpVideo["organization_id"]]["schoolname"] = $tmpVideo["name"];
  	$resultData[$tmpVideo["organization_id"]]["video_p"] = $tmpVideo["num"];
  }

  //影片瀏覽時間4
  foreach ($vSpentTimeData as $sCol => $tmpSpentTime) {
    $resultData[$tmpSpentTime["organization_id"]]["postcode"] = $tmpSpentTime["postcode"];
  	$resultData[$tmpSpentTime["organization_id"]]["city_name"] = $tmpSpentTime["city_name"];
  	$resultData[$tmpSpentTime["organization_id"]]["schoolname"] = $tmpSpentTime["name"];
  	$resultData[$tmpSpentTime["organization_id"]]["video_t"] = round($tmpSpentTime["total_sec"]/3600,2);
  }

  //練習題測驗人數5
  foreach ($vPracData as $key => $tmpPrac){
    $resultData[$tmpPrac["organization_id"]]["postcode"] = $tmpPrac["postcode"];
  	$resultData[$tmpPrac["organization_id"]]["city_name"] = $tmpPrac["city_name"];
  	$resultData[$tmpPrac["organization_id"]]["schoolname"] = $tmpPrac["name"];
  	$resultData[$tmpPrac["organization_id"]]["prac"] = $tmpPrac["num"];
  }

  // 測驗總人數
  foreach ($vExamViewData as $sCol => $tmpExam) {
    $resultData[$tmpExam["organization_id"]]["postcode"] = $tmpExam["postcode"];
  	$resultData[$tmpExam["organization_id"]]["city_name"] = $tmpExam["city_name"];
  	$resultData[$tmpExam["organization_id"]]["schoolname"] = $tmpExam["name"];
  	$resultData[$tmpExam["organization_id"]]["exam_total"] = $tmpExam["num"];
  }

  // $sSQLUsage = $dbh->prepare("INSERT INTO
  //   report_dailyusage (sn, organization_id, city_name, postcode, city_area, name, exam_total, video_watching_total, video_spend_time, exercise_total, datetime_log)
  //   VALUES (NULL, :organization_id, :city_name, :postcode,:city_area, :name, :exam_total, :video_watching_total, :video_spend_time, :exercise_total, :datetime_log)");
  //
  // foreach ($resultData as $key=>$value) {
  //   if($value["exam_total"]=='') $value["exam_total"]=0;
  //   if($value["video_p"]=='') $value["video_p"]=0;
  //   if($value["video_t"]=='') $value["video_t"]=0;
  //   if($value["prac"]=='') $value["prac"]=0;
  //
  // 	$sSQLUsage->bindValue(':organization_id', $key, PDO::PARAM_STR);
  // 	$sSQLUsage->bindValue(':city_name', $value["city_name"], PDO::PARAM_STR);
  //   $sSQLUsage->bindValue(':postcode', $value["postcode"], PDO::PARAM_STR);
  // 	$sSQLUsage->bindValue(':city_area', $ref_cityarea[$value["postcode"]][1], PDO::PARAM_STR);
  // 	$sSQLUsage->bindValue(':name', $value["schoolname"], PDO::PARAM_STR);
  // 	$sSQLUsage->bindValue(':exam_total', $value["exam_total"], PDO::PARAM_STR);
  // 	$sSQLUsage->bindValue(':video_watching_total', $value["video_p"], PDO::PARAM_STR);
  // 	$sSQLUsage->bindValue(':video_spend_time', $value["video_t"], PDO::PARAM_STR);
  // 	$sSQLUsage->bindValue(':exercise_total', $value["prac"], PDO::PARAM_STR);
  //   $sSQLUsage->bindValue(':datetime_log', date("Y-m-d, H:i:s"), PDO::PARAM_STR);
  // 	$sSQLUsage->execute();
  // }
  //-------------------------------------------------------------------------------------------------------------------

  $vUserData = get_object_vars($_SESSION['user_data']);
  $sUserLevel = $vUserData['access_level'];
  $sManageCity = $vUserData['city_name'];
  $sFindData = '*'; // 資料分類

  $sSQLCond = "SELECT $sFindData FROM report_dailyusage ";
  switch ($sUserLevel) {
    case '41': // 縣市政府
    $sSQLCond .= "WHERE city_name IN('$sManageCity') ";
      break;

    case '51': // 教育部
      break;
  }

  if('search' === $_POST['time']) {
    $sSQLCond .= ' AND (datetime_log > "'.$_POST["search_start"].'" AND datetime_log < "'.$_POST["search_end"].'")';
  }
  else{
    $sSQLCond .= ' AND datetime_log > "'.$_POST["time"].'"';
  }

  $sSQLCond .= "GROUP BY organization_id ORDER BY organization_id";

  $oSQLCond = $dbh->prepare($sSQLCond);
  $oSQLCond->execute();
  $vReportData = $oSQLCond->fetchAll(\PDO::FETCH_ASSOC);

  foreach ($vReportData as $tmpData) {
    $vCityData[$tmpData['city_name']] = $tmpData['city_name'];
    $vCiryArea[$tmpData['city_name']][] = array($tmpData['postcode'], $tmpData['city_area'], $tmpData['city_name']);
    $vSchoolData[$tmpData['city_name']][] = array($tmpData['postcode'], $tmpData['name']);
  }

  // 縣市
  $vCitySelect = array();
  $vCitySelect[] = '<select id="select_city">';
  $vCitySelect[] =   '<option value="">縣市</option>';
  foreach ($vCityData as $tmpData) {
    $vCitySelect[] = '<option value="'.$tmpData.'">'.$tmpData.'</option>';
  }
  $vCitySelect[] = '</select>';
  $vCitySelect[] = '<select id="select_zipcode">';
  $vCitySelect[] =   '<option value="區">區</option>';
  $vCitySelect[] = '</select>';
  $vCitySelect[] = '<select id="select_school">';
  $vCitySelect[] =   '<option value="學校">學校</option>';
  $vCitySelect[] = '</select>';

  // Excel
  make_excel($resultData);
  function make_excel($resultData) {
  	$date = date("Ymd_His");
  	$excel_content[0]=["學校代碼","縣市","學校","測驗人數","影片瀏覽人數","影片瀏覽時間(小時)","練習題測驗人數"];
  	foreach ($resultData as $key=>$value){
  		$excel_content[$key+1] = [$key,$value["city_name"],$value["schoolname"],$value["exam_total"],$value["video_p"],$value["video_t"],$value["prac"]];
  	}
  	$objPHPExcel = new PHPExcel();
  	$objPHPExcel->setActiveSheetIndex(0);
  	$objPHPExcel->getActiveSheet()->fromArray($excel_content, null, 'A1');
  	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
  	$filename= 'use_dayilyusage.xlsx';
  	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
  	$objWriter->save(_ADP_PATH.'data/tmp/'.$filename);
  }

  // 時間條件
  $sUserSearch = '';
	if($_POST['time'] == '0000-00-00' || !isset($_POST['time'])) {
		$sUserSearch = "全部時間";
	}
  elseif($_POST["time"]==date( "Y-m-d", mktime (0,0,0,date("m") ,date("d")-7, date("Y")))){
		$sUserSearch = "最近一週";
	}
  elseif($_POST["time"]==date( "Y-m-d", mktime (0,0,0,date("m") ,date("d")-14, date("Y")))){
		$sUserSearch = "最近兩週";
	}
  elseif ($_POST["time"]==date( "Y-m-d", mktime (0,0,0,date("m")-1 ,date("d"), date("Y")))){
		$sUserSearch = "最近一個月";
	}
  else {
		$sUserSearch = "指定區間".$_POST["search_start"]."~".$_POST["search_end"];
	}
?>
<!DOCTYPE HTML>
<html>
<header></header>
<script>
  var oCityArea = $.parseJSON('<?php echo json_encode($vCiryArea); ?>');
  var oSchool = $.parseJSON('<?php echo json_encode($vSchoolData); ?>');

	$(function() {
    $("table").children("thead").find("td,th").each(function(){
        var idx = $(this).index();
        var td = $(this).closest("table").children("tbody")
                        .children("tr:first").children("td,th").eq(idx);
        $(this).width() > td.width() ? td.width($(this).width()) : $(this).width(td.width());
    });
    $("#search_start").click(function() {
  	  $("#setrange").attr("checked",true);
  	});
    $("#search_end").click(function() {
  	  $("#setrange").attr("checked",true);
  	});

    // 選擇縣市, 區及學校需變動
    $('#select_city').change(function() {
      if ('' === $('#select_city').val()) return

      // 區
      $("#select_zipcode").empty();
      $.each(oCityArea[$('#select_city').val()], function(iInx, vData) {
        $('#select_zipcode').append($('<option>', {value:vData[0]}).text(vData[1]));
      });

      // 學校
      $("#select_school").empty();
      $.each(oSchool[$('#select_city').val()], function(iInx, vData) {
        console.log(vData);
        if ($('#select_zipcode').val() === vData[0]) {
          $('#select_school').append($('<option>', {value:vData[1]}).text(vData[1]));
        }
      });

      $('#hiCity').val($('#select_city').val());
      $('#hiArea').val($('#select_zipcode').val());
      $('#hiSchool').val($('#select_school').val());
    });

    // 選擇區 學校需變動
    $('#select_zipcode').change(function() {
      if ('' === $('#select_zipcode').val()) return

      // 學校
      $("#select_school").empty();
      $.each(oSchool[$('#select_city').val()], function(iInx, vData) {
        if ($('#select_zipcode').val() === vData[0]) {
          $('#select_school').append($('<option>', {value:vData[1]}).text(vData[1]));
        }
        $('#hiSchool').val($('#select_school').val());
      });

      $('#hiArea').val($('#select_zipcode').val());
    });
	});
</script>
<style>
  table tbody, table thead {display: inline-block;table-layout:fixed;}
  table tbody {overflow:auto;height:750px;weight:50%;}
  #main_cond label {cursor:pointer;}
  #tab_indicator thead {cursor:pointer;}
  #tab_indicator > * > * > *:nth-of-type(1) {min-width:100px;}
  #tab_indicator > * > * > *:nth-of-type(2) {width:85px;}
  #tab_indicator > * > * > *:nth-of-type(3) {width:85px;}
  #tab_indicator > * > * > *:nth-of-type(4) {width:210px;}
  #tab_indicator > * > * > *:nth-of-type(5) {width:130px;}
  #tab_indicator > * > * > *:nth-of-type(6) {width:130px;}
  #tab_indicator > * > * > *:nth-of-type(7) {width:130px;}
  #tab_indicator > * > * > *:nth-of-type(8) {width:135px;}
  #tab_indicator > * > * > *:nth-of-type(9) {width:130px;}
</style>
  <div class="content2-Box">
	  <div class="path">目前位置：系統使用狀況報表</div>
      <div class="choice-box">
        <div class="choice-title">報表</div>
          <ul class="choice work-cholic">
        	  <li><a href="modules.php?op=modload&name=schoolReport&file=report_dailyusage" class="current"><i class="fa fa-caret-right"></i>各校使用狀況</a></li>
        		<li><a href="modules.php?op=modload&name=schoolReport&file=report_userinfo2"><i class="fa fa-caret-right"></i>各校學習狀況</a></li>
          </ul>
   		 </div>
      <div class="left-box">
        依定區搜尋
<?php echo implode('', $vCitySelect); ?>
      </div>
 			<div class="right-box">
			  <form id="main_cond" method="post" action="modules.php?op=modload&name=schoolReport&file=report_dailyusage">
			    <label><input type="radio" name="time" value="0000-00-00" checked>全部時間</label>
			    <label><input type="radio" name="time" value="<?php echo date( "Y-m-d", mktime (0,0,0,date("m") ,date("d")-7, date("Y")));?>">最近一週</label>
			    <label><input type="radio" name="time" value="<?php echo date( "Y-m-d", mktime (0,0,0,date("m") ,date("d")-14, date("Y")));?>">最近兩週</label>
			    <label><input type="radio" name="time" value="<?php echo date( "Y-m-d", mktime (0,0,0,date("m")-1 ,date("d"), date("Y")));?>">最近一個月</label>
			    <label><input type="radio"  id="setrange" name="time" value="search">指定區間</label>
			    <input type="date" placeholder="yyyy/mm/dd" style="width:180px" id="search_start" name="search_start" value="<?php echo $search_start;?>">～
      	  <input type="date" placeholder="yyyy/mm/dd" style="width:180px" id="search_end" name="search_end" value="<?php echo $search_end;?>">
          <input type="hidden" id="hiCity" name="hiCity">
          <input type="hidden" id="hiArea" name="hiArea">
          <input type="hidden" id="hiSchool" name="hiSchool">
			    <input type="submit" name="sreach" class="btn02" style="width:70px; display: inline;" value="查詢">
	      </form>
		  <font class="color-blue">搜尋範圍：<?php echo $sUserSearch; ?></font>
  		<div style="text-align:right;display:inline;"><a href="<?php echo './data/tmp/use_chinese.xlsx';?>">檔案下載</a></div>
  		<div class="table_scroll" >
  			<table id="tab_indicator" class="datatable" data-sortable>
  				<thead>
    				<tr>
    					<th>學校代號</th>
    					<th>縣市</th>
              <th>區</th>
    					<th>學校</th>
    					<th>測驗人數</th>
    					<th>影片瀏覽<br>人數</th>
    					<th>影片瀏覽<br>時間(小時)</th>
    					<th>練習題<br>測驗人數</th>
    				</tr>
  				</thead>
  				<tbody>
<?php
	//debug_msg("第".__LINE__."行 resultData ", $resultData);
  foreach ($resultData as $key=>$value) {
		if($value["exam_total"]=='') $value["exam_total"]=0;
		if($value["video_p"]=='') $value["video_p"]=0;
		if($value["video_t"]=='') $value["video_t"]=0;
		if($value["prac"]=='') $value["prac"]=0;
?>
  					<tr>
  						<td><?php echo $key; ?></td>
  						<td><?php echo $value["city_name"]; ?></td>
              <td><?php echo $ref_cityarea[$value["postcode"]][1]; ?></td>
  						<td><?php echo $value["schoolname"]; ?></td>
  						<td data-sortable-type="numeric"><?php echo $value["exam_total"]; ?></td>
  						<td data-sortable-type="numeric"><?php echo $value["video_p"]; ?></td>
  						<td data-sortable-type="numeric"><?php echo $value["video_t"]; ?></td>
  						<td data-sortable-type="numeric"><?php echo $value["prac"]; ?></td>
  					</tr>
<?php } ?>
  			  </tbody>
  			</table>
  		</div>
    </div>
  </div>
</html>
