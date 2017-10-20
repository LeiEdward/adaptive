<?php
  include_once('include/config.php');
  include_once('classes/PHPExcel.php');
  include_once('include/ref_cityarea.php');

  if (!isset($_SESSION)) {
    session_start();
  }

  // 取得 user 資料
  $vUserData = get_object_vars($_SESSION['user_data']);

  // 取得報表資料
  $vReportData = getReprotData($vUserData, $_POST);

  // 整理報表資料
  $vReportData = handleData($vReportData);

  // 取得圖表資料
  $vChart = getChartData($vReportData, $_POST);

  // 權限控制開放搜尋內容
  getUserACL($vUserData);

  // 下拉選單條件設定
  $vSelect = getSelector($vCityData, $vGrade, $vSubject);

  // 時間範圍
  $sUserSearch = getCondetionRange($_POST);

  // 整理資料, 統一變數傳至HTML
  $sJSOject = arraytoJS(array('City' => $vCityData,
                              'CityArea' => $vCiryArea,
                              'School' => $vSchool,
                              'Grade' => $vGrade,
                              'Subject' => $vSubject,
                              'UserCond' => $sUserSearch,
                              'CondCity' => $_POST['hiCity'],
                              'CondArea' => $_POST['hiArea'],
                              'CondSchool' => $_POST['hiSchool'],
                              'Chart' => $vChart));

function getReprotData($vUserData, $vData) {
  global $dbh;

  $sUserLevel = $vUserData['access_level'];
  $sManageCity = $vUserData['city_name'];
  $sSemeYear = $vUserData['semeYear'];
  $sCityCode = $vUserData['city_code'];

  $sReprotSQL = "SELECT * FROM report_dailyusage ";
  switch ($sUserLevel) {
    case '41': // 縣市政府
      $sReprotSQL .= "WHERE city_name IN('$sManageCity') ";
      if(isset($vData['time'])) $sReprotSQL .= " AND ";
      break;

    case '51': // 教育部
      if(isset($vData['time'])) $sReprotSQL .= " WHERE ";
      break;
  }
  if(isset($vData['time'])) {
    if ('search' === $vData['time']) {
      $sReprotSQL .= ' (datetime_log >= "'.$vData['search_start'].'" AND datetime_log <= "'.$vData['search_end'].' 23:59:59") ';
    }
    else {
      $sReprotSQL .= ' datetime_log > "'.$vData['time'].' "';
    }
  }
  if (isset($vData['hiCity']) && !empty($vData['hiCity'])) {
    $sReprotSQL .= ' AND city_name IN("'.$vData['hiCity'].'") ';
  }
  if (isset($vData['hiArea']) && !empty($vData['hiArea'])) {
    $sReprotSQL .= ' AND city_area IN("'.$vData['hiArea'].'") ';
  }
  if (isset($vData['hiSchool']) && !empty($vData['hiSchool'])) {
    $sReprotSQL .= ' AND name IN("'.$vData['hiSchool'].'") ';
  }
  $sReprotSQL .= " GROUP BY organization_id ORDER BY organization_id";

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
  foreach($vReportData as $vReport) {
    if ('' != $vReport['node']) {
      $vNode = unserialize($vReport['node']);
      $sName = preg_replace('/\t|\s+/', '', $vReport['name']);
      $vChart['school'][] = $sName;
      $tmpDatat = array();
      foreach ($vNode as $vData) {
        foreach ($vData as $sGrade => $vNodeData) {
          if (!empty($vCond['hiSubject']) && $vCond['hiSubject'] == $vNodeData['name']) {
            $tmpDatat[$vReport['organization_id']]['nopassnode'] += $vNodeData['nopassnode'];
            $tmpDatat[$vReport['organization_id']]['passnode'] += $vNodeData['passnode'];
            $tmpDatat[$vReport['organization_id']]['allnode'] += $vNodeData['allnode'];
          }

          if (!empty($vCond['hiGrade']) && $vCond['hiGrade'] == $sGrade) {
            $tmpDatat[$vReport['organization_id']]['nopassnode'] += $vNodeData['nopassnode'];
            $tmpDatat[$vReport['organization_id']]['passnode'] += $vNodeData['passnode'];
            $tmpDatat[$vReport['organization_id']]['allnode'] += $vNodeData['allnode'];
          }

          if(empty($vCond['hiSubject']) && empty($vCond['hiGrade'])) {
            $tmpDatat[$vReport['organization_id']]['nopassnode'] += $vNodeData['nopassnode'];
            $tmpDatat[$vReport['organization_id']]['passnode'] += $vNodeData['passnode'];
            $tmpDatat[$vReport['organization_id']]['allnode'] += $vNodeData['allnode'];
          }
        }
      }
      $vChart['nopassnode'][] = $tmpDatat[$vReport['organization_id']]['nopassnode'];
      $vChart['passnode'][] = $tmpDatat[$vReport['organization_id']]['passnode'];
      $vChart['allnode'][] = $tmpDatat[$vReport['organization_id']]['allnode'];
    }
  }
  return $vChart;
}

function getUserACL($vUserData) {
  global $dbh, $vCityData, $vCiryArea, $vSchool, $vGrade, $vSubject;

  $vSchool = array();
  $vCityData = array();
  $vCiryArea = array();
  $sUserLevel = $vUserData['access_level'];
  $sManageCity = $vUserData['city_name'];
  $sSQLCond = "SELECT city_name, city_area, postcode, name, node, organization_id FROM report_dailyusage ";
  switch($sUserLevel) {
    case '41':
      $sSQLCond .=  "WHERE city_name IN('$sManageCity') AND LENGTH(node) > 1";
      break;

    default:
      $sSQLCond .=  "WHERE LENGTH(node) > 1";
      break;
  }
  $oCond = $dbh->prepare($sSQLCond);
  $oCond->execute();
  $vCond = $oCond->fetchAll(\PDO::FETCH_ASSOC);
  foreach ($vCond as $tmpData) {
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
  ksort($vGrade);
  ksort($vSubject);
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
?>
<!DOCTYPE HTML>
<html>
<header>
  <!-- 統計圖套件 -->
  <script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/echarts-all-3.js"></script>
  <script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts-stat/ecStat.min.js"></script>
  <script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/extension/dataTool.min.js"></script>
  <script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/map/js/china.js"></script>
  <script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/map/js/world.js"></script>
  <script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=ZUONbpqGBsYGXNIYHicvbAbM"></script>
  <script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/extension/bmap.min.js"></script>
  <!-- Loading套件 -->
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@1.5.4/src/loadingoverlay.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@1.5.4/extras/loadingoverlay_progress/loadingoverlay_progress.min.js"></script>
</header>
<script>
  var oItem = $.parseJSON('<?php echo $sJSOject; ?>');
  var oUserSelect = {
    cond: oItem.UserCond,
    city: oItem.CondCity,
    area: oItem.CondArea,
    school: (!!oItem.CondSchool) ? oItem.CondSchool : '全部學校'
  };

	$(function() {
    $.LoadingOverlay("show");

    $(document).ready(function() {
      $.LoadingOverlay("hide");
    });

    if (null !== oItem.Chart) {
      var dom = document.getElementById("main_chart");
      var myChart = echarts.init(dom);
      var option = {
        textStyle: {fontWeight: 'bold', fontSize: '14'},
        title: {text: oUserSelect.CondSchool, subtext: oUserSelect.cond},
        tooltip: {trigger: 'axis', axisPointer: {type: 'shadow'}},
        legend: {data: ['通過節總點人數', '未通過總人數', '全部人數']},
        toolbox: {show : true,
          feature : {
            saveAsImage : {show: true, title: '圖片', name: '各校學習狀況-長條圖'}
          }
        },
        grid: {left: '3%',right: '4%', bottom: '3%', containLabel: true},
        xAxis: {type:'value', boundaryGap:[0, 1]},
        yAxis: {type: 'category',data: oItem.Chart.school},
        series: [{name:'通過節總點人數', type:'bar', data:oItem.Chart.passnode},
                 {name:'未通過總人數', type:'bar',data:oItem.Chart.nopassnode},
                 {name:'全部人數', type:'bar',data:oItem.Chart.allnode}]
      };
      myChart.setOption(option, true);

      // 圓餅圖 搜尋條件要選學校才出現
      if (('' !== oItem.CondSchool && 'string' === typeof oItem.CondSchool) || 1 === oItem.Chart.school.length) {
        var oDivPeople = document.getElementById("school_people");
        oDivPeople.style.height = '700px';
        var chart_people = echarts.init(oDivPeople);
        var oPeople = {
          // tooltip: {trigger: 'item', formatter: "{a} <br/>{b}: {c} ({d}%)"},
          legend: {orient: 'vertical',x: 'left', data:['通過節總點人數','未通過總人數']},
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
                        {value:oItem.Chart.passnode, name:'通過節總點人數' + oItem.Chart.passnode + '人'},
                        {value:oItem.Chart.nopassnode, name:'未通過總人數' + oItem.Chart.nopassnode + '人'}
                      ]
                  }
              ]
        };
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

    // if ('' !== oUserSelect.city) {
    //   $('#select_city').val(oUserSelect.city);
    //   $('#hiCity').val(oUserSelect.city);
    // }
    // if ('' !== oUserSelect.area) {
    //   $('#select_area').val(oUserSelect.area);
    //   $('#hiArea').val(oUserSelect.area);
    // }
    // if ('' !== oUserSelect.school) {
    //   $('#select_school').val(oUserSelect.school);
    //   $('#hiSchool').val(oUserSelect.school);
    // }

	});
</script>
<style>
  #main_cond label {cursor:pointer;}
  #tab_indicator thead {cursor:pointer;}
  #tab_indicator > * > * > * {vertical-align:middle;}
  #tab_indicator > * > * > *:nth-of-type(1) {min-width:105px;}
  #tab_indicator > * > * > *:nth-of-type(2) {width:85px;}
  #tab_indicator > * > * > *:nth-of-type(3) {width:85px;}
  #tab_indicator > * > * > *:nth-of-type(4) {width:230px;}
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
  		<!-- <div style="text-align:right;display:inline;"><a href="<?php echo './data/tmp/use_dayilyusage.xlsx'; ?>" style="text-decoration:underline;">檔案下載</a></div> -->
  		<div id="main_chart" style="height:700px;"></div>
      <div id="school_people"></div>
    </div>
  </div>
</html>
