<?php
session_start();
require_once('../../include/config.php');
require_once('../../include/adp_API.php');

$user_id = $_SESSION['user_id'];
$user_data = new UserData($user_id);

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
</head>
<body>
  <div class="table_scroll" style="width:98%;margin:5px auto;">
  	<table class="datatable" id="mission_EP">
      <tbody>
        <tr>
          <th width="5%">&nbsp;</th>
          <th>能力指標</th>
          <th width="50%">指標內容說明</th>
          <th>終止年級</th>
          <th>移除</th>
        </tr>
      </tbody>
      <tr align="center" style="background-color: rgb(255, 255, 255);">
        <td>1</td>
        <td>1-n-01</td>
        <td><input type="hidden" name="ep_id[]" value="1-n-01">能認識100以內的數及「個位」、「十位」的位名，並進行位值單位的換算。</td>
        <td><input type="text" name="endYear[]" placeholder="請輸入" style="width:50px" value="1">年級</td>
        <td><input type="button" class="btn05" value="移除" btntype="delete"></td>
      </tr>
    </table>
    <div align="center">
      <input type="button" id="OKBtn2" class="btn04" value="完成" style="width:100px;"> <input type="button" id="cancelBtn2" class="btn04" value="取消" style="width:100px;">
    </div>
  </div>
</body>
</html>
