<?php
include_once "include/config.php";
include_once 'include/adp_API.php';
include_once 'classes/PHPExcel.php';
include_once 'classes/PHPExcel/IOFactory.php';

if (!isset($_SESSION)) {
	session_start();
}
$seme = getYearSeme();

?>
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

$sql_school='SELECT o.organization_id, o.type, o.name, c.city_code, c.city_name, a.post_code, a.area
              FROM organization AS o , city AS c , city_area AS a
              WHERE substr(o.address,2,3)=a.post_code AND a.city=c.city_name AND o.used=1
              GROUP BY o.organization_id';
$result_school=$dbh->prepare($sql_school);
$result_school->execute();
$city=array();
$orglist=array();

while ($row_school = $result_school->fetch(\PDO::FETCH_ASSOC)){

	if(!array_key_exists($row_school['city_code'],$city)){
		$city[$row_school['city_code']]=$row_school['city_name'];
	}
	if(!array_key_exists($row_school['organization_id'],$orglist)){
		$orglist[$row_school['organization_id']]=$row_school['name'];
	}
}	 

if (isset($_POST['btnOK']) || isset($_POST["btnOK2"])) {
	
	if(isset($_POST['btnOK'])) $upload_file = $HTTP_POST_FILES['stuFile'];
	if(isset($_POST['btnOK2'])) $upload_file = $HTTP_POST_FILES['stuFile2'];
	
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
				$errStr = "File troppo pesante";
				echo '<script language="javascript">alert(\"超過限制檔案大小\");</script>';//跳出錯誤訊息
			}
			$_ext_ = explode(".", $_name_);
			$_ext_ = strtolower($_ext_[count($_ext_)-1]);
	
			$file_name_title=$_file_['name'];
	
			if(!in_array($_ext_, $_accepted_FILE_extensions_) && count($_accepted_FILE_extensions_) > 0){
				$errStr = "Estensione non valida";
				echo "<script>javascript:alert(\"請檢查檔案格式\");</script>";//跳出錯誤訊息
			}
			if(!is_dir(DESTINATION_FILE_FOLDER) && is_writeable(DESTINATION_FILE_FOLDER)){
				$errStr = "Cartella di destinazione non valida";
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
	}else{
		echo 'empty HTTP_POST_FILES';
	}
	
	$file= _ADP_TMP_UPLOAD_PATH.$newFilename;
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
	$sumSuccess = 0;
	$sumFail = 0;
	$sumexist = 0;
	foreach($sheetData as $key => $col){
		$arr="";
		//讀EXL 以行
		if($key != 1){
			
			$pid_len = strlen($col["C"]);
			$mark_pid = str_repeat('*',$pid_len - 5);
			$new_priori_name = $mark_pid.substr($col["C"], -5).$col["B"];
			//echo "Pid: $col[C], Name: $col[B], new: $new_priori_name <br>";
			
			//查詢資料是否存在
			$query =  $dbh->prepare("SELECT * FROM user_info WHERE uname =:uname AND grade =:grade AND class =:class AND organization_id =:org_id");
			$query->bindValue(':uname', $col["B"], PDO::PARAM_STR);
			$query->bindValue(':grade', $col["E"], PDO::PARAM_STR);
			$query->bindValue(':class', $col["F"], PDO::PARAM_STR);
			$query->bindValue(':org_id', $_POST["org"], PDO::PARAM_STR);
			$query->execute();
			$row = $query->fetchAll(\PDO::FETCH_ASSOC);
			$num = count($row);
			
			if($num >0){
				//更新 user_info.
				$sql = 'UPDATE user_info SET priori_name = :priori_name
				WHERE uname =:uname AND grade =:grade AND class =:class AND organization_id =:org_id';
				$update2 = $dbh->prepare($sql);
				$update2->bindValue(':priori_name', $new_priori_name, PDO::PARAM_STR);
				$update2->bindValue(':uname', $col["B"], PDO::PARAM_STR);
				$update2->bindValue(':grade', $col["E"], PDO::PARAM_STR);
				$update2->bindValue(':class', $col["F"], PDO::PARAM_STR);
				$update2->bindValue(':org_id', $_POST["org"], PDO::PARAM_STR);
				$update2->execute();
					
				if ($update2->errorCode()!='00000'){
					$sumFail++; //列出失敗的資料至 excel 中
					$msg = debug_msg("第".__LINE__."行 update2->errorInfo() ", $update2->errorInfo());
				}else {
					$msg='';
					$sumSuccess++; //列出成功的資料至 excel 中
				}
			}else{
				$sumexist++; //列出不存在的資料至 excel 中
			}
			//echo $sumSuccess.',';
		}
	}
	if(isset($_POST['btnOK'])) $showtitle="匯入學生名單";
	if(isset($_POST['btnOK2'])) $showtitle="匯入個案名單";
	
	$showmesg = "成功筆數：$sumSuccess , 失敗筆數：$sumFail , 資料不存在筆數：$sumexist ";;
	//echo "成功筆數：$sumSuccess , 失敗筆數：$sumFail , 資料不存在筆數：$sumexist ";
	
}
?>

<div class="content2-Box">
    <div class="path">目前位置：匯入補救教學</div>
    <div class="main-box">
  		<div class="class-list2-box">
	  		<div class="title01">匯入學生名單</div>
	  		<div class="class-list2 test-search">
	  			<form method="post" action="modules.php?op=modload&name=remedyTest&file=import_data" enctype="multipart/form-data" name="form1">
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
	  			<div class="class-root-box3">上傳檔案：<input type="file" id="stuFile" name="stuFile"></div>
	  			<input type="submit" class="btn01" id="btnOK" name="btnOK" value="確定">
	  			</form>
	  		</div>
  		</div>
  		<div class="class-list2-box">
  			<div class="title01">匯入個案名單</div>
	  		<div class="class-list2 test-search">
	  			<form method="post" action="modules.php?op=modload&name=remedyTest&file=import_data" enctype="multipart/form-data" name="form1">
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
	  			<div class="class-root-box3">上傳檔案：<input id="stuFile2" name="stuFile2" type="file"></div>
	  			<input type="submit" class="btn01" id="btnOK2" name="btnOK2" value="確定">
	  			</form>
  			</div>
  		</div>
  		<div class="class-list2-box">
  			<div class="title01">匯入學生測驗報告統計表</div>
	  		<div class="class-list2 test-search">
	  			<form method="post" action="modules.php?op=modload&name=remedyTest&file=import_data" enctype="multipart/form-data" name="form1">
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
	  			<div class="class-root-box3">上傳檔案：<input id="stuFile3" name="stuFile3" type="file"></div>
	  			<input type="submit" class="btn01" id="btnOK3" name="btnOK3" value="確定">
	  			</form>
  			</div>
  		</div>
  		<div class="class-list2-box">
	  		<div>
				<div class="info">
					<div class="info-title">說明</div>
					<?php if($showmesg !=''){?>
					  <div class="info-content">
					   <div><i class="fa fa-exclamation"></i></div>
					   <div><?php echo $showtitle;?></div>
					   
					   <div style="text-align:center;"><?php echo $showmesg;?></div>
					  </div>
					<?php }?>
				</div>
			</div>
	  		
  		</div>
</div>

