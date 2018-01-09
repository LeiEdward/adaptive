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

      $sCpID = $_POST['cp_id'];
      $sMissionName = $_SESSION['remedyTest'][$sCpID]['name'];
      $sStudentID = $_POST['studentid'];
      $sSubjectID = $_POST['subject'];

      $vNode = $_SESSION['remedyTest'][$sCpID][$sStudentID][$sNodeName];
      $sNode = '';
      foreach ($vNode as $sName => $sChk) {
        if ($sChk) {
          if ('' == $sNode) {
            $sNode = $sName;
          }
          $sNode .= _SPLIT_SYMBOL.$sName;
        }
      }
      echo $sNode;
      // $sAssignMissionSQL->bindValue(':mission_nm', $sMissionName, PDO::PARAM_STR);
      // $sAssignMissionSQL->bindValue(':target_id', $sStudentID, PDO::PARAM_STR);
      // $sAssignMissionSQL->bindValue(':subject_id', $sSubjectID, PDO::PARAM_INT);
      // $sAssignMissionSQL->bindValue(':node', $sNode, PDO::PARAM_STR);
      // $sAssignMissionSQL->bindValue(':date', $_POST["enddate"], PDO::PARAM_STR);
      // $sAssignMissionSQL->bindValue(':Sdate', $_POST["strdate"], PDO::PARAM_STR);
      // $sAssignMissionSQL->bindValue(':semester', $_POST["semester"], PDO::PARAM_STR);
      // $sAssignMissionSQL->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
      // $sAssignMissionSQL->bindValue(':target_type', $_POST["mission_class"], PDO::PARAM_STR);
      // $sAssignMissionSQL->bindValue(':mission_type', $_POST["mission_type"], PDO::PARAM_STR);
      // $sAssignMissionSQL->bindValue(':create_date', $time, PDO::PARAM_STR);
      // $sAssignMissionSQL->bindValue(':endYear', $endYear_node, PDO::PARAM_STR);
      // $sAssignMissionSQL->bindValue(':topic_num', $topic_num, PDO::PARAM_INT);
      // $sAssignMissionSQL->bindValue(':exam_type', $exam_type, PDO::PARAM_INT);
      // $sAssignMissionSQL->execute();
      break;

    // 預覽任務
    default:
      $sCpID = $_GET['cp_id'];
      $sStudentID = $vUserData['organization_id'].'-'.$_GET['studentid'];
      $vNodeData = $_SESSION['remedyTest'][$sCpID][$sStudentID];

      if (empty($vNodeData)) {
        $sShowNoMisson = '沒有選擇任務';
      }

      $sNode = '';
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

      if (count($vIndicatorData) <= 0) {
        $sShowNoMisson = '沒有選擇任務';
      }

      // 整理資料, 統一變數傳至HTML
      $sJSOject = arraytoJS(array('Indicator' => $vIndicatorData,
                                  'Student' => $sStudentID,
                                  'Subjectid' => $_GET['subjectid'],
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

            var oPost = {};
            oPost.status = 'assign';
            oPost.cp_id = oItem.cp_id;
            oPost.studentid = oItem.studentid;
            oPost.subject = oItem.subjectid;

            $.ajax({
                url: './modules/remedyTest/remedial_mission.php',
                data: oPost,
                method: "POST",
                success: function (sRtn) {
                  console.log(sRtn);
                },
                error: function (jqXHR, textStatus, errorMessage) {
                  alert('指派失敗, 請重試');
                  return;
                }
            }); // ajax end
          }); // OKBtn end

          $('#cancelBtn').click(function () {

          });
        }
      }); // Vue end
    }); // document ready end
  }); // Jquery end

</script>
</head>
<body>
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
        <tr v-for="(item, index) in Indicator">
          <td>{{index+1}}</td>
          <td>{{item.indicator}}</td>
          <td>{{item.indicate_name}}</td>
          <td><input type="button" class="btn05" value="移除" btntype="delete"></td>
        </tr>
      </tbody>
    </table>
    <span id="tipshow"><?php echo $sShowNoMisson; ?></span>
    <div align="center">
      <button id="OKBtn" class="btn04" style="width:100px;">確定指派</button>
      <button id="cancelBtn" class="btn04 vbox-close" style="width:100px;">取消指派</button>
    </div>
  </div>
</body>
</html>
