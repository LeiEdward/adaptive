<?php
  require_once('../../include/config.php');
  require_once('../../include/adp_API.php');

  if (!isset($_SESSION)) {
  	session_start();
  }

  $vRtn = array();
  $oSQLUplaodReponse = $dbh->prepare("INSERT INTO message_response
     (response_sn, message_sn, response_content, remsg_create_user, res_resmsgto_user, remsg_create_time, remsg_delete_flag)
     VALUES (NULL, :message_sn, :response_content, :remsg_create_user, :res_resmsgto_user, :remsg_create_time, :remsg_delete_flag)");

  $sMessageid = $_POST['message_sn'];
  $sContent = $_POST['response_content'];
  $sCreateUser = $_POST['remsg_create_user'];
  $sDele = $_POST['remsg_delete_flag'];
  $sTime = date("Y-m-d H:i:s");

  // 單獨回覆訊息
  if (isset($_POST['res_resmsgto_user']) && $_POST['res_resmsgto_userid']) {
    $sResMsgto_user = $_POST['res_resmsgto_user'];
    $sResMsgto_userid = $_POST['res_resmsgto_userid'];
    if (false !== strpos($sContent, '@'.$sResMsgto_user)) {
      $sContent = str_replace('@'.$sResMsgto_user, '<i style="color:rgb(150, 150, 255);">@'.$sResMsgto_user.'</i>',$sContent);
    }
  }

  $oSQLUplaodReponse->bindValue(':message_sn', $sMessageid, PDO::PARAM_STR);
  $oSQLUplaodReponse->bindValue(':response_content', strip_tags($sContent), PDO::PARAM_STR);
  $oSQLUplaodReponse->bindValue(':remsg_create_time', $sTime, PDO::PARAM_STR);
  $oSQLUplaodReponse->bindValue(':remsg_create_user', $sCreateUser, PDO::PARAM_STR);
  $oSQLUplaodReponse->bindValue(':res_resmsgto_user', $sResMsgto_userid, PDO::PARAM_STR);
  $oSQLUplaodReponse->bindValue(':remsg_delete_flag', $sDele, PDO::PARAM_STR);

  $vRtn['STATUS'] = '';
  if ($oSQLUplaodReponse->execute()) {
    $vRtn['STATUS'] = 'SUCCESS';
    $vRtn['TIME'] = $sTime;
    $vRtn['CREATEUSER'] = id2uname($sCreateUser);
    $vRtn['CONTENT'] = $sContent;
  }

  echo json_encode($vRtn);
