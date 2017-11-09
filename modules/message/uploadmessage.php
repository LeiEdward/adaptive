<?php
  require_once('../../include/config.php');
  require_once('../../include/adp_API.php');

  if (!isset($_SESSION)) {
  	session_start();
  }

  $oSQLUplaodMessage = $dbh->prepare("INSERT INTO message_master
     (msg_sn, touser_id, msg_type, msg_content, create_time, create_user, attachefile, read_mk, delete_flag)
     VALUES (NULL, :touser_id, :msg_type, :msg_content, :create_time, :create_user, :attachefile, :read_mk, :delete_flag)");

  $sToUserid = $_POST['touser_id'];
  $sContent = $_POST['msg_content'];
  $sCreateUser = $_POST['create_user'];
  $sAttached = $_POST['attachefile'];
  $sRead_mk = $_POST['read_mk'];
  $sDele = $_POST['delete_flag'];

  $oSQLUplaodMessage->bindValue(':touser_id', $sToUserid, PDO::PARAM_STR);
  $oSQLUplaodMessage->bindValue(':msg_type', '2', PDO::PARAM_STR); // msg_type:2 親師
  $oSQLUplaodMessage->bindValue(':msg_content', strip_tags($sContent), PDO::PARAM_STR);
  $oSQLUplaodMessage->bindValue(':create_time', date("Y-m-d H:i:s"), PDO::PARAM_STR);
  $oSQLUplaodMessage->bindValue(':create_user', $sCreateUser, PDO::PARAM_STR);
  $oSQLUplaodMessage->bindValue(':attachefile', $sAttached, PDO::PARAM_STR);
  $oSQLUplaodMessage->bindValue(':read_mk', $sRead_mk, PDO::PARAM_STR);
  $oSQLUplaodMessage->bindValue(':delete_flag', $sDele, PDO::PARAM_STR);
  $oSQLUplaodMessage->execute();


  echo json_encode($_POST);
