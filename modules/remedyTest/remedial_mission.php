<?php
  session_start();
  require_once('../../include/adp_API.php');

  // 取得 user 資料
  $vUserData = get_object_vars($_SESSION['user_data']);

  switch($_POST['status']) {
    // 勾選節點
    case 'checking':
      $sChecked = $_POST['checked'];

      // $vPostData[0] userid
      // $vPostData[1] nodename
      // $vPostData[2] cpid
      $vPostData = explode('>>>', $_POST['data']);
      $sStudentID = $vUserData['organization_id'].'-'.$vPostData[0];
      $sNodeName = $vPostData[1];
      $sCpID = $vPostData[2];

      // !!
      $_SESSION['remedyTest'][$sCpID]['name'] = $_POST['missionname'];
      $_SESSION['remedyTest'][$sCpID][$sStudentID][$sNodeName] = $sChecked;

      break;

    // 確定指派
    case 'assign':
      $sAssignMissionSQL = $dbh->prepare("INSERT INTO mission_info (
        mission_nm,
        target_id,
        subject_id,
        cp_id,
        node,
        date,
        semester,
        teacher_id,
        target_type,
        mission_type,
        create_date,
        start_time,
        endYear,
        topic_num,
        exam_type) VALUES (
          :mission_nm,
          :target_id,
          :subject_id,
          :cp_id,
          :node,
          :date,
          :semester,
          :teacher_id,
          :target_type,
          :mission_type,
          :create_date,
          :Sdate,
          :endYear,
          :topic_num,
          :exam_type)"
        );

      $vPara = array();
      $vPara['cp_id'] = $_POST['cp_id'];
      $vPara['studentid'] = $_POST['studentid'];

      $sNode = '';
      $vNode = $_SESSION['remedyTest'][$vPara['cp_id']][$vPara['studentid']];
      foreach ($vNode as $sName => $sChk) {
        if ('true' == $sChk) {
          if ('' == $sNode) {
            $sNode = $sName;
          }
          else {
            $sNode .= _SPLIT_SYMBOL.$sName;
          }
        }
      }

      if (isset($_POST['starttime']) && '' !== $_POST['starttime']) {
        $vPara['start_date'] = $_POST['starttime'];
      }
      else {
        $vPara['start_date'] = '0000-00-00 00:00:00';
      }

      if (isset($_POST['limittime']) && '' !== $_POST['limittime']) {
        $vPara['end_date'] = $_POST['limittime'];
      }
      else {
        $vPara['end_date'] = '0000-00-00';
      }

      $vPara['node'] = $sNode;
      $vPara['subject'] = $_POST['subject'];
      $vPara['user_id'] = $vUserData['user_id'];
      $vPara['semes'] = getYearSeme();
      $vPara['mission_name'] = $_POST['mission_nm'];
      $vPara['target_type'] = 'I';
      $vPara['mission_type'] = '3';
      $vPara['create_time'] = date("Y-m-d H:i:s");
      $vPara['endYear'] = '1';
      $vPara['exam_type'] = $_POST['exam_type'];

      // 計算縱貫題數
      // require_once('../../classes/indicateAdaptiveTestStructure.php');
      // $indicateTest = new indicateAdaptiveTestStructure($value[mission_sn]);
      // $total_items = $indicateTest->showTotalItems();
      $vPara['topic_num'] = 0;

      $sAssignMissionSQL->bindValue(':mission_nm', $vPara['mission_name'], PDO::PARAM_STR);
      $sAssignMissionSQL->bindValue(':target_id', $vPara['studentid'], PDO::PARAM_STR);
      $sAssignMissionSQL->bindValue(':subject_id', $vPara['subject'], PDO::PARAM_INT);
      $sAssignMissionSQL->bindValue(':cp_id', $vPara['cp_id'], PDO::PARAM_STR);
      $sAssignMissionSQL->bindValue(':node', $vPara['node'], PDO::PARAM_STR);
      $sAssignMissionSQL->bindValue(':date', $vPara['end_date'], PDO::PARAM_STR);
      $sAssignMissionSQL->bindValue(':Sdate', $vPara['start_date'], PDO::PARAM_STR);
      $sAssignMissionSQL->bindValue(':semester', $vPara['semes'], PDO::PARAM_STR);
      $sAssignMissionSQL->bindValue(':teacher_id', $vPara['user_id'], PDO::PARAM_STR);
      $sAssignMissionSQL->bindValue(':target_type', $vPara['target_type'], PDO::PARAM_STR);  // C全班 I個人
      $sAssignMissionSQL->bindValue(':mission_type', $vPara['mission_type'], PDO::PARAM_STR); // type:teach , exam, remedyTeach
      $sAssignMissionSQL->bindValue(':create_date', $vPara['create_time'], PDO::PARAM_STR);
      $sAssignMissionSQL->bindValue(':endYear', $vPara['endYear'], PDO::PARAM_STR);
      $sAssignMissionSQL->bindValue(':topic_num', $vPara['topic_num'], PDO::PARAM_INT);
      $sAssignMissionSQL->bindValue(':exam_type', $vPara['exam_type'], PDO::PARAM_INT);
      $sAssignMissionSQL->execute();

      // print_r($vPara);

      // !!
      $_SESSION['remedyTest'][$sCpID][$sStudentID][$sNodeName] = null;
      unset($_SESSION['remedyTest'][$vPara['cp_id']][$vPara['studentid']]);
      break;

    // 預覽任務
    default:
      $sCpID = $_GET['cp_id'];
      $sStudentID = $vUserData['organization_id'].'-'.$_GET['studentid'];
      $vNodeData = $_SESSION['remedyTest'][$sCpID][$sStudentID];

      // $sTitle = $_GET['studentid'].' '.$_GET['studentname'];
      $sTitle = $_GET['studentname'];
      $sNode = '';
      if (!empty($vNodeData)) {
        foreach ($vNodeData as $sName => $sStatus) {
          if ('' == $sNode && 'true' == $sStatus) {
            $sNode = $sName;
          }

          if ('true' == $sStatus) {
            $sNode .= "','".$sName;
          }
        }

        // 取資料
        $vIndicatorData = getIndicatorData($sNode);
      }

      if (count($vIndicatorData) <= 0) {
        $sShowNoMisson = '<td align="center" bgcolor="#FFFFFF" id="empty22" colspan="4">請勾選增加任務內容</td>';
      }

      // 整理資料, 統一變數傳至HTML
      $sJSOject = arraytoJS(array('Indicator' => $vIndicatorData,
                                  'studentid' => $sStudentID,
                                  'subjectid' => $_GET['subjectid'],
                                  'cp_id' => $_GET['cp_id']));

      // echo '<pre>';
      // print_r($vIndicatorData);

      break;
  }

function getIndicatorData($sNode) {
  global $dbh;

  $sIndicatorSQL = "SELECT * FROM indicator_info WHERE indicator IN('$sNode')";
  $oIndicator = $dbh->prepare($sIndicatorSQL);
  $oIndicator->execute();
  $vIndicator = $oIndicator->fetchAll(\PDO::FETCH_ASSOC);

  return $vIndicator;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>因材網</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=yes,maximum-scale=5.0,minimum-scale=1.0">
<link href='favicon.ico' rel='icon' type='image/x-icon'/>
<link rel="stylesheet" href="../../css/cms-index.css" type="text/css">
<link rel="stylesheet" href="../../css/cms-header.css" type="text/css">
<link rel="stylesheet" href="../../css/cms-footer.css" type="text/css">
<style>
  .table_scroll {width:98%;margin:5px auto;}
  .table_scroll > tbody > tr {background-color:rgb(255, 255, 255);}
  .table_scroll > tbody > tr > td {text-align:center;}
</style>
<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.13/vue.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@1.5.4/src/loadingoverlay.min.js"></script>
<script>
var oItem = $.parseJSON('<?php echo $sJSOject; ?>');
  console.log(oItem);

  $(function () {
    $(document).ready(function () {
      // Vue
      var vm = new Vue({
        el: '#div_indicator',
        data: oItem,
        mounted: function () {
          $('#OKBtn').click(function () {
            $.LoadingOverlay("show");

            var oPost = {};
            oPost.status = 'assign';
            oPost.cp_id = oItem.cp_id;
            oPost.studentid = oItem.studentid;
            oPost.subject = oItem.subjectid;
            oPost.exam_type = $('input[name=exam_type]:checked').val();

            if ('' === $('#mission_nm').val()) {
              alert('請輸入 "任務名稱"');
              return;
            }
            oPost.mission_nm = $('#mission_nm').val();

            if ('1' == $('input[name=strdate]:checked').val()) {
              if (16 !== $('#starttime').val().length) {
                alert('請檢查 "開始時間" 是否有設完整');
                return;
              }
              oPost.starttime = $('#starttime').val();
            }

            if ('1' == $('input[name=enddate]:checked').val()) {
              if (10 !== $('#limittime').val().length) {
                alert('請檢查 "結束時間" 是否有設完整');
                return;
              }
              oPost.limittime = $('#limittime').val();
            }

            $.ajax({
                url: 'remedial_mission.php',
                data: oPost,
                method: "POST",
                success: function (sRtn) {
                  parent.location.reload();
                },
                error: function (jqXHR, textStatus, errorMessage) {
                  alert('指派失敗, 請重試');
                  return;
                }
            }); // ajax end
          }); // OKBtn end
        }
      }); // Vue end
    }); // document ready end
  }); // Jquery end

</script>
</head>
<body>
  <div class="title01" style="text-align:left;">步驟一：建立 <b><?php echo $sTitle; ?></b> 的任務</div>
  <div class="class-list2 test-search" style="width:98%;margin:5px auto;">
    <div class="work-box-33" style="width: calc(50% - 5px);">任務名稱：<br>
      <input type="text" id="mission_nm" placeholder="請輸入任務名稱" required>
    </div>
    <div class="work-box-33" style="width: calc(50% - 5px);">縱貫式測驗模式：<br>
      <label><input type="radio" name="exam_type" value="0" checked> 適性全測&nbsp;&nbsp;</label>
      <label><input type="radio" name="exam_type" value="1">適性</label>
    </div>
    <div class="work-box-33" style="width: calc(50% - 5px);">開始時間：<br>
      <label><input type="radio" name="strdate" value="0" checked> 不設限&nbsp;&nbsp;</label>
      <label>
        <input type="radio" name="strdate" value="1">
        <input type="datetime-local" id="starttime" class="input-normal" style="min-height: 34px; font-size: 19px;">
      </label>
    </div>
    <div class="work-box-33" style="width: calc(50% - 5px);">完成時限：<br>
      <label><input type="radio" name="enddate" value="0" checked> 不設限&nbsp;&nbsp;</label>
      <label>
        <input type="radio" name="enddate" value="1">
        <input type="date" id="limittime" class="input-normal" placeholder="yyyy/mm/dd" style="width:200px;">
      </label>
    </div>
  </div>
  <div class="after-20"></div>
  <div class="title01" style="text-align:left;">步驟二：確定任務</div>
  <div id="div_indicator" class="table_scroll">
  	<table class="datatable">
      <colgroup>
        <col style="width:5%;" />
        <col />
        <col style="width:50%;"/>
        <col />
        <col />
      </colgroup>
      <thead>
        <tr>
          <th>&nbsp;</th>
          <th>能力指標</th>
          <th>指標內容說明</th>
          <th>移除</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="(null == Indicator) || Indicator"><?php echo $sShowNoMisson; ?></tr>
        <tr v-for="(item, index) in Indicator">
          <td>{{index+1}}</td>
          <td>{{item.indicator}}</td>
          <td>{{item.indicate_name}}</td>
          <td><input type="button" class="btn05" value="移除" btntype="delete"></td>
        </tr>
      </tbody>
    </table>
    <div align="center">
      <button id="OKBtn" class="btn04" style="width:100px;">確定指派</button>
      <!-- <button id="cancelBtn" class="btn04 vbox-close" style="width:100px;">取消指派</button> -->
    </div>
  </div>
</body>
</html>
