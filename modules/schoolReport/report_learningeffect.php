<?php
  include_once('include/config.php');
  include_once('classes/PHPExcel.php');
  include_once('include/ref_cityarea.php');

  if (!isset($_SESSION)) {
    session_start();
  }

  // 取得 user 資料
  $vUserData = get_object_vars($_SESSION['user_data']);
  $sUserLevel = $vUserData['access_level'];
  $sManageCity = $vUserData['city_name'];
  $sOrganization = $vUserData['organization_id'];
  $sSemeYear = $vUserData['semeYear'];
  $sCityCode = $vUserData['city_code'];

  // 查詢條件設定
  getUserACL($sUserLevel, $sManageCity);

  // 依地區搜尋條件
  $sCitySelect = getSelector($vCityData);

  // 整理資料, 統一變數傳至HTML
  $sJSOject = arraytoJS(array('City' => $vCityData,
                              'CityArea' => $vCiryArea,
                              'School' => $vSchool,
                              'UserCond' => $sUserSearch,
                              'CondCity' => $_POST['hiCity'],
                              'CondArea' => $_POST['hiArea'],
                              'CondSchool' => $_POST['hiSchool'],
                              'Chart' => $vChart
                      ));

function getUserACL($sUserLevel, $sManageCity) {
  global $dbh, $vCityData, $vCiryArea, $vSchool;

  $vSchool = array();
  $vCityData = array();
  $vCiryArea = array();
  $sSQLCond = "SELECT city_name, city_area, postcode, name FROM report_dailyusage ";
  if ('41' == $sUserLevel) {
    $sSQLCond .=  "WHERE city_name IN('$sManageCity') ";
  }
  $oCond = $dbh->prepare($sSQLCond);
  $oCond->execute();
  $vCond = $oCond->fetchAll(\PDO::FETCH_ASSOC);
  foreach ($vCond as $tmpData) {
    $vSchool[$tmpData['city_name']][$tmpData['name']] = array($tmpData['postcode'], $tmpData['city_area'], $tmpData['name']);
    $vCityData[$tmpData['city_name']] = $tmpData['city_name'];
    $vCiryArea[$tmpData['city_name']][$tmpData['city_area']] = array($tmpData['postcode'], $tmpData['city_area'], $tmpData['city_name']);
  }
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
?>
<!DOCTYPE HTML>
<html>
<header>
<script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/echarts-all-3.js"></script>
<script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts-stat/ecStat.min.js"></script>
<script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/extension/dataTool.min.js"></script>
<script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/map/js/china.js"></script>
<script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/map/js/world.js"></script>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=ZUONbpqGBsYGXNIYHicvbAbM"></script>
<script type="text/javascript" src="http://echarts.baidu.com/gallery/vendors/echarts/extension/bmap.min.js"></script>
</header>
<script>
  var oItem = $.parseJSON('<?php echo $sJSOject; ?>');
  var oUserSelect = {
    cond: oItem.UserCond,
    city: oItem.CondCity,
    area: oItem.CondArea,
    school: (!!oItem.CondSchool && '' !== oItem.CondSchool) ? oItem.CondSchool : '全部學校'
  };

	$(function() {

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
			    <input type="submit" name="sreach" class="btn02" style="width:70px; display: inline;" value="查詢">
	      </form>
		  <font class="color-blue">搜尋範圍：<?php echo $sUserSearch; ?></font>
  		<div style="text-align:right;display:inline;"><a href="<?php echo './data/tmp/use_dayilyusage.xlsx';?>" style="text-decoration:underline;">檔案下載</a></div>
  		<div id="main_chart" style="height:700px;"></div>
      <div id="school_people"></div>
      <div id="school_time"></div>
    </div>
  </div>
</html>
