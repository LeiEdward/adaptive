<?php
  /*
    TODO 2018-01-04: Edward 暫時只顯示數學科目，其他科目只需微調有!!註記部分
  */
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
  if (!empty($_POST['select_date'])) {
    $vCond['select_date'] = $_POST['select_date'];
  }
  if (!empty($_POST['select_subject'])) {
    $vCond['select_subject'] = $_POST['select_subject'];
  }
  if (!empty($_POST['select_class'])) {
    $vCond['select_class'] = $_POST['select_class'];
  }
  if (!empty($_POST['select_version'])) {
    $vCond['select_version'] = $_POST['select_version'];
  }
  $sSetcondition = '';
  if (empty($vCond)) {
    $sSetcondition = '請輸入查詢條件或查無資料';
  }

  // 取得主要資料
  $vReportData = getRecodeData();

  // 整理資料放回原本的 $vReportData 中
  $vReportData = handleData();

  // 下拉選單條件設定
  $sCondSelect = getSelector();

  // 查詢條件顯示
  $sUserSearch = getCondetionRange();

  // 整理資料, 統一變數傳至HTML
  $sJSOject = arraytoJS(array('setcondition' => $sSetcondition,
                              'Report' => $vReportData['remedial']['report'],
                              'concept' => $vReportData['concept'],
                              'indicator_item' => $vReportData['remedial']['indicator_item']));

function getRecodeData() {
  global $dbh, $vUserData, $vCond;

  //
  $sOrganization = $vUserData['organization_id'];

  //
  $sVersion = $vCond['select_version'];
  $sExamDate = $vCond['select_date'];
  $sClass = $vCond['select_class'];
  $sSubjectSn = $vCond['select_subject'];
  $sMapSn = subject2mapSN($sSubjectSn);

  // !!
  // $vSQLConjunctions = array('');
  // for ($i=0,$iMax=count($vCond)-1; $i<$iMax ; $i++) {
  for ($i=0,$iMax=count($vCond); $i<$iMax ; $i++) {
    $vSQLConjunctions[] = 'AND';
  }

  // 1.查詢考試基本資料
  $sPrioriSQL = "SELECT * FROM exam_record_priori
  LEFT JOIN concept_priori ON exam_record_priori.cp_id = concept_priori.cp_id
  LEFT JOIN map_node_student_status ON exam_record_priori.user_id = map_node_student_status.user_id
  LEFT JOIN user_info ON user_info.user_id = exam_record_priori.user_id
  WHERE user_info.organization_id = '$sOrganization'";
  if (!empty($vCond)) {
    // !!
    $sPrioriSQL.= " AND map_node_student_status.map_sn IN('".subject2mapSN(2)."') ";
  }

  if (!empty($sSubjectSn)) {
    $sPrioriSQL.= array_shift($vSQLConjunctions)." subject_id IN('$sSubjectSn') ";
  }
  // !!
  // if (!empty($sMapSn)) {
  //   $sPrioriSQL.= array_shift($vSQLConjunctions)." map_node_student_status.map_sn IN('$sMapSn') ";
  // }
  if (!empty($sVersion)) {
    $sPrioriSQL.= array_shift($vSQLConjunctions)." version IN('$sVersion') ";
  }
  if (!empty($sExamDate)) {
    $sPrioriSQL.= array_shift($vSQLConjunctions)." exam_record_priori.date like('%$sExamDate%') ";
  }
  if (!empty($vCond)) {
    $sPrioriSQL.= " ORDER BY exam_record_priori.user_id ";
  }
  // echo $sPrioriSQL;
  $oPriori = $dbh->prepare($sPrioriSQL);
  $oPriori->execute();
  $vPrioriData = $oPriori->fetchAll(\PDO::FETCH_ASSOC);

  // 2.取得查詢條件
  $sConditionSQL = "SELECT * FROM exam_record_priori
  LEFT JOIN concept_priori ON exam_record_priori.cp_id = concept_priori.cp_id
  LEFT JOIN map_node_student_status ON exam_record_priori.user_id = map_node_student_status.user_id";
  $oCondition = $dbh->prepare($sConditionSQL);
  $oCondition->execute();
  $vCondition = $oCondition->fetchAll(\PDO::FETCH_ASSOC);

  $vCondSelect = array();
  foreach($vCondition as $vData) {
    $vCondSelect['date'][] = substr($vData['date'],0 ,10);
    $vCondSelect['subject'][] = $vData['subject_id'];
    $vCondSelect['version'][] = $vData['version'];
  }

  // 2.對應老師和教學科目
  // !! $sSub = implode(array_unique($vCondSelect['subject']), "','");
  $sSub = 2; // 暫時只顯示數學
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
  $vPrioriData['Condition']['version'] = array_unique($vCondSelect['version']);
  $vPrioriData['Condition']['date'] = array_unique($vCondSelect['date']);
  $vPrioriData['Condition']['subject'] = array_unique($vCondSelect['subject']);
  $vPrioriData['Condition']['class'] = array_unique($vCondSelect['class']);


  // echo 'Priori: '.$sPrioriSQL;
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
    $vBigNodeStatus = unserialize($vData['bNodes_Status']);
    $vAdpNodeStatus = array();
    foreach ($vIndicatorItem as $key => $vItem) {
      if (empty($vBigNodeStatus[$vItem]['bstatus:'])) $vBigNodeStatus[$vItem]['bstatus:'] = '';
      $vAdpNodeStatus[$key]['adpstatus'] = $vBigNodeStatus[$vItem]['bstatus:'];
      $vAdpNodeStatus[$key]['priori'] = $vPrioriRemedyRate[$key];
    }

    $vNewData[] = array(
      'cp_id' => $vData['cp_id'],
      'concept' => $vData['concept'],
      'user_name' => $vData['uname'],
      'user_id' => $vUser[1],
      'priori_remedy_rate' => $vAdpNodeStatus
    );
    $vReportData['remedial']['report'] = $vNewData;
  }
  $vReportData['remedial']['indicator_item'] = $vIndicatorItem;

  // 標題，取第一筆資料的為主
  $vReportData['concept'] = $vNewData[0]['concept'];

  // echo '<pre>';
  // print_r($vReportData);
  return $vReportData;
}

function getSelector() {
  global $vReportData, $vCond;

  $bDataEmpty = false;
  if (empty($vReportData)) $bDataEmpty = true;

  $vSelect = array();
  $vSelect[] = '<select id="select_date" name="select_date">';
  $vSelect[] =   '<option value="">日期</option>';
  if (!$bDataEmpty) {
    foreach ($vReportData['Condition']['date'] as $sDate) {
      $sSelected = '';
      if ($vCond['select_date'] == $sDate) {
        $sSelected = 'selected';
      }
      $vSelect[] = '<option value="'.$sDate.'" '.$sSelected.'>'.$sDate.'</option>';
    }
  }
  $vSelect[] = '</select>';
  $vSelect[] = '<select id="select_subject" name="select_subject">';
  $vSelect[] =   '<option value="">科別</option>';
  if (!$bDataEmpty) {
    foreach ($vReportData['Condition']['subject'] as $sSubject) {
      $sSelected = '';
      if ($vCond['select_subject'] == $sSubject) {
        $sSelected = 'selected';
      }
      $vSelect[] = '<option value="'.$sSubject.'" '.$sSelected.'>'.id2subject($sSubject).'</option>';
    }
  }
  $vSelect[] = '</select>';
  $vSelect[] = '<select id="select_class" name="select_class">';
  $vSelect[] =   '<option value="">班級</option>';
  if (!$bDataEmpty) {
    foreach ($vReportData['Condition']['class'] as $sClass) {
      $sSelected = '';
      if ($vCond['select_class'] == $sClass) {
        $sSelected = 'selected';
      }
      $vSelect[] = '<option value="'.$sClass.'" '.$sSelected.'>'.  $sClass.'</option>';
    }
  }
  $vSelect[] = '</select>';
  $vSelect[] = '<select id="select_version" name="select_version">';
  $vSelect[] =   '<option value="">第幾次匯入</option>';
  if (!$bDataEmpty) {
    foreach ($vReportData['Condition']['version'] as $sVersion) {
      $sSelected = '';
      if ($vCond['select_version'] == $sVersion) {
        $sSelected = 'selected';
      }
      $vSelect[] = '<option value="'.$sVersion.'" '.$sSelected.'>第'.  $sVersion.'次匯入</option>';
    }
  }
  $vSelect[] = '</select>';

  return implode('', $vSelect);
}

function getCondetionRange() {
  global $vCond;

  if (!empty($vCond['select_date'])) {
    $sUserSearch = $vCond['select_date'].' / ';
  }
  else {
    $sUserSearch = '全部時間 / ';
  }

  if (!empty($vCond['select_subject'])) {
    $sUserSearch .= id2subject($vCond['select_subject']).' / ';
  }
  else {
    $sUserSearch .= '全部科別 / ';
  }

  if (!empty($vCond['select_class'])) {
    $sUserSearch .= $vCond['select_class'].' / ';
  }
  else {
    $sUserSearch .= '全部班級 / ';
  }

  if (!empty($vCond['select_version'])) {
    $sUserSearch .= '第 '.$vCond['select_version'].' 次匯入';
  }
  else {
    $sUserSearch .= '全部匯入次數';
  }

  return $sUserSearch;
}
?>
<!DOCTYPE html>
<html>
<style>
  .tippic {display:inline-table;font-size:15px;white-space:nowrap;padding-top:40px;}
  .tippic > dd {display:table-row;}
  .tippic > dd > * {display:table-cell;}
  .tippic > dd > i {font-style:normal;}

  /* 外層 */
  .tbl_content {display:flex;}

  /* 節點TABLE */
  .scroll_detail {flex:1;display:inline-block;overflow:auto;white-space:nowrap;}
  /* .scroll_detail > .tbl_detail > tbody > tr > td */
  .scroll_detail > .tbl_detail .rem_point {display:inline-block;width:50%;border-right: 1px solid #000;}
  .scroll_detail > .tbl_detail .adp_point {display:inline-block;width:50%;vertical-align:middle;}
  .scroll_detail > .tbl_detail .adp_point > i {margin-left:8px;}
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
  .tbl_head > tbody > tr > td, .tbl_foot > tbody > tr > td  {height:64px;width:90px;}
  .tbl_head > tbody > tr:last-of-type, .tbl_foot > tbody > tr:last-of-type {height:75px;}

  /* .tbl_foot 任務 TABLE */
  .tbl_foot {margin-left:-6px;z-index:0}
  .tbl_foot > tbody > tr > td {background-color:#FFF;}

  .rem_naver {display:block;width:25px;height:25px;background-image:url('./images/start/p5-4-01.png');background-size:100%;background-position:center;background-repeat:no-repeat;}
  .rem_help {display:block;width:25px;height:25px;background-image:url('./images/start/p5-4-02.png');background-size:100%;background-position:center;background-repeat:no-repeat;}
  .rem_pass {display:block;width:25px;height:25px;background-image:url('./images/start/p5-4-03.png');background-size:100%;background-position:center;background-repeat:no-repeat;}
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.13/vue.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@1.5.4/src/loadingoverlay.min.js"></script>
<script>
  var oItem = $.parseJSON('<?php echo $sJSOject; ?>');

  $(function () {
    $('#btn_query').click(function (){
      $.LoadingOverlay("show");
    });

    $(document).ready(function () {
      $('#div_title').html(oItem.concept);
      $.LoadingOverlay("show");

      if ('' !== oItem.setcondition) {
        $('#div_remedial').html('<div>' + oItem.setcondition + '</div>');
        $.LoadingOverlay("hide");
        return;
      }
      if (null == oItem.Report) {
        $('#div_remedial').html('<div>報表條件：<?php echo $sUserSearch; ?>查無資料</div>');
        $.LoadingOverlay("hide");
        return;
      }

      // Vue
      var vueRemedial = new Vue({
        el: '#div_remedial',
        data: oItem,
        mounted: function () {
          $.LoadingOverlay("hide");
        },
        methods: {
          matchClass: function(sAdpStatus) {
            switch (sAdpStatus) {
              case '0':
                return 'rem_help';
                break;
              case '1':
                return 'rem_pass';
                break;

              default:
                return 'rem_naver';
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
      <span id="tipshow">報表條件：<?php echo $sUserSearch ?></span>

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
                <td v-for="oItem in oReport.priori_remedy_rate">
                  <div>
                    <span class="rem_point" style="font-family:sans-serif">{{oItem.priori}}</span>
                    <span class="adp_point"><i v-bind:class="matchClass(oItem.adpstatus)"></i></span>
                    <label class="assign_mission"><input type="checkbox"></label>
                  </div>
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
