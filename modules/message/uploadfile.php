<?php
  $vRtn = array();
  if (!isset($_FILES['import_file']['tmp_name'])) {
    $vRtn['STATUS'] = 'ERR';
    $vRtn['MSG'] = '錯誤代碼: MD_MSG_UFx008';
    echo json_encode($vRtn);
  }

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
      $vRtn['STATUS'] = 'OK';
      break;

    default:
      $vRtn['STATUS'] = 'ERR';
      $vRtn['MSG'] = '錯誤代碼: MD_MSG_UFx031';

      echo json_encode($vRtn);
      break;
  }
  if ('ERR' === $vRtn['STATUS']) return;

  if (move_uploaded_file($_FILES['import_file']['tmp_name'], '../../data/message/'.$_FILES['import_file']['name'])) {
    $vRtn['STATUS'] = 'SUCCESS';
  }
  else {
    $vRtn['STATUS'] = 'ERR';
    $vRtn['MSG'] = '錯誤代碼: MD_MSG_UFx041';
  }
  // print_r($vRtn);
  echo json_encode($vRtn);
