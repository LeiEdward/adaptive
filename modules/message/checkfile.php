<?php
  require_once('../../include/config.php');
  require_once('../../include/adp_API.php');

  if (!isset($_SESSION)) {
  	session_start();
  }

  $vRtn = array();
  $sFileType = $_FILES['import_file']['type'];
  switch($sFileType) {
    case 'application/vnd.openxmlformats-officedocument.presentationml.presentation': //pptx
    case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document': // docx
    case 'application/vnd.openxmlformats-officedocument.presentationml.presentation': // xlsx
    case 'application/vnd.ms-powerpoint': // ppt
    case 'application/vnd.ms-excel': // xls
    case 'application/msword': // doc
    case 'application/pdf'; //pdf
    case 'text/plain': // text
    case 'image/jpeg':
    case 'image/png':
    case 'image/gif':
    case 'image/bmp':
      $vRtn['STATUS'] = 'SUCCESS';
      break;

    default:
      $vRtn['STATUS'] = 'ERR';
      $vRtn['MSG'] = '錯誤代碼: MD_MSG_UFx024';
      break;
  }

  $sTimeSatmp = microtime(true);
  $sFileName = $_SESSION['user_id'].'_'.str_replace('.', '_', $sTimeSatmp).'_'.$_FILES['import_file']['name'];
  $_SESSION['message']['uploadfile'][] = $sFileName;
  if ('SUCCESS' === $vRtn['STATUS'] && move_uploaded_file($_FILES['import_file']['tmp_name'], '../../tmp/'.$sFileName)) {
  }
  else {
    $vRtn['STATUS'] = 'ERR';
    $vRtn['MSG'] = '請重新上傳，錯誤代碼: MD_MSG_UFx040';
  }

  echo json_encode($vRtn);
