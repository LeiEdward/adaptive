<?php
  require_once('./include/config.php');
  require_once('./include/adp_API.php');

  if (!isset($_SESSION)) {
    session_start();
  }

  // 取得 user 資料
  $vUserData = get_object_vars($_SESSION['user_data']);

  // 下拉選單條件設定
  $sCondSelect = getSelector();


// 取得下拉式條件
function getSelector($vCityData = array()) {
  $vCitySelect = array();
  $vCitySelect[] = '<select id="select_date">';
  $vCitySelect[] =   '<option value="">請選擇(日期)</option>';
  $vCitySelect[] = '</select>';
  $vCitySelect[] = '<select id="select_subject">';
  $vCitySelect[] =   '<option value="區">請選擇(科別)</option>';
  $vCitySelect[] = '</select>';
  $vCitySelect[] = '<select id="select_class">';
  $vCitySelect[] =   '<option value="學校">請選擇(班級)</option>';
  $vCitySelect[] = '</select>';

  return implode('', $vCitySelect);
}
?>
<body>
  <div class="content2-Box">
	  <div class="path">目前位置：補救診斷報告</div>
    <div class="left-box">
      搜尋條件
<?php echo $sCondSelect; ?>
    </div>
		<div class="right-box">
      <div class="title01">討論主題</div>
    </div>
  </div>
</body>
</html>
