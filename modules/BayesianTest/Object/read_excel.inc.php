<?php
require_once 'Excel/reader.php';
function read_excel($path){
	      $excel_data=new Spreadsheet_Excel_Reader();
              $excel_data->setOutputEncoding('utf-8');
              $excel_data->read($path);
               for ($i = 2; $i <= $excel_data->sheets[0]['numRows']; $i++) {
	          for ($j = 2; $j <= $excel_data->sheets[0]['numCols']; $j++) {
				  if($excel_data->sheets[0]['cells'][$i][$j]!=NULL){
	              $data[$i-2][$j-2]=$excel_data->sheets[0]['cells'][$i][$j];}
                  }
               }
              return $data;

	 }
?>