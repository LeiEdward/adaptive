<?php
include_once "include/config.php";
include_once 'include/adp_API.php';
include_once 'classes/PHPExcel.php';
include_once 'classes/PHPExcel/IOFactory.php';

if (!isset($_SESSION)) {
	session_start();
}else {
	$_SESSION["NotimportData"]='';
	$_SESSION["FailImport"]='';
	$_SESSION["SuccessImport"]='';
	$_SESSION["NotExistData"]='';
}
$seme = getYearSeme();
$seme_year=getNowSemeYear();

?>
<script>
function chk(){
	var file = $("#stuFile").val();
	var file2 = $("#stuFile2").val();

	if( file ==''){
		if(file2 == '') {
			alert("在 匯入學生與個案名單 中，請至少選擇一個檔案，謝謝！");
			return false;
		}else return true;
	}else return true;
}

function chk2(){
	var file = $("#stuFile3").val();

	if( file ==''){
		alert("在 匯入測驗報告統計表 中，請選擇檔案，謝謝！");
		return false;
	}else return true;
}
</script>
<?php
if (phpversion() > "4.0.6") {
	$HTTP_POST_FILES = &$_FILES;
}
define("MAX_FILE_SIZE",51200000);
define("DESTINATION_FILE_FOLDER", _ADP_TMP_UPLOAD_PATH);
$_accepted_FILE_extensions_ = "xls,xlsx";

if(strlen($_accepted_FILE_extensions_) > 0){
	$_accepted_FILE_extensions_ = @explode(",",$_accepted_FILE_extensions_);
} else {
	$_accepted_FILE_extensions_ = array();
}

if(empty($_POST["org"])){
	$org = $_SESSION[user_data]->organization_id;
}else $org = $_POST["org"];

if($_SESSION[user_data]->access_level>=50){
	$sql_school='SELECT o.organization_id, o.type, o.name, c.city_code, c.city_name, a.post_code, a.area
              FROM organization AS o , city AS c , city_area AS a
              WHERE substr(o.address,2,3)=a.post_code AND a.city=c.city_name AND o.used=1
              GROUP BY o.organization_id';
}else{
	$sql_school='SELECT o.organization_id, o.type, o.name, c.city_code, c.city_name, a.post_code, a.area
              FROM organization AS o , city AS c , city_area AS a
              WHERE substr(o.address,2,3)=a.post_code AND a.city=c.city_name AND o.used=1
				AND o.organization_id =:org_id';
}

$result_school=$dbh->prepare($sql_school);
$result_school->bindValue(':org_id', $org, PDO::PARAM_STR);
$result_school->execute();
$city=array();
$orglist=array();
$stu_list = array();
$stu_record = array();
$ind = array();
$pri = array();
$status1 = array();
$status2 = array();
$status3 = array();
$total = array();

function getUserID($priori_name){
	global  $school_user;
	$end = false;
	foreach ($school_user as $k=>$kv){
		//print_r($kv);
		if($kv["priori_name"] !='' && $kv["priori_name"] == $priori_name){
			return $kv["user_id"];
		}
	}
}

function checkImportData($import_data){
	global  $thisrange_data;
	$end = '';
	foreach ($thisrange_data as $k=>$kv){
		if($kv["exam_user"] !='' && $kv["exam_user"] == $import_data){
			$end = 'exist';
			return $end;
		}
	}
}

function make_excel($title, $resultData, $resultData2, $resultData3){
	$date = date("Ymd_His");
	$excel_content[0]=$title[0];
	$excel_content[1]=$title[1];
	$excel_content[2]=$title[2];
	$ii=0; $jj=0; $kk=0;
	foreach ($resultData as $key=>$value){
		$ii++;
		if(is_array($value)) {
			$tmpvlue =array();
			array_push($tmpvlue, $ii);
			foreach ($value as $ikey=> $ivalue){
				array_push($tmpvlue, $ivalue);
			}
			$excel_content[$key+3] = $tmpvlue;
		}else $excel_content[$key+3] = [$ii,$value]; //array()
	}
	$excel_content2[0]=$title[0];
	$excel_content2[1]=$title[1];
	$excel_content2[2]=$title[2];
	foreach ($resultData2 as $key=>$value){
		$jj++;
		if(is_array($value)) {
			$tmpvlue =array();
			array_push($tmpvlue, $jj);
			foreach ($value as $jkey=> $jvalue){
				array_push($tmpvlue, $jvalue);
			}
			$excel_content2[$key+3] = $tmpvlue;
		}else $excel_content2[$key+3] = [$jj,$value]; //array()
	}
	
	//$excel_content3 = array();
	$excel_content3[0]=$title[0];
	$excel_content3[1]=$title[1];
	$excel_content3[2]=$title[2];
	
	foreach ($resultData3 as $key=>$value){
		$kk++;
		if(is_array($value)) {
			$tmpvlue =array();
			array_push($tmpvlue, $kk);
			foreach ($value as $kkey=> $kvalue){
				array_push($tmpvlue, $kvalue);
			}
			$excel_content3[$key+3] = $tmpvlue;
		}else $excel_content3[$key+3] = [$kk,$value]; //array()
	}
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->createSheet();
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->setTitle('成功明細列表');
	$objPHPExcel->getActiveSheet()->fromArray($excel_content, null, 'A1', true);
	
	if(!empty($excel_content2)){
		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setTitle('失敗明細列表');
		$objPHPExcel->getActiveSheet()->fromArray($excel_content2, null, 'A1');
	}
	
	if(!empty($excel_content3)){
		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(2);
		$objPHPExcel->getActiveSheet()->setTitle('資料不存在明細列表');
		$objPHPExcel->getActiveSheet()->fromArray($excel_content3, null, 'A1');
	}
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$filename= substr($_SESSION['user_id'], 0, 6).$date.'_import.xlsx';
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save(_ADP_PATH.'data/tmp/'.$filename);
	return $filename;
}

function make_excel2($title, $resultData){
	$date = date("Ymd_His");
	$excel_content[0]=$title[0];
	$excel_content[1]=$title[1];
	$ii=0; 
	foreach ($resultData as $key=>$value){
		$ii++;
		if(is_array($value)) {
			$tmpvlue =array();
			array_push($tmpvlue, $ii);
			foreach ($value as $ikey=> $ivalue){
				array_push($tmpvlue, $ivalue);
			}
			$excel_content[$key+2] = $tmpvlue;
		}else $excel_content[$key+2] = [$ii,$value]; //array()
	}
	
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->createSheet();
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->setTitle('學生資料列表');
	$objPHPExcel->getActiveSheet()->fromArray($excel_content, null, 'A1', true);

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$filename= substr($_SESSION['user_id'], 0, 6).$date.'_import.xlsx';
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save(_ADP_PATH.'data/tmp/'.$filename);
	return $filename;
}

function uploadFileCopy($upload_file){
	global $_accepted_FILE_extensions_;
	
	if(!empty($upload_file)){
		if(is_uploaded_file($upload_file['tmp_name']) && $upload_file['error'] == 0){
			$_file_ = $upload_file;
			$errStr = "";
			$_name_ = $_file_['name'];
			$_type_ = $_file_['type'];
			$_tmp_name_ = $_file_['tmp_name'];
			$_size_ = $_file_['size'];
			header ('Content-type: text/html; charset=utf-8');//指定編碼
			if($_size_ > MAX_FILE_SIZE && MAX_FILE_SIZE > 0){
				$errStr = "File too heavy";
				echo '<script language="javascript">alert(\"超過限制檔案大小\");</script>';//跳出錯誤訊息
			}
			$_ext_ = explode(".", $_name_);
			$_ext_ = strtolower($_ext_[count($_ext_)-1]);
	
			$file_name_title=$_file_['name'];
	
			if(!in_array($_ext_, $_accepted_FILE_extensions_) && count($_accepted_FILE_extensions_) > 0){
				$errStr = "Invalid extension";
				echo "<script>javascript:alert(\"請檢查檔案格式\");</script>";//跳出錯誤訊息
			}
			if(!is_dir(DESTINATION_FILE_FOLDER) && is_writeable(DESTINATION_FILE_FOLDER)){
				$errStr = "Destination folder is invalid";
				echo "<script>javascript:alert(\"必須指定資料夾目錄\");</script>";//跳出錯誤訊息
			}
			if(empty($errStr)){
				$newFilename = $_SESSION["user_id"].'-'.date("YmdHis.").$_ext_;//變數$newname取得新檔案名，供寫入資料庫
				if(@copy($_tmp_name_,DESTINATION_FILE_FOLDER . "/" . $newFilename)){ //修改自動重新命名
						
					//header("Location: " . no_error);
				} else {
					echo "<script>javascript:alert(".$errStr.");</script>";//回上一頁history.back()
					exit;                                  //停止後續程式碼的繼續執行
					//header("Location: " . yes_error);
				}
			} else {
				echo "<script>javascript:alert(".$errStr.");</script>";//回上一頁history.back()
				exit;	                               //停止後續程式碼的繼續執行
				//header("Location: " . yes_error);
			}
		}
		return $newFilename;
	}else{
		return 'empty HTTP_POST_FILES';
	}
}

while ($row_school = $result_school->fetch(\PDO::FETCH_ASSOC)){

	if(!array_key_exists($row_school['city_code'],$city)){
		$city[$row_school['city_code']]=$row_school['city_name'];
	}
	if(!array_key_exists($row_school['organization_id'],$orglist)){
		$orglist[$row_school['organization_id']]=$row_school['name'];
	}
}	 

if (isset($_POST['btnOK']) || isset($_POST["btnOK3"])) {
	
	$arrupload_file = array();
	$showmesg = array();
	$showtitle = array();
	$return_file = array();
	if(isset($_POST['btnOK']) && !empty($HTTP_POST_FILES['stuFile'])){
		if($HTTP_POST_FILES['stuFile']['name'] !=''){
			array_push($arrupload_file, $HTTP_POST_FILES['stuFile']);
		}
	}
	
	if(isset($_POST['btnOK']) && !empty($HTTP_POST_FILES['stuFile2'])){
		if($HTTP_POST_FILES['stuFile2']['name'] !=''){
			array_push($arrupload_file, $HTTP_POST_FILES['stuFile2']);
		}
	}
	
	if(isset($_POST['btnOK3']) && !empty($HTTP_POST_FILES['stuFile3'])){
		array_push($arrupload_file, $HTTP_POST_FILES['stuFile3']);
	}
	
	if(!empty($arrupload_file)){
		foreach ($arrupload_file as $akey=>$avlue){
			//echo 'file name:'.$avlue['name'];
			$upload_file = $avlue;
			$tmp_filename = uploadFileCopy($upload_file);
			
			$file= _ADP_TMP_UPLOAD_PATH.$tmp_filename;
			//echo $file;
			if(is_file($file)){
				try {  //下載 檔案
					$objPHPExcel = PHPExcel_IOFactory::load($file);
				} catch(Exception $e) {
					die('Error loading file "'.pathinfo($file,PATHINFO_BASENAME).'": '.$e->getMessage());
				}
				$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);  //撈取資料
				$sumSheet = count($sheetData);
			}
			$sumSuccess = 0; $arrSuccess = array();
			$sumFail = 0; $arrFail = array();
			$sumexist = 0; $arrExist = array(); $sunNotexist =0;
			$sumdouble = 0; $arrDouble = array();
			
			foreach($sheetData as $key => $col){
				$arr="";
				//讀EXL 以行
				if(isset($_POST["btnOK3"]) && $key == 1){
					$import_title =$col["A"];
					$tmp = explode('-', $import_title);
					$tmp2 = explode(' ', trim($tmp[1]));//201705 數學
					$import_range = trim($tmp2[0]);
					$subject_nm = trim($tmp2[1]);
					
					if($subject_nm =='國語文') $subject_nm ='國語';
						
					$sql_sub = "SELECT subject_id FROM `subject` where name =:name and display=1";
					$result = $dbh->prepare($sql_sub);
					$result->bindValue(':name', $subject_nm, PDO::PARAM_STR);
					$result->execute();
					$rowData = $result->fetch(PDO::FETCH_ASSOC);
					$sub_id = $rowData["subject_id"];
						
					$sql_con = "SELECT cp_id FROM `concept_priori` WHERE subject_id =:sub_id AND exam_range =:range ORDER by cp_sn DESC";
					$result2 = $dbh->prepare($sql_con);
					$result2->bindValue(':sub_id', $sub_id, PDO::PARAM_STR);
					//$result2->bindValue(':org_id', $_POST["org"], PDO::PARAM_STR);
					$result2->bindValue(':range', $import_range, PDO::PARAM_STR);
					$result2->execute();
					$rowData2 = $result2->fetch(PDO::FETCH_ASSOC);
					$last_cp_id = $rowData2["cp_id"];
					$tmp3 = substr($last_cp_id, 8, strlen($last_cp_id));
					$seqence = (int)$tmp3 +1;
						
					$cp_id = sprintf("%02d%06d%04d",$sub_id,$import_range,$seqence);
					//echo "cp_id: $cp_id";
					$time = date ("Y-m-d H:i:s");
				}
			
				if($key != 1){
						
					if (isset($_POST['btnOK'])){
			
						$pid_len = strlen($col["C"]);
						$mark_pid = str_repeat('*',$pid_len - 5);
						$new_priori_name = $mark_pid.substr($col["C"], -5).$col["B"];
						//echo "Pid: $col[C], Name: $col[B], new: $new_priori_name <br>";
							
						//查詢資料是否存在
						$query =  $dbh->prepare("SELECT * FROM user_info WHERE uname =:uname AND grade =:grade AND organization_id =:org_id"); /* AND class =:class*/
						$query->bindValue(':uname', $col["B"], PDO::PARAM_STR);
						$query->bindValue(':grade', $col["E"], PDO::PARAM_STR);
						//$query->bindValue(':class', $col["F"], PDO::PARAM_STR);
						$query->bindValue(':org_id', $_POST["org"], PDO::PARAM_STR);
						$query->execute();
						$row = $query->fetchAll(\PDO::FETCH_ASSOC);
						$num = count($row);
						$time = date ("Y-m-d H:i:s");
							
						if($num >0){
							//更新 user_info.
							$sql = 'UPDATE user_info SET priori_name = :priori_name, update_date = :update_date
							WHERE uname =:uname AND grade =:grade AND organization_id =:org_id';  //AND class =:class
							$update2 = $dbh->prepare($sql);
							$update2->bindValue(':priori_name', $new_priori_name, PDO::PARAM_STR);
							$update2->bindValue(':update_date', $time, PDO::PARAM_STR);
							$update2->bindValue(':uname', $col["B"], PDO::PARAM_STR);
							$update2->bindValue(':grade', $col["E"], PDO::PARAM_STR);
							//$update2->bindValue(':class', $col["F"], PDO::PARAM_STR);
							$update2->bindValue(':org_id', $_POST["org"], PDO::PARAM_STR);
							$update2->execute();
			
							if ($update2->errorCode()!='00000'){
								$sumFail++; //列出失敗的資料至 excel 中
								$_SESSION["FailImport"][$sumFail] = $col["B"];
								array_push($arrFail, $col);
								$msg = debug_msg("第".__LINE__."行 update2->errorInfo() ", $update2->errorInfo());
							}else {
								$msg='';
								$sumSuccess++; //列出成功的資料至 excel 中
								$_SESSION["SuccessImport"][$sumSuccess] = $col["B"];
								array_push($arrSuccess, $col);
							}
						}else{
							$sumexist++; //列出不存在的資料至 excel 中
							$_SESSION["NotExistData"][$sumexist] = $col["B"];
							array_push($arrExist, $col);
						}
						
					}
						
					if(isset($_POST["btnOK3"]) && $key == 2){
						$ii=0;
						foreach ($col as $colkey => $colvalue) {
							$ii++;
							if($ii >7){
								$stu_list[$colkey]["exam_user"] = $col[$colkey];
							}
						}
						
						//檢查是否已經有匯入的資料存在…
						$sql_con3="SELECT a.cp_id, subject_id, organization_id, exam_range, version, b.exam_user, b.user_id 
									FROM concept_priori a, exam_record_priori b WHERE a.cp_id = b.cp_id
									AND subject_id =:sub_id AND organization_id =:org_id AND exam_range =:range"; // AND exam_user =:exam_user 
						$result5 = $dbh->prepare($sql_con3);
						//$result5->bindValue(':exam_user', $sub_id, PDO::PARAM_STR);
						$result5->bindValue(':sub_id', $sub_id, PDO::PARAM_STR);
						$result5->bindValue(':org_id', $_POST["org"], PDO::PARAM_STR);
						$result5->bindValue(':range', $import_range, PDO::PARAM_STR);
						$result5->execute();
						$thisrange_data = $result5->fetchAll(\PDO::FETCH_ASSOC);
						
						//查詢系統對應的user_id
						$query =  $dbh->prepare("SELECT uid, user_id, priori_name FROM user_info WHERE organization_id =:org_id");
						$query->bindValue(':org_id', $_POST["org"], PDO::PARAM_STR);
						$query->execute();
						$school_user = $query->fetchAll(PDO::FETCH_ASSOC);
						
						foreach ($stu_list as $listkey=> $listvalue){
							$exist_status = checkImportData($listvalue["exam_user"]);
							//echo 'stu list:'.$listvalue["exam_user"].'--'.$exist_status.',';
							$stu_list[$listkey]["exist"] = $exist_status;
							if($exist_status == 'exist'){//記錄重覆的測驗記錄
								$sumdouble++;
								array_push($arrDouble, $listvalue["exam_user"]);
							}
							$tmp_user = getUserID($listvalue["exam_user"]);
							if($tmp_user =='') $sunNotexist++;
						}
						$lastnum = count($stu_list) - (int)$sumdouble - (int)$sunNotexist; //沒有重覆的筆數
						//echo 'last:'.$lastnum;
			
						if($sub_id !='' && $lastnum >0){
							
							$sql_con2 = "SELECT count(*) as num FROM `concept_priori` WHERE subject_id =:sub_id AND organization_id =:org_id AND exam_range =:range";
							$result3 = $dbh->prepare($sql_con2);
							$result3->bindValue(':sub_id', $sub_id, PDO::PARAM_STR);
							$result3->bindValue(':org_id', $_POST["org"], PDO::PARAM_STR);
							$result3->bindValue(':range', $import_range, PDO::PARAM_STR);
							$result3->execute();
							$count3 = $result3->fetch();
							$version = (int)$count3["num"] +1;
							
							//寫入匯入記錄 concept_priori
							$sql2 = 'INSERT INTO `concept_priori`(`cp_id`, `subject_id`, `organization_id`, `exam_range`, `concept`,
							`import_date`, `create_user`, version) VALUES (:cp_id, :subject_id, :org_id, :exam_range, :concept, :import_date,
							:create_user, :version)';
							$insert2 = $dbh->prepare($sql2);
							$insert2->bindValue(':cp_id', $cp_id, PDO::PARAM_STR);
							$insert2->bindValue(':subject_id', $sub_id, PDO::PARAM_STR);
							$insert2->bindValue(':org_id', $_POST["org"], PDO::PARAM_STR);
							$insert2->bindValue(':exam_range', $import_range, PDO::PARAM_STR);
							$insert2->bindValue(':concept', $import_title, PDO::PARAM_STR);
							$insert2->bindValue(':import_date', $time, PDO::PARAM_STR);
							$insert2->bindValue(':create_user', $_SESSION["user_id"], PDO::PARAM_STR);
							$insert2->bindValue(':version', $version, PDO::PARAM_STR);
							$insert2->execute();
								
						}else{
							if($sub_id ==''){
								$tmpmesg='匯入資料的科目不存在';
								array_push($showmesg, $tmpmesg);
								break;
							}
						}
			
					}
						
					if(isset($_POST["btnOK3"]) && $key >3){
						//基本學習內容
						if(strpos($col["B"],' ') !== false){
							$tmp5 = explode(' ', $col["B"]);
							if(strpos($tmp5[0],'能') !== false){
								$spilt = explode('能', $tmp5[0]);
								$tmp5 = $spilt;
							}
						}else{
							if(strpos($col["B"],'能') !== false){
								$tmp5 = explode('能', $col["B"]);
							}else{
								$tmp5[0] = $col["B"];
							}
						}
						
						//能力指標
						if(strpos($col["C"],' ') !== false){
							$tmp6 = explode(' ', $col["C"]);
							if(strpos($tmp6[0],'能') !== false){
								$spilt = explode('能', $tmp6[0]);
								$tmp6 = $spilt;
							}
						}else{
							if(strpos($col["C"],'能') !== false){
								$tmp6 = explode('能', $col["C"]);
							}
						}
						
						//抓取主檔資料
						array_push($pri, $tmp5[0]);
						array_push($ind, $tmp6[0]);
						array_push($status1, trim($col["D"]));
						array_push($status2, trim($col["E"]));
						array_push($status3, trim($col["F"]));
						array_push($total, trim($col["G"]));
			
						$ii=0;
						foreach ($col as $colkey2 => $colvalue2) { //抓取各學生的測驗資料
							$ii++;
							if($ii >7){
								$tmp_usernm = $stu_list[$colkey2]["exam_user"];
								
								if($stu_list[$colkey2]["exist"] == 'exist'){ //重覆的
									$_SESSION["NotimportData"][$tmp_usernm][$key] = $colvalue2;
									//echo 'col:'.$colkey2.', user:'.$tmp_usernm.', 作答：'.$colvalue2.'<br>';
								}else{
									$stu_record[$tmp_usernm][$key] = $colvalue2;
								}
								
							}
						}
					}
						
				}
			}
			if (isset($_POST['btnOK'])){
				//寫入excel
				$excel_content[0]=[$avlue['name']];
				$excel_content[1]=[''];
				$excel_content[2]=["項次", "序號", "姓名",	"身分證統一編號", "入學年度", "目前年級", "班級", "座號", "身分類別"];
				$return_file_tmp = make_excel($excel_content, $arrSuccess, $arrFail, $arrExist);
					
				array_push($showmesg, "成功筆數：$sumSuccess , 失敗筆數：$sumFail , 資料不存在筆數：$sumexist ");
				array_push($return_file, $return_file_tmp);
				
				echo '<script>
				$(document).ready(function () {
					$("#btnResult").click();
				});
				</script>';
			}
			
		}
	}

	//print_r($stu_list);
	//print_r($stu_record);
	
	if(isset($_POST['btnOK3'])){
		if($cp_id !=''){
			if($lastnum >0){
				$str_ind = implode(_SPLIT_SYMBOL, $ind);
				$str_pri = implode(_SPLIT_SYMBOL, $pri);
				$str_status1 = implode(_SPLIT_SYMBOL, $status1);
				$str_status2 = implode(_SPLIT_SYMBOL, $status2);
				$str_status3 = implode(_SPLIT_SYMBOL, $status3);
				$str_total = implode(_SPLIT_SYMBOL, $total);
				//寫入匯入記錄 concept_priori
				$sql3 = 'UPDATE `concept_priori` SET `indicator_item` =:indicator_item, `indicator_priori` =:indicator_priori,
						`status1_num` =:status1_num, `status2_num` =:status2_num, `status3_num` =:status3_num , `total_num` =:total_num
						WHERE cp_id =:cp_id';
				$update3 = $dbh->prepare($sql3);
				$update3->bindValue(':indicator_item', $str_ind, PDO::PARAM_STR);
				$update3->bindValue(':indicator_priori', $str_pri, PDO::PARAM_STR);
				$update3->bindValue(':status1_num', $str_status1, PDO::PARAM_STR);
				$update3->bindValue(':status2_num', $str_status2, PDO::PARAM_STR);
				$update3->bindValue(':status3_num', $str_status3, PDO::PARAM_STR);
				$update3->bindValue(':total_num', $str_total, PDO::PARAM_STR);
				$update3->bindValue(':cp_id', $cp_id, PDO::PARAM_STR);
				$update3->execute();
									
				//var_dump($school_user);
					
				//寫入測驗記錄 exam_record_priori
				foreach ($stu_record as $rkey => $rvalue){
					//echo 'user:'.$rkey.'-->';
					//print_r($rvalue);
					$str_record = implode(_SPLIT_SYMBOL, $rvalue); //每位學生的測驗資料
					$str_record = str_replace('無', 'N', $str_record);//將 無測驗的資料 轉為 符號N
				
					$user_id = getUserID($rkey);
					//echo "stu_id: $user_id , <br>";
					if($user_id !=''){
						$sql5 = "INSERT INTO `exam_record_priori`(`exam_user`, `user_id`, `cp_id`, `date`, `priori_remedy_rate`)
					VALUES (:exam_user, :user_id, :cp_id, :date, :priori_remedy_rate)";
						$insert3 = $dbh->prepare($sql5);
						$insert3->bindValue(':exam_user', $rkey, PDO::PARAM_STR);
						$insert3->bindValue(':user_id', $user_id, PDO::PARAM_STR);
						$insert3->bindValue(':cp_id', $cp_id, PDO::PARAM_STR);
						$insert3->bindValue(':date', $time, PDO::PARAM_STR);
						$insert3->bindValue(':priori_remedy_rate', $str_record, PDO::PARAM_STR);
						$insert3->execute();
							
						if ($insert3->errorCode()!='00000'){
							$_SESSION["FailImport"][$sumFail] = $rkey;
							$sumFail++; //列出失敗的資料至 excel 中
							array_push($arrFail, $rkey);
							$msg = debug_msg("第".__LINE__."行 $insert3->errorInfo() ", $insert3->errorInfo());
						}else {
							$msg='';
							$_SESSION["SuccessImport"][$sumSuccess] = $rkey;
							$sumSuccess++; //列出成功的資料至 excel 中
							array_push($arrSuccess, $rkey);
						}
					}else{
						$_SESSION["NotExistData"][$sumexist] = $rkey;
						$sumexist++; //列出不存在的資料至 excel 中
						array_push($arrExist, $rkey);
					}
				
				}
			}
			
			$excel_content[0] = [$import_title];
			$excel_content[1] = ["基本學習內容", str_replace(_SPLIT_SYMBOL, ",",$str_pri)];
			$excel_content[2] = ["序號","姓名"];
			$return_file_tmp = make_excel($excel_content, $arrSuccess, $arrDouble, $arrExist);
			
			array_push($showmesg, "成功筆數：$sumSuccess , 資料重覆筆數：$sumdouble , 資料不存在筆數：$sumexist ");
			array_push($return_file, $return_file_tmp);
		}
		echo '<script>
			$(document).ready(function () {
				$("#btnResult").click();
			});
			</script>';
	}
	
	if(isset($_POST['btnOK']) && !empty($HTTP_POST_FILES['stuFile'])) array_push($showtitle, "匯入學生名單");
	if(isset($_POST['btnOK']) && !empty($HTTP_POST_FILES['stuFile2'])) array_push($showtitle, "匯入個案名單");
	if(isset($_POST['btnOK3'])) array_push($showtitle, "匯入學生測驗報告統計表");
	//echo "成功筆數：$sumSuccess , 失敗筆數：$sumFail , 資料不存在筆數：$sumexist ";
	//print_r($_SESSION["NotimportData"]);
}

//查詢系統對應的user_id
if(isset($_POST['btnDL'])){
	$query =  $dbh->prepare("SELECT uname, priori_name, grade, class as classmn FROM user_info , user_status WHERE organization_id =:org_id 
				AND used =1 AND user_info.user_id = user_status.user_id AND user_status.access_level =1");
	$query->bindValue(':org_id', $_POST["org"], PDO::PARAM_STR);
	$query->execute();
	$school_user = $query->fetchAll(PDO::FETCH_ASSOC);
	$count =  $query->rowCount();
	
	$query2 = $dbh->prepare("SELECT COUNT(uid) as num FROM user_info , user_status WHERE organization_id =:org_id  
				AND used =1 AND user_info.user_id = user_status.user_id AND user_status.access_level =1 AND user_info.priori_name !=''");
	$query2->bindValue(':org_id', $_POST["org"], PDO::PARAM_STR);
	$query2->execute();
	$count2 = $query2->fetch();
	
	
	$excel_content[0] = ["學校代碼", $_POST["org"]];
	$excel_content[1] = ["序號","姓名","補救教學專用對應學生資料欄位","年級","班級"];
	$return_file = make_excel2($excel_content, $school_user);
	
	$showtitle="已匯入的學生資料";
	$showmesg="總學生人數： $count ，已對應補救教學的學生人數：  $count2[num] 。";
	
}

if(isset($_POST['btnExam'])){
	
	$exam_data = array();
	$exam_data2 = array();
	
	$str_query2="SELECT * FROM `concept_priori` WHERE organization_id = :org_id";
	$query2 =  $dbh->prepare($str_query2);
	$query2->bindValue(':org_id', $_POST["org"], PDO::PARAM_STR);
	$query2->execute();
	$exam_tmp2 = $query2->fetchAll(PDO::FETCH_ASSOC);
	$count2_data = $query2->rowCount();
	
	$str_query="SELECT exam_user, exam_title, cp_id, priori_remedy_rate FROM `exam_record_priori` WHERE user_id LIKE :org_id ";
	$query =  $dbh->prepare($str_query);
	$query->bindValue(':org_id', $_POST["org"].'-%', PDO::PARAM_STR);
	$query->execute();
	$exam_tmp = $query->fetchAll(PDO::FETCH_ASSOC);
	$count_data = $query->rowCount();

	$excel_content[0] = ["學校代碼", $_POST["org"]];
	$excel_content[1] = ["序號","學生姓名","測驗名稱","測驗內容"];
	$ii=0;
	foreach ($exam_tmp2 as $ekey=> $evalue){
		
		$exam_data[$ii]["import_date"] = '匯入日期：'.$evalue["import_date"];
		$exam_data[$ii]["concept"] = $evalue["concept"];
		$tmp_arr = explode(_SPLIT_SYMBOL, $evalue["indicator_priori"]);
		foreach ($tmp_arr as $tkey=> $tvalue){
			$fieldnm= 'ind'.$tkey;
			$exam_data[$ii][$fieldnm] = $tvalue;
		}
		$ii++;
		foreach ($exam_tmp as $ekey2=> $evalue2){
			if($evalue2["cp_id"] == $evalue["cp_id"]){
				$exam_data[$ii]["exam_user"] = $evalue2["exam_user"];
				$exam_data[$ii]["exam_title"] = $evalue2["exam_title"];
				$tmp_arr2 = explode(_SPLIT_SYMBOL, $evalue2["priori_remedy_rate"]);
				foreach ($tmp_arr2 as $tkey2=> $tvalue2){
					$fieldnm2= 'ind'.$tkey2;
					$exam_data[$ii][$fieldnm2] = $tvalue2;
				}
				$ii++;
			}
		}
		//要新增一行空白的！
		$ii++;
	}
	
	$return_file = make_excel2($excel_content, $exam_data);
	
	$showtitle="已匯入的測驗統計表";
	$showmesg="資料筆數： $count_data 。";
}

$query_PR =  $dbh->prepare("SELECT import_date FROM concept_priori WHERE organization_id =:org_id ORDER BY import_date DESC LIMIT 0,1");
$query_PR->bindValue(':org_id', $org, PDO::PARAM_STR);
$query_PR->execute();
$PR_data = $query_PR->fetch();

$query_update = $dbh->prepare("SELECT max(update_date) update_date FROM `user_info` WHERE organization_id =:org_id");
$query_update->bindValue(':org_id', $org, PDO::PARAM_STR);
$query_update->execute();
$UP_data = $query_update->fetch();

?>

<div class="content2-Box">
    <div class="path">目前位置：匯入補救教學</div>
    <div class="main-box">
  		<div class="class-list2-box">
	  		<div class="title01">匯入學生與個案名單</div>
	  		<div class="class-list2 test-search">
	  			<form method="post" action="modules.php?op=modload&name=remedyTest&file=import_data_combine_list" enctype="multipart/form-data" name="form1">
	  			<div class="class-root-box3">學年度 &nbsp;&nbsp;：<?php echo  Semester_id2FullName($seme);?></div>
	  			<div class="class-root-box3">就讀學校：
	  				<select id="org" name="org" class="input-normal" >
       					<?php foreach ($orglist as $key=>$value){
       						if($org == $key){ // && $_POST["btn02"]!=''
       							$att = "selected=\"selected\"";
       						}else $att = '';
       					?>
       					<option value="<?php echo $key;?>" <?php echo $att;?>><?php echo $value;?></option>
       					<?php }?>
       				</select>
	  			</div>
	  			<div class="class-root-box3">♦ 學生名單的上傳檔案：<input type="file" id="stuFile" name="stuFile"></div>
	  			<div class="class-root-box3">♦ 個案名單的上傳檔案：<input type="file" id="stuFile2" name="stuFile2" ></div>
	  			<input type="submit" onclick="return chk();" class="btn04" id="btnOK" name="btnOK" value="確定">
	  			<div class="class-root-box3">最近匯入時間： <?php echo $UP_data["update_date"];?>  <input type="submit" class="btn01" id="btnDL" name="btnDL" value="查詢學生名單"> </div>
	  			</form>
	  			
	  		</div>
  		</div>
  		<!-- <div class="class-list2-box">
  			<div class="title01">匯入個案名單</div>
	  		<div class="class-list2 test-search">
	  			<form method="post" action="modules.php?op=modload&name=remedyTest&file=import_data_combine" enctype="multipart/form-data" name="form2">
	  			<div class="class-root-box3">學年度 &nbsp;&nbsp;：<?php echo  Semester_id2FullName($seme);?></div>
	  			<div class="class-root-box3">就讀學校：
	  				<select id="org" name="org" class="input-normal">
       					<?php foreach ($orglist as $key=>$value){
       						if($org == $key){ // && $_POST["btn02"]!=''
       							$att = "selected=\"selected\"";
       						}else $att = '';
       					?>
       					<option value="<?php echo $key;?>" <?php echo $att;?>><?php echo $value;?></option>
       					<?php }?>
       				</select>
	  			</div>
	  			<div class="class-root-box3">上傳檔案：<input id="stuFile2" name="stuFile2" type="file" required></div>
	  			<input type="submit" class="btn04" id="btnOK2" name="btnOK2" value="確定"> 
	  			</form>
  			</div>
  		</div> -->
  		<div class="class-list2-box">
  			<div class="title01">匯入學生測驗報告統計表</div>
	  		<div class="class-list2 test-search">
	  			<form method="post" action="modules.php?op=modload&name=remedyTest&file=import_data_combine_list" enctype="multipart/form-data" name="form3">
	  			<div class="class-root-box3">學年度 &nbsp;&nbsp;：<?php echo  Semester_id2FullName($seme);?></div>
	  			<div class="class-root-box3">就讀學校：
	  				<select id="org" name="org" class="input-normal" >
       					<?php foreach ($orglist as $key=>$value){
       						if($org == $key){ // && $_POST["btn02"]!=''
       							$att = "selected=\"selected\"";
       						}else $att = '';
       					?>
       					<option value="<?php echo $key;?>" <?php echo $att;?>><?php echo $value;?></option>
       					<?php }?>
       				</select>
	  			</div>
	  			<div class="class-root-box3">上傳檔案：<input id="stuFile3" name="stuFile3" type="file" ></div>
	  			<input type="submit" onclick="return chk2();" class="btn04" id="btnOK3" name="btnOK3" value="確定">
	  			<div class="class-root-box3">最近匯入時間： <?php echo $PR_data["import_date"];?> <input type="submit" class="btn01" id="btnExam" name="btnExam" value="查詢測驗統計表"></div>
	  			</form>
	  			<!-- <div class="class-root-box3"><a class="venoboxframe vbox-item btn01"  data-title="編輯測驗統計表" data-gall="gall-frame2" data-type="iframe" href="modules.php?op=modload&name=remedyTest&file=edit_examRecord&screen=frame">編輯測驗統計表</a></div> -->
  			</div>
  		</div>
  		<div class="class-list2-box">
	  		<div>
				<div class="info">
					<div class="info-title">說明</div>
					<?php 
						if(!empty($showmesg) !=''){?>
					  <div class="info-content">
					   <div><i class="fa fa-exclamation"></i></div>
					   <?php 
					 	if(is_array($showmesg)){
					  		foreach ($showmesg as $skey=> $svalue){
					   ?>
					   <div><?php echo $showtitle[$skey];?></div>
					   <div style="text-align:center;"><?php echo $svalue;?></div>
					   <div style="text-align:left; "><a class="btn02" href="<?php echo "./data/tmp/$return_file[$skey]";?>">下載匯入結果清單</a></div>
					   <div><a id="btnResult" class="venoboxframe vbox-item btn01"  data-title="編輯測驗統計表" data-gall="gall-frame2" data-type="iframe" href="modules.php?op=modload&name=remedyTest&file=show_resultDetail&screen=frame">查看匯入結果清單</a></div>
					   <?php 		 
					   		}
					 	}else{
					   ?>
					   <div><?php echo $showtitle;?></div>
					   <div style="text-align:center;"><?php echo $showmesg;?></div>
					   <div style="text-align:left; display:inline-block;"><a class="btn02" href="<?php echo "./data/tmp/$return_file";?>">下載查詢結果清單</a></div>
					  <?php }?>
					  </div>
					<?php }?>
					
				</div>
			</div>
	  		
  		</div>
</div>

