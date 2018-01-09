<?php
/*
20171129  新增按下重新讀取時，重新讀取暫存紀錄。

edit by fuyun
*/


/*
-1：還沒做
0：錯
1：對
2：預測對
3：預測對但做錯
4：預測對也做對
*/

CLASS indicateAdaptiveTestStructure{
  
  //SET
  private $sNode_items=2; //設定每個小節點題數
  
  private $mission_sn;  //任務代碼
  private $mission_data;  //任務資料
    /*
    mission_data['upmost_nodes']：最上層 bNode，array
    mission_data['subject_id']：學科
    mission_data['exam_type']：測驗類型，0適性全測，1適性選題
    mission_data['endYear']：節點終止年級，array
    mission_data['map_sn']：map_sn
    */
  private $node_link_data;  //節點的上下位關係及出題順序
    /*
    node_link_data['bNodes_link']['origin'][上位node][下位node]=0 or 1，上下位節點是否存在。所有的 bNodes。無遞移性。
    node_link_data['bNodes_link']['transitivity'][上位node][下位node]=0 or 1，上下位節點是否存在。所有的 bNodes。有遞移性。
    node_link_data['sNodes_link'][bNode]['origin'][上位node][下位node]=0 or 1，上下位節點是否存在。所有的 sNodes。無遞移性。
    node_link_data['sNodes_link'][bNode]['transitivity'][上位node][下位node]=0 or 1，上下位節點是否存在。所有的 sNodes。有遞移性。
    node_link_data['bNodes_origin_link_DESC'][下位node][上位node]=0 or 1，上下位節點是否存在。所有的 bNodes。無遞移性。
    node_link_data['sNodes_origin_link_DESC'][bNode][下位node][上位node]=0 or 1，上下位節點是否存在。所有的 sNodes。無遞移性。    
    node_link_data['bNodes_order'] 大節點出題順序。
    node_link_data['sNodes_order'] 小節點出題順序。(決定順序：大節點->小節點)
    */
  private $bNode_sNodes_count;  //大節點中的小節點數量，bNode_sNodes_count[bNode]
  private $record_bNodes_status;  //大節點測驗紀錄，record_bNodes_status[bNode]
  private $record_sNodes_status;  //小節點測驗紀錄，record_sNodes_status[sNode]
  private $sNode_now; //目前施測試題組的 sNode
  private $step;  //目前施測的階段。1：適性；2：適性全測
  private $sNode_items_left;  //小節點剩餘 item_sn，array，sNode_items_left[]=item_sn
  private $sNode_response;  //目前小節點內的作答反應，不受上位節點影響，只有 o or 1
  private $item_num;  //題號
  private $record_answer; //原始作題紀錄
  private $record_response; //二元作答記錄
  private $record_client_time;  //各題作答時間紀錄
  private $record_item_sn;  //item_sn紀錄
  private $during_time; //總作答時間
  private $start_time;  //試卷開始時間
  private $start_date;  //試卷開始日期
  private $total_items; //總題數
  
  function __construct($mission_sn){
    $this->mission_sn=$mission_sn;
    //取得任務資料
    $this->mission_data=$this->getMissionData();
    //取得節點的上下位關係及出題順序
    $this->node_link_data=$this->getNodeLinkData($this->mission_data['upmost_nodes'],$this->mission_data['subject_id'],$this->mission_data['endYear']);
    $this->node_link_data['bNodes_origin_link_DESC']=$this->getLinkDESC($this->node_link_data['bNodes_link']['origin']);
    foreach($this->node_link_data['bNodes_order'] as $val){
      $this->node_link_data['sNodes_origin_link_DESC'][$val]=$this->getLinkDESC($this->node_link_data['sNodes_link'][$val]['origin']);      
    }
    //取得大節點中的小節點數量
    $this->bNode_sNodes_count=$this->getSNodesCount();
    foreach($this->node_link_data['bNodes_order'] as $val){
      $this->record_bNodes_status[$val]=0;
    }
    foreach($this->node_link_data['sNodes_order'] as $val){
      $this->record_sNodes_status[$val]=-1;
    }
    $this->total_items=$this->getTotalItems();
    $this->step=1;  //先進行適性階段
    $this->item_num=0;
    $this->during_time=0;
    $this->start_time=date("U"); //開始時間
    $this->start_date=date("Y-m-d, H:i"); //測驗日期
  }
  
  public function getNextItem(){

    
    $i=0;
    $get_next_item=0;
    $count_left_items=$this->sNode_items;
    while($get_next_item==0){
      if($this->item_num==0){
        //debug_msg("第".__LINE__."行 this->node_link_data[sNodes_order] ", $this->node_link_data['sNodes_order']);
        $this->sNode_now=$this->node_link_data['sNodes_order'][$i];
        //debug_msg("第".__LINE__."行 this->sNode_now ", $this->sNode_now);
        $this->sNode_items_left_tmp=$this->getSNodeItems($this->sNode_now);
        //debug_msg("第".__LINE__."行 this->sNode_items_left_tmp ", $this->sNode_items_left_tmp);
        if(count($this->sNode_items_left_tmp)==0){
          unset($this->record_sNodes_status[$this->sNode_now]);
          $i++;
          continue;
        }
        for($i=0;$i<$count_left_items;$i++){
          if($this->sNode_items_left_tmp[$i]>0){
            $this->sNode_items_left[]=$this->sNode_items_left_tmp[$i];
          }          
        }
        $get_next_item=1;      
      }else{
        if(count($this->sNode_items_left)==0){
          if($this->sNode_now!=''){
            $this->modifyNodeStatus();
            if($this->step==1){
              $this->modifyLowNodeStatus();
            }
            $this->checkExamEnd();
            //debug_msg("第".__LINE__."行 this->step ", $this->step);
          }
          
          $this->sNode_now=$this->getNextSNode();
          if($this->sNode_now==''){$this->checkExamEnd();}
          //debug_msg("第".__LINE__."行 this->sNode_now ", $this->sNode_now);
          unset($this->sNode_response);
          $this->sNode_items_left_tmp=$this->getSNodeItems($this->sNode_now);
          //debug_msg("第".__LINE__."行 this->sNode_items_left_tmp ", $this->sNode_items_left_tmp);
          if(count($this->sNode_items_left_tmp)==0){continue;}

          foreach($this->record_item_sn as $key=>$val){
            if(in_array($val,$this->sNode_items_left_tmp)){
              $this->sNode_response[]=$this->record_response[$key];
              $key_tmp=array_search($val,$this->sNode_items_left_tmp);
              unset($this->sNode_items_left_tmp[$key_tmp]);
              $this->sNode_items_left_tmp=array_values($this->sNode_items_left_tmp);
              $count_left_items-=1;            
            }
          }
          
          if($count_left_items>0){
            for($i=0;$i<$count_left_items;$i++){
              if($this->sNode_items_left_tmp[$i]>0){
                $this->sNode_items_left[]=$this->sNode_items_left_tmp[$i];
                $get_next_item=1;
              }          
            }        
          }                                 
        }else{
          $get_next_item=1;
        }  
        //暫存
        $this->examPause();
      }      
    }
    //debug_msg("第".__LINE__."行 this->step ", $this->step);
    //debug_msg("第".__LINE__."行 小節點出題順序 ", $this->node_link_data['sNodes_order']);
    //debug_msg("第".__LINE__."行 this->record_sNodes_status ", $this->record_sNodes_status);
    //debug_msg("第".__LINE__."行 this->record_bNodes_status ", $this->record_bNodes_status);
    //debug_msg("第".__LINE__."行 this->sNode_now ", $this->sNode_now);
    //debug_msg("第".__LINE__."行 this->record_item_sn ", $this->record_item_sn);
    //debug_msg("第".__LINE__."行 this->sNode_items_left ", $this->sNode_items_left);
    //debug_msg("第".__LINE__."行 this->mission_data[exam_type] ", $this->mission_data['exam_type']);
    $this->item_num++;
    return $this->sNode_items_left[0];
  }
  
  //檢查是否答對，並做好各項紀錄
  public function checkAns($user_answer,$client_item_idle_time){
    $item_sn_now=$this->sNode_items_left[0];
    $itemAns=$this->getAns($item_sn_now);
    if($user_answer==$itemAns){
      $this->sNode_response[]=1;
      $res_tmp=1;
    }else{
      $this->sNode_response[]=0;
      $res_tmp=0;
    }
    $this->makeRecResponse($res_tmp);
    $this->record_answer[]=$user_answer;
    $this->record_client_time[]=$client_item_idle_time;
    $this->during_time+=$client_item_idle_time;
    $this->record_item_sn[]=$item_sn_now;

    array_shift($this->sNode_items_left);    
  }
  
  //檢查是否有中斷資料，並恢復
  public function checkExamPause(){
    $get_pause_data=$this->getExamPauseData();
    if($get_pause_data){
      //確定 step
      $this->step=1;
      foreach($this->record_sNodes_status as $val){
        if($val==-1 || $val==5){
          $sNode_left++;
        }
      }
      if($sNode_left==0){
        if($this->mission_data['exam_type']==0){
          $this->step=2;
        }
      }
    }
    $this->item_num=count($this->record_item_sn);
    //debug_msg("第".__LINE__."行 this->item_num ", $this->item_num);
    //debug_msg("第".__LINE__."行 this->record_sNodes_status ", $this->record_sNodes_status);
    //debug_msg("第".__LINE__."行 this->record_item_sn ", $this->record_item_sn);
    //debug_msg("第".__LINE__."行 this->step ", $this->step);
    //die();
  }
  
  //取得題號
  public function showItemNum(){return $this->item_num;}
  //取得總題數(不管適性)
  public function showTotalItems(){return $this->total_items;}
  
  public function showBNodeOrder(){return $this->node_link_data['bNodes_order'];}
  //取得小節點出題順序。
  public function showSNodeOrder(){return $this->node_link_data['sNodes_order'];}
  //取得 mission subject id
  public function showMissionSubjectID(){return $this->mission_data['subject_id'];}
  //取得 mission map_sn
  public function showMissionMapSN(){return $this->mission_data['map_sn'];}
  
  public function showMissionName(){return $this->mission_data['mission_nm'];}
  
  public function showBNodeSNodesCount(){return $this->bNode_sNodes_count;}
  
  //取得試題資料
  private function getItemInfo($item_sn){
    $sql='SELECT * FROM concept_item WHERE item_sn=:item_sn';
    $result=$dbh->prepare($sql);
    $result->bindParam(':item_sn',$item_sn, PDO::PARAM_INT);
    $result->execute();  
    while ($row=$result->fetch(PDO::FETCH_ASSOC)){
      $item_info['item_filename']=$row['item_filename'];
      $item_info['op_filename']=explode(_SPLIT_SYMBOL,row['op_filename']);
      if(end($item_info['op_filename'])==''){
        array_pop($item_info['op_filename']);
      }
      $item_info['indicator']=$row['indicator'];
    }
    return $item_info;
  }
  
  //取得任務資料
  private function getMissionData(){
    global $dbh;
    
    $sql='SELECT mission_nm , node , subject_id , mission_type , exam_type , endYear FROM mission_info WHERE mission_sn=:mission_sn';
    $result=$dbh->prepare($sql);
    $result->bindParam(':mission_sn',$this->mission_sn, PDO::PARAM_INT);
    $result->execute();
    $count=$result->rowCount();
    if($count<1){die('<br><br>本測驗暫時停止服務！<br><br>');}  
    while ($row=$result->fetch(PDO::FETCH_ASSOC)){
      if ($row['mission_type']!=3){
        die('<br><br>本測驗暫時停止服務！<br><br>');
      }
      $mission_data['mission_nm']=$row['mission_nm'];
      $mission_data['upmost_nodes']=explode(_SPLIT_SYMBOL,$row['node']);
      if(end($mission_data['upmost_nodes'])==''){
        array_pop($mission_data['upmost_nodes']);
      }
      $mission_data['subject_id']=$row['subject_id'];
      $mission_data['exam_type']=$row['exam_type'];
      $mission_data['endYear']=explode(_SPLIT_SYMBOL,$row['endYear']);
      if(end($mission_data['endYear'])==''){
        array_pop($mission_data['endYear']);
      }
    }
    
    $sql_map='SELECT map_sn FROM map_info WHERE subject_id=:subject_id AND display="1"';
    $result_map=$dbh->prepare($sql_map);
    $result_map->bindParam(':subject_id',$mission_data['subject_id'], PDO::PARAM_INT);
    $result_map->execute();
    while ($row_map=$result_map->fetch(PDO::FETCH_ASSOC)){
      $mission_data['map_sn']=$row_map['map_sn'];  
    }
    
    return $mission_data;    
  }
  
  //決定節點上下位結構的 function。end
  private function getNodeLinkData($upmost_nodes,$subject,$end_years=0){
    global $dbh;
    //找出所有大節點
    $upmost_nodes_array=$upmost_nodes;
    $end_years_array=$end_years;
    //$upmost_nodes_array=explode(_SPLIT_SYMBOL,$upmost_nodes);
    //if(end($upmost_nodes_array)==''){array_pop($upmost_nodes_array);}
    //$end_years_array=explode(_SPLIT_SYMBOL,$end_years);
    //if(end($end_years_array)==''){array_pop($end_years_array);}
    $bNodes_info=$this->getBNodesInfo($upmost_nodes_array,$subject,$end_years_array); 
    //$bNodes_info['link'][上位node][下位node]=1，但是只有1沒有0的node。無遞移性。
    //$bnodes_info['bNodes_array'] 所有 bNodes
    //debug_msg("第".__LINE__."行 bNodes_info ", $bNodes_info);
    $nodesLink['bNodes_link']=$this->getLink($bNodes_info['link'],$bNodes_info['bNodes_array']);
    //nodesLink['bNodes_link']['origin'][上位node][下位node]=0 or 1，上下位節點是否存在。所有的 bNodes。無遞移性。
    //nodesLink['bNodes_link']['transitivity'][上位node][下位node]=0 or 1，上下位節點是否存在。所有的 bNodes。有遞移性。
    //debug_msg("第".__LINE__."行 nodesLink['bNodes_link'] ", $nodesLink['bNodes_link']);

    //小節點
    foreach($nodesLink['bNodes_link']['origin'] as $key => $val){
      $sNodes_info[$key]=$this->getSNodesInfo($key,$subject);
      $nodesLink['sNodes_link'][$key]=$this->getLink($sNodes_info[$key]['link'],$sNodes_info[$key]['sNodes_array']);
      //debug_msg("第".__LINE__."行 nodesLink[sNodes_link][key] ", $nodesLink['sNodes_link'][$key]);
    }
    //debug_msg("第".__LINE__."行 nodesLink[sNodes_link] ", $nodesLink['sNodes_link']);
    
    //大節點順序(依有遞移的下位節點數量決定)
    
    foreach($nodesLink['bNodes_link']['transitivity'] as $key => $val){
      $tmp=sNode2bNode($key,$this->mission_data['subject_id']);
      $year=$tmp[0];
      $bNodes_order_tmp[$year][$key]=array_sum($nodesLink['bNodes_link']['transitivity'][$key]);    
    }
    krsort($bNodes_order_tmp);
    //debug_msg("第".__LINE__."行 bNodes_order_tmp ", $bNodes_order_tmp);
    $bNodes_order=array();
    foreach($bNodes_order_tmp as $key=>$val){
      $bNodes_order_tmp2=$this->nodesSort($bNodes_order_tmp[$key]);
      //debug_msg("第".__LINE__."行 bNodes_order_tmp2 ", $bNodes_order_tmp2);
      foreach($bNodes_order_tmp2 as $val2){
        array_push($bNodes_order,$val2);
      }            
    }
    //$bNodes_order=$this->nodesSort($bNodes_order_tmp);
    $nodesLink['bNodes_order']=$bNodes_order;
    //debug_msg("第".__LINE__."行 bNodes_order ", $bNodes_order);
  
    //小節點順序
    $sNodes_order=array();
    foreach($bNodes_order as $val1){
      //萬一沒有小節點
      if(count($nodesLink['sNodes_link'][$val1]['transitivity'])>0){
        foreach($nodesLink['sNodes_link'][$val1]['transitivity'] as $key2 => $val2){
          $sNodes_order_tmp1[$val1][$key2]=array_sum($nodesLink['sNodes_link'][$val1]['transitivity'][$key2]);
        }
        $sNodes_order_tmp2=$this->nodesSort($sNodes_order_tmp1[$val1]);
        foreach($sNodes_order_tmp2 as $val3){
          array_push($sNodes_order,$val3);
        }
      }
    }
    //debug_msg("第".__LINE__."行 sNodes_order ", $sNodes_order);  
    //debug_msg("第".__LINE__."行 sNodes_order_tmp1 ", $sNodes_order_tmp1);
    $nodesLink['sNodes_order']=$sNodes_order;  
    
    return $nodesLink;                                        
  }

  private function getBNodesInfo($upmost_nodes_array,$subject,$end_years_array){
    global $dbh;  
    
    $bNodes_array['bNodes_array']=array();
    foreach($upmost_nodes_array as $key1=>$val1){
      if(!in_array($val1,$bNodes_array['bNodes_array'])){
        $bNodes_array['bNodes_array'][]=$val1;
        $bNodes_tmp[]=$val1;
        
        while(count($bNodes_tmp)>0){
          $sql='SELECT indicate_low FROM indicateTest Where indicate=:indicate AND subject=:subject';
          $result=$dbh->prepare($sql);
          $result->bindParam(':indicate',$bNodes_tmp[0], PDO::PARAM_STR);
          $result->bindParam(':subject',$subject, PDO::PARAM_INT);
          $result->execute();
          while ($row=$result->fetch(PDO::FETCH_ASSOC)){
            $new_bNodes=explode(_SPLIT_SYMBOL,$row['indicate_low']);
            if(end($new_bNodes)==''){
              array_pop($new_bNodes);
            }
            foreach($new_bNodes as $val2){
              
              //比對停止年級
              if($end_years_array[$key1]!=0){
                $tmp=sNode2bNode($val2,$subject);
                $node_year=$tmp[0];
                if($end_years_array[$key1]>$node_year){
                  continue;
                }
              }
              
              //建立下位關係
              $bNodes_array['link'][$bNodes_tmp[0]][$val2]=1;
              
              //比對相同節點
              if(in_array($val2,$bNodes_array['bNodes_array'])){
                continue;
              }
              
              $bNodes_array['bNodes_array'][]=$val2;
              $bNodes_tmp[]=$val2;        
            }
          }
          array_shift($bNodes_tmp);
        }    
      }
  
    }
    return $bNodes_array;
  }
  
  private function getSNodesInfo($bNode,$subject){
    global $dbh;
  
    $indicate=$bNode."%";
    $sql_sNodes='SELECT indicate FROM indicateTest WHERE indicate LIKE :indicate AND subject=:subject AND class="sNodes"';
    $result_sNodes=$dbh->prepare($sql_sNodes);
    $result_sNodes->bindParam(':indicate',$indicate, PDO::PARAM_STR);
    $result_sNodes->bindParam(':subject',$subject, PDO::PARAM_INT);
    $result_sNodes->execute();
    while ($row_sNodes=$result_sNodes->fetch(PDO::FETCH_ASSOC)){
      $sNode=$row_sNodes['indicate'];
      if(isset($sNodes_array) && in_array($sNode,$sNodes_array)){continue;}
      $sNodes_info['sNodes_array'][]=$sNode;
    }
    
    $ii=count($sNodes_info['sNodes_array']);
    for($i=0;$i<$ii;$i++){
      $sql_link='SELECT indicate_low FROM indicateTest Where indicate=:indicate AND subject=:subject';
      $result_link=$dbh->prepare($sql_link);
      $result_link->bindParam(':indicate',$sNodes_info['sNodes_array'][$i], PDO::PARAM_STR);
      $result_link->bindParam(':subject',$subject, PDO::PARAM_INT);
      $result_link->execute();
      while ($row_link=$result_link->fetch(PDO::FETCH_ASSOC)){
        $sNodes_low=explode(_SPLIT_SYMBOL,$row_link['indicate_low']);
        if(end($sNodes_low)==''){
          array_pop($sNodes_low);
        }
        foreach($sNodes_low as $val){
          if(in_array($val,$sNodes_info['sNodes_array'])){
            $sNodes_info['link'][$sNodes_info['sNodes_array'][$i]][$val]=1;        
          }
        }      
      }
    }
    return $sNodes_info;
  }
  
  private function getLink($nodes_link,$nodes){
    //debug_msg("第".__LINE__."行 nodes ", $nodes);
    $ii=count($nodes);
    for($i=0;$i<$ii;$i++){
      for($j=0;$j<$ii;$j++){
        if($nodes_link[$nodes[$i]][$nodes[$j]]==1){
          $link_data[$i][$j]=1;      
        }else{
          $link_data[$i][$j]=0;
        }      
      }
    }
    for($i=0;$i<$ii;$i++){
      for($j=0;$j<$ii;$j++){
        if($link_data[$i][$j]==1){
          $link_data_name['origin'][$nodes[$i]][$nodes[$j]]=1;      
        }else{
          $link_data_name['origin'][$nodes[$i]][$nodes[$j]]=0;
        }      
      }
    }
    //debug_msg("第".__LINE__."行 link_data ", $link_data);
    for($i=0;$i<$ii;$i++){
      $tmpj1=$link_data[$i];
      //debug_msg("第".__LINE__."行 nodes[i] ", $nodes[$i]);
      //debug_msg("第".__LINE__."行 tmpj ", $tmpj);
      while(1==1){
        $tmpj2=$tmpj1;
        for($j=0;$j<$ii;$j++){
          if($tmpj2[$j]==1){
            for($k=0;$k<$ii;$k++){
              if($link_data[$j][$k]==1){
                $tmpj2[$k]=1;
              }
            }
          }
        }
        if($tmpj2==$tmpj1){
          break;
        }else{
          $tmpj1=$tmpj2;
        }    
      }
      //debug_msg("第".__LINE__."行 tmpj2 ", $tmpj2);
      $link_data[$i]=$tmpj2;
    }
  
    for($i=0;$i<$ii;$i++){
      for($j=0;$j<$ii;$j++){
        if($link_data[$i][$j]==1){
          $link_data_name['transitivity'][$nodes[$i]][$nodes[$j]]=1;      
        }else{
          $link_data_name['transitivity'][$nodes[$i]][$nodes[$j]]=0;
        }      
      }
    }
  
    return $link_data_name;
  }
  
  private function bNodesSort($bNodes){
    
  }
  
  //根據下位節點總和數的 array 排出節點順序。下位節點總和相同時，以數字大者優先。
  private function nodesSort($nodes){
    //debug_msg("第".__LINE__."行 nodes ", $nodes);
    //$key_tmp=array_keys($nodes);
    $value_tmp=array_values($nodes);
    $value_tmp=array_unique($value_tmp);
    rsort($value_tmp);
    $new_nodes=array();
    foreach($value_tmp as $val){
      $keys_tmp=array_keys($nodes,$val);
      rsort($keys_tmp);
      foreach($keys_tmp as $val2){
        array_push($new_nodes,$val2);
      }
    }
    return $new_nodes;
  }
  
  //取得相反關係([high][low]->[low][high])
  private function getLinkDESC($array){
    foreach($array as $key1=>$val1){
      foreach($val1 as $key2=>$val2){
        $new_array[$key2][$key1]=$array[$key1][$key2];
      }
    }
    return $new_array;
  }  
  //決定節點上下位結構的 function。end  

  private function getSNodesCount(){   
    foreach($this->node_link_data['sNodes_order'] as $val){
      $bNode_tmp=sNode2bNode($val ,$this->mission_data['subject_id']);
      $bNode=$bNode_tmp[1];
      $sNodes_count[$bNode]++;
    }
    return $sNodes_count;
  }
  
  private function getTotalItems(){
    global $dbh;
    
    //foreach($this->node_link_data['sNodes_order'] as $val){
      //$indicator_sql[]='"'.$val.'"';
    //}    
    $indicator_str=implode(',',$this->node_link_data['sNodes_order']);
    $total_items=0;
    $sql='SELECT concept_item.indicator , concept_item.item_sn FROM concept_item INNER JOIN
            (SELECT indicator , GROUP_CONCAT(item_sn) grouped_item_sn 
            FROM concept_item 
            WHERE FIND_IN_SET(indicator,"'.$indicator_str.'") 
            AND double_item=0 AND paper_vol=1 
            AND (cs_id LIKE "028%" OR cs_id LIKE "023%" OR cs_id LIKE "021%" OR cs_id LIKE "020%") GROUP BY indicator) group_max 
          ON concept_item.indicator=group_max.indicator AND FIND_IN_SET(item_sn, grouped_item_sn) BETWEEN 1 AND '.$this->sNode_items;
    $result=$dbh->prepare($sql);
    $result->execute();  
    while ($row=$result->fetch(PDO::FETCH_ASSOC)){
      $total_items++;      
    }
    return $total_items;    
  }
  
  private function getNextSNode(){
    //debug_msg("第".__LINE__."行 this->record_sNodes_status ", $this->record_sNodes_status);
    foreach($this->record_sNodes_status as $key => $val){
      if($this->step==1){
        if($val==-1 || $val==5){
          $sNode=$key;
          break;
        }else{
          if($this->exam_type==1){
            $this->setRecordNan($key);
          }
        }
      }elseif($this->step==2){
        if($val==-1 || $val==2 || $val==5){
          $sNode=$key;
          break;
        }
      }
    }
    return $sNode;
  }

  private function getSNodeItems($sNode){
    global $dbh;
    
    $sql='SELECT item_sn 
          FROM concept_item 
          WHERE indicator=:indicator AND double_item=0 AND paper_vol=1 
          AND (cs_id LIKE "028%" OR cs_id LIKE "023%" OR cs_id LIKE "021%" OR cs_id LIKE "020%") 
          ORDER BY rand()';
    $result=$dbh->prepare($sql);
    $result->bindParam(':indicator',$sNode, PDO::PARAM_STR);
    $result->execute();  
    while ($row=$result->fetch(PDO::FETCH_ASSOC)){
      $item_array[]=$row['item_sn'];
    }
    return $item_array;  
  }
  
  //取得試題解答
  private function getAns($item_sn){
    global $dbh;
    $sql='SELECT op_ans FROM concept_item WHERE item_sn=:item_sn';
    $result=$dbh->prepare($sql);
    $result->bindParam(':item_sn',$item_sn, PDO::PARAM_INT);
    $result->execute();  
    while ($row=$result->fetch(PDO::FETCH_ASSOC)){
      $itemAns=$row['op_ans'];
    }
    return $itemAns;
  }
  
  //紀錄作答記錄
  private function makeRecResponse($res){
    switch($this->record_sNodes_status[$this->sNode_now]){
      case '-1':
        $this->record_response[]=$res;
        break;
      case '2':
        $this->record_response[]=$res+3;
        break;
      case '5':
        $this->record_response[]=$res;      
    }    
  }
  
  //設定答題資料為 nan
  private function setRecordNan($sNode){
    $item_array=$this->getSNodeItems($sNode);
    foreach($item_array as $val){
      $this->record_answer[]=Nan;
      $this->record_response[]=Nan;
      $this->record_client_time[]=Nan;
      $this->record_item_sn[]=$val;       
    }  
  }

  private function modifyNodeStatus(){
    $sNode=$this->sNode_now;
    $tmp=sNode2bNode($sNode,$this->mission_data['subject_id']);
    $bNode=$tmp[1];
    //大小節點 status
    if(count($this->sNode_response)!=0){
      $sNode_average=array_sum($this->sNode_response)/count($this->sNode_response);
    
      if($this->record_sNodes_status[$sNode]==5){$this->record_sNodes_status[$sNode]=-1;}
      if($sNode_average==1){
        switch($this->record_sNodes_status[$sNode]){
          case '-1':
            $this->record_sNodes_status[$sNode]=1;
            $this->record_bNodes_status[$bNode]+=1;
            break;
          case '2':
            $this->record_sNodes_status[$sNode]=4;
        }
      }else{
        switch($this->record_sNodes_status[$sNode]){
          case '-1':
            $this->record_sNodes_status[$sNode]=0;
            break;
          case '2':
            $this->record_sNodes_status[$sNode]=3;
            $this->record_bNodes_status[$bNode]-=1;
        }    
      }
    }else{
      unset($this->record_sNodes_status[$sNode]);
    }
  }
  
  private function modifyLowNodeStatus(){
    $sNode=$this->sNode_now;
    $tmp=sNode2bNode($sNode,$this->mission_data['subject_id']);
    $bNode=$tmp[1];
    
    //修正同一大節點下之小節點
    //debug_msg("第".__LINE__."行 this->node_link_data ", $this->node_link_data['sNodes_link'][$bNode]['transitivity'][$sNode]);
    foreach($this->node_link_data['sNodes_link'][$bNode]['transitivity'][$sNode] as $key1 => $val1){
      if($val1==1){
        //$origin_status=$this->record_sNodes_status[$key1];
        if($this->record_sNodes_status[$key1]==-1){
          $status=$this->changeStatus($this->record_sNodes_status[$key1] , $this->record_sNodes_status[$sNode]);
          $this->record_sNodes_status[$key1]=$status[0];
          $this->record_bNodes_status[$bNode]+=$status[1];
        }
      }
    }
    
    //確定大節點是否需要修正
    $check=1;
    foreach($this->node_link_data['sNodes_link'][$bNode]['transitivity'] as $key2 => $val2){      
      //if($this->record_sNodes_status[$key2]==-1 || $this->record_sNodes_status[$key2]==-5){
      if($this->record_sNodes_status[$key2]==-1){
        $check=0;
      }    
    }
    //修改下位大節點中的小節點 status
    if($check==1){
      $average=$this->record_bNodes_status[$bNode]/$this->bNode_sNodes_count[$bNode];
      if($average==1){
        foreach($this->node_link_data['bNodes_link']['transitivity'][$bNode] as $key3 => $val3){
          if($val3==1){
            foreach($this->node_link_data['sNodes_link'][$key3]['origin'] as $key4 => $val4){
              if($this->record_sNodes_status[$key4]==-1){
                $status=$this->changeStatus($this->record_sNodes_status[$key4] , 1);
                $this->record_sNodes_status[$key4]=$status[0];
                $this->record_bNodes_status[$key3]+=$status[1];
              }
            }
          }
        }          
      }
      /*
      else{
        foreach($this->node_link_data['bNodes_link']['transitivity'][$bNode] as $key3 => $val3){
          if($val3==1){
            foreach($this->node_link_data['sNodes_link'][$key3]['origin'] as $key4 => $val4){
              if($this->record_sNodes_status[$key4]==-1){
                $status=$this->changeStatus($this->record_sNodes_status[$key4] , 0);
                $this->record_sNodes_status[$key4]=$status[0];
                $this->record_bNodes_status[$key3]+=$status[1];
              }
            }
          }
        }      
      }
      */
    }
  }

  private function changeStatus($origin,$high){
    if($high==0){
      $status[0]=-1;
      $status[1]=0;
    }elseif($high==1){
      $status[0]=2;
      $status[1]=1;
    }elseif($high==3){
      $status[0]=-1;
      $status[1]=0;
    }elseif($high==4){
      $status[0]=2;
      $status[1]=1;
    }
    return $status;    
  }
  
  /*舊的備份
  private function changeStatus($origin,$high){
    if($origin==-1){
      if($high==0){
        $status[0]=5;
        $status[1]=0;
      }elseif($high==1){
        $status[0]=2;
        $status[1]=1;
      }elseif($high==3){
        $status[0]=5;
        $status[1]=0;
      }elseif($high==4){
        $status[0]=2;
        $status[1]=1;
      }
    }elseif($origin==2){
      if($high==0){
        $status[0]=5;
        $status[1]=-1;
      }elseif($high==1){
        $status[0]=2;
        $status[1]=0;
      }elseif($high==3){
        $status[0]=2;
        $status[1]=0;
      }elseif($high==4){
        $status[0]=2;
        $status[1]=0;
      }
    }elseif($origin==5){
      if($high==0){
        $status[0]=5;
        $status[1]=0;
      }elseif($high==1){
        $status[0]=2;
        $status[1]=1;
      }elseif($high==3){
        $status[0]=2;
        $status[1]=1;
      }elseif($high==4){
        $status[0]=2;
        $status[1]=1;
      }
    }
    return $status;    
  }  
  */
  
  //確定測驗是否結束以及是否換階段
  private function checkExamEnd(){
    $sNode_left=0;
    if($this->step==1){
      foreach($this->record_sNodes_status as $key => $val){
        if($val==-1 || $val==5){
          $sNode_left++;
        }
      }
      //debug_msg("第".__LINE__."行 sNode_left ", $sNode_left);
      if($sNode_left==0){
        if($this->mission_data['exam_type']==0){
          $this->step=2;
        }else{
          $this->updateExamineeSql();
          $this->makeExamSql();
          //$RedirectTo="Location: modules.php?op=modload&name=indicateTest&file=adaptiveExamResult&map_sn=".$this->mission_data['map_sn']."&subject=".$this->mission_data['subject_id']."&result_sn=".$this->mission_sn;
          //$RedirectTo="Location: modules.php?op=modload&name=indicateTest&file=adaptiveExamResult&result_sn=".$this->mission_sn;
          //Header($RedirectTo);
          require_once "./modules/indicateTest/adaptiveExamResult.php";
          //fixed by fuyun for 顯示的受試者可指定 20171127
          //$reportHtml=adaptiveExamResult($this->mission_sn,'0');
          $reportHtml=adaptiveExamResult($_SESSION['user_id'],$this->mission_sn,'0');
          echo $reportHtml;
          die();
        }
      }
    }elseif($this->step==2){
      foreach($this->record_sNodes_status as $key => $val){
        if($val==-1 || $val==2 || $val==5){
          $sNode_left++;
        }
      }
      if($sNode_left==0){
        $this->updateExamineeSql();
        $this->makeExamSql();
        //$RedirectTo="Location: modules.php?op=modload&name=indicateTest&file=adaptiveExamResult&map_sn=".$this->mission_data['map_sn']."&subject=".$this->mission_data['subject_id']."&result_sn=".$this->mission_sn;
        //$RedirectTo="Location: modules.php?op=modload&name=indicateTest&file=adaptiveExamResult&result_sn=".$this->mission_sn;
        //Header($RedirectTo);
        require_once "./modules/indicateTest/adaptiveExamResult.php";
        $reportHtml=adaptiveExamResult($_SESSION['user_id'],$this->mission_sn,'0');
        echo $reportHtml;
        die();
      }  
    }    
  }
  
  private function updateExamineeSql(){
    global $dbh;
    
    $map_sn=$this->mission_data['map_sn'];
    $subject=$this->mission_data['subject_id'];  
    
    $sql='
      SELECT *
      FROM map_node_student_status
      WHERE user_id="'.$_SESSION[user_id].'"
      AND map_sn="'.$map_sn.'"
    ';
    
    $re=$dbh->query( $sql );
    $data=$re->fetch();
    if( !is_array($data) ) die( __FILE__.' user no map_node_student_status.' );
    $bNodeStatus = unserialize($data['bNodes_Status']);
    $sNodeStatus = unserialize($data['sNodes_Status_FR']);
    //debug_msg("第".__LINE__."行 bNodeStatus ", $bNodeStatus);
    //debug_msg("第".__LINE__."行 sNodeStatus ", $sNodeStatus);
    
    //之前通過的…
    /*
    foreach( $sNodeStatus as $sNode){
      $tmp = sNode2bNode( $sNode,  $subject);
      $bNode = $tmp[1];
      $bNodeStatus[ $bNode ]['total']+=1;
    }
    */
    //初始化有測的大節點
    foreach( $this->record_sNodes_status as $key=>$ary ){
    	$tmp = sNode2bNode( $key,  $subject);
    	$bNode = $tmp[1];
    	//初始化 小節點總合值 為0
    	$bNodeStatus[$bNode]['total']=0;
    	$bNodeStatus[$bNode]['status:']=0;
    }
    
    //這次通過的…再依小節點計算大節點已通過的小節點數
    foreach( $this->record_sNodes_status as $key=>$ary ){
      $skill_remedy_rate_s[]=$key.':'.$ary;    

    	$tmp = sNode2bNode( $key,  $subject);
    	$bNode = $tmp[1];
      if($ary==-1){
        continue;
      }elseif($ary==1 || $ary==2 || $ary==4){
        $sNodeStatus[$key]['status:']=1;
        $bNodeStatus[$bNode]['status:']+=1;
      }else{
        $sNodeStatus[$key]['status:']=0;
      }
      $bNodeStatus[$bNode]['total']+=1;
    }
    //計算大節點的通過與否
    foreach( $this->record_bNodes_status as $key=>$ary ){
    	if($this->bNode_sNodes_count[$key] == $bNodeStatus[$key]['status:']){ 
    		if($bNodeStatus[$key]['total'] >0){
    			$bNodeStatus[$key]['bstatus:']=1;
    		}
    	}else{
        $bNodeStatus[$key]['bstatus:']=0;      
      }
    }
    //debug_msg("第".__LINE__."行 this->record_bNodes_status ", $this->record_bNodes_status);
    //debug_msg("第".__LINE__."行 bNodeStatus ", $bNodeStatus);
    //die();
  	$sql_count='
      SELECT count(*)
      FROM map_node_student_status
      WHERE user_id="'.$_SESSION[user_id].'"
      AND map_sn="'.$map_sn.'"
    ';
  	$re_count=$dbh->query($sql_count);
  	$count=$re_count->fetch();
  	//debugBAI(__LINE__,__FILE__,$data);
  	if( $count[0]==0 ){
  		$sql_up='
        INSERT INTO map_node_student_status
        ( user_id , map_sn , bNodes_Status , sNodes_Status_FR )
        VALUE
        ( "'.$_SESSION[user_id].'",
          "'.$map_sn.'",
          "'.serialize($bNodeStatus).'",
          "'.serialize($sNodeStatus).'" )
  	
      ';
  		//debugBAI(__LINE__,__FILE__, $sql, 'print_r' );
  		$re_up=$dbh->query($sql_up);
  		$data_up=$re_up->fetch();
    }else{
      $sql_up='
        UPDATE map_node_student_status
        SET bNodes_Status =\''.serialize($bNodeStatus).'\',
            sNodes_Status_FR =\''.serialize($sNodeStatus).'\'
        WHERE user_id="'.$_SESSION[user_id].'"
        AND map_sn="'.$map_sn.'"
      ';
      //debugBAI(__LINE__,__FILE__,$sql_up,'print_r');
      $re_up=$dbh->query($sql_up);
      $data_up=$re_up->fetch();    
    }                                                
  }
  
  private function makeExamSql(){
    global $dbh;

    //正確率
    $score_tmp=0;
    $tmp=0;
    foreach($this->record_response as $val){
      if($val==1 or $val==2 or $val==4){
        $score_tmp++;
      }
    }
    foreach($this->record_sNodes_status as $status){
      if($status==2){
        $tmp=$tmp+$this->sNode_items;
      }
    }
    $score=(($score_tmp+$tmp)/(count($this->record_response)+$tmp))*100;
    $score=round($score);
    
    $questions=implode( _SPLIT_SYMBOL,$this->record_item_sn);
    $stuAns=implode( _SPLIT_SYMBOL,$this->record_answer);
    $binary_res=implode( _SPLIT_SYMBOL,$this->record_response);
    foreach( $this->record_sNodes_status as $sNode=>$status ){
      $skill_remedy_rate_s_array[]= $sNode.':'.$status;
    }
    $skill_remedy_rate_s=implode(_SPLIT_SYMBOL,$skill_remedy_rate_s_array);
    foreach( $this->record_bNodes_status as $bNode=>$status ){
      $skill_remedy_rate_b_array[]= $bNode.':'.$status;
    }
    $skill_remedy_rate_b=implode(_SPLIT_SYMBOL,$skill_remedy_rate_b_array);
    $stop_time=date("U");
    $client_items_idle_time=implode( _SPLIT_SYMBOL,$this->record_client_time);

    //debugBAI(__LINE__,__FILE__,$_SESSION[indicateInfo],'print_r');
    //由節點出題的cs_id數學科都設 026021899
    if( $this->mission_data['subject_id']==2 ) $cs_id='026021899';
    elseif( $this->mission_data['subject_id']==1 ) $cs_id='026011899';
    elseif( $this->mission_data['subject_id']==3 ) $cs_id='026031899';
    if( $cs_id=='' ) die( __FILE__.' cs_id is null.' );

    //delete tmp data
    $sql_tmp='DELETE FROM exam_record_indicate_tmp WHERE cs_id=:cs_id AND result_sn=:result_sn AND user_id=:user_id';
    $result_tmp=$dbh->prepare($sql_tmp);
    $result_tmp->bindParam(':cs_id',$cs_id, PDO::PARAM_INT);
    $result_tmp->bindParam(':result_sn',$this->mission_sn, PDO::PARAM_INT);
    $result_tmp->bindParam(':user_id',$_SESSION[user_id], PDO::PARAM_STR);
    $result_tmp->execute();
    
    //將學生作答資料存入 exam_record_indicate ，避免跟試卷施測的重複，並且不刪除舊的
    $sql='INSERT INTO exam_record_indicate 
          (user_id, cs_id, date, start_time, stop_time, during_time, client_items_idle_time, score , questions, stuAns, binary_res, skill_remedy_rate_s, skill_remedy_rate_b, result_sn) 
          VALUES (:user_id, :cs_id, :date, :start_time, :stop_time, :during_time, :client_items_idle_time, :score , :questions, :stuAns, :binary_res, :skill_remedy_rate_s, :skill_remedy_rate_b, :result_sn)';
    $result=$dbh->prepare($sql);
    $result->bindParam(':user_id',$_SESSION[user_id], PDO::PARAM_STR);
    $result->bindParam(':cs_id',$cs_id, PDO::PARAM_INT);
    $result->bindParam(':date',$this->start_date, PDO::PARAM_STR);
    $result->bindParam(':start_time',$this->start_time, PDO::PARAM_INT);
    $result->bindParam(':stop_time',$stop_time, PDO::PARAM_INT);
    $result->bindParam(':during_time',$this->during_time, PDO::PARAM_INT);
    $result->bindParam(':client_items_idle_time',$client_items_idle_time, PDO::PARAM_STR);
    $result->bindParam(':score',$score, PDO::PARAM_INT);
    $result->bindParam(':questions',$questions, PDO::PARAM_STR);
    $result->bindParam(':stuAns',$stuAns, PDO::PARAM_STR);
    $result->bindParam(':binary_res',$binary_res, PDO::PARAM_STR);
    $result->bindParam(':skill_remedy_rate_s',$skill_remedy_rate_s, PDO::PARAM_STR);
    $result->bindParam(':skill_remedy_rate_b',$skill_remedy_rate_b, PDO::PARAM_STR);
    $result->bindParam(':result_sn',$this->mission_sn, PDO::PARAM_INT);
    $result->execute();   
  }
  
  private function examPause(){
  	global $dbh;
  	
  	$questions=implode( _SPLIT_SYMBOL,$this->record_item_sn);
  	$stuAns=implode( _SPLIT_SYMBOL,$this->record_answer);
    $binary_res=implode( _SPLIT_SYMBOL,$this->record_response);
    foreach( $this->record_sNodes_status as $sNode=>$status ){
      $skill_remedy_rate_s_array[]= $sNode.':'.$status;
    }
    $skill_remedy_rate_s=implode(_SPLIT_SYMBOL,$skill_remedy_rate_s_array);
    foreach( $this->record_bNodes_status as $bNode=>$status ){
      $skill_remedy_rate_b_array[]= $bNode.':'.$status;
    }
    $skill_remedy_rate_b=implode(_SPLIT_SYMBOL,$skill_remedy_rate_b_array);
    $stop_time=date("U");
    $client_items_idle_time=implode( _SPLIT_SYMBOL,$this->record_client_time);
    $bNodeAry=implode( _SPLIT_SYMBOL,$this->node_link_data['bNodes_order']);
    $sNodeAry=implode( _SPLIT_SYMBOL,$this->node_link_data['sNodes_order']);  	
  	
  	//debugBAI(__LINE__,__FILE__,$_SESSION[indicateInfo],'print_r');
    //由節點出題的cs_id數學科都設 026021899
    if( $this->mission_data['subject_id']==2 ) $cs_id='026021899';
    elseif( $this->mission_data['subject_id']==1 ) $cs_id='026011899';
    elseif( $this->mission_data['subject_id']==3 ) $cs_id='026031899';
    if( $cs_id=='' ) die( __FILE__.' cs_id is null.' );
  	//加作答時間 $_SESSION[indicateInfo][itemAry]
  	
  	//將學生作答資料存入 exam_record_indicate_tmp
  	$sql='
      SELECT count(*)
      FROM exam_record_indicate_tmp
      WHERE user_id="'.$_SESSION[user_id].'"
      AND cs_id="'.$cs_id.'"
      AND result_sn="'.$this->mission_sn.'"
    ';
  	$re=$dbh->query($sql);
  	$data=$re->fetch();
  	//debugBAI(__LINE__,__FILE__,$data);
  	if( $data[0]==0 ){
  		$sql='
        INSERT INTO exam_record_indicate_tmp
        ( user_id, cs_id, date, start_time, stop_time, during_time, client_items_idle_time, questions, stuAns, binary_res, skill_remedy_rate_s, skill_remedy_rate_b, result_sn, itemSort_b, itemSort_s )
        VALUE
        ( "'.$_SESSION[user_id].'",
          "'.$cs_id.'",
          "'.$this->start_date.'",
          "'.$this->start_time.'",
          "'.$stop_time.'", 
          "'.$this->during_time.'",
          "'.$client_items_idle_time.'",
          "'.$questions.'",
          "'.$stuAns.'",
          "'.$binary_res.'",
          "'.$skill_remedy_rate_s.'",
          "'.$skill_remedy_rate_b.'",
          "'.$this->mission_sn.'",
          "'.$bNodeAry.'",
          "'.$sNodeAry.'" )
  	
      ';
  		//debugBAI(__LINE__,__FILE__, $sql, 'print_r' );
  		$re=$dbh->query($sql);
  		$data=$re->fetch();
  	
  		if( $data[1]!=null ) die( __FILE__.' can not insert DB.<br> '.$sql );
  	}elseif( $data[0]>0 ){
  		$sql='
        UPDATE exam_record_indicate_tmp
        SET date="'.$this->start_date.'",
            stop_time="'.$stop_time.'",
            client_items_idle_time="'.$client_items_idle_time.'",
            during_time="'.$this->during_time.'",
            questions ="'.$questions.'",
            stuAns="'.$stuAns.'",
            binary_res ="'.$binary_res.'",
            skill_remedy_rate_s="'.$skill_remedy_rate_s.'",
            skill_remedy_rate_b="'.$skill_remedy_rate_b.'",
            itemSort_b="'.$bNodeAry.'",
            itemSort_s="'.$sNodeAry.'"
        WHERE user_id="'.$_SESSION[user_id].'"
        AND cs_id="'.$cs_id.'"
        AND result_sn="'.$this->mission_sn.'"
      ';
  		$re=$dbh->query($sql);
  		$data=$re->fetch();
  		//debugBAI(__LINE__,__FILE__, [$re,$data], 'print_r' );
  		if( $data[1]!=null ) die( __FILE__.' can not UPDATE DB.<br> '.$sql );
  	} 
  }
  
  private function getExamPauseData(){
    global $dbh;
    
    $get_data=0;
    $sql='SELECT start_time , client_items_idle_time , questions , stuAns , binary_res , skill_remedy_rate_s , skill_remedy_rate_b 
          FROM exam_record_indicate_tmp 
          WHERE user_id=:user_id AND result_sn=:result_sn';
    $result=$dbh->prepare($sql);
    $result->bindParam(':user_id',$_SESSION['user_id'], PDO::PARAM_STR);
    $result->bindParam(':result_sn',$this->mission_sn, PDO::PARAM_INT);
    $result->execute();
    while ($row=$result->fetch(PDO::FETCH_ASSOC)){
      $get_data=1;
      $this->start_time=$row['start_time'];
      $this->record_client_time=explode(_SPLIT_SYMBOL,$row['client_items_idle_time']);
      $this->record_answer=explode(_SPLIT_SYMBOL,$row['stuAns']);
      $this->record_response=explode(_SPLIT_SYMBOL,$row['binary_res']);
      $item_sn_tmp=explode(_SPLIT_SYMBOL,$row['questions']);
      unset($this->record_item_sn);
      foreach($this->record_answer as $key => $val){
        $this->record_item_sn[]=$item_sn_tmp[$key];
      }
      
    	$temp1 = explode(_SPLIT_SYMBOL,$row['skill_remedy_rate_s']);
    	foreach ($temp1 as $key=>$value){
    		$s1 = explode(':', $value);
        if(in_array($s1[0],array_keys($this->record_sNodes_status))){$this->record_sNodes_status[$s1[0]]=$s1[1];}    		
    	}
      //debug_msg("第".__LINE__."行 this->record_sNodes_status ", $this->record_sNodes_status);
      //die();
    	
    	$temp2 = explode(_SPLIT_SYMBOL,$row['skill_remedy_rate_b']);//skill_remedy_rate_b
    	foreach ($temp2 as $key=>$value){
    		$s2 = explode(':', $value);
        if(in_array($s2[0],array_keys($this->record_bNodes_status))){$this->record_bNodes_status[$s2[0]]=$s2[1];}
    	}                      
    }
    return $get_data;
  }
}

?>