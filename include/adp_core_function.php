<?php
//adp_core_function.php  系統共用函數

//編號->使用者等級
function id2UserLevel($id) {
	global $dbh;
	$sql = "select access_level from user_status where user_id = '$id'";
	$data = $dbh->query($sql);
	$row = $data->fetch();
	return $row['access_level'];
}


//-- 編號->出版商
function id2publisher($id) {   //編號->出版商
	global $dbh;
	$sql = "select publisher from publisher where publisher_id = '$id'";
	//$data =& $dbh2->getOne($sql);
	$data = $dbh->query($sql);
	$row = $data->fetch();
	//echo "<br>".$row['publisher']."<br>";
	return $row['publisher'];
}

//-- 編號->科目名稱
function id2subject($id) {
	global $dbh;
	$sql = "select name from subject where subject_id = '$id'";
	//$data =& $dbh2->getOne($sql);
	$data = $dbh->query($sql);
	$row = $data->fetch();
	//echo "<br>".$row['name']."<br>";
	return $row['name'];
}

//-- 編號->城市名稱
function id2city($id) {
	global $dbh;
	$sql = "select city_name from city where city_code = '$id'";
	//$data = $dbh2->getOne($sql);
	$data = $dbh->prepare($sql);
	$data->bindParam( ":city_name", $city_name );
	$data->execute();
	$row = $data->fetch();
	//echo "<br>".$row[0]."<br>";
	return $row['city_name'];
}

//-- 編號->機關(就讀學校)名稱
function id2org($org_id) {
	global $dbh;
	$sql = "select name from organization where organization_id = '$org_id'";
	//$data =& $dbh->getOne($sql);
	$data = $dbh->query($sql);
	$row = $data->fetch();
	//echo "<br>".$row['name']."<br>";
	return $row['name'];
}

//-- 編號->補習班(施測地點)名稱
function id2firm($firm_id) {
	//global $dbh;
	//$sql = "select name from firm where firm_id = '$firm_id'";
	//$data =& $dbh->getOne($sql);
	$data = '台中教育大學';
	//echo "<br>".$data."<br>";
	return $data;
}

//-- 編號->都市名+學校名稱
function id2CityOrg($org_id) {
	global $dbh;
	$sql = "select name, city_code from organization where organization_id = '$org_id'";
	$data = $dbh->query($sql);
	$row = $data->fetch();
	//print_r($row);
	//$city=id2city($row['city_code']);
	$city=$row['city_code'];
	return $city.'-'.$row['name'];
}

//-- 編號->都市名+補習班名稱
function id2CityFirm($firm_id) {
	global $dbh;
	$sql = "select name, city_code from firm where firm_id = '$firm_id'";
	$data = $dbh->query($sql);
	$row = $data->fetchRow();
	$city=id2city($row['city_code']);
	return $city.'-'.$row['name'];
}

//-- 帳號->存取等級
function id2level($id) {
	global $dbh;
	$sql = "select access_title from user_access WHERE access_level='$id'";
	//$data =& $dbh->getOne($sql);
	$data = $dbh->prepare($sql);
	$data->bindParam( ":access_title", $access_title );
	$data->execute();
	$row = $data->fetch();
	//echo "<br>".$row[0]."<br>";
	return $row['access_title'];
}

//-- 帳號->姓名
function id2uname($id) {
	global $dbh;
	$sql = "select uname from user_info WHERE user_id='$id'";
	//$data =& $dbh->getOne($sql);
	$data = $dbh->query($sql);
	$row = $data->fetch();
	//echo '<br>'.$row['uname'].'<br>';
	return $row['uname'];
}

//-- 帳號->單元概念名稱
function id2csname($id) {
	global $dbh;
	$sql = "select concept from concept_info WHERE cs_id='$id'";
	//$data =& $dbh->getOne($sql);
	$data = $dbh->query($sql);
	$row = $data->fetch();
	//echo "<br>".$row['concept']."<br>";
	return $row['concept'];
}

//-- 帳號->施測類型
function id2ExamType($id) {
	global $dbh;
	$sql = "select type from exam_type WHERE type_id='$id'";
	//$data =& $dbh->getOne($sql);
	$data = $dbh->query($sql);
	$row = $data->fetch();
	return $row['type'];
}

function id2ItemEduPara($id) {
	global $dbh;
	$sql = "select edu_parameter from exam_edu_parameter WHERE sn='$id' limit 0,1";
	//$data =& $dbh->getOne($sql);
	$data = $dbh->query($sql);
	$row = $data->fetch();
	return $row['edu_parameter'];
}

//-- 組合cs_id
function get_csid($pid, $sid, $vid, $uid) {
	$cs_id=sprintf("%03d%02d%02d%02d",$pid, $sid, $vid, $uid);
	return $cs_id;
}

//-- 組合ep_id
function get_epid($pid, $sid, $vid, $uid, $paper_vol) {
	$cs_id=sprintf("%03d%02d%02d%02d%02d",$pid, $sid, $vid, $uid, $paper_vol);
	return $cs_id;
}

function explode_cs_id($cs_id) {
	$data[0]=intval(substr($cs_id, 0, 3));   //123碼為publisher_id(版本)
	$data[1]=intval(substr($cs_id, 3, 2));   //45碼為subject_id(科目)
	$data[2]=intval(substr($cs_id, 5, 2));   //67碼為vol(冊別)
	$data[3]=intval(substr($cs_id, 7, 2));   //89碼為unit(單元)
	return $data;
}

function explode_ep_id($ep_id) {
	$data[0]=substr($ep_id, 0, 3);   //1,2,3碼為publisher_id(版本)
	$data[1]=substr($ep_id, 3, 2);   //4,5碼為subject_id(科目)
	$data[2]=substr($ep_id, 5, 2);   //6,7碼為vol(冊別)
	$data[3]=substr($ep_id, 7, 2);   //8,9碼為unit(單元)
	$data[4]=substr($ep_id, 9, 2);   //10,11碼為paper_vol(卷別)
	return $data;
}

function CSid2FullName($cs_id) {
	global $dbh;
	if(strpos($cs_id, _SPLIT_SYMBOL) == TRUE){  //cs_id中有 _SPLIT_SYMBOL
		$special_cs_id=$cs_id;
		list($q_cs_ida, $q_cs_unit) = split(_SPLIT_SYMBOL, $special_cs_id);
		$cs_id=$q_cs_ida.sprintf("%02d", $q_cs_unit);
		$my_csname="全";
	}
	$sql = "select publisher_id,subject_id,vol,unit,grade,concept,indicator_relation,indicator_item,indicator_threshold,indicator_item_nums, indicator_item_relation from concept_info WHERE cs_id='$cs_id' limit 0,1";
/*
	$data =& $dbh->getOne($sql);

	if ($data!==null) {
		$is_poly=1;
	}
*/

	$result = $dbh->query($sql);
	//echo $sql;
	$data = $result->fetch();
	if (!(in_array("", $data))) {
		$is_poly=1;
	}


	$cs_info=explode_cs_id($cs_id);
	$my_p=id2publisher($data[publisher_id]).' ';
	$my_s=id2subject($data[subject_id]);
	if($is_poly==1){
		if(intval($data[vol])%2==1){
			$tmp_vol="Ａ";
		}else{
			$tmp_vol="Ｂ";
		}
		$my_v=num2chinese(ceil(intval($data[vol])/2))."年級".$tmp_vol;
		$my_u="";
	}else{
		$my_v="第".intval($cs_info[2])."冊";
		$my_u="第".intval($cs_info[3])."單元";
	}
	if(!isset($my_csname)){
		$my_csname=$data[concept];
	}
	$cs_title=$my_p.$my_s.$my_v.$my_u.'【'.$my_csname.'】';

	return $cs_title;
}

function EPid2FullName($ep_id) {
	$cs_id=EPid2CSid($ep_id);
	$cs_title=CSid2FullName($cs_id);

	$ep_info=explode_ep_id($ep_id);
	/*
	$my_p=id2publisher(intval($ep_info[0])).' ';
	$my_s=id2subject(intval($ep_info[1]));
	$my_v="第".intval($ep_info[2])."冊";
	$my_u="第".intval($ep_info[3])."單元";
	$my_csname=id2csname(EPid2CSid($ep_id));
	*/
	$cs_title=$cs_title.'－卷'.$ep_info[4];
	return $cs_title;
}

function getChtExamTitle($cs_id, $paper_vol){

	$my_csid=explode_cs_id($cs_id);
	$my_p=id2publisher(intval($my_csid[0])).' ';
	$my_s=id2subject(intval($my_csid[1]));
	$my_v="第".intval($my_csid[2])."冊";
	$my_u="第".intval($my_csid[3])."單元";
	$my_csname=id2csname($cs_id);
	if($my_csid[0]=="006"){  //多點記分，能力指標
      $vid=intval($my_csid[2]);
   	$tmp_vol="Ｂ";
		if($vid%2==1){
			$tmp_vol="Ａ";
		}
		$my_v=num2chinese(ceil($vid/2))."年級".$tmp_vol;
   }
   if(strpos($cs_id, _SPLIT_SYMBOL) == TRUE){
      $cs_title=$my_p.$my_s.$my_v.'【全】';
   }else{
      $cs_title=$my_p.$my_s.$my_v.$my_u.'【'.$my_csname.'】';
   }
   if($paper_vol!=0){
      $cs_title.='-卷'.$paper_vol;
   }

   return $cs_title;
}

/*
function CSid2IndicatorFullName($cs_id) {
	$cs_info=explode_cs_id($cs_id);
	$my_p=id2publisher(intval($cs_info[0])).' ';
	$my_s=id2subject(intval($cs_info[1]));
	$my_v="第".intval($cs_info[2])."冊";
	$my_u="第".intval($cs_info[3])."單元";
	$my_csname=id2csname($cs_id);
	$my_csname="全";
	$cs_title=$my_p.$my_s.$my_v.$my_u.'【'.$my_csname.'】';
	return $cs_title;
}

function EPid2IndicatorFullName($ep_id) {
	$ep_info=explode_ep_id($ep_id);
	$my_p=id2publisher(intval($ep_info[0])).' ';
	$my_s=id2subject(intval($ep_info[1]));
	$my_v="第".intval($ep_info[2])."冊";
	$my_u="第".intval($ep_info[3])."單元";

	$my_v=num2chinese(ceil(intval($my_csid[2])/2))."年級";
	$my_u="";
	if(strpos($row[cs_id], _SPLIT_SYMBOL) == TRUE){  //cs_id中有 _SPLIT_SYMBOL
		$my_csname="全";
	}
	$cs_title=$my_p.$my_s.$my_v.$my_u.'【'.$my_csname.'】-卷'.$ep_info[4];
	$cs_title=$my_p.$my_s.$my_v.$my_u.'【'.$my_csname.'】-卷'.$row['paper_vol'];
	return $cs_title;
}
*/

function EPid2CSid($ep_id) {
	$data=substr($ep_id, 0, 9);   //前9碼為cs_id
	return $data;
}

//-- 學校機關->城市編號
function org2citycode($id) {
	//global $dbh;
	//$sql = "select city_code from organization where organization_id = '$id'";
	//$data =& $dbh->getOne($sql);
	//舊寫法
	$data=intval(substr($id, 0, 2));   //前2碼為city_code
	return $data;
}

//-- 阿拉伯數字->國字數字
function num2chinese($id) {

	$ary=array('O','一','二','三','四','五','六','七','八','九','十','十一','十二','十三','十四','十五','十六','十七','十八','十九','二十','二十一','二十二','二十三','二十四','二十五',);
	$data=$ary[$id];

	return $data;
}

//數字轉字母
function num2English( $id ){
	$ary=array( '0','A','B','C','D','E','F','G','H','I','J','K','L','M','N' );
	$data=$ary[$id];

	return $data;
}

//-- 冊別->年級學期
function vol2grade($subject,$id) {
	if($subject=='自然')    $id+=4;
	$sch=ceil(intval($id)/2);
	$sem=intval($id)%2;
	if($sem==1) $sem="上學期";
	else  $sem="下學期";
	if($subject=='數學' || $subject=='國語' || $subject=='自然')
		$sch=num2chinese($sch).'年級';

	return $sch.$sem;
}

//-- 逐字轉換utf8字串為big5
function  utf8_2_big5($utf8_str)  {
	$i=0;
	$len  =  strlen($utf8_str);
	$big5_str="";
	for  ($i=0;$i<$len;$i++)  {
		$sbit  =  ord(substr($utf8_str,$i,1));
		if  ($sbit  <  128)  {
			$big5_str.=substr($utf8_str,$i,1);
		}  else  if($sbit  >  191  &&  $sbit  <  224)  {
			$new_word=iconv("UTF-8","Big5",substr($utf8_str,$i,2));
			$big5_str.=($new_word=="")?"■":$new_word;
			$i++;
		}  else  if($sbit  >  223  &&  $sbit  <  240)  {
			$new_word=iconv("UTF-8","Big5",substr($utf8_str,$i,3));
			$big5_str.=($new_word=="")?"■":$new_word;
			$i+=2;
		}  else  if($sbit  >  239  &&  $sbit  <  248)  {
			$new_word=iconv("UTF-8","Big5",substr($utf8_str,$i,4));
			$big5_str.=($new_word=="")?"■":$new_word;
			$i+=3;
		}
	}
	return  $big5_str;
}

//-- 強制檔案下載
function force_download ($data, $name, $mimetype='', $filesize=false) {
    // File size not set?
    if ($filesize == false OR !is_numeric($filesize)) {
        $filesize = strlen($data);
    }

    // Mimetype not set?
    if (empty($mimetype)) {
        $mimetype = 'application/octet-stream';
    }

    // Make sure there's not anything else left
    ob_clean_all();

    // Start sending headers
    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false); // required for certain browsers
    header("Content-Transfer-Encoding: binary");
    header("Content-Type: " . $mimetype);
    header("Content-Length: " . $filesize);
    header("Content-Disposition: attachment; filename=\"" . $name . "\";" );

    // Send data
    echo $data;
    die();
}

function ob_clean_all () {
    $ob_active = ob_get_length () !== false;
    while($ob_active) {
        ob_end_clean();
        $ob_active = ob_get_length () !== false;
    }

    return true;
}

function exam_clean_all () {
	unset($_SESSION['cs_id']);
	unset($_SESSION['Student_Data']);
	unset($_SESSION['Concept_Data']);
	unset($_SESSION['paper_vol']);
	unset($_SESSION['start_time']);
	unset($_SESSION['date']);
	unset($_SESSION["selected_item"]);
	unset($_SESSION["rec_user_answer"]);
	unset($_SESSION["selected_item_rec"]);
	unset($_SESSION['exam_type']);
	unset($_SESSION['done_nums']);
	unset($_SESSION["Test_Model"]);
	unset($_SESSION['auth_ep']);
	unset($_SESSION['chk_guess']);
	unset($_SESSION['correct_item_num']);
	unset($_SESSION['guess_item']);
	unset($_SESSION['response2stage']);
}

//-- 錯誤回報
function error_report2db($user_id) {
	global $dbh;

	$msg_time = date("Y-m-d H:i");
	$cs_info=explode_cs_id($_SESSION['cs_id']);
	$my_p=id2publisher($cs_info[0]);
	$my_s=id2subject($cs_info[1]);
	$my_v=$cs_info[2];
	$my_u=$cs_info[3];
	$item_title=$my_p.$my_s.'-第'.$my_v.'冊第'.$my_u.'單元-卷'.$_SESSION['paper_vol'].'-第'.$_SESSION["selected_item"].'題';
	$subject="問題回報-".$user_id."-".$item_title;
	$msg_text=$item_title.'有問題，請檢查，謝謝！';

	$sql = "select user_id from user_status WHERE access_level>80";
	$result =$dbh->query($sql);
	while ($row=$result->fetchRow()){
		$query = 'INSERT INTO priv_msgs (msg_type_id, subject, from_userid, to_userid, msg_time, msg_text, read_msg) VALUES (?,?,?,?,?,?,?)';
		$data = array(1, $subject, $user_id, $row[user_id], $msg_time, $msg_text, 0);

		$result1 =$dbh->query($query, $data);
		if (PEAR::isError($result1)) {
			echo "錯誤訊息：".$result1->getMessage()."<br>";
			echo "錯誤碼：".$result1->getCode()."<br>";
			echo "除錯訊息：".$result1->getDebugInfo()."<br>";
			die();
		}else{
			$status="問題回報成功！";
		}
	}

	return $status;
}

function chk_file_exist($exec_file, $exec_line){
	$wait_sec=3;
	if(!file_exists($exec_file) || !is_file($exec_file)){
		// echo "錯誤發生，請重新執行！<br><br>";
		// debug_msg("exec_file", $exec_file);
		// echo "<br>line: $exec_line <br>";
		// //sleep($wait_sec);
		// echo '<a href="modules.php?op=main">按此返回</a>';
		debugBai( __LINE__, __FILE__, ['錯誤發生，請重新執行！',$exec_file,$exec_line] );
		FEETER();
		//die();
	}
}

function get_image_mime($filename)
{
    global $img_define;

    if(preg_match('/\.([^\.]+)$/', $filename, $matches)) {
        if($mime = $img_define["image_mime"][strtolower($matches[1])]) {
            return $mime;
        }
    }
    return;
}

function str2compiler($str){
	$str=str_replace('1','~',$str);
	//$str=str_replace('2','@',$str);  //好像有問題
	//$str=str_replace('3','-',$str);
	$str=str_replace('3',':',$str);
	$str=str_replace('5','/',$str);
	//$str=str_replace('6','_',$str);
	//$str=str_replace('7','.',$str);
	$str=str_replace('7','*',$str);
	$data=strrev($str);

	return $data;
}

function compiler2str($str){
	$str=str_replace('~','1',$str);
	//$str=str_replace('@','2',$str);
	//$str=str_replace('-','3',$str);
	$str=str_replace(':','3',$str);
	$str=str_replace('/','5',$str);
	//$str=str_replace('_','6',$str);
	$str=str_replace('*','7',$str);
	$data=strrev($str);

	return $data;
}

function pass2compiler($pass){
	$newpass=str_split($pass, 3);
	$fin="";
	for($i=0;$i<count($newpass);$i++){
		$newpa[$i]=strrev($newpass[$i]);
		$fin.=$newpa[$i];
	}
	return $fin;
}

function creat_csv($filename, $header, $content, $big5=1){
	$csv_file_loc=_ADP_TMP_UPLOAD_PATH.$filename;
	if ($fp = fopen($csv_file_loc, "w+")) { //表示第一筆作答反應，先印出標頭
		$astr="";
		$header.="\r\n";
		if($big5==1){
			$astr=utf8_2_big5($header);
		}else{
			$astr=$header;
		}
		fwrite($fp, $astr);
		$ii=count($content);
		for($i=0;$i<$ii;$i++){
			$astr="";
			$content[$i].="\r\n";
			if($big5==1){
				$astr=utf8_2_big5($content[$i]);
			}else{
				$astr=$content[$i];
			}
			//debug_msg("第".__LINE__."行 astr ", $astr);
			fwrite($fp, $astr);
		}
		fclose($fp);  //關閉檔案
	}

}

function debug_msg($title, $showarry){
	echo "<pre>";
	echo "<br>$title<br>";
	print_r($showarry);
}

function modify_pic_pix($ini, $set_val){
		$PImgProp=GetImageSize($ini);
		$pic_pix[0]=intval($PImgProp[0]);  //圖片寬度
		$mini=1;
		while($pic_pix[0]>$set_val){
			$mini=$mini-0.01;
			$pic_pix[0]=ceil(intval($PImgProp[0])*$mini);
		}
		$pic_pix[1]=ceil(intval($PImgProp[1])*$mini);  //輸出圖片高度

		return $pic_pix;
}

function read_csv($import_data_path, $BIG2UTF){
	$j=0;
	$fp = fopen($import_data_path, "r");
	while ( $ROW = fgetcsv($fp, $prop['size']) ) {  // 在資料列有內容時（長度大於 0），才做以下動作
		if ( strlen($ROW[0]) && $j!=0 && $ROW[0]!='') { //第一筆為抬頭，不檢查，ROW[0]：姓名不為空值，ROW[1]：密碼不為空值
			//debug_msg("第".__LINE__."行 ROW ", $ROW);
			for($i=1;$i<sizeof($ROW);$i++){
				if($BIG2UTF=="yes"){
					$ROW[$i]=iconv("BIG5", "UTF-8", $ROW[$i]);   //轉成utf-8
				}
				$Matrix[$j][$i] = trim($ROW[$i]); //去除空白
			}
		}
		$j++;
	}
	fclose($fp);
	return $Matrix;
}

function getCSlicense($user_id){
	global $dbh;
   //改成確認等級  access_level
	$sql = "select access_level from user_status WHERE user_id='".$user_id."' LIMIT 0,1";
	$result =$dbh->query($sql);
	while ($row=$result->fetch(PDO::FETCH_ASSOC)){
         $user_access_level=$row['access_level'];
	}
   if($user_access_level>90 ){
      $sql = "select * from publisher";
      $result =$dbh->query($sql);
      while ($row=$result->fetch(PDO::FETCH_ASSOC)){
         $publish[]=$row[publisher_id];
      }

      $sql = "select * from subject";
      $result =$dbh->query($sql);
      while ($row=$result->fetch(PDO::FETCH_ASSOC)){
         $subject[]=$row[subject_id];
      }
	  $ii=count($publish);
	  $jj=count($subject);
      for($i=0;$i<$ii;$i++){
         for($j=0;$j<$jj;$j++){
            $OpenedCS[]=sprintf("%03d%02d", $publish[$i], $subject[$j]);
         }
      }

   }else{
      $sql = "select subject from subject_acnt WHERE acnt='".$user_id."' ORDER BY subject";
   }
   //echo "<br>".$sql."<br>";
   $result = $dbh->query($sql);
   while ($data = $result->fetch(PDO::FETCH_ASSOC)){
      $OpenedCS[]=$data[subject];
   }

   return $OpenedCS;
}


function getTestedCSlicense($user_id){
	global $dbh;

	//找出所有已測驗過的單元cs_id
	$sql = "select distinct(cs_id) from exam_record WHERE user_id='".$user_id."' ORDER BY cs_id";
	//debug_msg("第".__LINE__."行 sql ", $sql);
	$result = $dbh->query($sql);
	while ($data = $result->fetchRow()) {
		$OpenedCS[]=$data[cs_id];
	}


	return $OpenedCS;
}



function getEPlicense($u_data){
   global $dbh, $user_data;

   $user_id=$u_data->user_id;
   if($user_id=="admin" || $user_data->access_level==91){
      $sql = "select distinct exam_paper_id from concept_item ORDER BY exam_paper_id ASC";
   }else{
      if($u_data->access_level<20){
         die("您無查閱權限");
      }
      $sql = "select exam_paper_id from exam_paper_access WHERE school_id='".$u_data->organization_id."'AND grade='".$u_data->grade."'AND class='".$u_data->class_name."' ORDER BY exam_paper_id ASC";
   }
   $result = $dbh->query($sql);
   while ($data = $result->fetchRow()) {
      $OpenedEP[]=$data[exam_paper_id];
   }

   return $OpenedEP;
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function getNowSemeYear(){
	$today = date("Y-n-j");
	$myday=explode('-',$today);
	$SemeYear=$myday[0]-1911;
	if($myday[1]<=7){
		$SemeYear=$SemeYear-1;
	}

	return $SemeYear;
}

function getYearSeme(){
	$today = date("Y-n-j");
	$myday=explode('-',$today);
	$SemeYear=$myday[0]-1911;
	if($myday[1]<=7){//還在上學年度
		$SemeYear=$SemeYear-1;
		if($myday[1]<=1){ //上學期
			$SemeYear=$SemeYear.'1';
		}else{ //下學期
			$SemeYear=$SemeYear.'2';
		}
	}else{
		$SemeYear=$SemeYear.'1';
	}

	return $SemeYear;
}


function getNumSenType(){
	global $dbh, $user_data;
	$sql = "select distinct(cs_name) from flash_concept_info ORDER BY cs_id ASC";

	$result = $dbh->query($sql);
	while ($data = $result->fetchRow()) {
		$NumSenType[]=$data[cs_name];
	}

	return $NumSenType;
}

function get_user_id($ep_id,$item_num) {   //取學生帳號
	global $dbh;
	$sql = "select user_id from mad_exam_record where exam_title = '$ep_id' AND item_num = '$item_num'";
	$result = $dbh->query($sql);
	while ($data = $result->fetchRow()) {
		$myuser[]=$data[user_id];
	}

	return $myuser;
}

function get_mad_res($ep_id,$item_num, $user_id) {
	global $dbh;
	$sql = "select exam_sn, org_res, oth_ans from mad_exam_record where exam_title = '$ep_id' AND item_num = '$item_num' AND user_id = '$user_id'";
	$result = $dbh->query($sql);
	while ($data = $result->fetchRow()) {
		$my_exam_sn=$data[exam_sn];
		$my_org_res=$data[org_res];
		$my_oth_ans=$data[oth_ans];
	}

	return array($my_exam_sn, $my_org_res, $my_oth_ans);
}

//概念sn轉名稱
function sn2name( $nodes ){
  global $dbh;

  //撈出節點的名稱
  $sql_bNodes_name='
    SELECT indicate_name
    FROM map_node
    WHERE indicate_id="'.$nodes.'"
  ';
  $re_bNodes_name=$dbh->query($sql_bNodes_name);
  $data_bNodes_name=$re_bNodes_name->fetch();

  return $data_bNodes_name[indicate_name];
}
//概念sn轉 類型(bNodes / sNodes)
function sn2class( $nodes ){
	global $dbh;

	//撈出節點的class
	$sql_bNodes_class='
    SELECT class as Nodeclass
    FROM map_node
    WHERE indicate_id="'.$nodes.'"
  ';
	$re_bNodes=$dbh->query($sql_bNodes_class);
	$data_mapNode_info=$re_bNodes->fetch();

	return $data_mapNode_info[Nodeclass];
}

function chkNodeFileExist( $node, $bNode, $subject ){
	global $dbh,$user_data;

	$map_sn=subject2mapSN($subject);
	//debugBAI( __LINE__,__FILE__, $map_sn,'print_r	' );
	//為了不讓顯示錯誤，目前先將學生版本搜尋移除
	//AND b.publisher_id = "'.$user_data->publisher_id[$subject].'"
	// foreach( $map_sn as $val ){
	//
	// 	$sql='
	// 		SELECT a.CR, a.DA, a.video, a.prac, a.teach, b.mapping_sn
	// 		FROM map_node a, publisher_mapping b
	// 		WHERE a.map_sn="'.$val.'"
	// 		AND a.indicate_id LIKE "%'.$node.'%"
	// 		AND b.indicator = "'.$bNode.'"
	//
	// 		AND b.subject_id = "'.$subject.'"
	// 	';
	// 	$re=$dbh->query($sql);
	// 	$tmp=$re->fetch();
	// 	//debugBAI( __LINE__,__FILE__, [$tmp,$sql],'print_r' );
	// 	if( is_array($tmp) ) $data = $tmp;
	// }
	//為了高中數學做的修正，以後一定要刪除
	if( $map_sn==9 ) $subject=2;
	$sql='
		SELECT a.CR, a.DA, a.video, a.prac, a.teach, b.mapping_sn
		FROM map_node a, publisher_mapping b
		WHERE a.map_sn="'.$map_sn.'"
		AND a.indicate_id LIKE "%'.$node.'%"
		AND b.indicator = "'.$bNode.'"

		AND b.subject_id = "'.$subject.'"
	';
	//debugBAI( __LINE__,__FILE__,$sql,'print_r' );
	$re=$dbh->query($sql);
	$data=$re->fetch();

	//debugBAI( __LINE__, __FILE__, $sql, 'print_r' );
	//if( !is_array($data) ) die( __FILE__.'  '.__LINE__ );

	return $data;
}

function debugBai( $line, $file, $data='', $type='console.log'  ){


  if( $data=='' ) return;
  switch( $type ){
    case 'echo':
      $info=array(
        'line'=>$line,
        'file'=>$file,
      );
      echo('<br><pre>');
      print_r($info);
      if( is_array($data) ) echo('type error, data is array');
      else echo('<br>'.$data);
    break;
    case 'print_r':
			echo('<pre>');
      print_r([ $line, $file, $data]);
    break;
    case 'var_dump':
      var_dump([ $line, $file, $data]);
    break;
    case 'console.log':
      echo('<script> console.log( '.json_encode([$line, $file, $data]).' ); </script>');
    break;
    default:
      echo('<script> console.log( '.json_encode([$line, $file, $data]).' ); </script>');
    break;
  }
}

//系統帳號由學校代號及學生學號所組成，故由此函數取得學生登入的學號(帳號)
function getStuSn($user_id){
	//尋找是否有'-'
	$TryStrpos=strpos($user_id,'-');
	if($TryStrpos !== false){
		$mysn=explode('-',$user_id);
		$user_id=$mysn[1];
	}
	return $user_id;
}

//取得已開通學校的下拉式選單
function getSchoolHTML(){
	global $dbh;

	$schoolHTML='<select name="org_id">';
	$sql = "select city_code, organization_id, name from organization where used='1' order by CAST(CONVERT(`city_code` USING big5) AS BINARY), CAST(CONVERT(`name` USING big5) AS BINARY)";
	//debug_msg("第".__LINE__."行 sql ", $sql);
	$result =$dbh->query($sql);
	while ($row=$result->fetch(PDO::FETCH_ASSOC)){
		//debug_msg("第".__LINE__."行 row ", $row);
		$schoolHTML.='<option value="'.$row['organization_id'].'">'.$row['city_code'].'-'.$row['name'].'</option>';
	}
	$schoolHTML.='</select>';

	return $schoolHTML;
}

//隨機取得字串
function getRandString($pw_length ='6'){
	$randpwd = '';
	for ($i = 0; $i < $pw_length; $i++)	{
		//只取大小寫的英文字母
		$rand_num= mt_rand(65, 122);
		while($rand_num>=91 and $rand_num<=96){
			$rand_num= mt_rand(65, 122);
		}
		$randpwd .= chr($rand_num);
	}
	return $randpwd;
}

//subject & map_sn
function subject2mapSN( $subject ){
  global $dbh;

  $sql_mapSN='
    SELECT map_sn
    FROM map_info
    WHERE subject_id="'.$subject.'"
    AND display="1"
  ';
  $re_mapSN=$dbh->query($sql_mapSN);
	$data_mapSN=$re_mapSN->fetch();
	$map_sn=$data_mapSN[map_sn];
  // while($data_mapSN=$re_mapSN->fetch()){
	// 	$map_sn[]=$data_mapSN[map_sn];
	// }


  return $map_sn;
}

function mapSN2subject( $map_sn ){
  global $dbh;

  $sql_subject='
    SELECT subject_id
    FROM map_info
    WHERE map_sn="'.$map_sn.'"
    AND display="1"
  ';
  $re_subject=$dbh->query( $sql_subject );
  $data_subject=$re_subject->fetch();
  $subject=$data_subject[subject_id];

  return $subject;
}

function sNode2bNode( $sNode, $subject ){
	//1是國文；2是數學；3是自然
	if( $subject==1 ){
		$tmp = explode( '-', $sNode );
		//if( $tmp[2]<10 ) $tmp[2]=str_replace('0','',$tmp[2]);
		if(count($tmp)>3) $year = (int)$tmp[3];
		else $year = (int)$tmp[1]*2;
		$bNode = $tmp[0].'-'.$tmp[1].'-'.$tmp[2];
		//die( ['test',$sNode,$bNode] );
	}elseif( $subject==2 ){
		$tmp = explode( '-', $sNode );
		$year = $tmp[0];
		$bNode = $tmp[0].'-'.$tmp[1].'-'.$tmp[2];
	}elseif( $subject==3 ){
		$tmp = explode( '-', $sNode );
		//if( strlen($tmp[1])!=2 ) echo( __FILE__.' '.$sNode.' '.__LINE__ );
		//echo( $tmp[1].' '.strlen($tmp[1]) );
		//$tmp2 = explode( '', $tmp[1] );
		//$year = $tmp2[0];
		$temp = substr( $tmp[1],0,1 );
		if($temp == '1') $year ='2';
		if($temp == '2') $year ='4';
		if($temp == '3') $year ='6';
		if($temp == '4') $year ='9';
		$bNode = $tmp[0].'-'.$tmp[1];
	}elseif( $subject==10 ){
		$tmp = explode( '-', $sNode );
		$year = $tmp[0];
		$bNode = $tmp[0].'-'.$tmp[1].'-'.$tmp[2];
	}else{
		$year=0;
		$bNode=$subject.'沒有在規則內。';
	}

	return [$year,$bNode];
}

function updateStuExam(){
	global $dbh;

	$sql='
		SELECT *
		FROM exam_record
	';
	$re=$dbh->query($sql);
	while( $data=$re->fetch() ){
		//if( $data[bNodeStatus]!=null ) continue;
		$exam_sn=$data[exam_sn];
		$exam_title = $data[exam_title];
		$cs_id=$data[cs_id];
		$tmp=explode_cs_id( $cs_id );
		$subject=$tmp[1];

		$question = explode(_SPLIT_SYMBOL, $data[questions]);
		$binary_res = explode(_SPLIT_SYMBOL, $data[binary_res]);
		//debugBAI( __LINE__,__FILE__,$binary_res,'print_r');
		foreach( $question as $key=>$itemNum ){
			if( $itemNum==null ) continue;
			$itemToF = $binary_res[($itemNum-1)];
			$sql_item2indicate = '
	      SELECT indicator
	      FROM concept_item
	      WHERE exam_paper_id="'.$exam_title.'"
	      AND item_num="'.$itemNum.'"
	    ';
	    $re_item2indicate=$dbh->query( $sql_item2indicate );
	    $data = $re_item2indicate->fetch();
	    if( is_array($data[indicator]) ) die('該概念下，1題不只一個概念');
	    $item2indicate = $data[indicator];
			//echo( $sql_item2indicate );
			//debugBAI( __LINE__,__FILE__,$itemToF,'print_r');
			if( $itemToF==1 OR $itemToF==2 OR $itemToF==4 ){
				$sNodeTrueItem[ $item2indicate ] += 1;
			}elseif( $itemToF==0 OR $itemToF==3 ){
				$sNodeFalseItem[ $item2indicate ] += 1;
			}
			$sNodeAllItem[ $item2indicate ]+=1;

			if( $sNodeTrueItem[ $item2indicate ]==null ) $sNodeTrueItem[ $item2indicate ]=0;
			if( $sNodeFalseItem[ $item2indicate ]==null ) $sNodeFalseItem[ $item2indicate ]=0;
			if( $sNodeAllItem[$item2indicate]==null ) die( 'the indicate no item.' );
		}

		if( count($sNodeTrueItem)!=count($sNodeAllItem) OR count($sNodeFalseItem)!=count($sNodeAllItem) )
			die('小節點概念數不一樣。');
		//debugBAI( __LINE__,__FILE__,[$sNodeTrueItem,$sNodeAllItem],'print_r');
		foreach( $sNodeAllItem as $indicate=>$allItemNum ){
			$trueNum = $sNodeTrueItem[$indicate];
			$allNum = $sNodeAllItem[$indicate];
			//紀錄該試卷下有多少大節點 [0]=year;[1]=bNode
			$bNode = sNode2bNode( $indicate,$subject );

			if( ($trueNum/$allNum)>0.8 ){
				$sNodeStatus[$indicate] = $indicate.':1';
				//大捷點下小節點通過個數
				$bNodePass[$bNode[1] ]+=1;
			}else{
				$sNodeStatus[$indicate] = $indicate.':0';
				//大捷點下小節點未通過個數
				$bNodeNoPass[$bNode[1]]+=1;
			}

			//大捷點下小節點個數，只算該卷
			$bNodeAll[$bNode[1]]+=1;

			if( $bNodePass[$bNode[1]]==null ) $bNodePass[$bNode[1]]=0;
			if( $bNodeNoPass[$bNode[1]]==null ) $bNodeNoPass[$bNode[1]]=0;
		}
		if( count($bNodePass)!=count($bNodeAll) OR count($bNodeNoPass)!=count($bNodeAll) )
			die('大節點概念數不一樣。');
		//大節點再該概念下的通過情況

		foreach( $bNodeAll as $sNode=>$allSnodeNum ){
			$passNum = $bNodePass[$sNode];
			if( ($passNum/$allSnodeNum)>0.8 ) $bNodeStatus[$sNode]=$sNode.':1';
			else $bNodeStatus[$sNode]=$sNode.':0';
		}
		if( !is_array($sNodeStatus) ){
			debugBAI( __LINE__,__FILE__, $sNodeStatus );
			$sNodeStatus=[1];
		}
		if( !is_array($bNodeStatus) ){
			debugBAI( __LINE__,__FILE__, $bNodeStatus );
			$bNodeStatus=[1];
		}

		//debugBAI( __LINE__,__FILE__, [$bNodeStatus,$sNodeStatus], 'print_r' );
		$sql_up='
			UPDATE exam_record
			SET bNodeStatus="'.implode( _SPLIT_SYMBOL, $bNodeStatus ).'",
					sNodeStatus="'.implode( _SPLIT_SYMBOL, $sNodeStatus ).'"
			WHERE exam_sn="'.$exam_sn.'"
		';
		$data_up = $dbh->query($sql_up);
	  $ChkRec = $data_up->fetch();
		unset($bNodeStatus);
		unset($sNodeStatus);
		unset($bNodeAll);
		unset($bNodePass);
		unset($bNodeNoPass);
		unset($sNodeTrueItem);
		unset($sNodeFalseItem);
		unset($sNodeAllItem);
		//debugBAI( __LINE__,__FILE__, [$sql_up,$data_up,$ChkRec], 'print_r' );
		//die();
	}
	echo('end.');
}

function echo_memory_usage() {
    //$mem_usage = memory_get_usage(true);
    $mem_usage =memory_get_peak_usage(true);
    if ($mem_usage < 1024){
        $mem_usage = $mem_usage." bytes";
    }elseif($mem_usage < 1048576){
        $mem_usage = round($mem_usage/1024,3)." KB";
    }else{
        $mem_usage = round($mem_usage/1048576,3)." MB";
    }

    return $mem_usage;
}

function csid2path($cs_id){
	$ex = explode_cs_id($cs_id);
	$path= implode( '/',$ex );

	return $path;
}

//加密
function string2secret($str){
	global $dbh, $module_name, $SubmitFile, $SiteFile, $user_data;

	$rand_seed1=rand(5,17);
	$randpwd1 = '';
	for ($i = 0; $i < $rand_seed1; $i++)	{
		$randpwd1 .= chr(mt_rand(33, 126));
	}
	$rand_seed2=rand(5,17);
	$randpwd2 = '';
	for ($i = 0; $i < $rand_seed2; $i++)	{
		$randpwd2 .= chr(mt_rand(33, 126));
	}
	$str_compiler=pass2compiler($str);
	$str_base64=base64_encode($str_compiler);
	$compiler_ary=preg_split('//', $str_base64, -1, PREG_SPLIT_NO_EMPTY);
	$ii=count($compiler_ary);
	$str_base64_compi='';
	for($i=0;$i<$ii;$i++){
		$str_base64_compi.=$compiler_ary[$i].chr(mt_rand(33, 126));
	}
	$split_str=sprintf("%02d",$rand_seed1).$randpwd2.$str_base64_compi.$randpwd1.sprintf("%02d",$rand_seed2);
	$secret=base64_encode($split_str);
	$secret=pass2compiler($secret);
	$secret=base64_encode($secret);

    return ($secret);
}

//解密
function secret2string($secret){
	global $dbh, $module_name, $SubmitFile, $SiteFile, $user_data;

	//debug_msg("第".__LINE__."行 _REQUEST ", $_REQUEST);

	$str=base64_decode($secret);
	$str=pass2compiler($str);
	$str=base64_decode($str);
	$randpwd1=intval(substr($str, 0, 2));
	$randpwd2=intval(substr($str, -2));
	$compiler_ary=preg_split('//', $str, -1, PREG_SPLIT_NO_EMPTY);
	$ii=count($compiler_ary);
	for($i=0;$i<$ii;$i++){
		if($i<$randpwd2+2 || $i>=($ii-$randpwd1-2)){
			unset($compiler_ary[$i]);
		}
	}
	$str='';
	$i=0;
	foreach($compiler_ary as $key=>$val){
		if($i%2==0){
			$str.=$val;
		}
		$i++;
	}
	$str=base64_decode($str);
	$str=pass2compiler($str);

    return $str;
}

/*
*function：檢測字串是否由純英文，純中文，中英文混合組成
*param string
*return 1:純英文;2:純中文;3:中英文混合
*/
function check_str($str=''){
	if(trim($str)==''){
		return '';
	}
	$m=mb_strlen($str,'utf-8');
	$s=strlen($str);
	if($s==$m){
		return 1;
	}
	if($s%$m==0 && $s%3==0){
		return 2;
	}
	return 3;
}

function valid_password($str, $len_max, $len_min)
{
	$plen=strlen($str);
	if ($plen<$len_min ) return '密碼輸入太短，請重新設定';
	if ($plen>$len_max) return '密碼輸入太長，請重新設定';
    if(preg_match("/^(?=.*\d)(?=.*[a-z]).{4,30}$/", $str))
        return 1;
    else
        return '不合法的密碼，請重新設定';
}

function user_id2org($user_id){
  global $dbh;

  $sql='SELECT organization_id FROM user_info WHERE user_id=:user_id';
  $result=$dbh->prepare($sql);
  $result->bindParam(':user_id', $user_id, PDO::PARAM_STR);
  $result->execute();
  while ($row=$result->fetch(PDO::FETCH_ASSOC)){
    $org_id=$row['organization_id'];
  }
  return $org_id;
}

//學期
function Semester_id2FullName($semester){
  $name=substr($semester,0,3).'學年度第'.substr($semester,-1).'學期';
  return $name;
}

// 將陣列整理成JSON字串, 傳給JS用
function arraytoJS($vData) {
	$sJSOject = array();
	if(!empty($vData) && is_array($vData)) {
		 $sJSOject = json_encode($vData);
	}
	return $sJSOject;
}
