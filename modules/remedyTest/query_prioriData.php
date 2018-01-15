<?php
require_once "include/config.php";
require_once "include/adp_API.php";
require_once "modules/remedyTest/print_prioriData.php";

if (!isset($_SESSION)) {
	session_start();
}

$user_id = $_SESSION['user_id'];
//學生的測驗資料
if(isset($_POST["btnQuery"])){
	$sql='SELECT a.exam_sn, a.exam_user, a.user_id, a.cp_id, b.subject_id, b.organization_id, b.exam_range
		FROM exam_record_priori a, concept_priori b
		WHERE a.cp_id = b.cp_id AND user_id=:user_id AND exam_range=:exam_range AND subject_id=:subject ORDER BY exam_sn DESC ';
	$result=$dbh->prepare($sql);
	$result->bindParam(':user_id',$user_id, PDO::PARAM_STR);
	$result->bindParam(':exam_range',$_POST["exam_range"], PDO::PARAM_STR);
	$result->bindParam(':subject',$_POST["subject"], PDO::PARAM_STR);
	$result->execute();
	$queryData = $result->fetchAll(PDO::FETCH_ASSOC);
	$count=$result->rowCount();
}else{
	$sql='SELECT a.exam_sn, a.exam_user, a.user_id, a.cp_id, b.subject_id, b.organization_id, b.exam_range
		FROM exam_record_priori a, concept_priori b
		WHERE a.cp_id = b.cp_id AND user_id=:user_id ORDER BY exam_sn DESC ';
	$result=$dbh->prepare($sql);
	$result->bindParam(':user_id',$user_id, PDO::PARAM_STR);
	$result->execute();
	$queryData = $result->fetchAll(PDO::FETCH_ASSOC);
	$count=$result->rowCount();
}

if(isset($_POST["status"])) $status = $_POST["status"];
else $status = 'all';

$query_cp = $queryData[0]["cp_id"];
$query_sn = $queryData[0]["exam_sn"];
//echo "cp: $query_cp , sn: $query_sn";

//科目
$subject_query = "SELECT subject_id, name as subject_nm FROM subject  WHERE display =1";
$subject_data = $dbh->prepare($subject_query);
$subject_data->execute();
$sub_d = $subject_data->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
//print_r($sub_d);
?>
<style>
  .tippic {display:table;font-size:14px;white-space:nowrap;}
  .tippic > dd {display:table-row;}
  .tippic > dd > * {display:table-cell;}
  .tippic > dd > i {font-style:normal;}

/* 其他 */
  .rem_naver {display:block;width:25px;height:25px;background-image:url('./images/start/p5-4-01.png');background-size:100%;background-position:center;background-repeat:no-repeat;}
  .rem_help {display:block;width:25px;height:25px;background-image:url('./images/start/p5-4-02.png');background-size:100%;background-position:center;background-repeat:no-repeat;}
  .rem_pass {display:block;width:25px;height:25px;background-image:url('./images/start/p5-4-03.png');background-size:100%;background-position:center;background-repeat:no-repeat;}
  .rem_left {cursor:pointer;position:absolute;top:-35px;right:165px;display:block;width:35px;height:35px;background-image:url('./images/g_left.svg');background-size:100%;background-position:center;background-repeat:no-repeat;}
  .rem_right {cursor:pointer;position:absolute;top:-35px;right:120px;display:block;width:35px;height:35px;background-image:url('./images/g_right.svg');background-size:100%;background-position:center;background-repeat:no-repeat;}
</style>

<script>
  $( function() {

  });
</script>
<div class="content2-Box">
    <div class="path">目前位置：補救診斷報告</div>
    <div class="main-box">
    	<div class="left-box-o">
	    	<div class="left-box-c discuss-select">
	    	<form method="post" action="modules.php?op=modload&name=remedyTest&file=query_prioriData" name="form1">
		    	<select id="exam_range" name="exam_range"><option value="">請選擇(日期)</option>
		    	<?php foreach ($queryData as $key=>$value){?>
		    		<option value="<?php echo $value["exam_range"]?>"><?php echo $value["exam_range"]?></option>
		    	<?php }?>	
		    	</select>
	    	
	    		<select id="subject" name="subject"><option value="">請選擇(科別)</option>
	    		<?php foreach ($queryData as $key=>$value){?>
	    			<option value="<?php echo $value["subject_id"]?>"><?php echo $sub_d[$value["subject_id"]][0]?></option>
	    		<?php }?>	
	    		</select>
	    		<input type="submit" id="btnQuery" name="btnQuery" class="btn02" value="提交">
	    	</form>
	    	</div>
	    	<br>
	    	<div class="left-box-c"> 
	    		節點狀態<br>
	    		<div id="all" class="btn07" style="width:100px">全部</div><br>
	    		<div id="noPass" class="btn07" style="width:100px">未精熟</div><br>
	    		<div id="Pass" class="btn07" style="width:100px">精熟</div>
	    		
	    		<dl class="tippic">
		          <dd>
		            <i>▼</i><span style="font-weight:bold;font-size:20px;padding-top:10px;">因材網圖例</span>
		          </dd>
		          <dd>
		            <i class="rem_naver"></i>
		            <span>&nbsp;未測驗</span>
		          </dd>
		          <dd>
		            <i class="rem_help"></i>
		            <span>&nbsp;待補救</span>
		          </dd>
		          <dd>
		            <i class="rem_pass"></i>
		            <span>&nbsp;精熟</span>
		          </dd>
		        </dl>
		        
	    		<dl class="tippic">
		          <dd>
		            <i>▼</i><span style="font-weight:bold;font-size:20px;">補救教學圖例</span>
		          </dd>
		          <dd>
		            <i>Ｏ</i><span>:所有試題均通過</span>
		          </dd>
		          <dd>
		            <i>Ｘ</i>
		            <span>:所有試題均未通過</span>
		          </dd>
		          <dd><i style="font-family:sans-serif;">△</i>
		            <span>:評部分試題未通過</span>
		          </dd>
		          <dd>
		            <i>N</i>
		            <span>:尚未有測驗資料</span>
		          </dd>
		        </dl>
		        
	    	</div>
    	</div>
    	<div class="right-box">
    	<?php 
    		if(!empty($query_cp) && !empty($query_sn)){
    			$prt[6] = prioriExamResult($user_id, $query_cp, $query_sn);
    			echo $prt[6];
    		}else echo '請選擇 日期 與 科別，謝謝！';
    	?>
    	</div>
    </div>
</div>

<script>
  $('#all').on( 'click', function(){
	  console.log('into all');
	  var sel_status = 'all';
	  var sel_rule=new Array();
	  
	  if( sel_status!=='' ) sel_rule.push('[id*="node:"]');
	  
	  $('tr').hide();
	  $('tr'+sel_rule.join('') ).show();
	  $('.tableTitle'+sel_rule.join('') ).show();
  } );

  $('#noPass').on( 'click', function(){
	  console.log('into noPass');
	  var sel_status = 'noPass';
	  var sel_rule=new Array();
	  
	  if( sel_status!=='' ) sel_rule.push('[id*="status:'+sel_status+'"]');
	  $('tr').hide();
	  $('tr'+sel_rule.join('') ).show();
	  $('.tableTitle'+sel_rule.join('') ).show();
  } );

  $('#Pass').on( 'click', function(){
	  console.log('into Pass');
	  var sel_status = 'Pass';
	  var sel_rule=new Array();
	  
	  if( sel_status!=='' ) sel_rule.push('[id*="status:'+sel_status+'"]');
	  $('tr').hide();
	  $('tr'+sel_rule.join('') ).show();
	  $('.tableTitle'+sel_rule.join('') ).show();
  } );


</script>