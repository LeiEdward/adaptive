<?php
include_once "include/config.php";
include_once 'include/adp_core_function.php';

if (!isset($_SESSION)) {
	session_start();
}

?>
<!--<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Assign Mission(任務指派)</title>
 
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="/resources/demos/style.css">
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
 -->
<style>
	.scrollit {
    	overflow:scroll;
    	height:200px;
	}
</style>
<script type="text/javascript">
var misIndCount =0, misEPCount =0, stu_array = [];

function addMissionInd(i,node){
	var table = document.getElementById("mission_ind");
	var tr = document.createElement('tr');	
		tr.align="center";	
		tr.style.backgroundColor="#FFFFFF";   	  
	var td = tr.appendChild(document.createElement('td'));
	var td2 = tr.appendChild(document.createElement('td'));
	var td3 = tr.appendChild(document.createElement('td'));
	var td4 = tr.appendChild(document.createElement('td'));
	
	var emptyRow = document.getElementById("empty2");
	if(emptyRow){
		emptyRow.parentNode.removeChild(emptyRow);
	}
	misIndCount++;
	td.innerHTML = misIndCount;
	td2.innerHTML = '<input type="hidden" name="node[]" value="'+node+'">'+node;
	td3.innerHTML = '<input type="checkbox" name="ms_data[]" value="video" checked onclick="return false;">影片 / <input type="checkbox" name="ms_data[]" value="prac" checked>練習題  / <input type="checkbox" name="ms_data[]" value="da"> 動態評量  / <input type="checkbox" name="ms_data[]" value="cr"> 互動式元件';
	td4.innerHTML = '<input type="button" class="btn05" value="移除" btnType="delete">';
	
	table.appendChild(tr); 
	//alert("hello..."+node);
	console.log(node);
}

function addMissionEP(i,ep_id, ep_nm, endYear){
	var table = document.getElementById("mission_EP");
	var tr = document.createElement('tr');	
		tr.align="center";	
		tr.style.backgroundColor="#FFFFFF";   	  
	var td = tr.appendChild(document.createElement('td'));
	var td3 = tr.appendChild(document.createElement('td'));
	var td2 = tr.appendChild(document.createElement('td'));
	var td5 = tr.appendChild(document.createElement('td'));
	var td4 = tr.appendChild(document.createElement('td'));
	
	var emptyRow = document.getElementById("empty22");
	if(emptyRow){
		emptyRow.parentNode.removeChild(emptyRow);
	}
	misEPCount++;
	td.innerHTML = misEPCount;
	td3.innerHTML = ep_id;
	td2.innerHTML = '<input type="hidden" name="ep_id[]" value="'+ep_id+'">'+ep_nm;
	td5.innerHTML = '<input type="text" name="endYear[]" placeholder="請輸入" style="width:50px" value="'+endYear+'">年級';
	//td3.innerHTML = '<input type="checkbox" name="exam_data[]" value="exam_vol" checked>診斷測驗 / <input type="checkbox" name="exam_data[]" value="ques" checked>學習問卷';
	td4.innerHTML = '<input type="button" class="btn05" value="移除" btnType="delete">';
	
	table.appendChild(tr); 
	//alert("hello..."+node);
	console.log(ep_id);
}

</script>

<script type="text/javascript">
$(function() {

	$("#starttime").click(function() {
		$("#strdate").attr("checked",true);
	});

	$("#limittime").click(function() {
		$("#enddate").attr("checked",true);
	});

	$("#cancelBtn").click(
			function(){
				var url="";
				//$(location).attr('href', url);
				window.history.back();
			}
	);	
	// 跨班即指派個人任務 BlueS 20170926
	$(".venoboxinline").click(
			function(){
				$.ajax({
					type : "POST" ,
					url : "modules/assignMission/prodb_assignment_data.php" ,
					data :{type: '4'},
					dataType:'json',
					success: function(data){	
						var i = 0;
						var num = 0;
// 						console.log('data:',data);	
						$("#msg").html("");
						$.each(data, function() {
							if(i==0){
								$("#msg").append('<table class="datatable" data-sortable ><thead><tr><th>'+data[i].grade+'年'+data[i].class+'班'+'</th></tr></thead><tr><td id=div_'+num+'>');
							}
							if(i > 0 && (data[i].grade != data[i-1].grade || data[i].class != data[i-1].class)){
								num++;
								$("#msg").append('<table class="datatable" data-sortable ><thead><tr><th>'+data[i].grade+'年'+data[i].class+'班'+'</th></tr></thead><tr><td id=div_'+num+'>');
							}
			        		$("#div_"+num).append('<li><span><input name="stu_id[]" type="checkbox" value=\"'+data[i].user_id+'\" /></span><span>'+data[i].uname+'</span></li>');	
			        		i++;			
			        	});
					},
					error: function(xhr, ajaxOptions, thrownError){
						alert('error...');
					}
				});
			}
		);
	
		
	$("#OKBtn").click(
		function(){
			var mission_nm = $("#mission_nm").val();
			var target_id = $('#target').val();
			var subject_id = $('#subject_name').val();
			var node = $("input[name='node[]']").map(function(){return $(this).val();}).get();
			var Edate = $('input[name=enddate]:checked').val(); //$('input[name=radio使用的name的值]:checked').val()
			var enddate;
			var semester = $("#seme").val();
			var mission_class = $("#mission_class").val();
			var mission_type = $("#mission_type").val();
			var table = document.getElementById("indicator");
			var table2 = document.getElementById("mission_ind");
			if(Edate == '0' || $('#limittime').val()==''){
				enddate ='0000-00-00';
			}else enddate = $('#limittime').val();
			//$('input[name^="node"]').each(function() {
				console.log('3:',node);
			//});
				$unique = $.unique(node.slice(0));
				
			if ($unique.length != node.length) {
				alert('新增任務內容重複，請移除！');
			} else {
			$.ajax({
				type : "POST" ,
				url : "modules/assignMission/prodb_assignment_data.php" ,
				data :{mission_nm : mission_nm, target_id: target_id, node: node, enddate: enddate, subject_id:subject_id,
					semester: semester, mission_class: mission_class, mission_type: mission_type, type: '1'},
				dataType:'text',
				success: function(data){
					var div_content = "";	
					var url = 'modules.php?op=modload&name=assignMission&file=assignment';			
					//$("#missionDiv").html(div_content);
					$( "#add_msg" ).dialog("open");
					//alert('save success');
					//location.href = url;
				},
				error: function(xhr, ajaxOptions, thrownError){
					alert('error...');
					//alert(xhr.status);//0: 请求未初始化
					//alert(thrownError);
				}
			});
			}
		}
	);
	$("#OKBtn2").click(
		function(){
			var mission_nm = $("#mission_nm").val();
			var class_id = $('#target').val();
			var subject_id = $('#exam_subject').val();
			var node = $("input[name='ep_id[]']").map(function(){return $(this).val();}).get();
			//var student = $("input[name='stu_id[]']").map(function(){return $(this).val();}).get();
			var exam_type = $("input[name='exam_data[]']:checked").map(function(){return $(this).val();}).get();
			var run_examtype = $('input[name=examrun_type]:checked').val(); //適性全測 or 省題
			var Edate = $('input[name=enddate]:checked').val(); //$('input[name=radio使用的name的值]:checked').val()
			var Sdate = $('input[name=strdate]:checked').val();
			var enddate, target_id;
			var semester = $("#seme").val();
			var mission_class = $("#mission_class").val();
			var mission_type = $("#mission_type").val();
			var table = document.getElementById("exam_ep");
			var table2 = document.getElementById("mission_EP");
			var endYear = $("input[name='endYear[]']").map(function(){return $(this).val();}).get();
			if(mission_class =='I' || mission_class =='B') target_id = stu_array;
			else target_id = class_id; 
			if(Edate == '0' || $('#limittime').val()==''){
				enddate ='0000-00-00';
			}else enddate = $('#limittime').val();
			if(Sdate == '0'){
				strdate ='0000-00-00';
			}else strdate = $('#starttime').val();
			//$('input[name^="node"]').each(function() {
				console.log('subject_id:',subject_id);
			//});
				$unique = $.unique(node.slice(0));
					
			if ( $unique.length != node.length ) {
				alert('新增任務內容重複，請移除！');
			} else if(mission_nm =='') {
				alert('請輸入任務名稱！');
			} else if(node.length < 1) {
				alert('請建立任務內容！');
			}else {
			$.ajax({
				type : "POST" ,
				url : "modules/assignMission/prodb_assignment_data.php" ,
				data :{mission_nm: mission_nm, target_id: target_id, subject_id: subject_id, node: node, enddate: enddate, 
					semester: semester, mission_class: mission_class, mission_type: mission_type, strdate: strdate, type: '1', 
					data_type: exam_type, endYear: endYear, run_examtype: run_examtype},
				dataType:'text',
				success: function(data){
					var div_content = "";
					var url = 'modules.php?op=modload&name=assignMission&file=mission_check';		
					//$("#missionDiv").html(div_content);
					//$( "#add_msg" ).dialog("open");
					alert('任務新增成功！');
					location.href = url;
				},
				error: function(xhr, ajaxOptions, thrownError){
					alert('error...');
					//alert(xhr.status);//0: 请求未初始化
					//alert(thrownError);
				}
			});
			}
		}
	);
	$('#publisher').change(function() {
		var subject = $("#subject_name").val();
		var publisher = $("#publisher").val();
	    console.log("subject:"+subject+",publisher:"+publisher);
		$.ajax({
		      url: 'modules/learn_video/prodb_concept_info.php',
	          //url: 'prodb_concept_info.php' ,
	          type : "POST" ,
	          data: {'subject':subject, 'call_type':'7', 'publisher':publisher},
	          dataType:'JSON',
	          success: function (returndata) {
	        	  var i = 0;
	        	  //$("#result").text(JSON.stringify(returndata));
	        	  $("#grade").empty();
	        	  $("#grade").append("<option value=\" \">請選擇</option>");
	        	  $.each(returndata, function() {
	        		  /*$("#grade").append("<option label=\"\" value=\"" + returndata[i].grade + 
	    	        	"\">" + returndata[i].grade + "年級</option>");*/	
	        		  $("#grade").append($('<option>', {value:returndata[i].grade}).text(returndata[i].grade + '年級'));
	        		  i++;			
	        	  });
	              //alert('success');
	          },
	          error: function(xhr, ajaxOptions, thrownError){
					alert(xhr.status);
					alert(thrownError);
			  }
		});
	});
	$('#grade').change(function() {
		var subject = $("#subject_name").val();
		var publisher = $("#publisher").val();
		var grade = $('#grade').val();
		var seme = $('#seme').val();
	    console.log("subject:"+subject+",publisher:"+publisher+",grade:"+grade);
		$.ajax({
		      url: 'modules/learn_video/prodb_concept_info.php',
	          //url: 'prodb_concept_info.php' ,
	          type : "POST" ,
	          data: {'subject':subject, 'call_type':'8', 'publisher':publisher, 'grade':grade, 'sems':seme},
	          dataType:'JSON',
	          success: function (returndata) {
	        	  var i = 0;
	        	 // $("#result").text(JSON.stringify(returndata));
	        	  $("#unit").empty();
	        	  $("#unit").append("<option value=\" \">請選擇</option>");
	        	  $.each(returndata, function() {
	        		  /*$("#unit").append("<option label=\"\" value=\"" + returndata[i].unit + 
	    	        	"\">" + returndata[i].unit_name + "</option>");*/	
	        		  $("#unit").append($('<option>', {value:returndata[i].unit}).text( returndata[i].unit_name ));
	        		  i++;			
	        	  });
	              //alert('success');
	          },
	          error: function(xhr, ajaxOptions, thrownError){
					alert(xhr.status);
					alert(thrownError);
			  }
		});
	    
	});
	$('#unit').change(function() {
		var subject = $("#subject_name").val();
		var publisher = $("#publisher").val();
		var grade = $('#grade').val();
		var unit = $('#unit').val();
		var elements = $();
		var table = document.getElementById("indicator");
		var star_url = $('#star_url').val();
	    console.log("subject:"+subject+",publisher:"+publisher+",grade:"+grade+",unit:"+unit);
		$.ajax({
		      url: 'modules/learn_video/prodb_concept_info.php',
	          //url: 'prodb_concept_info.php' ,
	          type : "POST" ,
	          data: {'subject':subject, 'call_type':'9', 'publisher':publisher, 'grade':grade, 'unit':unit },
	          dataType:'JSON',
	          success: function (returndata) {
	        	  var i = 0, i2 = 0;
	        	  var find_node='';
	        	  var emptyRow = document.getElementById("empty");
	        	  
	        	  if(emptyRow){
	        	        emptyRow.parentNode.removeChild(emptyRow)
	        	  }
	        	  //$("#result").text(JSON.stringify(returndata));
	        	  table.innerHTML = "";
	        	  $.each(returndata, function() {
	        		  find_node =find_node+returndata[i2].indicator+'@XX@';
	        		  i2++;	
	        	  });
	        	  console.log(find_node); 
	        	  $.each(returndata, function() {
		        	  var tr = document.createElement('tr');
		        	  	tr.style="vertical-align:middle;";
		        	  	tr.style.backgroundColor="#FFFFFF";         	  
	        	  	  var td = tr.appendChild(document.createElement('td'));
	        	  	  var td2 = tr.appendChild(document.createElement('td'));
		        	  td.innerHTML = '<input type="hidden" name="ind[]" value="'+returndata[i].indicator+'">'+returndata[i].indicator;
		        	  td2.innerHTML = '<input type="button" class="btn05" value="加入" onclick="addMissionInd('+i+', \''+returndata[i].indicator+'\')">';
					if(i == 0){
						var td3 = tr.appendChild(document.createElement('td'));
	        	  	  //td3.width = 600; 
	        	  	  //td3.align="center";
	        	  		td3.rowSpan= returndata.length;
		        		td3.innerHTML = '<iframe src="'+star_url+'&'+'find_node='+find_node+'" width="550px" height="300px" frameborder="0" scrolling="no"></iframe>';
					}
	    	          //elements = elements.add('<input type="hidden" name="ind[]" value="'+returndata[i].indicator+'">'+returndata[i].indicator+'');
	        		  i++;			
	        		  table.appendChild(tr); 
	        	  });
	              //alert('success....'+i);
	          },
	          error: function(xhr, ajaxOptions, thrownError){
					alert(xhr.status);
					alert(thrownError);
			  }
		});
	    
	});
	$('#exam_publisher').change(function() {
		var subject = $("#exam_subject").val();
		var publisher = $("#exam_publisher").val();
	    console.log("subject:"+subject+",publisher:"+publisher);
		$.ajax({
		      url: 'modules/learn_video/prodb_concept_info.php',
	          //url: 'prodb_concept_info.php' ,
	          type : "POST" ,
	          data: {'subject':subject, 'call_type':'7', 'publisher':publisher},
	          dataType:'JSON',
	          success: function (returndata) {
	        	  var i = 0;
	        	  //$("#result").text(JSON.stringify(returndata));
	        	  $("#exam_vol").empty();
	        	  $("#exam_vol").append("<option value=\" \">請選擇</option>");
	        	  $.each(returndata, function() {
	        		  /*$("#exam_vol").append("<option label=\"\" value=\"" + returndata[i].grade + 
	    	        	"\">" + returndata[i].grade + "年級 </option>");*/	
	        		  $("#exam_vol").append($('<option>', {value:returndata[i].grade}).text(returndata[i].grade + '年級 '));
	        		  i++;			
	        	  });
	              //alert('success');
	          },
	          error: function(xhr, ajaxOptions, thrownError){
					alert(xhr.status);
					alert(thrownError);
			  }
		});
	});	
	$('#exam_subject').change(function() {
		$("#exam_publisher").val('');
		$("#exam_vol").val('0');
		$("#exam_unit").val('');
		var subject = $("#exam_subject").val();
		$.ajax({
		      url: 'modules/learn_video/prodb_concept_info.php',
	          //url: 'prodb_concept_info.php' ,
	          type : "POST" ,
	          data: {'subject':subject, 'call_type':'11'},
	          dataType:'JSON',
	          success: function (returndata) {
	        	  var i = 0;
	        	  console.log("map_sn:"+returndata[i].map_sn);
	        	  $("#star_map").val(returndata[i].map_sn);
	        	  $("#exam_publisher").empty();
	        	  $("#exam_publisher").append("<option value=\" \">請選擇</option>");
	        	  $.each(returndata, function() {
		        	  if(i >0){
	        		  /*$("#exam_publisher").append("<option label=\"\" value=\"" + returndata[i].publisher_id + 
	    	        	"\">" + returndata[i].publisher + "</option>");*/
	        		  $("#exam_publisher").append($('<option>', {value:returndata[i].publisher_id}).text( returndata[i].publisher ));
		        	  }	
	    	          i++;		
	        	  });
	          },
	          error: function(xhr, ajaxOptions, thrownError){
					alert(xhr.status);
					alert(thrownError);
			  }
		});
	});

	$('#exam_vol').change(function() {
		var subject = $("#exam_subject").val();
		var publisher = $("#exam_publisher").val();
		var vol = $("#exam_vol").val();
		var seme = 0; //$('#seme').val();
	    console.log("subject:"+subject+",publisher:"+publisher+",vol:"+vol+",seme:"+seme);
		$.ajax({
		      url: 'modules/learn_video/prodb_concept_info.php',
	          //url: 'prodb_concept_info.php' ,
	          type : "POST" ,
	          data: {'subject':subject, 'call_type':'8', 'publisher':publisher, 'grade':vol, 'sems':seme},
	          dataType:'JSON',
	          success: function (returndata) {
	        	  var i = 0;
	        	  //$("#result").text(JSON.stringify(returndata));
	        	  $("#exam_unit").empty();
	        	  $("#exam_unit").append("<option value=\" \">請選擇</option>");
	        	  $.each(returndata, function() {
	        		  var yearSems = returndata[i].sems.substr(3,1);
	        		  if(yearSems == '1') desc ='(上)' ;
		        	  else desc = '(下)';
	        		  /*$("#exam_unit").append("<option sems=\""+returndata[i].sems+"\" value=\"" + returndata[i].unit + 
	    	        	"\">"+desc+" 第"+ returndata[i].unit + "單元【"  + returndata[i].unit_name + "】</option>");*/	
	        		  $("#exam_unit").append($('<option>', {sems:returndata[i].sems,value:returndata[i].unit}).text(desc+" 第"+ returndata[i].unit + "單元【" + returndata[i].unit_name + '】'));
	        		  i++;			
	        	  });
	              //alert('success');
	          },
	          error: function(xhr, ajaxOptions, thrownError){
					alert(xhr.status);
					alert(thrownError);
			  }
		});
	});
	
	$('#exam_unit').change(function() {
		var subject = $("#exam_subject").val();
		var publisher = $("#exam_publisher").val();
		var grade = $("#exam_vol").val();
		var unit = $("#exam_unit").val();
		var table = document.getElementById("exam_ep");
		var map_no = $('#star_map').val();
		var sems = $('option:selected', this).attr('sems');
		console.log("subject:"+subject+",publisher:"+publisher+",grade:"+grade+",unit:"+unit+",map_no:"+map_no+",sems:"+sems);
		//var EP_id = publisher+subject+vol+unit+paper_vol;
		$.ajax({
		      url: 'modules/learn_video/prodb_concept_info.php',
	          //url: 'prodb_concept_info.php' ,
	          type : "POST" ,
	          data: {'subject':subject, 'call_type':'9', 'publisher':publisher, 'grade':grade, 'unit':unit, 'map_no':map_no, 'sems':sems },
	          dataType:'JSON',
	          success: function (returndata) {
	        	  var i = 0, i2 = 0;
	        	  var find_node='';
	        	  var emptyRow = document.getElementById("empty");
	        	  
	        	  if(emptyRow){
	        	        emptyRow.parentNode.removeChild(emptyRow)
	        	  }
	        	  //$("#result").text(JSON.stringify(returndata));
	        	  table.innerHTML = "";
	        	  $.each(returndata, function() {
	        		  find_node =find_node+returndata[i2].indicate_id+'@XX@';
	        		  i2++;	
	        	  });
	        	  console.log(find_node); 
	        	  $.each(returndata, function() {
		        	  var tr = document.createElement('tr');
		        	  	tr.style="vertical-align:middle;";
		        	  	tr.style.backgroundColor="#FFFFFF";     
	        	  	  var td3 = tr.appendChild(document.createElement('td'));		        	  	    	  
	        	  	  var td = tr.appendChild(document.createElement('td'));
	        	  		  td.style.width="60%";
	        	  	  var td2 = tr.appendChild(document.createElement('td'));
	        	  	  var Year = '1';
	        	  	  td3.innerHTML = returndata[i].indicate_id;
		        	  td.innerHTML = '<input type="hidden" name="ind[]" value="'+returndata[i].indicate_id+'"><div class="show" title="'+returndata[i].indicate_id+'">'+returndata[i].indicate_name+'</div>';
		        	  td2.innerHTML = '<input type="button" class="btn05" value="加入" onclick="addMissionEP('+i+', \''+returndata[i].indicate_id+'\',\''+returndata[i].indicate_name+'\',\''+Year+'\')">';
					if(i == 0){
						var td3 = tr.appendChild(document.createElement('td'));
	        	  	  //td3.width = 600; 
	        	  	  //td3.align="center";
	        	  		td3.rowSpan= returndata.length;
		        	  //td3.innerHTML = '<iframe src="'+star_url+'&map_sn='+map_no+'&find_nodes='+find_node+'" width="550px" height="300px" frameborder="0" scrolling="no"></iframe>';
					}
	    	          //elements = elements.add('<input type="hidden" name="ind[]" value="'+returndata[i].indicator+'">'+returndata[i].indicator+'');
	        		  i++;			
	        		  table.appendChild(tr); 
	        	  });
	              //alert('success....'+i);
	          },
	          error: function(xhr, ajaxOptions, thrownError){
					alert(xhr.status);
					alert(thrownError);
			  }
		});
	});
	$('#mission_EP').on('click', '.btn05', function(e){
		   $(this).closest('tr').remove()
	});
	$('#mission_ind').on('click', '.btn05', function(e){
		   $(this).closest('tr').remove()
	});

	$('#mission_class').change(function() {
		var select_class = $('option:selected', this).attr('class');
		$('#target').val(select_class);
	});
});

function switch_class(s){
	if(s =='I'){
		veno_chick.style.display='';
		batch_chick.style.display='none';
	}
	if(s =='C'){
		veno_chick.style.display='none';
		batch_chick.style.display='none';
	}
	if(s =='B'){
		batch_chick.style.display='';
		veno_chick.style.display='none';
	}
}

function apply_btn(){
	while(stu_array.length > 0) {
		stu_array.pop();
	}

	jQuery("input[name='stu_id[]']").each(function() {
		if(jQuery(this).prop('checked')){
			var data = $(this).val();
			stu_array.push(data);
		}
	});
	jQuery('.vbox-close, .vbox-overlay').trigger('click');

}



</script>
</head>
<?php 
$indicator = array();
if($user_data->class_name < 10){
	$class_no = '0'.$user_data->class_name;
}else $class_no = $user_data->class_name;

$target_class = $user_data->organization_id.'-'.$user_data->grade.$class_no;
$class_nm = $user_data->grade.'年'.$user_data->class_name.'班';

$date = date ("Y-m-d");
$user_id = $_SESSION['user_id'];
$this_seme = getYearSeme();

$start_url  = "modules/D3/app/index_view.php?aa=".base64_encode($_SESSION[user_id])."";
//教師教授班級
$teach_class = $dbh->prepare("SELECT DISTINCT teacher_id, grade, class FROM seme_teacher_subject a
		WHERE organization_id = :organization_id AND teacher_id = :teacher_id AND seme_year_seme = :seme");
$teach_class->bindValue(':organization_id', $user_data->organization_id, PDO::PARAM_INT);
$teach_class->bindValue(':teacher_id', $user_id, PDO::PARAM_INT);
$teach_class->bindValue(':seme', $this_seme, PDO::PARAM_INT);
$teach_class->execute();
$teach_sub = $teach_class->fetchAll(\PDO::FETCH_ASSOC);
$class_count = $teach_class->rowCount();

//班級人數
function class_num($dbh2, $org, $grade, $class){
	$class_data = $dbh2->prepare("SELECT * FROM user_info a, user_status b
			WHERE a.user_id = b.user_id and b.access_level IN ('1','9','8') and organization_id = :organization_id AND grade = :grade AND class = :class");
	$class_data->bindValue(':organization_id', $org, PDO::PARAM_STR);
	$class_data->bindValue(':grade', $grade, PDO::PARAM_INT);
	$class_data->bindValue(':class', $class, PDO::PARAM_INT);
	$class_data->execute();
	$sub_class = $class_data->fetchAll(\PDO::FETCH_ASSOC);
	//print_r($sub_class);
	return $sub_class;
}

//科目
$subject_data = $dbh->prepare("SELECT subject_id, name as subject_nm 
		FROM subject WHERE display = :display");
$subject_data->bindValue(':display', '1', PDO::PARAM_STR);
$subject_data->execute();
$sub_d = $subject_data->fetchAll(\PDO::FETCH_ASSOC);

//出版社
if( isset($_POST['exam_subject']) && ($_POST['exam_subject'] !=' ')){
	$pub_data = $dbh->prepare("SELECT DISTINCT a.publisher_id, a.publisher
				FROM publisher a, publisher_mapping b WHERE b.subject_id = :subject AND a.remark = :remark AND a.publisher_id = b.publisher_id AND b.indicator !=0");
	$pub_data->bindValue(':subject', $_POST['exam_subject'], PDO::PARAM_INT);
	$pub_data->bindValue(':remark', '1', PDO::PARAM_STR);
	$pub_data->execute();
	$pub_d = $pub_data->fetchAll(\PDO::FETCH_ASSOC);
}


//縱貫式下拉選單
$ep_pub_data = $dbh->prepare("SELECT DISTINCT a.publisher_id, b.publisher FROM concept_info a, publisher b 
		WHERE a.publisher_id = b.publisher_id AND b.remark = :remark");
$ep_pub_data->bindValue(':remark', '2', PDO::PARAM_STR);
$ep_pub_data->execute();
$ep_pub_row = $ep_pub_data->fetchAll(\PDO::FETCH_ASSOC);

$ep_pub_data2 = $dbh->prepare("SELECT DISTINCT a.subject_id, b.name as subject_nm FROM concept_info a, subject b WHERE a.subject_id = b.subject_id");
$ep_pub_data2->execute();
$ep_pub_row2 = $ep_pub_data2->fetchAll(\PDO::FETCH_ASSOC);
//print_r($ep_pub_row2);

//年級
if( isset($_POST['subject_name']) && ($_POST['subject_name'] !=' ')){
	$grade_data = $dbh->prepare("SELECT DISTINCT grade FROM publisher_mapping
				where subject_id = :subject and publisher_id = :publisher");
	$grade_data->bindValue(':subject', $_POST['subject_name'], PDO::PARAM_STR);
	$grade_data->bindValue(':publisher', $_POST['publisher'], PDO::PARAM_STR);
	$grade_data->execute();
	$sub_grade = $grade_data->fetchAll(\PDO::FETCH_ASSOC);
}

//單元
if( isset($_POST['grade']) && ($_POST['grade'] !=' ')){
	$unit_data = $dbh->prepare("SELECT DISTINCT unit FROM publisher_mapping c
			where c.subject_id = :subject and c.publisher_id = :publisher
			 and c.grade = :grade ORDER BY unit");
	$unit_data->bindValue(':subject', $_POST['subject_name'], PDO::PARAM_INT);
	$unit_data->bindValue(':publisher', $_POST['publisher'], PDO::PARAM_INT);
	$unit_data->bindValue(':grade', $_POST['grade'], PDO::PARAM_INT);
	$unit_data->execute();
	$sub_unit = $unit_data->fetchAll(\PDO::FETCH_ASSOC);
}

//任務查詢
$mission_data = $dbh->prepare("SELECT a.mission_sn, a.mission_nm, a.target_type, a.node, b.user_id, b.nodes_pass, b.all_pass 
		FROM mission_info as a left JOIN mission_result as b on (a.mission_sn = b.mission_sn)
		WHERE teacher_id = :teacher_id ORDER BY a.mission_sn DESC");
$mission_data->bindValue(':teacher_id', $user_id, PDO::PARAM_STR);
//$mission_data->bindValue(':mission_type', $_POST['mission_type'], PDO::PARAM_STR);
//$mission_data->bindValue(':target_id', $_POST['target_id'], PDO::PARAM_STR);
//$mission_data->bindValue(':create_date', $_POST['create_date'], PDO::PARAM_STR);
$mission_data->execute();
$sub_mission = $mission_data->fetchAll(\PDO::FETCH_ASSOC);

//已完成人數
function finish_count($dbh2, $ms_sn){
	$finish_num = 0;
	$finish_data = $dbh2->prepare("SELECT COUNT(*) num FROM mission_result WHERE mission_sn = :ms_sn AND all_pass = :all_pass");
	$finish_data->bindValue(':ms_sn', $ms_sn, PDO::PARAM_INT);
	$finish_data->bindValue(':all_pass', '1', PDO::PARAM_INT);
	$finish_data->execute();
	$sub_finish = $finish_data->fetch(\PDO::FETCH_ASSOC);
	return $sub_finish["num"];
}

function update_finish($dbh2, $user, $ind){
	$finish_data2 = $dbh2->prepare("SELECT a.user_id, b.indicator, MAX(finish_rate) rate FROM `video_review_record` a, video_concept_item b 
			WHERE a.video_item_sn = b.video_item_sn AND a.user_id = :user_id AND b.indicator = :indicator
			GROUP BY a.user_id, b.indicator HAVING MAX(finish_rate) = 100");
	$finish_data2->bindValue(':user_id', $user, PDO::PARAM_INT);
	$finish_data2->bindValue(':indicator', $ind, PDO::PARAM_INT);
	$finish_data2->execute();
	$sub_finish2 = $finish_data2->fetchAll(\PDO::FETCH_ASSOC);
	if(count($sub_finish2) > 0){
		return true;
	}else return false;
}

function update_data($dbh2, $ms_sn){
	$pass = false;
	$finish_data3 = $dbh2->prepare("SELECT a.node, a.target_id, a.target_type, a.mission_type FROM mission_info a
			WHERE mission_sn = :ms_sn");
	$finish_data3->bindValue(':ms_sn', $ms_sn, PDO::PARAM_INT);
	$finish_data3->execute();
	$sub_finish3 = $finish_data3->fetch(\PDO::FETCH_ASSOC);
	
	$node_arr = explode(_SPLIT_SYMBOL, $sub_finish3['node']);
	if(empty($node_arr[count($node_arr)]) && count($node_arr)>1 ){
		unset($node_arr[count($node_arr)-1]);
	}
	//print_r($node_arr);
	if($sub_finish3["target_type"] =='C'){
		$target = explode('-',$sub_finish3["target_id"]);
	}
	//echo substr($target[1], 1, 3);
	$class_num = $dbh2->prepare("SELECT a.user_id FROM user_info a, user_status b
		WHERE a.user_id = b.user_id and b.access_level IN ('1','9','8') and organization_id = :organization_id 
			AND grade = :grade AND class = :class");
	$class_num->bindValue(':organization_id', $target[0], PDO::PARAM_STR);
	$class_num->bindValue(':grade', substr($target[1], 0, 1), PDO::PARAM_STR);
	$class_num->bindValue(':class', substr($target[1], 1, 3), PDO::PARAM_INT);
	$class_num->execute();
	$sub_classnum = $class_num->fetchAll(\PDO::FETCH_ASSOC);
	//print_r($sub_classnum);
	
	if($sub_finish3["mission_type"] =='1'){
		foreach ($sub_classnum as $key => $value){
			foreach ($target as $key2 => $value2){
				$pass = update_finish($dbh2, $value["user_id"], $value2);
				//echo 'pass:'.$pass;
				if(!$pass) break;
			}
			if($pass){
				$time = date ("YmdHis");
				
				$result_query = $dbh2->prepare("SELECT * FROM mission_result a
				WHERE a.user_id = :user_id and mission_sn = :mission_sn");
				$result_query->bindValue(':user_id', $value["user_id"], PDO::PARAM_STR);
				$result_query->bindValue(':mission_sn', $ms_sn, PDO::PARAM_STR);
				$result_query->execute();
				$sub_result = $result_query->fetchAll(\PDO::FETCH_ASSOC);
				
				if(count($sub_result) == 0){
					$result = $dbh2->prepare("INSERT INTO mission_result (`user_id`, `mission_sn`, `nodes_pass`, `all_pass`, `update_date`)
					VALUES (:user_id, :mission_sn, :nodes_pass, :all_pass, :update_date)");
					$result->bindValue(':user_id', $value["user_id"], PDO::PARAM_STR);
					$result->bindValue(':mission_sn', $ms_sn, PDO::PARAM_STR);
					$result->bindValue(':nodes_pass', $sub_finish3['node'], PDO::PARAM_STR);
					$result->bindValue(':all_pass', '1', PDO::PARAM_STR);
					$result->bindValue(':update_date', $time, PDO::PARAM_STR);
					$result->execute();
				}
			}
		}
		
	}
}



?>
<body>
<div id="result"></div>
<!-- <div id="content" class="content-Box"> -->
	<div class="content2-Box">
    	<div class="path">目前位置：任務指派</div>
       <div class="main-box">
       		<div class="left-box discuss-select">
               <a href="modules.php?op=modload&name=assignMission&file=assignment" class="btn02 current">指派任務</a>
               <a href="modules.php?op=modload&name=assignMission&file=mission_check" class="btn02">任務進度</a>
               <a href="modules.php?op=modload&name=assignMission&file=mission_modify" class="btn02">任務維護</a>
            </div>
			<div class="right-box">
				<div class="title01">步驟一：選擇任務</div>
				<div class="choice-box">
	               	<div class="choice-title">任務</div>
	                	<ul class="choice work-cholic">
	                  		<li><a href="modules.php?op=modload&name=assignMission&file=assignment2"><i class="fa fa-caret-right"></i>知識結構學習</a></li>
	                  		<li><a href="modules.php?op=modload&name=assignMission&file=assignment3" class="current"><i class="fa fa-caret-right"></i>縱貫診斷測驗</a></li>  
	                  		<li><a href="modules.php?op=modload&name=assignMission&file=assignment"><i class="fa fa-caret-right"></i>單元診斷測驗</a></li> 
	                  		<input type="hidden" id="mission_type" name="mission_type" value="3">
	                	</ul> 	
				</div>
				<div class="title01">步驟二：任務建立</div>
					<div class="class-list2 test-search">
                <div class="work-box-33" style="width: calc(50% - 5px);">
                任務名稱：<br>
                <input type="text" id="mission_nm" name="mission_nm" placeholder="請輸入任務名稱">
                </div>
                <div class="work-box-33" style="width: calc(50% - 5px);">
                對象：<br>
                <select id="mission_class" name="mission_class" onChange="switch_class(this.options[this.selectedIndex].value)">
                <?php foreach ($teach_sub as $tclass=>$tvalue){
                		if($tvalue["class"] < 9){
                			$tmp_class = '0'.$tvalue["class"];
                		}else $tmp_class = $tvalue["class"];
                		
                		$class = $user_data->organization_id.'-'.$tvalue["grade"].$tmp_class;
                		if($target_class == $class){
                			$tmp_select= "selected=\"selected\"";
                		}else $tmp_select ='';
                	?>
                	<option value="C" class="<?php echo $class;?>" <?php echo $tmp_select;?>>全班(<?php echo $tvalue["grade"].'年'.$tvalue["class"].'班';?>)</option>
                <?php }
                	if($class_count == 0){
                ?><option value="C" class="<?php echo $target_class;?>" >全班(<?php echo $class_nm;?>)</option>
                <?php }?>
                	<option value="B">多個班級</option>
                	<option value="I">個別學生</option>
                </select><input type="hidden" id="target" value="<?php echo $target_class?>" size="10">
                <div id="veno_chick" style="display:none;">
                	<a id="SelectStudent" class="venoboxinline btn01" data-title="挑選學生" data-gall="gall-frame" data-type="inline" href="#inline-content_class" style="width:250px; display:inline-block; min-width:inherit; vertical-align:top; margin:0;">挑選學生</a>
                	<div id="inline-content_class" class="personal-inline">
                        <ul class="choice-student" style="width: 100%;">
                        <div id="msg" style="width: 100%;"></div>
                        </ul>
                        <div align="center"><a href="#" class="btn04" onclick="apply_btn()">加入</a></div>
                    </div>
                  </div>
                  
                  <!--  新增批次指派班級   BlueS 20170707  第一個陣列為"patch" -->
					<div id="batch_chick" style="display:none;">
					<a class="venoboxinline btn01" data-title="挑選班級" data-gall="gall-frame" data-type="inline" href="#inline-content-batch" style="width:250px; display:inline-block; min-width:inherit; vertical-align:top; margin:0;">同時指派多個班級</a>
                	<div id="inline-content-batch" class="personal-inline">
                        <ul class="choice-student">
                        <?php
                        
                        	foreach ($teach_sub as $key=>$value) {
                        		if($value["class"] < 10){
                        			$sclass = '0'.$value["class"];
                        		}else $sclass = $value["class"];
                        	?>
                        		<li><span><input name="stu_id[]" type="checkbox" value="<?php echo $user_data->organization_id."-".$value["grade"].$sclass;?>" /></span><span><?php echo $value["grade"]."年".$value["class"]."班";?></span></li>
                        <?php };?>
                        </ul>
                        <div align="center"><a href="#" class="btn04" onclick="apply_btn()" >加入</a></div>
                    </div>
                    </div>
					<!-- 新增批次指派班級   BlueS 20170707 -->
                  
				</div>
				<div class="work-box-33" style="width: calc(50% - 5px);"> 
                開始時間：<br>
                <span><input type="radio" name="strdate" value="0" checked> 不設限&nbsp;&nbsp;</span><span> <input type="radio" id="strdate" name="strdate" value="1"> <input type="datetime-local" id="starttime" class="input-normal" style="min-height: 34px; font-size: 19px;"></span>
                </div>
                <div class="work-box-33" style="width: calc(50% - 5px);">
                完成時限：<br>
                <span><input type="radio" name="enddate" value="0" checked> 不設限&nbsp;&nbsp;</span><span> <input type="radio" id="enddate" name="enddate" value="1"> <input type="date" id="limittime" class="input-normal" placeholder="yyyy/mm/dd" style="width:200px;"></span>
                </div>
               </div>
               <div class="after-20"></div>
               <div class="title01">步驟三：建立任務類型</div>
            	<div class="class-list2 test-search table_scroll">
            	<input type="hidden" id="seme" name="seme" value="<?php echo getYearSeme();?>">
            		<font class="color-blue">診斷類型：<input type="radio" id="examrun_type" name="examrun_type" value="0" checked>全測 <input type="radio" id="examrun_type" name="examrun_type" value="1" >適性省題</font>
            		<br><!--  -->科目：<select id="exam_subject" class="input-normal"><option value="">請選擇</option>
						<?php foreach ($sub_d as $key=>$value){?>
						<option value="<?php echo $value["subject_id"];?>"><?php echo $value["subject_nm"];?></option>
						<?php }?>
						</select>
            		版本：<select id="exam_publisher" class="input-normal"><option value="">請選擇</option>
						<?php foreach ($pub_d as $key=>$value){?>
						<option value="<?php echo $value["publisher_id"];?>"><?php echo $value["publisher"];?></option>
						<?php }?>
						</select>
					年級：<select id="exam_vol" class="input-normal"><option value="0">預設</option></select>
					單元：<select id="exam_unit" class="input-normal"><option value="">無</option></select>
					
					<input type="hidden" id="star_map">
					<div class="result-box table_scroll">
						<table class="datatable" id=exam_ep>
							<thead>
								<tr><td>診斷測驗(能力指標)</td><td>能力指標說明</td><td>請點選加入按鈕</td></tr>
							</thead>
							<tbody>
								<tr>
									<td align="center" bgcolor="#FFFFFF" id="empty12" colspan="3">請選擇上方版本/單元等條件，過濾診斷測驗之能力指標</td>
								</tr>
							</tbody>
							<!-- <tfoot>
								<tr><td><input type="button" value="全部加入" class="btn05"></td><td><input type="button" value="全部取消" class="btn05"></td></tr>
							</tfoot> -->
						</table>
					</div>
            	</div>
            	<div class="after-20"></div>
            	<div class="title01">步驟四：建立任務內容</div>
            	<div class="table_scroll">
            		<table class="datatable" id="mission_EP">
							<tr><th width="5%">&nbsp;</th><th>能力指標</th><th width="50%">指標內容說明</th><th>終止年級</th><th>移除</th></tr>
							<tr>
								<td align="center" bgcolor="#FFFFFF" id="empty22" colspan="5">請點選測驗加入按鈕增加任務內容</td>
							</tr>
					</table>
					<div align="center">
					<input type="button" id="OKBtn2" class="btn04" value="完成" style="width:100px;"> <input type="button" id="cancelBtn2" class="btn04" value="取消" style="width:100px;"></div>
            	</div>
					
			</div>
				
			</div>
		</div>
<!-- 	<div class="bottom-pto"><img src="images/content-bg.png"></div>
	</div>
</body>
</html>	 -->