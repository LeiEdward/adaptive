<?php
  require_once('./include/config.php');
  require_once('./include/adp_API.php');

  if (!isset($_SESSION)) {
    session_start();
  }

  // 取得 user 資料
  $vUserData = get_object_vars($_SESSION['user_data']);

  if ($vUserData['access_level'] !== USER_TEACHER) {
    echo '無權限瀏覽';
    return;
  }

  $vReportData = getRecodeData();

  // 下拉選單條件設定
  $sCondSelect = getSelector();

// 取得下拉式條件
function getSelector() {
  global $vReportData;

  $bDataEmpty = false;
  if (empty($vReportData)) $bDataEmpty = true;

  $vSelect = array();
  $vSelect[] = '<select id="select_date">';
  $vSelect[] =   '<option value="">請選擇(日期)</option>';
  if (!$bDataEmpty) {
    foreach ($vReportData['Condition']['date'] as $sDate) {
      $vSelect[] = '<option value="'.$sDate.'">'.$sDate.'</option>';
    }
  }
  $vSelect[] = '</select>';
  $vSelect[] = '<select id="select_subject">';
  $vSelect[] =   '<option value="">請選擇(科別)</option>';
  if (!$bDataEmpty) {
    foreach ($vReportData['Condition']['subject'] as $sSubject) {
      $vSelect[] = '<option value="'.$sSubject.'">'.id2subject($sSubject).'</option>';
    }
  }
  $vSelect[] = '</select>';
  $vSelect[] = '<select id="select_class">';
  $vSelect[] =   '<option value="">請選擇(班級)</option>';
  if (!$bDataEmpty) {
    foreach ($vReportData['Condition']['class'] as $sClass) {
      $vSelect[] = '<option value="'.$sClass.'">'.  $sClass.'</option>';
    }
  }
  $vSelect[] = '</select>';

  return implode('', $vSelect);
}

// 取得學生
// SELECT * FROM 'exam_record_priori'
function getRecodeData() {
  global $dbh, $vUserData;

  $sPrioriSQL = "SELECT * FROM exam_record_priori
  LEFT JOIN concept_priori ON exam_record_priori.cp_id = concept_priori.cp_id";

  $oPriori = $dbh->prepare($sPrioriSQL);
  $oPriori->execute();
  $vPrioriData = $oPriori->fetchAll(\PDO::FETCH_ASSOC);

  $vCond = array();
  foreach($vPrioriData as $vData) {
    $vCond['date'][] = substr($vData['date'],0 ,10);
    $vCond['subject'][] = $vData['subject_id'];
  }

  $vPrioriData['Condition']['date'] = array_unique($vCond['date']);
  $vPrioriData['Condition']['subject'] = array_unique($vCond['subject']);


  $sSub = implode($vPrioriData['Condition']['subject'], ',');
  $sUserID = $vUserData['user_id'];
  $sClassSQL = "SELECT * FROM seme_teacher_subject WHERE teacher_id LIKE '%$sUserID%'
  AND subject_id IN ($sSub)";
  $oClass = $dbh->prepare($sClassSQL);
  $oClass->execute();
  $vClassData = $oClass->fetchAll(\PDO::FETCH_ASSOC);

  foreach ($vClassData as $vData) {
    $vCond['class'][] = $vData['grade'].'年'.$vData['class'].'班';
  }
  $vPrioriData['Condition']['class'] = array_unique($vCond['class']);

  // echo '<pre>';
  // print_r($vPrioriData['Condition']);
  return $vPrioriData;
}
?>
<body>
  <div class="content2-Box">
	  <div class="path">目前位置：補救診斷報告</div>
    <div class="left-box">
      搜尋條件
<?php echo $sCondSelect; ?>
      <input id="btn_query" name="btn_query" type="submit" value="查詢" class="btn02">
    </div>
		<div class="right-box">
      <div class="title01">討論主題</div>
    </div>
  </div>
</body>
</html>
