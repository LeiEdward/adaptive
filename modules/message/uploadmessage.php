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

  // 上傳檔案
  $vRtn = array();
  $vRtn['STATUS'] = 'SUCCESS';
  if (1 <= $sAttached) {
    $sDeleteFile = $_POST['deletefile'];

    // 取得現在 message_master 流水號
    $oSQLMessageInx = $dbh->prepare("SELECT auto_increment FROM information_schema.tables
      WHERE table_schema = 'kbnattest'
      AND table_name = 'message_master'");
    $oSQLMessageInx->execute();
    $vIndex = $oSQLMessageInx->fetch();
    $sIndex = $vIndex['auto_increment']-1;

    $sFilePath = '../../data/message/'.$_SESSION['user_id'];

    if (isset($_SESSION['message']['uploadfile'])) {
      if (!is_dir($sFilePath)) {
        mkdir($sFilePath, 0777);
        chmod($sFilePath, 0777);
      }

      foreach ($_SESSION['message']['uploadfile'] as $vFile) {
        if (false !== strrpos($sDeleteFile, $vFile['name'])) continue;

        if (file_exists('../../tmp/'.$vFile['name'])) {
          if (rename('../../tmp/'.$vFile['name'], $sFilePath.'/'.$vFile['name'])) {
            $vTmpFile[] = $vFile['name'];
          }
          else {
            $vRtn['STATUS'] = 'ERR';
            $vRtn['MSG'] = '檔案遺失請重新上傳，錯誤代碼: MD_MSG_UMx058';
          }
        }
      }
    }
    else {
      $vRtn['STATUS'] = 'ERR';
      $vRtn['MSG'] = '檔案上傳異常，錯誤代碼: MD_MSG_UMx065';
    }

    if (!empty($vTmpFile)) {
      $sFile = implode("','", $vTmpFile);
      $oSQLUploadfile = $dbh->prepare("UPDATE message_fileattached
         SET message_sn = $sIndex
         WHERE file_replacename IN('$sFile')");
      $oSQLUploadfile->execute();
    }
  }

  !!
  $_SESSION['message']['uploadfile'] = null;
  unset($_SESSION['message']['uploadfile']);

  echo json_encode($vRtn);
