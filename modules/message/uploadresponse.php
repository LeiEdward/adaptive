<?php
  require_once('../../include/config.php');
  require_once('../../include/adp_API.php');

  if (!isset($_SESSION)) {
  	session_start();
  }

  $oSQLUplaodMessage = $dbh->prepare("INSERT INTO message_response
     (response_sn, message_sn, response_content, create_user, create_time)
     VALUES (NULL, :message_sn, :response_content, :create_user, :create_time");

  $sToUserid = $_POST['touser_id'];
  $sContent = $_POST['msg_content'];
  $sCreateUser = $_POST['create_user'];
  $sAttached = $_POST['attachefile'];
  $sRead_mk = $_POST['read_mk'];
  $sDele = $_POST['delete_falg'];

  $oSQLUplaodMessage->bindValue(':touser_id', $sToUserid, PDO::PARAM_STR);
  $oSQLUplaodMessage->bindValue(':msg_type', '0', PDO::PARAM_STR);
  $oSQLUplaodMessage->bindValue(':msg_content', strip_tags($sContent), PDO::PARAM_STR);
  $oSQLUplaodMessage->bindValue(':create_time', date("Y-m-d H:i:s"), PDO::PARAM_STR);
  $oSQLUplaodMessage->bindValue(':create_user', $sCreateUser, PDO::PARAM_STR);
  $oSQLUplaodMessage->bindValue(':attachefile', $sAttached, PDO::PARAM_STR);
  $oSQLUplaodMessage->bindValue(':read_mk', $sRead_mk, PDO::PARAM_STR);
  $oSQLUplaodMessage->bindValue(':delete_falg', $sDele, PDO::PARAM_STR);
  $oSQLUplaodMessage->execute();

  echo json_encode($_POST);
