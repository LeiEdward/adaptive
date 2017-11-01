<?php
  include_once('include/config.php');
  include_once('classes/PHPExcel.php');
  include_once('classes/PHPExcel/IOFactory.php');

  if (!isset($_SESSION)) {
  	session_start();
  }

  $vUertACL = array('41', '51');

  // 取得 user 資料
  $vUserData = get_object_vars($_SESSION['user_data']);

  if (false === array_search($vUserData['uid'], $vUertACL)) {
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

  // 取得報表資料
  $vReportData = getReprotData($vCond);

  // 整理報表資料
  $vReportData = handleData($vReportData);
  if (isset($_GET['download']) && 'report_dailyusage' === $_GET['download']) {
    make_excel($vReportData);
  }

  // 取得圖表資料
  $vChart = getChartData($vReportData, $vCond);

  // 查詢條件設定
  getUserACL();

  // 下拉選單條件設定
  $sCitySelect = getSelector($vCityData);

  // 時間範圍
  $sUserSearch = getCondetionRange($vCond);


  // 整理資料, 統一變數傳至HTML
  $sJSOject = arraytoJS(array('UserCode' => $vUserData['uid'],
                              'City' => $vCityData,
                              'CityArea' => $vCiryArea,
                              'School' => $vSchool,
                              'UserCond' => $sUserSearch,
                              'CondCity' => $vCond['hiCity'],
                              'CondArea' => $vCond['hiArea'],
                              'CondSchool' => $vCond['hiSchool'],
                              'Chart' => $vChart
                      ));

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
      $sName = preg_replace('/\t|\s+/', '', $vReport[$sField]);
      $tmpData[$vReport[$sField]]['examtotal'] += $vReport['exam_total'];
      $tmpData[$vReport[$sField]]['watching'] += $vReport['video_watching_total'];
      $tmpData[$vReport[$sField]]['exercise'] += $vReport['exercise_total'];
      $tmpData[$vReport[$sField]]['spendtime'] += $vReport['video_spend_time'];
    }

    foreach ($tmpData as $sKey => $vData) {
      $vChart['category'][] = $sKey;
      $vChart['examtotal'][] = $tmpData[$sKey]['examtotal'];
      $vChart['watching'][] = $tmpData[$sKey]['watching'];
      $vChart['exercise'][] = $tmpData[$sKey]['exercise'];
      $vChart['spendtime'][] = $tmpData[$sKey]['spendtime'];
    }

    return $vChart;
  }

  function getUserACL() {
    global $dbh, $vUserData, $vCityData, $vCiryArea, $vSchool;

    $vSchool = array();
    $vCityData = array();
    $vCiryArea = array();
    $sUserLevel = $vUserData['access_level'];
    $sManageCity = $vUserData['city_name'];
    $sSQLCond = "SELECT city_name, city_area, postcode, name, organization_id FROM report_dailyusage ";
    if ('41' == $sUserLevel) {
      $sSQLCond .=  "WHERE city_name IN('$sManageCity') ";
    }
    $sSQLCond .= ' ORDER BY postcode, city_area';
    $oCond = $dbh->prepare($sSQLCond);
    $oCond->execute();
    $vCond = $oCond->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($vCond as $tmpData) {
      if ('190039' != $tmpData['organization_id'] && '190041' != $tmpData['organization_id']) {
        $vCityData[$tmpData['city_name']] = $tmpData['city_name'];
        $vCiryArea[$tmpData['city_name']][$tmpData['city_area']] = array($tmpData['postcode'], $tmpData['city_area'], $tmpData['city_name']);
        $vSchool[$tmpData['city_name']][$tmpData['name']] = array($tmpData['postcode'], $tmpData['city_area'], $tmpData['name']);
      }
    }
  }

  function getCondetionRange($vData) {
    if (!empty($vData['hiCity'])) $sUserSearch = $vData['hiCity'].' / ';
    if (!empty($vData['hiArea'])) $sUserSearch .= $vData['hiArea'].' / ';
    if (!empty($vData['hiSchool'])) $sUserSearch .= $vData['hiSchool'].' / ';

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

  function make_excel($vReportData) {
    global $vUserData;

  	$date = date("Ymd_His");
  	$excel_content[0] = array("學校代碼", "縣市", "區", "學校", "測驗人數", "影片瀏覽人數", "影片瀏覽時間(小時)", "練習題測驗人數");

  	foreach ($vReportData as $vData) {
      if('' == $vData['exam_total']) $vData['exam_total'] = '0';
      if('' == $vData['video_watching_total']) $vData['video_watching_total']='0';
  		if('' == $vData['video_spend_time']) $vData['video_spend_time']='0';
  		if('' == $vData['exercise_total']) $vData['exercise_total']='0';

  		$excel_content[$vData['organization_id']] = array($vData['organization_id'],
                                                        $vData['city_name'],
                                                        $vData['city_area'],
                                                        $vData['name'],
                                                        $vData['exam_total'],
                                                        $vData['video_watching_total'],
                                                        $vData['video_spend_time'],
                                                        $vData['exercise_total']);
  	}
  	$objPHPExcel = new PHPExcel();
  	$objPHPExcel->setActiveSheetIndex(0);
  	$objPHPExcel->getActiveSheet()->fromArray($excel_content, null, 'A1');
  	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
  	$filename = 'report_dailyusage_'.$vUserData['uid'].'.xlsx';
  	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
  	$objWriter->save(_ADP_PATH.'data/tmp/'.$filename);
  }

  function getSelector($vCityData) {
    $vCitySelect = array();
    $vCitySelect[] = '<select id="select_city">';
    $vCitySelect[] =   '<option value="">縣市</option>';
    if (!empty($vCityData)) {
      foreach ($vCityData as $tmpData) {
        $vCitySelect[] = '<option value="'.$tmpData.'">'.$tmpData.'</option>';
      }
    }
    $vCitySelect[] = '</select>';
    $vCitySelect[] = '<select id="select_area">';
    $vCitySelect[] =   '<option value="區">區</option>';
    $vCitySelect[] = '</select>';
    $vCitySelect[] = '<select id="select_school">';
    $vCitySelect[] =   '<option value="學校">學校</option>';
    $vCitySelect[] = '</select>';

    return implode('', $vCitySelect);
  }

  function arraytoJS($vData) {
    $sJSOject = array();
    if(!empty($vData) && is_array($vData)) {
       $sJSOject = json_encode($vData);
    }
    return $sJSOject;
  }

  function getReprotData($vData) {
    global $dbh, $vUserData;

    $sUserLevel = $vUserData['access_level'];
    $sManageCity = $vUserData['city_name'];
    $sSemeYear = $vUserData['semeYear'];
    $sCityCode = $vUserData['city_code'];

    $sReprotSQL = "SELECT * FROM report_dailyusage ";
    switch ($sUserLevel) {
      case '41': // 縣市政府
        $sReprotSQL .= "WHERE city_name IN('$sManageCity') ";
        if(isset($vData['time']) || isset($vData['hiCity'])) $sReprotSQL .= " AND ";
        break;

      case '51': // 教育部
        if(isset($vData['time']) || isset($vData['hiCity'])) $sReprotSQL .= " WHERE ";
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

  function sub_name($sub) {
  	global $dbh;

  	$sql = "SELECT map_name FROM `map_info` where subject_id='$sub' AND display=1";
  	$data = $dbh->query($sql);
  	$row = $data->fetch();
  	return $row['map_name'];
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
    area: oItem.CondArea
  };

  var sTitle = '';
  if ('' !== oUserSelect.city && null !== oUserSelect.city) {
    sTitle = oUserSelect.city + ' ';
  }
  if ('' !== oUserSelect.area && null !== oUserSelect.area) {
    sTitle += oUserSelect.area;
  }
  sTitle += ' ';
  oUserSelect.school = (!!oItem.CondSchool) ? oItem.CondSchool : sTitle;

	$(function() {
    $.LoadingOverlay("show");

    $(document).ready(function() {
      $.LoadingOverlay("hide");
    });

    $("#search_start").click(function() {
  	  $("#setrange").attr("checked",true);
  	});
    $("#search_end").click(function() {
  	  $("#setrange").attr("checked",true);
  	});

    if (null !== oItem.Chart && 0 !== oItem.Chart.length) {
      // chart
      var dom = document.getElementById("main_chart");
      var myChart = echarts.init(dom);
      var option = {
        textStyle: {fontWeight: 'bold', fontSize: '14'},
        title: {text: oUserSelect.school, subtext: oUserSelect.cond},
        tooltip: {trigger: 'axis', axisPointer: {type: 'shadow'}},
        legend: {data: ['測驗人數', '影片瀏覽人數', '練習題測驗人數', '影片瀏覽時間(小時)']},
        toolbox: {show : true,
          feature : {
            saveAsImage : {show: true, title: '圖片', name: '各校使用狀況-長條圖'}
          }
        },
        grid: {left: '3%',right: '4%', bottom: '3%', containLabel: true},
        xAxis: {type:'value', boundaryGap:[0, 1]},
        yAxis: {type: 'category',data: oItem.Chart.category},
        series: [{name:'測驗人數', type:'bar', data:oItem.Chart.examtotal},
                 {name:'影片瀏覽人數', type:'bar',data:oItem.Chart.watching},
                 {name:'練習題測驗人數', type:'bar',data:oItem.Chart.exercise},
                 {name:'影片瀏覽時間(小時)', type:'bar',data:oItem.Chart.spendtime}]
      };
        myChart.setOption(option, true);

        // 圓餅圖 搜尋條件要選學校才出現
        if (('' !== oItem.CondSchool && 'string' === typeof oItem.CondSchool) || 1 === oItem.Chart.category.length) {
          var oDivPeople = document.getElementById("school_people");
          oDivPeople.style.height = '700px';
          var oPeople = {
            // tooltip: {trigger: 'item', formatter: "{a} <br/>{b}: {c} ({d}%)"},
            legend: {orient: 'vertical',x: 'left', data:['測驗人數共','影片瀏覽人數','練習題測驗人數']},
            series : [{ name: oItem.Chart.category,
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
                          {value:oItem.Chart.examtotal, name:'測驗人數共' + oItem.Chart.examtotal + '人'},
                          {value:oItem.Chart.watching, name:'影片瀏覽人數' + oItem.Chart.watching + '人'},
                          {value:oItem.Chart.exercise, name:'練習題測驗人數'+ oItem.Chart.exercise + '人'}
                        ]
                    }
                ]
          };
          var chart_people = echarts.init(oDivPeople);
          chart_people.setOption(oPeople, true);

        // 要有影片瀏覽時間才顯示
        if (0 != oItem.Chart.spendtime[0]) {
          var oDivTime = document.getElementById("school_time");
          oDivTime.style.height = '700px';
          var oTime = {
            // tooltip: {trigger: 'item', formatter: "{a} <br/>{b}: {c} ({d}%)"},
            legend: {orient: 'vertical',x: 'left', data:['影片瀏覽時間(小時)']},
            series : [{ name: oItem.Chart.category,
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
                        data:[{value:oItem.Chart.spendtime, name:'影片瀏覽共' + oItem.Chart.spendtime + '小時'}]
                    }
                ]
          };
          var chart_time = echarts.init(oDivTime);
          chart_time.setOption(oTime, true);
        }
    }
  }
  else {
    $("#main_chart").text('查無資料!');
  }

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

  $('#download_file').click(function () {
    $.ajax({
      url: 'modules.php?op=modload&name=schoolReport&file=report_dailyusage',
      type: 'GET',
      data: {
        download: 'report_dailyusage'
      },
      error: function(xhr) {
        alert('無法下載, 請稍後嘗試!');
      },
      success: function(response) {
        window.location.href = './data/tmp/report_dailyusage_' +  oItem.UserCode + '.xlsx';
      }
    });
  });

});
</script>
<style>
  #main_cond label {cursor:pointer;}
</style>
<body>
  <div class="content2-Box">
	  <div class="path">目前位置：各校使用狀況</div>
      <div class="choice-box">
        <div class="choice-title">報表</div>
          <ul class="choice work-cholic">
        	  <li><a href="modules.php?op=modload&name=schoolReport&file=report_dailyusage" class="current"><i class="fa fa-caret-right"></i>各校使用狀況</a></li>
        		<li><a href="modules.php?op=modload&name=schoolReport&file=report_learningeffect"><i class="fa fa-caret-right"></i>各校學習狀況</a></li>
          </ul>
   		 </div>
      <div class="left-box">
        依定區搜尋
<?php echo $sCitySelect; ?>
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
			    <input type="submit" id="sreach" name="sreach" class="btn02" style="width:70px; display: inline;" value="查詢">
	      </form>
		  <font class="color-blue">搜尋範圍：<?php echo $sUserSearch; ?></font>
  		<div id="download_file" style="text-align:right;display:inline;cursor:pointer;"><a>檔案下載</a></div>
  		<div id="main_chart" style="height:700px;"></div>
      <div id="school_people"></div>
      <div id="school_time"></div>
    </div>
  </div>
</body>
</html>
