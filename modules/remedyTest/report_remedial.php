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

  // echo $sClassSQL;
  // echo '<pre>';
  // print_r($vPrioriData);
  return $vPrioriData;
}

function handleData() {
  global $vReportData;

  $vNewData = array();
  foreach ($vReportData as $vData) {
    if ('' == $vData['cp_id'] || '' == $vData['concept']) continue;

    $vUser = explode('-', $vData['user_id']);
    $vPrioriRemedyRate = explode(_SPLIT_SYMBOL, $vData['priori_remedy_rate']);
    $vIndicatorItem = explode(_SPLIT_SYMBOL, $vData['indicator_item']);

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
.tippic {display:inline-table;font-size:15px;white-space:nowrap;}
.tippic > dd {display:table-row;}
.tippic > dd > * {display:table-cell;}
.tippic > dd > i {font-style:normal;}

/* 外層 */
.tbl_content {display:flex;}

/* 節點TABLE */
.scroll_detail {flex:1;display:inline-block;overflow:auto;white-space:nowrap;}
/* .scroll_detail > .tbl_detail > tbody > tr > td */
.scroll_detail > .tbl_detail .rem_point {display:inline-block;width:50%;border-right: 1px solid #000;}
.scroll_detail > .tbl_detail .adp_point {display:inline-block;width:50%;}
.scroll_detail > .tbl_detail .assign_mission {display:block;border-top: 1px solid #000;}
.scroll_detail > .tbl_detail .assign_mission > input {transform:scale(1.5);}
/* scrollbar */
.scroll_detail::-webkit-scrollbar-track {-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);border-radius: 10px;background-color: #F5F5F5;}
.scroll_detail::-webkit-scrollbar {height:10px; width: 10px;background-color: #F5F5F5;}
.scroll_detail::-webkit-scrollbar-thumb {border-radius: 10px;-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);background-color: #555;}

/* 共用 */
.scroll_detail > .tbl_detail , .tbl_head , .tbl_foot {border:2px solid rgb(160,160,143);border-collapse:collapse;border-spacing:0px;}
.scroll_detail > .tbl_detail, .scroll_detail > .tbl_detail > thead > tr > th:first-child, .scroll_detail > .tbl_detail > tbody > tr > td:first-child {border-left:none;}
.scroll_detail > .tbl_detail > thead > tr > th:last-child, .scroll_detail > .tbl_detail > tbody > tr > td:last-child  {border-right:none;}
.scroll_detail > .tbl_detail > thead > tr > th, .tbl_head > thead > tr > th, .tbl_foot > thead > tr > th {padding:10px 20px 10px 20px;text-align:center;background-color:rgb(196, 227, 191);border:2px solid rgb(160,160,143);}
.scroll_detail > .tbl_detail > tbody > tr > td, .tbl_head > tbody > tr > td, .tbl_foot > tbody > tr > td {text-align:center;border:2px solid rgb(160,160,143);}

/* .tbl_head 學生姓名 TABLE */
.tbl_head > tbody > tr > td, .tbl_foot > tbody > tr > td  {height:64px;width:90px;}

/* .tbl_foot 任務 TABLE */
.tbl_foot {margin-left:-6px;z-index:0}
.tbl_foot > tbody > tr > td {background-color:#FFF;}

.rem_naver {width:30px;background-image:url('./images/start/p5-4-01.png');background-size:100%;background-position:center;background-repeat:no-repeat;}
.rem_help {width:30px;background-image:url('./images/start/p5-4-02.png');background-size:100%;background-position:center;background-repeat:no-repeat;}
.rem_pass {width:30px;background-image:url('./images/start/p5-4-03.png');background-size:100%;background-position:center;background-repeat:no-repeat;}
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.13/vue.min.js"></script>
<script>
  var oItem = $.parseJSON('<?php echo $sJSOject; ?>');

  $(function () {
    $(document).ready(function () {
      $('#div_title').html(oItem.concept);

      // Vue
      var vueRemedial = new Vue({
        el: '#div_remedial',
        data: oItem,
        methods: {
          matchClass: function(sBridge, oArg) {
            switch (sBridge) {
              case 'rem_point':
                return ('△' == oArg.data) ? 'sans-serif' : '';
                break;
            }
          }
        }
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

      <div id="div_title" class="title01" style="float:left;display:inline-block;width:650px;"></div>
      <div>
        <dl class="tippic">
          <dd>
            <i>Ｏ</i><span>表示評量指標所有試題均通過</span>
          </dd>
          <dd>
            <i>Ｘ</i>
            <span>表示評量指標部分試題未通過</span>
          </dd>
          <dd><i style="font-family:sans-serif;">△</i>
            <span>表示評量指標所有試題均未通過</span>
          </dd>
        </dl>
        <dl class="tippic">
          <dd>
            <i class="rem_naver"></i>
            <span>未測驗</span>
          </dd>
          <dd>
            <i class="rem_help"></i>
            <span>待補救</span>
          </dd>
          <dd>
            <i class="rem_pass"></i>
            <span>精熟</span>
          </dd>
        </dl>
      </div>
      <span>測驗班級：</span>

      <div class="tbl_content">
        <table class="tbl_head">
          <thead>
            <tr>
              <th colspan="2">範圍</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in Report">
              <td>{{item.user_id}}</td>
              <td>{{item.user_name}}</td>
            </tr>
          </tbody>
        </table>

        <div class="scroll_detail">
          <table class="tbl_detail">
            <thead>
              <tr>
                <th v-for="item in indicator_item">{{item}}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="oReport in Report">
                <td v-for="item in oReport.priori_remedy_rate" style="width:100px;">
                  <span class="rem_point" style="font-family:sans-serif">{{item}}</span>
                  <span class="adp_point"></span>
                  <label class="assign_mission"><input type="checkbox"></label>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <table class="tbl_foot">
          <thead>
            <tr>
              <th>任務內容</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in Report">
              <td>
                <i class="fa fa-edit"></i>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
