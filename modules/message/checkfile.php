<?php
  require_once('../../include/config.php');
  require_once('../../include/adp_API.php');

  if (!isset($_SESSION)) {
  	session_start();
  }

  $vRtn = array();

  // 取得 user 資料
  $vUserData = get_object_vars($_SESSION['user_data']);
  $sUserID = $vUserData['user_id'];

  $vRtn['STATUS'] = 'SUCCESS';

  switch($_FILES['import_file']['type']) {
    case 'application/vnd.openxmlformats-officedocument.presentationml.presentation': //pptx
    case 'application/vnd.ms-powerpoint': // ppt
      $sFileType = 'office_powerpoint';
      break;
    case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document': // docx
    case 'application/msword': // doc
      $sFileType = 'office_word';
      break;
    case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': // xlsx
    case 'application/vnd.ms-excel': // xls
      $sFileType = 'office_excel';
      break;
    case 'application/pdf'; //pdf
      $sFileType = 'PDF';
      break;
    case 'text/plain': // text
      $sFileType = 'TEXT';
      break;
    case 'image/jpeg':
    case 'image/png':
    case 'image/gif':
    case 'image/bmp':
      $sFileType = 'image';
      break;

    default:
      $vRtn['STATUS'] = 'ERR';
      $vRtn['MSG'] = '檔案格式錯誤。錯誤代碼: MD_MSG_UFx041';

      echo json_encode($vRtn);
      exit();
      break;
  }

  $sTimeSatmp = microtime(true);
  $sFileName = $_SESSION['user_id'].'_'.str_replace('.', '_', $sTimeSatmp).'_'.$_FILES['import_file']['name'];
  $sFileSrc = './data/message/'.$sUserID.'/'.$sFileName;

  // !!
  $_SESSION['message']['uploadfile'][] = array('name' => $sFileName,
                                               'orgnialname' => $_FILES['import_file']['name']);

  if ('SUCCESS' === $vRtn['STATUS'] && move_uploaded_file($_FILES['import_file']['tmp_name'], '../../tmp/'.$sFileName)) {
    $oSQLUploadfile = $dbh->prepare("INSERT INTO message_fileattached
       (file_sn, message_sn, file_orgnialname, file_replacename, filesrc, filetype, filesize, upload_userid, upload_time) VALUES (NULL, NULL, :file_orgnialname, :file_replacename, :filesrc, :filetype, :filesize, :upload_userid, :upload_time)");
  	$oSQLUploadfile->bindValue(':file_orgnialname', $_FILES['import_file']['name'], PDO::PARAM_STR);
    $oSQLUploadfile->bindValue(':file_replacename', $sFileName, PDO::PARAM_STR);
    $oSQLUploadfile->bindValue(':filesrc', $sFileSrc, PDO::PARAM_STR);
  	$oSQLUploadfile->bindValue(':filetype', $sFileType, PDO::PARAM_STR);
  	$oSQLUploadfile->bindValue(':filesize', round(($_FILES['import_file']['size']/FILESIZE_MB), 2), PDO::PARAM_STR);
    $oSQLUploadfile->bindValue(':upload_userid', $sUserID, PDO::PARAM_STR);
    $oSQLUploadfile->bindValue(':upload_time', date("Y-m-d H:i:s"), PDO::PARAM_STR);
  	$oSQLUploadfile->execute();
    $vRtn['fileid'] = $sFileName;
  }
  else {
    $vRtn['STATUS'] = 'ERR';
    $vRtn['MSG'] = '請重新上傳，錯誤代碼: MD_MSG_UFx071';
  }

  echo json_encode($vRtn);
