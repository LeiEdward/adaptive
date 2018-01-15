<?php
include_once "include/config.php";
include_once 'include/adp_API.php';

if (!isset($_SESSION)) {
	session_start();
}
$seme = getYearSeme();
$seme_year=getNowSemeYear();

?>

<?php
if(empty($_POST["org"])){
	$org = $_SESSION[user_data]->organization_id;
}else $org = $_POST["org"];

$city=array();
$orglist=array();
$exam_data = array();
$exam_data2 = array();

$sql_school='SELECT o.organization_id, o.type, o.name, c.city_code, c.city_name, a.post_code, a.area
              FROM organization AS o , city AS c , city_area AS a
              WHERE substr(o.address,2,3)=a.post_code AND a.city=c.city_name AND o.used=1
              GROUP BY o.organization_id';
$result_school=$dbh->prepare($sql_school);
$result_school->execute();

while ($row_school = $result_school->fetch(\PDO::FETCH_ASSOC)){

	if(!array_key_exists($row_school['city_code'],$city)){
		$city[$row_school['city_code']]=$row_school['city_name'];
	}
	if(!array_key_exists($row_school['organization_id'],$orglist)){
		$orglist[$row_school['organization_id']]=$row_school['name'];
	}
}

$str_query2="SELECT * FROM `concept_priori` WHERE organization_id = :org_id";
$query2 =  $dbh->prepare($str_query2);
$query2->bindValue(':org_id', $org, PDO::PARAM_STR);
$query2->execute();
$exam_tmp2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$count2_data = $query2->rowCount();

$str_query="SELECT exam_user, exam_title, cp_id, priori_remedy_rate FROM `exam_record_priori` WHERE user_id LIKE :org_id ";
$query =  $dbh->prepare($str_query);
$query->bindValue(':org_id', $org.'-%', PDO::PARAM_STR);
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
?>

<div class="content2-Box">
    <div class="path">目前位置：編輯測驗統計表</div>
    <div class="main-box">
    	<form method="post" action="modules.php?op=modload&name=remedyTest&file=edit_examRecord" enctype="multipart/form-data" name="form3">
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
	  		<div class="class-root-box3">測驗日期：</div>
	  		
	  		<input type="submit" onclick="return chk2();" class="btn04" id="btnOK3" name="btnOK3" value="查詢">
  			
  		</form>
    </div>
</div>

