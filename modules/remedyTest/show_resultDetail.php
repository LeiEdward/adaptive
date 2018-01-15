<?php
include_once "include/config.php";
include_once 'include/adp_API.php';

if (!isset($_SESSION)) {
	session_start();
}

?>
<?php
//print_r($_SESSION["NotimportData"]);
if($_SESSION["NotimportData"] !=''){
	$Noimportnum = count($_SESSION["NotimportData"]);
}
if($_SESSION["FailImport"] !=''){
	$Failnum = count($_SESSION["FailImport"]);
}
if($_SESSION["SuccessImport"] !=''){
	$Successnum = count($_SESSION["SuccessImport"]);
}
if($_SESSION["NotExistData"] !=''){
	$NoExistnum = count($_SESSION["NotExistData"]);
	//print_r($_SESSION["NotExistData"]);
}

//echo '尚未匯入筆數：'.$Noimportnum;
?>

<div class="content2-Box">
    <div class="path">目前位置：查詢結果清單</div>
    <div class="main-box">
    <?php if($NoExistnum >0) {?>
		<div>學生資料不存在，故尚未匯入資料(<?php echo $NoExistnum;?>筆)</div>
    	<div class="mov-result-detail-list">
    		<div class="mov-result-detail-right">序號</div><div class="mov-result-detail-left">學生姓名</div>
    	</div>
    	<?php 
    		if($_SESSION["NotExistData"] !=''){
    			$i=1;
    			foreach ($_SESSION["NotExistData"] as $key=> $value){
    				echo '<div class="mov-result-detail-list">';
    				echo '<div class="mov-result-detail-right">'.$i.'</div>';
    				echo '<div class="mov-result-detail-left">'.$value.'</div>';
    				echo '</div>';
    				$i++;
    			}
    		}
		}	
		if($Noimportnum >0){	
    ?>
    	<div>該測驗資料已存在，故尚未匯入資料(<?php echo $Noimportnum;?>筆)</div>
    	<div class="mov-result-detail-list">
    		<div class="mov-result-detail-right">序號</div><div class="mov-result-detail-left">學生姓名</div>
    	</div>
    	<?php 
    		if($_SESSION["NotimportData"] !=''){
    			$i=1;
    			foreach ($_SESSION["NotimportData"] as $key=> $value){
    				echo '<div class="mov-result-detail-list">';
    				echo '<div class="mov-result-detail-right">'.$i.'</div>';
    				echo '<div class="mov-result-detail-left">'.$key.'</div>';
    				echo '</div>';
    				$i++;
    			}
    		}
		}
		if($Successnum >0){	
    		?>
    	<div>已完成匯入資料(<?php echo $Successnum;?>筆)</div>
    	<div class="mov-result-detail-list">
    		<div class="mov-result-detail-right">序號</div>
    		<div class="mov-result-detail-left">學生姓名</div>
    	</div>
    	<?php 
    		if($_SESSION["SuccessImport"] !=''){
    			$i=1;
    			foreach ($_SESSION["SuccessImport"] as $key=> $value){
    				echo '<div class="mov-result-detail-list">';
    				echo '<div class="mov-result-detail-right">'.$i.'</div>';
    				echo '<div class="mov-result-detail-left">'.$value.'</div>';
    				echo '</div>';
    				$i++;
    			}
    		}
		}
		if(Failnum >0){	
    		?>
    	<div>匯入失敗資料(<?php echo $Failnum;?>筆)</div>
    	<div class="mov-result-detail-list">
    		<div class="mov-result-detail-right">序號</div>
    		<div class="mov-result-detail-left">學生姓名</div>
    	</div>
    	<?php 
    		if($_SESSION["FailImport"] !=''){
    			$i=1;
    			foreach ($_SESSION["FailImport"] as $key=> $value){
    				echo '<div class="mov-result-detail-list">';
    				echo '<div class="mov-result-detail-right">'.$i.'</div>';
    				echo '<div class="mov-result-detail-left">'.$value.'</div>';
    				echo '</div>';
    				$i++;
    			}
    		}
		}	
    		?>	
    		
    </div>
</div>