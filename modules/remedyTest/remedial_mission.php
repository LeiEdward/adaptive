<?php
  session_start();
  require_once('../../include/adp_API.php');

  // 取得 user 資料
  $vUserData = get_object_vars($_SESSION['user_data']);

  switch($_POST['status']) {
    // 勾選節點
    case 'checking':
      $sChecked = $_POST['checked'];
      $vPostData = explode('>>>', $_POST['data']);
      $sStudentID = $vUserData['organization_id'].'-'.$vPostData[0];
      $sNodeName = $vPostData[1];

      // !!
      $_SESSION['remedyTest'][$sStudentID][$sNodeName] = $sChecked;
      break;

    default:
      if (empty($_GET['studentid'])) return;

      $sStudentID = $vUserData['organization_id'].'-'.$_GET['studentid'];
      $vNodeData = $_SESSION['remedyTest'][$sStudentID];
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
      $sJSOject = arraytoJS(array('Indicator' => $vIndicatorData));

      // echo '<pre>';
      // print_r($vIndicatorData);

    // update
    // $result = $dbh->prepare("INSERT INTO mission_info (mission_nm, target_id, subject_id, node, date, semester, teacher_id, target_type, mission_type, create_date, start_time, endYear, topic_num, exam_type)
    //   VALUES (:mission_nm, :target_id, :subject_id, :node, :date, :semester, :teacher_id, :target_type, :mission_type, :create_date, :Sdate, :endYear, :topic_num, :exam_type)");//Sdate開始時間 BlueS 2017.07.06
    // $result->bindValue(':mission_nm', $_POST["mission_nm"], PDO::PARAM_STR);
    // $result->bindValue(':target_id', $target_data, PDO::PARAM_STR);
    // $result->bindValue(':subject_id', $_POST["subject_id"], PDO::PARAM_INT);
    // $result->bindValue(':node', $ind_node, PDO::PARAM_STR);
    // $result->bindValue(':date', $_POST["enddate"], PDO::PARAM_STR);
    // $result->bindValue(':Sdate', $_POST["strdate"], PDO::PARAM_STR);
    // $result->bindValue(':semester', $_POST["semester"], PDO::PARAM_STR);
    // $result->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
    // $result->bindValue(':target_type', $_POST["mission_class"], PDO::PARAM_STR);
    // $result->bindValue(':mission_type', $_POST["mission_type"], PDO::PARAM_STR);
    // $result->bindValue(':create_date', $time, PDO::PARAM_STR);
    // $result->bindValue(':endYear', $endYear_node, PDO::PARAM_STR);
    // $result->bindValue(':topic_num', $topic_num, PDO::PARAM_INT);
    // $result->bindValue(':exam_type', $exam_type, PDO::PARAM_INT);
    // $result->execute();
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
        data: oItem
      }); // Vue end
    }); // document ready end
  }); // Jquery end

</script>
</head>
<body>
  <div id="div_indicator" class="table_scroll">
    <span id="tipshow"><?php echo $sShowNoMisson; ?></span>

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
          <td>{{index}}</td>
          <td>{{item.indicator}}</td>
          <td>{{item.indicate_name}}</td>
          <td><input type="button" class="btn05" value="移除" btntype="delete"></td>
        </tr>
      </tbody>
    </table>
    <div align="center">
      <input type="button" id="OKBtn2" class="btn04" value="確定指派" style="width:100px;">
      <input type="button" id="cancelBtn2" class="btn04" value="取消指派" style="width:100px;">
    </div>
  </div>
</body>
</html>
