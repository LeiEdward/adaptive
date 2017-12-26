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

  // 統一接變數
  $vCond = array();
  if ('' !== $_POST['select_date']) {
    $vCond['select_date'] = $_POST['select_date'];
  }
  if ('' !== $_POST['select_subject']) {
    $vCond['select_subject'] = $_POST['select_subject'];
  }
  if ('' !== $_POST['select_class']) {
    $vCond['select_class'] = $_POST['select_class'];
  }

  $sTip = '';
  if (empty($vCond)) $sTip = '請輸入查詢條件';


  // 取得主要資料
  $vReportData = getRecodeData();

  // 整理資料放回原本的 $vReportData 中
  $vReportData = handleData();

  // 下拉選單條件設定
  $sCondSelect = getSelector();

  // 整理資料, 統一變數傳至HTML
  $sJSOject = arraytoJS(array('tip' => $sTip,
                              'Report' => $vReportData['remedial']['report'],
                              'concept' => $vReportData['concept'],
                              'indicator_item' => $vReportData['remedial']['indicator_item']));


function getRecodeData() {
  global $dbh, $vUserData, $vCond;

  // 1.先取得考試基本資料
  $sPrioriSQL = "SELECT * FROM exam_record_priori
  LEFT JOIN concept_priori ON exam_record_priori.cp_id = concept_priori.cp_id";

  $oPriori = $dbh->prepare($sPrioriSQL);
  $oPriori->execute();
  $vPrioriData = $oPriori->fetchAll(\PDO::FETCH_ASSOC);

  $vCondSelect = array();
  foreach($vPrioriData as $vData) {
    $vCondSelect['date'][] = substr($vData['date'],0 ,10);
    $vCondSelect['subject'][] = $vData['subject_id'];
  }

  // 2.對應老師和教學科目
  $sSub = implode(array_unique($vCondSelect['subject']), "','");
  $sUserID = $vUserData['user_id'];

  $sClassSQL = "SELECT * FROM seme_teacher_subject WHERE teacher_id LIKE '%$sUserID%'
  AND subject_id IN ('$sSub')";
  // echo $sClassSQL;
  $oClass = $dbh->prepare($sClassSQL);
  $oClass->execute();
  $vClassData = $oClass->fetchAll(\PDO::FETCH_ASSOC);

  foreach ($vClassData as $vData) {
    $vCondSelect['class'][] = $vData['grade'].'年'.$vData['class'].'班';
  }

  // 將資料整理回 $vPrioriData, Return用
  $vPrioriData['Condition'] = array();
  $vPrioriData['Condition']['date'] = array_unique($vCondSelect['date']);
  $vPrioriData['Condition']['subject'] = array_unique($vCondSelect['subject']);
  $vPrioriData['Condition']['class'] = array_unique($vCondSelect['class']);

  // echo '<pre>';
  // print_r($vPrioriData);
  return $vPrioriData;
}

function handleData() {
  global $vReportData;

  $vNewData = array();
  foreach ($vReportData as $vData) {
    if ('' == $vData['cp_id'] || '' == $vData['concept']) continue;

    $vUser = explode('-', $vData['user_id']);$vUser = explode('-', $vData['user_id']);
    $vPrioriRemedyRate = explode('@XX@', $vData['priori_remedy_rate']);
    $vIndicatorItem = explode('@XX@', $vData['indicator_item']);

    $vNewData[] = array(
      'cp_id' => $vData['cp_id'],
      'concept' => $vData['concept'],
      'user_name' => id2uname($vData['user_id']),
      'user_id' => $vUser[1],
      'priori_remedy_rate' => $vPrioriRemedyRate
    );
    $vReportData['remedial']['report'] = $vNewData;
  }
  $vReportData['remedial']['indicator_item'] = $vIndicatorItem;
  $vReportData['concept'] = $vNewData[0]['concept'];

  // echo '<pre>';
  // print_r($vReportData);
  return $vReportData;
}

function getSelector() {
  global $vReportData;

  $bDataEmpty = false;
  if (empty($vReportData)) $bDataEmpty = true;

  $vSelect = array();
  $vSelect[] = '<select id="select_date" name="select_date">';
  $vSelect[] =   '<option value="">請選擇(日期)</option>';
  if (!$bDataEmpty) {
    foreach ($vReportData['Condition']['date'] as $sDate) {
      $vSelect[] = '<option value="'.$sDate.'">'.$sDate.'</option>';
    }
  }
  $vSelect[] = '</select>';
  $vSelect[] = '<select id="select_subject" name="select_subject">';
  $vSelect[] =   '<option value="">請選擇(科別)</option>';
  if (!$bDataEmpty) {
    foreach ($vReportData['Condition']['subject'] as $sSubject) {
      $vSelect[] = '<option value="'.$sSubject.'">'.id2subject($sSubject).'</option>';
    }
  }
  $vSelect[] = '</select>';
  $vSelect[] = '<select id="select_class" name="select_class">';
  $vSelect[] =   '<option value="">請選擇(班級)</option>';
  if (!$bDataEmpty) {
    foreach ($vReportData['Condition']['class'] as $sClass) {
      $vSelect[] = '<option value="'.$sClass.'">'.  $sClass.'</option>';
    }
  }
  $vSelect[] = '</select>';

  return implode('', $vSelect);
}
?>
<!DOCTYPE html>
<html>
<style>
  .left_header {width:150px;}
  .left_header > thead > tr > * {border-radius:0px !important;}
  .left_header > tbody > tr > * {height:4em;border-radius:0px !important;}
</style>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.1/vue.js"></script>
<script>
  var oItem = $.parseJSON('<?php echo $sJSOject; ?>');

  $(function () {
    $(document).ready(function () {
      $('#div_title').html(oItem.concept);

      // Vue
      var vueRemedial = new Vue({
        el: '#div_remedial',
        data: oItem
      });
    });
  });
</script>
<body>
  <div class="content2-Box">
	  <div class="path">目前位置：補救診斷報告</div>
    <div class="left-box">
      <form method="POST">
        <?php echo $sCondSelect; ?>
        <input id="btn_query" name="btn_query" type="submit" value="查詢" class="btn02">
      </form>
    </div>
		<div id="div_remedial" class="right-box">
      <div id="div_title" class="title01"></div>
      <!-- <span>測驗班級：</span> -->
      <dl style="width:150px;display:inline-block;">
        <dt>範圍</dt>
        <dd v-for="item in Report">
          <sapn>{{item.user_id}}</sapn>
          <sapn>{{item.user_name}}</sapn>
        </dd>
        <dd></dd>
      </dl>

      <div class="detail" style="width:500px;display:inline-block;overflow:auto;white-space:nowrap;">
        <dl style="display:inline-block;">
          <dt v-for="item in indicator_item" style="display:inline-block;">{{item}}</dt>
          <dd v-for="oReport in Report">
            <sapn v-for="item in oReport.priori_remedy_rate">
              <sapn>{{item}}<sapn>
              <i>(圖)</i>
              <sapn><input type="checkbox"></sapn>
            </sapn>
          </dd>
        </dl>
      </div>

      <dl style="width:150px;display:inline-block;">
        <dt>任務內容</dt>
        <dd v-for="item in Report">
          <i class="fa fa-edit"></i>
        </dd>
        <dd></dd>
      </dl>
    </div>
  </div>
</body>
</html>
