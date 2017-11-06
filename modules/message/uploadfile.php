<?php
require_once('../../include/config.php');
require_once('../../include/adp_API.php');

if (move_uploaded_file($_FILES['import_file']['tmp_name'], '../../data/message/'.$_FILES['import_file']['name'])) {
  $sCont = file_get_contents('../../data/message/'.$_FILES['import_file']['name']);
}
else {
  echo 'NO';
}
