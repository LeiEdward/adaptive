<?php
  require_once('../../include/config.php');
  require_once('../../include/adp_API.php');

  if (!isset($_SESSION)) {
  	session_start();
  }
  $sUpload = $_POST['upload'];

  $vRtn = array();
  switch($sUpload) {
    // 新增
    case 'INS':
      $oSQLUplaodReponse = $dbh->prepare("INSERT INTO message_response
         (response_sn, message_sn, response_content, remsg_create_user, res_resmsgto_user, remsg_create_time, remsg_delete_flag)
         VALUES (NULL, :message_sn, :response_content, :remsg_create_user, :res_resmsgto_user, :remsg_create_time, :remsg_delete_flag)");

      $sMessageid = $_POST['message_sn'];
      $sContent = $_POST['response_content'];
      $sCreateUser = $_POST['remsg_create_user'];
      $sDele = $_POST['remsg_delete_flag'];
      $sTime = date("Y-m-d H:i:s");
      $sResMsgto_userid = $_POST['res_resmsgto_userid'];

      $oSQLUplaodReponse->bindValue(':message_sn', $sMessageid, PDO::PARAM_STR);
      $oSQLUplaodReponse->bindValue(':response_content', strip_tags($sContent), PDO::PARAM_STR);
      $oSQLUplaodReponse->bindValue(':remsg_create_time', $sTime, PDO::PARAM_STR);
      $oSQLUplaodReponse->bindValue(':remsg_create_user', $sCreateUser, PDO::PARAM_STR);
      $oSQLUplaodReponse->bindValue(':res_resmsgto_user', $sResMsgto_userid, PDO::PARAM_STR);
      $oSQLUplaodReponse->bindValue(':remsg_delete_flag', $sDele, PDO::PARAM_STR);

      if ($oSQLUplaodReponse->execute()) {
        $vRtn['STATUS'] = 'SUCCESS';
        $vRtn['TIME'] = $sTime;
        $vRtn['CREATEUSER'] = id2uname($sCreateUser);
        $vRtn['CONTENT'] = $sContent;
      }
      break;

    // 刪除
    case 'DEL':
      $sMessageID = $_POST['message_sn'];
      $oSQLDeleteReponse = $dbh->prepare("UPDATE message_response SET remsg_delete_flag = '1' WHERE response_sn = :response_sn");
      $oSQLDeleteReponse->bindValue(':response_sn', $sMessageID, PDO::PARAM_STR);
      $oSQLDeleteReponse->execute();

      $oSQLCheckMessage = $dbh->prepare("SELECT remsg_delete_flag FROM message_response WHERE response_sn = :response_sn");
      $oSQLCheckMessage->bindValue(':response_sn', $sMessageID, PDO::PARAM_STR);
      $oSQLCheckMessage->execute();
      $vMessage = $oSQLCheckMessage->fetch();

      if ('1' != $vMessage['remsg_delete_flag']) {
        $vRtn['STATUS'] = 'ERR';
        $vRtn['MSG'] = '訊息刪除失敗: MD_MSG_URESx062 '. $sMessageID;
      }
      else {
        $vRtn['STATUS'] = 'SUCCESS';
        $vRtn['MESSAGEID'] = $sMessageID;
      }
      break;
  }

  echo json_encode($vRtn);
