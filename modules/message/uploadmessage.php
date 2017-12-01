<?php
  require_once('../../include/config.php');
  require_once('../../include/adp_API.php');

  if (!isset($_SESSION)) {
  	session_start();
  }
  $sUpload = $_POST['upload'];

  $vRtn = array();
  $vRtn['STATUS'] = 'SUCCESS';
  switch($sUpload) {
    // 新增
    case 'INS':
      $sToUserid = base64_decode($_POST['touser_id']);
      $sContent = strip_tags($_POST['msg_content']);
      $sCreateUser = $_POST['create_user'];
      $sAttached = $_POST['attachefile'];
      $sRead_mk = $_POST['read_mk'];
      $sDele = $_POST['delete_flag'];

      $oSQLUplaodMessage = $dbh->prepare("INSERT INTO message_master
         (msg_sn, touser_id, togroup, msg_type, msg_content, create_time, create_user, attachefile, read_mk, delete_flag)
         VALUES (NULL, :touser_id, :togroup, :msg_type, :msg_content, :create_time, :create_user, :attachefile, :read_mk, :delete_flag)");

     // 判斷留言對象是單一還是群發
     if (false !== strpos($sToUserid, '>>>ALL')) {
        $sToUserid = str_replace('>>>ALL', '', $sToUserid);
        $oSQLUplaodMessage->bindValue(':touser_id', '', PDO::PARAM_STR);
        $oSQLUplaodMessage->bindValue(':togroup', $sToUserid, PDO::PARAM_STR);
      }
      else {
        $oSQLUplaodMessage->bindValue(':touser_id', $sToUserid, PDO::PARAM_STR);
        $oSQLUplaodMessage->bindValue(':togroup', '', PDO::PARAM_STR);
      }
      $oSQLUplaodMessage->bindValue(':msg_type', '2', PDO::PARAM_STR); // msg_type:2 親師
      $oSQLUplaodMessage->bindValue(':msg_content', $sContent, PDO::PARAM_STR);
      $oSQLUplaodMessage->bindValue(':create_time', date("Y-m-d H:i:s"), PDO::PARAM_STR);
      $oSQLUplaodMessage->bindValue(':create_user', $sCreateUser, PDO::PARAM_STR);
      $oSQLUplaodMessage->bindValue(':attachefile', $sAttached, PDO::PARAM_STR);
      $oSQLUplaodMessage->bindValue(':read_mk', $sRead_mk, PDO::PARAM_STR);
      $oSQLUplaodMessage->bindValue(':delete_flag', $sDele, PDO::PARAM_STR);
      $oSQLUplaodMessage->execute();

      // 上傳檔案
      if (1 <= $sAttached) {
        $sDeleteFile = $_POST['deletefile'];

        // 取得現在 message_master 流水號
        $oSQLMessageInx = $dbh->prepare("SELECT auto_increment FROM information_schema.tables
          WHERE table_schema = '$db_dbn'
          AND table_name = 'message_master'");
        $oSQLMessageInx->execute();
        $vIndex = $oSQLMessageInx->fetch();
        $sIndex = $vIndex['auto_increment'] - 1;

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
                $vRtn['MSG'] = '檔案遺失請重新上傳，錯誤代碼: MD_MSG_UMx065';
              }
            }
          }
        }
        else {
          $vRtn['STATUS'] = 'ERR';
          $vRtn['MSG'] = '檔案上傳異常，錯誤代碼: MD_MSG_UMx081';
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
      break;

    case 'MOD':
      $sMessageID = $_POST['messageid'];
      $oSQLDeleteMessage = $dbh->prepare("UPDATE message_master SET read_mk = '1' WHERE msg_sn = :msg_sn");
      $oSQLDeleteMessage->bindValue(':msg_sn', $sMessageID, PDO::PARAM_STR);
      $oSQLDeleteMessage->execute();

      $oSQLCheckMessage = $dbh->prepare("SELECT read_mk FROM message_master WHERE msg_sn = :msg_sn");
      $oSQLCheckMessage->bindValue(':msg_sn', $sMessageID, PDO::PARAM_STR);
      $oSQLCheckMessage->execute();
      $vMessage = $oSQLCheckMessage->fetch();
      if ('1' != $vMessage['read_mk']) {
        $vRtn['STATUS'] = 'ERR';
        $vRtn['MSG'] = '訊息標記失敗: MD_MSG_UMx0110';
      }
      $vRtn['LOG'] = 'mod';
      break;

    // 刪除
    case 'DEL':
      $sMessageID = $_POST['messageid'];
      $oSQLDeleteMessage = $dbh->prepare("UPDATE message_master SET delete_flag = '1' WHERE msg_sn = :msg_sn");
      $oSQLDeleteMessage->bindValue(':msg_sn', $sMessageID, PDO::PARAM_STR);
      $oSQLDeleteMessage->execute();

      $oSQLCheckMessage = $dbh->prepare("SELECT delete_flag FROM message_master WHERE msg_sn = :msg_sn");
      $oSQLCheckMessage->bindValue(':msg_sn', $sMessageID, PDO::PARAM_STR);
      $oSQLCheckMessage->execute();
      $vMessage = $oSQLCheckMessage->fetch();
      if ('1' != $vMessage['delete_flag']) {
        $vRtn['STATUS'] = 'ERR';
        $vRtn['MSG'] = '訊息刪除失敗: MD_MSG_UMx0127';
      }
      break;
  }

  echo json_encode($vRtn);
