<?php
  include_once('include/config.php');
  include_once('classes/PHPExcel.php');
  include_once('include/ref_cityarea.php');

  if (!isset($_SESSION)) {
    session_start();
  }

  $vUertACL = array('41', '51');

  // 取得 user 資料
  $vUserData = get_object_vars($_SESSION['user_data']);

  if (false === array_search($vUserData['access_level'], $vUertACL)) {
    echo '無權限瀏覽';
    return;
  }

  // 統一接變數
  $vCond = array();
  if ('' !== $_POST['time']) {
    $vCond['time'] = $_POST['time'];
  }
  if ('' !== $_POST['search_start']) {
    $vCond['search_start'] = $_POST['search_start'];
  }
  if ('' !== $_POST['search_end']) {
    $vCond['search_end'] = $_POST['search_end'];
  }
  if ('' !== $_POST['hiCity']) {
    $vCond['hiCity'] = $_POST['hiCity'];
  }
  if ('' !== $_POST['hiArea']) {
    $vCond['hiArea'] = $_POST['hiArea'];
  }
  if ('' !== $_POST['hiSchool']) {
    $vCond['hiSchool'] = $_POST['hiSchool'];
  }
  if ('' !== $_POST['hiSubject']) {
    $vCond['hiSubject'] = $_POST['hiSubject'];
  }
  if ('' !== $_POST['hiGrade']) {
    $vCond['hiGrade'] = $_POST['hiGrade'];
  }

  // 取得報表資料
  $vReportData = getReprotData($vCond);

  // 整理報表資料
  $vReportData = handleData($vReportData);

  // 取得圖表資料
  $vChart = getChartData($vReportData, $vCond);

  if (isset($_GET['download']) && 'report_learningeffect' === $_GET['download']) {
    make_excel($vReportData);
  }

  // 權限控制開放搜尋內容
  getUserACL();

  // 下拉選單條件設定
  $vSelect = getSelector($vCityData, $vGrade, $vSubject);

  // 時間範圍
  $sUserSearch = getCondetionRange($vCond);

  // 整理資料, 統一變數傳至HTML
  $sJSOject = arraytoJS(array('UserCode' => $vUserData['uid'],
                              'City' => $vCityData,
                              'CityArea' => $vCiryArea,
                              'School' => $vSchool,
                              'Grade' => $vGrade,
                              'Subject' => $vSubject,
                              'UserCond' => $sUserSearch,
                              'CondCity' => $vCond['hiCity'],
                              'CondArea' => $vCond['hiArea'],
                              'CondSchool' => $vCond['hiSchool'],
                              'CondGrade' => $vCond['hiGrade'],
                              'CondSubject' => $vCond['hiSubject'],
                              'Chart' => $vChart));

function getReprotData($vData) {
  global $dbh, $vUserData;

  $sUserLevel = $vUserData['access_level'];
  $sManageCity = $vUserData['city_name'];
  $sSemeYear = $vUserData['semeYear'];
  $sCityCode = $vUserData['city_code'];

  $sReprotSQL = "SELECT * FROM report_dailyusage";

  switch ($sUserLevel) {
    case '41': // 縣市政府
      $sReprotSQL .= " WHERE city_name IN('$sManageCity') ";
      if(isset($vData['time']) || isset($vData['hiCity'])) $sReprotSQL .= " AND ";
      break;

    case '51': // 教育部
      if(isset($vData['time']) || isset($vData['hiCity'])) $sReprotSQL .= " WHERE ";
      break;
  }

  if (isset($vData['time'])) {
    if ('search' === $vData['time']) {
      $sReprotSQL .= ' (datetime_log >= "'.$vData['search_start'].'" AND datetime_log <= "'.$vData['search_end'].' 23:59:59") ';
    }
    else {
      $sReprotSQL .= ' datetime_log > "'.$vData['time'].' "';
    }
  }

  if(!empty($vData['time']) && !empty($vData['hiCity'])) $sReprotSQL .= " AND ";

  if (isset($vData['hiCity']) && !empty($vData['hiCity'])) {
    $sReprotSQL .= ' city_name IN("'.$vData['hiCity'].'") ';
  }

  if(!empty($vData['hiCity']) && !empty($vData['hiArea'])) $sReprotSQL .= " AND ";

  if (isset($vData['hiArea']) && !empty($vData['hiArea'])) {
    $sReprotSQL .= ' city_area IN("'.$vData['hiArea'].'") ';
  }

  if(!empty($vData['hiArea']) && !empty($vData['hiSchool'])) $sReprotSQL .= " AND ";

  if (isset($vData['hiSchool']) && !empty($vData['hiSchool'])) {
    $sReprotSQL .= ' name IN("'.$vData['hiSchool'].'") ';
  }

  $sReprotSQL .= " GROUP BY organization_id ORDER BY postcode DESC";

  $oReprot = $dbh->prepare($sReprotSQL);
  $oReprot->execute();
  $vReportData = $oReprot->fetchAll(\PDO::FETCH_ASSOC);

  return $vReportData;
}

function handleData($vReportData) {
  if (empty($vReportData)) return array();

  $vNewData = array();
  foreach ($vReportData as $sKey => $vReport) {
    if ('190039' != $vReport['organization_id'] && '190041' != $vReport['organization_id']) {
      $vNewData[$sKey] = $vReport;
    }
  }
  return $vNewData;
}

function getChartData($vReportData, $vCond) {
  if (empty($vReportData)) return;

  global $vUserData;
  $sUserLevel = $vUserData['access_level'];
  switch ($sUserLevel) {
    case '41': // 縣市政府
      $sField = 'name';
      break;

    case '51': // 教育部
      //預設看縣市
      $sField = 'city_name';

      // 選擇城市要看區
      if ('' != $vCond['hiCity']) {
        $sField = 'city_area';
      }

      // 選擇區要看學校
      if ('' != $vCond['hiArea']) {
        $sField = 'name';
      }
      break;
  }

  foreach($vReportData as $vReport) {
     if ('' != $vReport['node']) {
      $vNode = unserialize($vReport['node']);
      foreach ($vNode as $vData) {
        foreach ($vData as $sGrade => $vNodeData) {
          if (!empty($vCond['hiSubject']) && $vCond['hiSubject'] == $vNodeData['name'] && empty($vCond['hiGrade'])) {
            $tmpData[$vReport[$sField]]['nopassnode'] += $vNodeData['nopassnode'];
            $tmpData[$vReport[$sField]]['passnode'] += $vNodeData['passnode'];
            $tmpData[$vReport[$sField]]['allnode'] += $vNodeData['allnode'];
          }
          elseif(!empty($vCond['hiGrade']) && $vCond['hiGrade'] == $sGrade  && empty($vCond['hiSubject'])) {
            $tmpData[$vReport[$sField]]['nopassnode'] += $vNodeData['nopassnode'];
            $tmpData[$vReport[$sField]]['passnode'] += $vNodeData['passnode'];
            $tmpData[$vReport[$sField]]['allnode'] += $vNodeData['allnode'];
          }
          elseif(empty($vCond['hiSubject']) && empty($vCond['hiGrade'])) {
            $tmpData[$vReport[$sField]]['nopassnode'] += $vNodeData['nopassnode'];
            $tmpData[$vReport[$sField]]['passnode'] += $vNodeData['passnode'];
            $tmpData[$vReport[$sField]]['allnode'] += $vNodeData['allnode'];
          }
          elseif($vCond['hiGrade'] == $sGrade && $vCond['hiSubject'] == $vNodeData['name']) {
            $tmpData[$vReport[$sField]]['nopassnode'] += $vNodeData['nopassnode'];
            $tmpData[$vReport[$sField]]['passnode'] += $vNodeData['passnode'];
            $tmpData[$vReport[$sField]]['allnode'] += $vNodeData['allnode'];
          }
        }
      }
    }
  }
  $vChart = array();
  if (!empty($tmpData)) {
    foreach ($tmpData as $sKey => $vData) {
      $vChart['category'][] = $sKey;
      $vChart['nopassnode'][] = $tmpData[$sKey]['nopassnode'];
      $vChart['passnode'][] = $tmpData[$sKey]['passnode'];
      $vChart['allnode'][] = $tmpData[$sKey]['allnode'];
    }
  }
  return $vChart;
}

function getUserACL() {
  global $dbh, $vUserData, $vCityData, $vCiryArea, $vSchool, $vGrade, $vSubject;

  $vSchool = array();
  $vCityData = array();
  $vCiryArea = array();
  $sUserLevel = $vUserData['access_level'];
  $sManageCity = $vUserData['city_name'];
  $sSQLCond = "SELECT city_name, city_area, postcode, name, node, organization_id FROM report_dailyusage ";
  switch($sUserLevel) {
    case '41':
      $sSQLCond .= "WHERE city_name IN('$sManageCity') AND LENGTH(node) > 1";
      break;

    default:
      $sSQLCond .= "WHERE LENGTH(node) > 1";
      break;
  }
  $sSQLCond .= ' ORDER BY postcode, city_area';
  $oCond = $dbh->prepare($sSQLCond);
  $oCond->execute();
  $vCond = $oCond->fetchAll(\PDO::FETCH_ASSOC);
  foreach ($vCond as $tmpData) {
    if ('190039' != $tmpData['organization_id'] && '190041' != $tmpData['organization_id']) {
        $vSchool[$tmpData['city_name']][$tmpData['name']] = array($tmpData['postcode'], $tmpData['city_area'], $tmpData['name']);
        $vCityData[$tmpData['city_name']] = $tmpData['city_name'];
        $vCiryArea[$tmpData['city_name']][$tmpData['city_area']] = array($tmpData['postcode'], $tmpData['city_area'], $tmpData['city_name']);

      $vNode = unserialize($tmpData['node']);
      foreach ($vNode as $vData) {
        foreach ($vData as $sGrade => $vNodeData) {
          $vGrade[$sGrade] = $sGrade;
          $vSubject[$vNodeData['name']] = $vNodeData['name'];
        }
      }
    }
  }
  if (!empty($vGrade) && !empty($vSubject)) {
    ksort($vGrade);
    ksort($vSubject);
  }
}

function getSelector($vCityData, $vGrade, $vSubject) {
  $vSelect = array();
  $vSelect['CitySelect'][] = '<select id="select_city">';
  $vSelect['CitySelect'][] =   '<option value="">縣市</option>';
  if (!empty($vCityData)) {
    foreach ($vCityData as $tmpData) {
      $vSelect['CitySelect'][] = '<option value="'.$tmpData.'">'.$tmpData.'</option>';
    }
  }
  $vSelect['CitySelect'][] = '</select>';
  $vSelect['CitySelect'][] = '<select id="select_area">';
  $vSelect['CitySelect'][] =   '<option value="區">區</option>';
  $vSelect['CitySelect'][] = '</select>';
  $vSelect['CitySelect'][] = '<select id="select_school">';
  $vSelect['CitySelect'][] =   '<option value="學校">學校</option>';
  $vSelect['CitySelect'][] = '</select>';

  $vSelect['Subject'][] = '<select id="select_subject">';
  $vSelect['Subject'][] =   '<option value="">科目</option>';
  if (!empty($vSubject)) {
    foreach ($vSubject as $tmpData) {
      $vSelect['Subject'][] = '<option value="'.$tmpData.'">'.$tmpData.'</option>';
    }
  }
  $vSelect['Subject'][] = '</select>';
  $vSelect['Subject'][] = '<select id="select_grade">';
  $vSelect['Subject'][] =   '<option value="年級">年級</option>';
  if (!empty($vGrade)) {
    foreach ($vGrade as $tmpData) {
      $vSelect['Subject'][] = '<option value="'.$tmpData.'">'.$tmpData.'年級</option>';
    }
  }
  $vSelect['Subject'][] = '</select>';

  return $vSelect;
}

function getCondetionRange($vData) {
  if (!empty($vData['hiCity'])) $sUserSearch = $vData['hiCity'].' / ';
  if (!empty($vData['hiArea'])) $sUserSearch .= $vData['hiArea'].' / ';
  if (!empty($vData['hiSchool'])) $sUserSearch .= $vData['hiSchool'].' / ';
  if (!empty($vData['hiSubject'])) $sUserSearch .= $vData['hiSubject'].' / ';
  if (!empty($vData['hiGrade'])) $sUserSearch .= $vData['hiGrade'].'年級 / ';

  if($vData['time'] == '0000-00-00' || !isset($vData['time'])) {
    $sUserSearch .= " 全部時間";
  }
  elseif($vData['time'] == date( "Y-m-d", mktime (0,0,0,date("m") ,date("d")-7, date("Y")))){
    $sUserSearch .= "最近一週";
  }
  elseif($vData['time'] == date( "Y-m-d", mktime (0,0,0,date("m") ,date("d")-14, date("Y")))){
    $sUserSearch .= "最近兩週";
  }
  elseif ($vData['time'] == date( "Y-m-d", mktime (0,0,0,date("m")-1 ,date("d"), date("Y")))){
    $sUserSearch .= "最近一個月";
  }
  else {
    $sUserSearch .= "指定區間".$vData['search_start']."~".$vData['search_end'];
  }

  return $sUserSearch;
}

function arraytoJS($vData) {
  $sJSOject = array();
  if(!empty($vData) && is_array($vData)) {
     $sJSOject = json_encode($vData);
  }
  return $sJSOject;
}

function make_excel($vReportData) {
  global $vUserData;

  $date = date('Ymd_His');
  $excel_content[0] = array('學校代號', '縣市', '區', '學校', '年級', '科目', '通過節點人數', '未通過總人數', '總人數');

  foreach ($vReportData as $vReport) {
    if('' != $vReport['node']) {
      $vNode = unserialize($vReport['node']);
        foreach ($vNode as $vData) {
          foreach ($vData as $sGrade => $vNodeData) {
            if ('' !== $vData['passnode'] || !isset($vData['passnode'])) $vData['passnode'] = '0';
            if ('' !== $vData['nopassnode'] || !isset($vData['nopassnode'])) $vData['nopassnode'] = '0';
            if ('' !== $vData['allnode'] || !isset($vData['allnode'])) $vData['allnode'] = '0';
            $excel_content[] = array($vReport['organization_id'],
                                     $vReport['city_name'],
                                     $vReport['city_area'],
                                     $vReport['name'],
                                     $sGrade.'年級',
                                     $vNodeData['name'],
                                     $vNodeData['passnode'],
                                     $vNodeData['nopassnode'],
                                     $vNodeData['allnode']);
          }
        }
    }
  }
  $objPHPExcel = new PHPExcel();
  $objPHPExcel->setActiveSheetIndex(0);
  $objPHPExcel->getActiveSheet()->fromArray($excel_content, null, 'A1');
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
  $filename= 'report_learningeffect_'.$vUserData['uid'].'.xlsx';
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
  $objWriter->save(_ADP_PATH.'data/tmp/'.$filename);
}
?>
<!DOCTYPE HTML>
<html>
<script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/echarts-all-3.js"></script>
<script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts-stat/ecStat.min.js"></script>
<script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/extension/dataTool.min.js"></script>
<script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/map/js/china.js"></script>
<script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/map/js/world.js"></script>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=ZUONbpqGBsYGXNIYHicvbAbM"></script>
<script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/extension/bmap.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@1.5.4/src/loadingoverlay.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@1.5.4/extras/loadingoverlay_progress/loadingoverlay_progress.min.js"></script>
<script>
  var oItem = $.parseJSON('<?php echo $sJSOject; ?>');
  var oUserSelect = {
    cond: oItem.UserCond,
    city: oItem.CondCity,
    area: oItem.CondArea,
    grade: oItem.CondGrade,
    subject: oItem.CondSubject,
  };

  var sTitle = '';
  if ('' !== oUserSelect.city && null !== oUserSelect.city) {
    sTitle = oUserSelect.city + ' ';
  }
  if ('' !== oUserSelect.area && null !== oUserSelect.area) {
    sTitle += oUserSelect.area + ' ';
  }
  if ('' !== oUserSelect.subject && null !== oUserSelect.subject) {
    sTitle += oUserSelect.subject + '科 ';
  }
  if ('' !== oUserSelect.grade && null !== oUserSelect.grade) {
    sTitle += oUserSelect.grade + '年級';
  }
  sTitle += ' ';

  oUserSelect.school = (!!oItem.CondSchool) ? oItem.CondSchool : sTitle;

	$(function() {
    $.LoadingOverlay("show");

    $(document).ready(function() {
      // $('#select_city').val(oUserSelect.city);
      // $('#select_area').val(oUserSelect.area);
      // $('#select_school').val(oUserSelect.school);
      // $('#select_subject').val(oUserSelect.subject);
      // $('#select_grade').val(oUserSelect.grade);
      // $('#hiSubject').val(oUserSelect.subject);
      // $('#hiGrade').val(oUserSelect.grade);
      $.LoadingOverlay("hide");
    });

    if (null !== oItem.Chart && 0 !== oItem.Chart.length) {
      var dom = document.getElementById("main_chart");
      var myChart = echarts.init(dom);
      var option = {
        textStyle: {fontWeight: 'bold', fontSize: '14'},
        title: {text: oUserSelect.school, subtext: oUserSelect.cond},
        tooltip: {trigger: 'axis', axisPointer: {type: 'shadow'}},
        legend: {data: ['通過節點人數', '未通過總人數', '全部人數']},
        toolbox: {show : true,
          feature : {
            saveAsImage : {show: true, title: '圖片', name: '各校學習狀況-長條圖'}
          }
        },
        grid: {left: '3%',right: '4%', bottom: '3%', containLabel: true},
        xAxis: {type:'value', boundaryGap:[0, 1]},
        yAxis: {type: 'category',data: oItem.Chart.category},
        series: [{name:'通過節點人數', type:'bar', data:oItem.Chart.passnode},
                 {name:'未通過總人數', type:'bar',data:oItem.Chart.nopassnode},
                 {name:'全部人數', type:'bar',data:oItem.Chart.allnode}]
      };
      myChart.setOption(option, true);

      // 圓餅圖 搜尋條件要選學校才出現
      if (('' !== oItem.CondSchool && 'string' === typeof oItem.CondSchool) || 1 === oItem.Chart.category.length) {
        var oDivPeople = document.getElementById("school_people");
        oDivPeople.style.height = '700px';
        var oPeople = {
          // tooltip: {trigger: 'item', formatter: "{a} <br/>{b}: {c} ({d}%)"},
          legend: {orient: 'vertical',x: 'left', data:['通過節點人數','未通過總人數']},
          series : [{ name: oItem.CondSchool,
                      type:'pie',
                      radius: ['50%', '70%'],
                      avoidLabelOverlap: false,
                      label: {
                          normal: {
                              show: false,
                              position: 'center'
                          },
                          emphasis: {
                              show: true,
                              textStyle: {
                                  fontSize: '30',
                                  fontWeight: 'bold'
                              }
                          }
                      },
                      labelLine: {
                          normal: {
                              show: false
                          }
                      },
                      data:[
                        {value:oItem.Chart.passnode, name:'通過節點人數' + oItem.Chart.passnode + '人'},
                        {value:oItem.Chart.nopassnode, name:'未通過總人數' + oItem.Chart.nopassnode + '人'}
                      ]
                  }
              ]
        };
        var chart_people = echarts.init(oDivPeople);
        chart_people.setOption(oPeople, true);
      }
    }
    else {
      $("#main_chart").text('查無資料!');
    }

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
      $("#select_area").empty();
      $('#select_area').append($('<option>', {value:''}).text('區'));
      $.each(oItem.CityArea[$('#select_city').val()], function(iInx, vData) {
        $('#select_area').append($('<option>', {value:vData[1]}).text(vData[1]));
      });

      // 學校
      $("#select_school").empty();
      $('#select_school').append($('<option>', {value:''}).text('學校'));
      $.each(oItem.School[$('#select_city').val()], function(iInx, vData) {
        if ($('#select_area').val() === vData[0]) {
          $('#select_school').append($('<option>', {value:vData[1]}).text(vData[1]));
        }
      });

      $('#hiCity').val($('#select_city').val());
      $('#hiArea').val($('#select_area').val());
      $('#hiSchool').val($('#select_school').val());
    });

    // 選擇區 學校需變動
    $('#select_area').change(function() {
      if ('' === $('#select_area').val()) return

      // 學校
      $("#select_school").empty();
      $('#select_school').append($('<option>', {value:''}).text('學校'));
      $.each(oItem.School[$('#select_city').val()], function(iInx, vData) {
        if ($('#select_area').val() === vData[1]) {
          $('#select_school').append($('<option>', {value:vData[2]}).text(vData[2]));
        }
      });
      $('#hiSchool').val($('#select_school').val());
      $('#hiArea').val($('#select_area').val());
    });

    // 學校
    $('#select_school').change(function() {
      if ('' === $('#select_school').val()) return
      $('#hiSchool').val($('#select_school').val());
    });

    // 科目
    $('#select_subject').change(function() {
      if ('' === $('#select_subject').val()) return
      $('#hiSubject').val($('#select_subject').val());
    });

    // 年級
    $('#select_grade').change(function() {
      if ('' === $('#select_grade').val()) return
      $('#hiGrade').val($('#select_grade').val());
    });

    $('#download_file').click(function () {
      $.ajax({
        url: 'modules.php?op=modload&name=schoolReport&file=report_learningeffect',
        type: 'GET',
        data: {
          download: 'report_learningeffect'
        },
        error: function(xhr) {
          alert('無法下載, 請稍後嘗試!');
        },
        success: function(response) {
          window.location.href = './data/tmp/report_learningeffect_' +  oItem.UserCode + '.xlsx';
        }
      });
    });

	});
</script>
<style>
  #main_cond label {cursor:pointer;}
</style>
  <div class="content2-Box">
	  <div class="path">目前位置：各校學習狀況報表</div>
      <div class="choice-box">
        <div class="choice-title">報表</div>
          <ul class="choice work-cholic">
        	  <li><a href="modules.php?op=modload&name=schoolReport&file=report_dailyusage"><i class="fa fa-caret-right"></i>各校使用狀況</a></li>
        		<li><a href="modules.php?op=modload&name=schoolReport&file=report_learningeffect" class="current"><i class="fa fa-caret-right"></i>各校學習狀況</a></li>
          </ul>
   		 </div>
      <div class="left-box">
        依定區搜尋
<?php echo implode('', $vSelect['CitySelect']); ?>
        科目 年級
<?php echo implode('', $vSelect['Subject']); ?>
      </div>
 			<div class="right-box">
			  <form id="main_cond" method="post" action="modules.php?op=modload&name=schoolReport&file=report_learningeffect">
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
          <input type="hidden" id="hiSubject" name="hiSubject">
          <input type="hidden" id="hiGrade" name="hiGrade">
			    <input type="submit" name="sreach" class="btn02" style="width:70px; display: inline;" value="查詢">
	      </form>
		  <font class="color-blue">搜尋範圍：<?php echo $sUserSearch; ?></font>
      <div id="download_file" style="text-align:right;display:inline;cursor:pointer;"><a>檔案下載</a></div>
  		<div id="main_chart" style="height:700px;"></div>
      <div id="school_people"></div>
    </div>
  </div>
</html>
