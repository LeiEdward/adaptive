<?php
  require_once('./include/config.php');
  require_once('./include/adp_API.php');

  if (!isset($_SESSION)) {
  	session_start();
  }

  // 取得 user 資料
  $vUserData = get_object_vars($_SESSION['user_data']);

  $vGroup = array();
  $vGroup = getGroup();

  $vTo = array();
  $vTo = getMessagetoWHO();

  $vMessageData = array();
  $vMessageData = getMessage();
  $vMessageData = handleData($vMessageData);
  $sButtonLable = (USER_PARENTS == $vUserData['access_level']) ? '留言給老師' : '留言給家長';

  // 整理資料, 統一變數傳至HTML
  $sJSOject = arraytoJS(array('Userid' => $vUserData['user_id'],
                              'Message' => $vMessageData));

  // function
  function getGroup() {
    global $dbh, $vUserData;

    $sACL = $vUserData['access_level'];
    $sUserID = $vUserData['user_id'];

    switch($sACL) {
      case USER_PARENTS:  // 家長身分

        $sSQLGroup = "SELECT * FROM user_family
          LEFT JOIN user_info ON user_family.user_id = user_info.user_id
          WHERE user_family.fuser_id = '$sUserID'";

        $sSQLGroup = $dbh->prepare($sSQLGroup);
        $sSQLGroup->execute();
        $vGroupInfo = $sSQLGroup->fetchAll(\PDO::FETCH_ASSOC);
        break;

      case USER_TEACHER:
        $sNowSeme = getYearSeme();

        $sSQLGroup = "SELECT * FROM seme_teacher_subject
          WHERE seme_year_seme = '$sNowSeme'
          AND teacher_id = '$sUserID'";

        $sSQLGroup = $dbh->prepare($sSQLGroup);
        $sSQLGroup->execute();
        $vGroupInfo = $sSQLGroup->fetchAll(\PDO::FETCH_ASSOC);

        // $sOrganizeID = $vUserData['organization_id'];
        // $sGrade = $vUserData['grade'];
        // $sClass = $vUserData['class_name'];
        //
        // $vGroup['togroup'][] = '['.$sOrganizeID.']'.'['.$sGrade.']'.'['.$sClass.']';
        break;
    }

    $vGroup = array();
    foreach ($vGroupInfo as $vInfo) {
      // $vGroup['info'] 家長發送的訊息對象
      $vGroup['info'][] = array('organization_id' => $vInfo['organization_id'],
                                'grade' => $vInfo['grade'],
                                'class' => $vInfo['class']);
      $vGroup['togroup'][] = '['.$vInfo['organization_id'].']'.'['.$vInfo['grade'].']'.'['.$vInfo['class'].']';
    }

    return $vGroup;
  }

  function getMessagetoWHO() {
    global $dbh, $vUserData, $vGroup, $sToAll;

    if (empty($vGroup)) return array();

    $vGrade = array();
    $vClass = array();
    $vOrganizeID = array();
    foreach ($vGroup['info'] as $vInfo) {
      $vOrganizeID[] = $vInfo['organization_id'];
      $vGrade[] = $vInfo['grade'];
      $vClass[] = $vInfo['class'];
    }
    $sOrganizeID = implode("','", $vOrganizeID);
    $sGrade = implode("','", $vGrade);
    $sClass = implode("','", $vClass);

    $sToAllGroup = implode(",", $vGroup['togroup']);
    $sToAll = $sToAllGroup.'>>>ALL';
    // echo $sToAll;
    $sACL = $vUserData['access_level'];
    switch($sACL) {
      // Parents to Teacher
      case USER_PARENTS:
        $sSQLTO = "SELECT *, teacher_id, teacher_id as towho FROM seme_teacher_subject
          LEFT JOIN user_info ON user_info.user_id = seme_teacher_subject.teacher_id
          WHERE seme_teacher_subject.organization_id IN ('$sOrganizeID')
          AND seme_teacher_subject.grade IN ('$sGrade')
          AND seme_teacher_subject.class IN ('$sClass')
          AND user_info.used = '1'
          GROUP by user_info.user_id";
        break;

      // Teacher to Parents
      case USER_TEACHER:
        $sSQLTO = "SELECT *, user_family.fuser_id as towho FROM user_info
          LEFT JOIN user_family ON user_info.user_id = user_family.user_id
          WHERE user_info.organization_id IN ('$sOrganizeID')
          AND user_info.grade IN ('$sGrade')
          AND user_info.class IN ('$sClass')
          AND user_info.used = '1'
          AND LENGTH(user_family.user_id) > 4
          GROUP by user_family.fuser_id";
        break;
    }
    // echo $sSQLTO;
    $oTO = $dbh->prepare($sSQLTO);
    $oTO->execute();
    $vTOWHO = $oTO->fetchAll(\PDO::FETCH_ASSOC);

    $vTo = array();
    if (!empty($vTOWHO) && is_array($vTOWHO)) {
      sort($vTOWHO);

      $vTo = array();
      $vTo[] = '<select id="sel_parent">';
      if (1 < count($vTOWHO)) {
        $vTo[] =   '<option value="'.base64_encode($sToAll).'">全部</option>';
      }
      $sGradeClass = '';
      foreach($vTOWHO as $vWHO) {
        // $sToName = id2uname($vWHO['user_id']);
        $sToName = id2uname($vWHO['towho']);
        if ($sGradeClass != $vWHO['grade'].$vWHO['class']) {
          if ('' !== $sGradeClass) {
            $vTo[] = '</optgroup>';
          }
          $sGradeClass = $vWHO['grade'].$vWHO['class'];
          $vTo[] = '<optgroup label="'.$vWHO['grade'].'年'.$vWHO['class'].'班">';
        }
        $vTo[] = '<option value="'.base64_encode($vWHO['towho']).'">'.$sToName.' </option>';
      }
      $vTo[] = '</select>';
    }

    return $vTo;
  }

  function getMessage() {
    global $dbh, $vUserData, $vGroup;

    $vMessageData = array();
    $sUserID = $vUserData['user_id'];

    $sGroup = '';
    if (!empty($vGroup['togroup'])) {
      foreach ($vGroup['togroup'] as $sGroupStr) {
        $sGroup .= " OR (message_master.togroup LIKE '%$sGroupStr%' AND message_master.delete_flag = '0') ";
      }
    }

    $sSQLMessage = "SELECT *, CONCAT(message_master.togroup,message_master.touser_id,message_master.create_user) AS CanSee FROM message_master
      LEFT JOIN message_response ON message_master.msg_sn = message_response.message_sn
      LEFT JOIN message_fileattached ON message_master.msg_sn = message_fileattached.message_sn
      WHERE message_master.msg_type = '2'
      AND message_master.delete_flag = '0'
      $sGroup
      UNION
      SELECT *, CONCAT(message_master.togroup,message_master.touser_id,message_master.create_user) AS CanSee FROM message_master
      RIGHT JOIN message_response ON message_master.msg_sn = message_response.message_sn
      RIGHT JOIN message_fileattached ON message_master.msg_sn = message_fileattached.message_sn
      WHERE message_master.msg_type = '2'
      AND message_master.delete_flag = '0'
      $sGroup
      ORDER BY msg_sn DESC";
      // debugBai('','',$sSQLMessage);
    $oMessage = $dbh->prepare($sSQLMessage);
    $oMessage->execute();
    $vMessageData = $oMessage->fetchAll(\PDO::FETCH_ASSOC);

    return $vMessageData;
  }

  function handleData($vMessageData) {
    global $vUserData, $vGroup;

    $sUserID = $vUserData['user_id'];
    $sACL = $vUserData['access_level'];

    $vNewData = array();
    foreach ($vMessageData as $key => $vMsg) {
      // echo $key.$vMsg['CanSee'].'<br/>';
      // 主訊息，因為資料集是UNION起來，所以要去除重複的編號
      if (!isset($vNewData['msg_'.$vMsg['msg_sn']])) {

        // $vMsg['CanSee'] 如果找不到對象是自己,訊息可能是群發
        if (FALSE === strpos($vMsg['CanSee'], $sUserID)) {

          // 確定是否有群發
          if (!empty($vGroup['togroup'])) {

            $iCount = 0;
            $iMax = Count($vGroup['togroup']);
            foreach ($vGroup['togroup'] as $sCanSeeGroup) {
              if (FALSE === strpos($vMsg['CanSee'], $sCanSeeGroup)) {
                $iCount++;
              }
              if ($iMax == $iCount) continue 2;
            }
          }

          // 因為 $vMsg['CanSee'] 是由 發送者+群組對象+收件者 組合起來，所以發送者不是自己時有可能會透過群組收到訊息，故須排除
          switch($sACL) {
            case USER_TEACHER: // 老師不能看到其他老師的，但可以收到其他家長發的訊息
              if ($vMsg['create_user'] != $sUserID && USER_TEACHER == id2UserLevel($vMsg['create_user'])) continue 2;
              break;
            case USER_PARENTS: // 不能看到其他家長的，但可以收到其他老師發的訊息
              if ($vMsg['create_user'] != $sUserID && USER_PARENTS == id2UserLevel($vMsg['create_user'])) continue 2;
              break;
          }
        }

        $vNewData['msg_'.$vMsg['msg_sn']]['msg_sn'] = $vMsg['msg_sn'];
        $vNewData['msg_'.$vMsg['msg_sn']]['touser_name'] = id2uname($vMsg['touser_id']);
        $vNewData['msg_'.$vMsg['msg_sn']]['create_user'] = id2uname($vMsg['create_user']);
        $vNewData['msg_'.$vMsg['msg_sn']]['create_userid'] = $vMsg['create_user'];
        $vNewData['msg_'.$vMsg['msg_sn']]['create_time'] = getPassTime($vMsg['create_time']);
        $vNewData['msg_'.$vMsg['msg_sn']]['msg_content'] = str_replace(array("\r", "\n", "\r\n", "\n\r"), '<br>', $vMsg['msg_content']);
        $vNewData['msg_'.$vMsg['msg_sn']]['attachefile'] = $vMsg['attachefile'];
        $vNewData['msg_'.$vMsg['msg_sn']]['read_mk'] = $vMsg['read_mk'];
        $vNewData['msg_'.$vMsg['msg_sn']]['delete_flag'] = $vMsg['delete_flag'];
        $vNewData['msg_'.$vMsg['msg_sn']]['response_total'] = 0;
        $vNewData['msg_'.$vMsg['msg_sn']]['checkResbtn'] = true;

        // 群發訊息 touser_id 會是NULL
        if (empty($vMsg['touser_id']) && !empty($vMsg['togroup'])) {
          if ($sUserID !== $vMsg['create_user']) {
            $vNewData['msg_'.$vMsg['msg_sn']]['touser_name'] = id2uname($sUserID);
          }
          else {
            $vNewData['msg_'.$vMsg['msg_sn']]['touser_name'] = (USER_PARENTS == $sACL)? '全部老師' : '全班家長';
          }
        }
      }

      // 主訊息附加檔案
      if (isset($vMsg['file_sn'])) {
        switch($vMsg['filetype']) {
          case 'image':
            $vNewData['msg_'.$vMsg['msg_sn']]['msgimg'][$vMsg['file_sn']] = array('filesrc' => $vMsg['filesrc']);
            break;

          default:
            $vNewData['msg_'.$vMsg['msg_sn']]['msgfile'][$vMsg['file_sn']] = array('file_orgnialname' => $vMsg['file_orgnialname'],
                                                                                   'file_replacename' => $vMsg['file_replacename'],
                                                                                   'upload_time' => substr($vMsg['upload_time'], 0, 16),
                                                                                   'filesrc' => $vMsg['filesrc']);
            break;
        }
      }

      // 回覆訊息
      if ($vMsg['response_sn'] && !isset($vNewData['msg_'.$vMsg['msg_sn']]['remsg'][$vMsg['response_sn']])) {
        // 刪除不顯示
        if ('1' == $vMsg['remsg_delete_flag']) continue;

        // 有指定對象回覆訊，只有接收者和發訊息者看的到訊息
        if (!empty($vMsg['res_resmsgto_user']) && $sUserID != $vMsg['res_resmsgto_user'] && $sUserID != $vMsg['remsg_create_user']) continue;

        // 只有發訊息者可以看到全部的訊息，接收者只能看見本身的訊息與發訊息者的對話
        // if ($sUserID != $vMsg['create_user'] && $sUserID != $vMsg['remsg_create_user'] && $vMsg['create_user'] != $vMsg['remsg_create_user']) continue;

        $vNewData['msg_'.$vMsg['msg_sn']]['response_total']++;
        $vNewData['msg_'.$vMsg['msg_sn']]['remsgindex'] = $vMsg['response_sn'];
        $vNewData['msg_'.$vMsg['msg_sn']]['remsg'][$vMsg['response_sn']] = array('response_content' => str_replace(array("\r", "\n", "\r\n", "\n\r"), ' ', $vMsg['response_content']),
                                                                                 'remsg_create_user' => id2uname($vMsg['remsg_create_user']),
                                                                                 'remsg_create_userid' => $vMsg['remsg_create_user'],
                                                                                 'res_resmsgto_user' => $vMsg['res_resmsgto_user'],
                                                                                 'remsg_create_time' => getPassTime($vMsg['remsg_create_time']));
      }
    }
    return $vNewData;
  }

function getPassTime($sTime) {
  $sNowTime = date("Y-m-d H:i:s");
  $sTime = substr($sTime, 0, 16);

  $sCreateTime = '';
  if (1 <= floor((strtotime($sNowTime) - strtotime($sTime))/ (60*60*24))) {
    $sCreateTime = floor((strtotime($sNowTime) - strtotime($sTime))/ (60*60*24)).'日';
  }
  else if (1 <= floor((strtotime($sNowTime) - strtotime($sTime))/ (60*60))) {
    $sCreateTime = floor((strtotime($sNowTime) - strtotime($sTime))/ (60*60)).'小時';
  }
  else if (1 <= floor((strtotime($sNowTime) - strtotime($sTime))/ (60))) {
    $sCreateTime = floor((strtotime($sNowTime) - strtotime($sTime))/ (60)).'分';
  }
  else if (1 <= floor((strtotime($sNowTime) - strtotime($sTime)))) {
    $sCreateTime = floor((strtotime($sNowTime) - strtotime($sTime))).'秒';
  }

  return $sCreateTime;
}
?>
<!DOCTYPE HTML>
<html>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
<style>
  body {font-family:"Microsoft JhengHei", "Arial", sans-serif !important};
  ul, li {margin:0px;padding:0px;list-style:none;}
  textarea {resize:none;}
  ins {text-decoration: none !important;}
  .main_content {height:700px;width:100%;overflow:hidden;overflow-y:auto;}

  .main_content::-webkit-scrollbar-track {-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);border-radius: 10px;background-color: #F5F5F5;}
  .main_content::-webkit-scrollbar {width: 10px;background-color: #F5F5F5;}
  .main_content::-webkit-scrollbar-thumb {border-radius: 10px;-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);background-color: #555;}
  .filebox::-webkit-scrollbar-track {-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);border-radius: 10px;background-color: #F5F5F5;}
  .filebox::-webkit-scrollbar {height:10px; width: 10px;background-color: #F5F5F5;}
  .filebox::-webkit-scrollbar-thumb {border-radius: 10px;-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);background-color: #555;}

  /* 留言浮動框 */
  .grid-item {position:relative;margin-bottom:10px;height:250;}
  .accordionPart > section {border:solid 1px #E3E3E3;}
  .accordionPart > section.grid-item {width:380px;float:left;}
  .accordionPart > section.open {width:100%;float:left;}
  .accordionPart > section.open > div.qa_content {display:inherit !important;background-color:#f6f7f9;}
  .accordionPart > section > div.qa_title {cursor:pointer;position:relative;}
  .accordionPart > section > div.delete::after {content:"";position:absolute;top:0px;right:0px;width:10px;height:10px;background-image: url("./images/toolbar/del.png");background-size:100%;background-position:center;background-repeat:no-repeat;}
  .accordionPart > section > div.qa_content {display:none;}

  /* 留言標題 */
  .qa_title > ul > li {position:relative;overflow:hidden;}
  .qa_title > ul > li > .name {color:rgb(0, 0, 255);}
  .qa_title > ul > li > .time {padding:0px 4px;margin-bottom:5px;font-size:14px;max-width:11em;color:#999;vertical-align:middle;white-space:nowrap;display:inline-block;text-overflow:ellipsis;overflow:hidden;}
  .qa_title > ul > li > span > .resmsg {color:rgb(150, 150, 255);cursor:pointer;}
  .qa_title > ul > li > .messageinfo {display:inline-block;height:25px;float:right;font-size:14px;margin-right:1em;}
  .qa_title > ul > li > .attachedfile {display:flex;height:25px;font-size:14px;vertical-align:middle;justify-content:flex-end;}
  .qa_title > ul > li > .attachedfile > .ico {display:inline-block;height:25px;width:25px;margin:0px 5px;background-size:80%;background-position:center;background-repeat:no-repeat;background-image: url("./images/toolbar/file.png");}
  .qa_title > ul > li > .attachedfile > .filename {flex:80;display:flex;justify-content:stretch;overflow:hidden;white-space: nowrap;}
  .qa_title > ul > li > .attachedfile > .filename > span {margin-right:4px;overflow:hidden;text-overflow:ellipsis;text-decoration:underline;}
  .qa_title > ul > li > .attachedfile > .filename > span:hover {color:rgb(0, 0, 255);}

  /* 留言回覆 */
  .qa_content > ul > li {padding:5px 0px;}
  .qa_content > ul > li > .name {color:rgb(0, 0, 255);}
  /*.qa_content > ul > li > .text {display:block;}*/
  .qa_content > ul > li > .time, .qa_content > ul > li > .resmsg, .qa_content > ul > li > .delmsg {font-size:14px;color:rgb(157, 157, 157);}
  .qa_content > ul > li > .resmsg, .qa_content > ul > li > .delmsg {color:rgb(150, 150, 255);cursor:pointer;}
  .qa_content > ul > li > .resmsg:active, .qa_content > ul > li > .delmsg:active {color:rgb(255, 193, 7);}
  .qa_content .input-group textarea {z-index:0 !important;height: 38px !important;overflow: hidden !important;}
  .qa_content .input-group button {z-index:0 !important;}

  /* 留言給家長 */
  .stamp {display:block;max-width:1150px;margin-bottom:4px;overflow-y:auto;background-color:#F8F8F8;}
  .stamp-button {cursor:pointer;margin:0px;margin-bottom:4px;}

  /* 留言功能 */
  .toolbar {display:table;}
  .toolbar > li {position:relative;display:table-cell;vertical-align: middle;height:40px;cursor:pointer;}
  .toolbar > li:nth-of-type(1) {cursor:text;}
  .toolbar > li::after {content:"|";display:block;position:absolute;top:2px;right:-3px;color:#BCBCBC;}
  .toolbar > li:nth-of-type(1)::after ,.toolbar > li:nth-of-type(2)::after, .toolbar > li:last-child::after {display:none;}
  .toolbar > li > i {position:relative;display:block;height:25px;width:25px;overflow:hidden;margin:0px 5px;background-size:80%;background-position:center;background-repeat:no-repeat;}
  .toolbar > li > i > input {position:absolute;top:0px;left:0px;opacity:0;max-width:25px;}
  .toolbar > li > i:hover {background-color:#E7E7E7;border:1px solid #999;}
  .toolbar > li > i.pic {background-image: url("./images/toolbar/picture.png");}
  .toolbar > li > i.file {opacity:0.6;background-image: url("./images/toolbar/addfile.png");}
  .toolbar > li.messageto > select {height:28px;line-height:21px;}

  /* 留言上傳檔案 */
  .filebox {display:block;overflow:hidden;overflow-x:auto;white-space:nowrap;}
  .filebox > li {position:relative;display:inline-block;vertical-align:top;width:150px;margin-right:4px;cursor:pointer;}
  .filebox > li.fileupload > div {position:relative;}
  .filebox > li.fileupload > div > img {position:absolute;top:36px;left:45px;opacity: 0.2;}
  .filebox > li.fileupload > div > span {display:block;position:absolute;top:50px;width:150px;overflow:hidden;text-overflow:ellipsis;}
  .filebox > .delete::after {content:"";position:absolute;top:0px;right:0px;width:10px;height:10px;background-image: url("./images/toolbar/del.png");background-size:100%;background-position:center;background-repeat:no-repeat;}

  /* 其他 */
  .messagetoico::after {content:"▸";color:#000;}
  .bd1 {border:1px solid rgb(164, 164, 164);}
  .h125 {height:125px;}
  .del {position:absolute;top:0px;right:0px;width:10px;height:10px;z-index:10;cursor:pointer;}
  .privatemsg {color:rgba(0, 150, 0, 1);font-size:14px;font-style:normal;}
  /*. {width:1260px;margin:0px auto;}*/
  /* RWD */
  @media screen and (max-width: 500px) {
    .accordionPart > section.grid-item {width:100%;float:left;}
    .main_content {height:auto;width:100%;overflow:hidden;overflow-y:auto;}
  }
</style>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.1/vue.js"></script>
<!-- masonry -->
<script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
<!-- Loading套件 -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@1.5.4/src/loadingoverlay.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@1.5.4/extras/loadingoverlay_progress/loadingoverlay_progress.min.js"></script>
<script src="https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.min.js"></script>
<script>
  var iFileSizeLimit = 3072000; // 3M (1M = 1024000)
  var oItem = $.parseJSON('<?php echo $sJSOject; ?>');
  var oReMsg = {};
  var sDelFile = '';

  $(function() {
		$.LoadingOverlay('show');

		$(document).ready(function() {

      // masonry
      var $grid = $('.grid').masonry({
			  itemSelector: '.grid-item',
			  columnWidth: $('#grid-item').width(),
        // horizontalOrder: true,
        // fitWidth: true,
				gutter: 10
			});

			var $stamp = $grid.find('.stamp');
			$stamp.hide();
			var isStamped = false;
			$('.stamp-button').on( 'click', function() {
				// stamp or unstamp element
			  if ( isStamped ) {
					$stamp.hide();
			    $grid.masonry( 'unstamp', $stamp );
			  }
        else {
					$stamp.show();
			    $grid.masonry( 'stamp', $stamp );
			  }
			  // trigger layout
			  $grid.masonry('layout');
			  // set flag
			  isStamped = !isStamped;
        $('.main_content').scrollTop(0);
			});

      // loadIMG
      $('section > div.qatitle > ul > li > img').imagesLoaded()
        .always( function( instance ) {
        })
        .done( function( instance ) {
          $grid.masonry('reloadItems');
          $grid.masonry('layout');
        })
        .fail( function() {
        })
        .progress( function( instance, image ) {
        });

      // Vue
      var vueMessage = new Vue({
        el: '#msg_content',
        data: oItem,
        mounted: function () {
          var oMsgQuestion = $('.grid-item');
          $grid.prepend(oMsgQuestion).masonry('prepended', oMsgQuestion);
          $.LoadingOverlay('hide');
          $('#msg_content').css('display', '');
        },
        methods: {
          matchClass: function(sMsgCreater) {
            return (sMsgCreater == oItem.Userid);
          },
          addremsg: function(oArg) {
            if (0 === this.Message[oArg.index].response_total) {
              // 留言資料
              this.Message[oArg.index].remsg = Object.assign({}, this.Message[oArg.index].remsg, {
                0: oArg.data
              });

              // 留言index
              this.$set(this.Message[oArg.index], 'remsgindex', 1);

              // 留言筆數
              this.$set(this.Message[oArg.index], 'response_total', 1);
            }
            else {
              // 留言資料
              this.$set(this.Message[oArg.index].remsg, this.Message[oArg.index].remsgindex + 1, oArg.data);

              // 留言index
              this.$set(this.Message[oArg.index], 'remsgindex', this.Message[oArg.index].remsgindex + 1);

              // 留言筆數
              this.$set(this.Message[oArg.index], 'response_total', this.Message[oArg.index].response_total + 1);
            }
            vueMessage.$nextTick(function () {
              $grid.masonry('reloadItems');
              $grid.masonry('layout');
            })
          },
          delremsg: function (oArg) {
            // console.log(this.Message['msg_' + oArg.MsgIndex].remsg[oArg.ReMsgIndex]);
            if (!!this.Message['msg_' + oArg.MsgIndex].remsg[oArg.ReMsgIndex]) {
              this.$set(this.Message['msg_' + oArg.MsgIndex].remsg, oArg.ReMsgIndex, {});
            }
            vueMessage.$nextTick(function () {
              $grid.masonry('reloadItems');
              $grid.masonry('layout');
            })
          },
          showResmsg: function (sArg) {
            // console.log(sArg);
            return (sArg !== oItem.Userid);
          },
          showDeleteresmsg: function(sArg, sCreateTime) {
            return (sArg === oItem.Userid && '剛剛' != sCreateTime);
          },
          showContent: function (sArg) {
            var sName = sArg.match(/@[\u4e00-\u9fa5_a-zA-Z0-9]+\s/g);
            return sArg.replace(/@[\u4e00-\u9fa5_a-zA-Z0-9]+\s/g, '<i class="privatemsg">' + sName +'</i>');
          },
          clickAuth: function (sIndx) {
            if (true == this.Message[sIndx].checkResbtn) {
              this.Message[sIndx].checkResbtn = false;
            }

            vueMessage.$nextTick();
            return;
          }
        }
      })

      // 送出留言
      $('#sumbit_btn').click(function() {
        if (!$('#sel_parent').val()) {
          alert('留言失敗，沒有送的對象!');
          return;
        }

        var oMessage = {
          upload: 'INS',
          attachefile: ($('.filebox').children().length > 0) ? '1': '0',
          create_user: oItem.Userid,
          delete_flag: '0',
          read_mk: '0',
          msg_content: $('#edit_text').val(),
          touser_id: $('#sel_parent').val(),
          deletefile: sDelFile
        };

        var sReg = /(\\)|(\')|(\")/g;
        var vMathErrorChart = oMessage.msg_content.match(sReg);
        if (null != vMathErrorChart && 0 < vMathErrorChart.length) {
          // oResponse.response_content = oResponse.response_content.replace(sReg, '');
          alert('內容有不合法的特殊字元!');
          return;
        }

        if ('' == oMessage.msg_content && '0' == oMessage.attachefile) {
          alert('留言失敗，沒有內容!');
          return;
        }
        $.ajax({
            url: './modules/message/uploadmessage.php',
            data: oMessage,
            method: "POST",
            success: function (sRtn) {
              var oRtn = JSON.parse(sRtn);
              if ('SUCCESS' === oRtn.STATUS) {
                location.reload();
              }
              else {
                alert(oRtn.MSG);
                return;
              }
            },
            error: function (jqXHR, textStatus, errorMessage) {
              alert('伺服器連線不穩定，請稍後再試!');
            }
        });
      });

      // 回覆留言
      $('.qa_content > .input-group > span.input-group-btn > #send_msg').click(function (e) {
        var sIndex;
        $(e.target).parents().map(function () {
          if ('qa_content' === $(this).attr('class')) {
            sIndex = $(this).prev().attr('id');
            sIndex = sIndex.substr(sIndex.indexOf('_') + 1);
          }
        });

        var oResponse = {
          upload: 'INS',
          message_sn: sIndex,
          remsg_create_user: oItem.Userid,
          response_content: $('#response_textmsg_' + sIndex).val(),
          remsg_delete_flag: '0'
        };

        if ('' === oResponse.response_content || !!oResponse.ERR) return;

        var sReg = /(\\)|(\')|(\")/g;
        var vMathErrorChart = oResponse.response_content.match(sReg);
        if (null != vMathErrorChart && 0 < vMathErrorChart.length) {
          // oResponse.response_content = oResponse.response_content.replace(sReg, '');
          alert('內容有不合法的特殊字元!');
          return;
        }

        var sFindStr = oResponse.response_content.match(/@[\u4e00-\u9fa5_a-zA-Z0-9]+\s/g);
        if ($.isEmptyObject(oReMsg) && null !== sFindStr) {
          alert('若要回覆，請點回覆按鈕選擇對象');
          return;
        }

        if (!!oReMsg && -1 != oResponse.response_content.indexOf('@' + oReMsg.res_resmsgto_user)) {
          oResponse.res_resmsgto_userid = oReMsg.res_resmsgto_userid;
          oResponse.res_resmsgto_user = oReMsg.res_resmsgto_user;
        }

        $.ajax({
            url: './modules/message/uploadresponse.php',
            data: oResponse,
            method: "POST",
            success: function (sRtn) {

              var oRtn = JSON.parse(sRtn);
              if ('SUCCESS' === oRtn.STATUS) {
                oRtn.CONTENT = oRtn.CONTENT.replace(/@[\u4e00-\u9fa5_a-zA-Z0-9]+\s/g, '<i class="privatemsg">@' + oReMsg.res_resmsgto_user + '</i> ');
                $('.qa_content > .input-group > span.input-group-btn > #toAuth').removeClass('btn-primary');

                vueMessage.addremsg({
                  index: 'msg_' + oResponse.message_sn,
                  data: {
                    // remsg_create_time: 'oRtn.TIME',
                    remsg_create_userid: oItem.Userid,
                    remsg_create_time: '剛剛',
                    remsg_create_user: oRtn.CREATEUSER,
                    response_content: oRtn.CONTENT
                  }
                });
                $('#response_textmsg_' + sIndex).val('');
                $grid.masonry('reloadItems');
              }
              else {
                alert(oRtn.MSG);
                return;
              }
            },
            error: function (jqXHR, textStatus, errorMessage) {
                alert('伺服器連線不穩定，請稍後再試!')
            }
        });
      });

      // 留言給作者本人
      $('.qa_content > .input-group > span.input-group-btn > #toAuth').click(function (e) {
        var sIndex, sRemsgIndex, sName, sRemsgUid;

        $(e.target).parents().map(function () {
          if ('qa_content' === $(this).attr('class')) {
            sIndex = $(this).prev().attr('id');
            sIndex = sIndex.substr(sIndex.indexOf('_') + 1);
          }
        });

        sRemsgUid = oItem.Message['msg_'+ sIndex].create_userid;
        sName = oItem.Message['msg_'+ sIndex].create_user;

        $('#response_textmsg_' + sIndex).focus();

        if ('' != $('#response_textmsg_' + sIndex).val()) {
          var sFindStr = $('#response_textmsg_' + sIndex).val().match(/@[\u4e00-\u9fa5_a-zA-Z0-9]+\s/g);
          if (null !== sFindStr && 0 < $('#response_textmsg_' + sIndex).val().match(/@[\u4e00-\u9fa5_a-zA-Z0-9]+\s/g).length) {
            $('#response_textmsg_' + sIndex).val($('#response_textmsg_' + sIndex).val().replace(/@[\u4e00-\u9fa5_a-zA-Z0-9]+\s/g, '@'+ sName + ' '));
          }
        }
        if (-1 == $('#response_textmsg_' + sIndex).val().indexOf('@'+ sName + ' ')) {
          insertAtCursor($('#response_textmsg_' + sIndex)[0], '@'+ sName + ' ');
        }

        if ($(e.target).hasClass('btn-primary')) {
          $('#response_textmsg_' + sIndex).val($('#response_textmsg_' + sIndex).val().replace(/@[\u4e00-\u9fa5_a-zA-Z0-9]+\s/g, ''));
        }

        oReMsg = {
          message_sn: sIndex,
          remsg_create_user: oItem.Userid,
          res_resmsgto_userid: sRemsgUid,
          res_resmsgto_user: sName,
          response_content: $('#response_textmsg_' + sIndex).val(),
          remsg_delete_flag: '0'
        };

        $(e.target).toggleClass('btn-primary');
      });

      // 回覆留言的訊息
      $('.qa_content > ul > li > .resmsg').click(function (e) {
        $('.qa_content > .input-group > span.input-group-btn > #toAuth').removeClass('btn-primary');

        var sIndex, sName, sRemsgUid;
        $(e.target).parents().map(function () {
          if ('qa_content' === $(this).attr('class')) {
            sIndex = $(this).prev().attr('id');
            sIndex = sIndex.substr(sIndex.indexOf('_') + 1);
            return;
          }
        });

        var sRemsgIndex = $(e.target).parent().parent().attr('id');
        sRemsgIndex = sRemsgIndex.substr(sRemsgIndex.indexOf('_') + 1);
        sRemsgUid = '';
        if (!!oItem.Message['msg_'+ sIndex].remsg[sRemsgIndex].remsg_create_userid) {
          sRemsgUid = oItem.Message['msg_'+ sIndex].remsg[sRemsgIndex].remsg_create_userid;
        }
        // console.log(123123);
        $(e.target).parent().prevAll().map(function (e) {
          if ('name' === $(this).attr('class')) {
            sName = $(this).html();
            return;
          }
        });
        $('#response_textmsg_' + sIndex).focus();

        if ('' != $('#response_textmsg_' + sIndex).val()) {
          var sFindStr = $('#response_textmsg_' + sIndex).val().match(/@[\u4e00-\u9fa5_a-zA-Z0-9]+\s/g);
          if (null !== sFindStr && 0 < $('#response_textmsg_' + sIndex).val().match(/@[\u4e00-\u9fa5_a-zA-Z0-9]+\s/g).length) {
            $('#response_textmsg_' + sIndex).val($('#response_textmsg_' + sIndex).val().replace(/@[\u4e00-\u9fa5_a-zA-Z0-9]+\s/g, '@'+ sName + ' '));
          }
        }

        if (-1 == $('#response_textmsg_' + sIndex).val().indexOf('@'+ sName + ' ')) {
          insertAtCursor($('#response_textmsg_' + sIndex)[0], '@'+ sName + ' ');
        }

        oReMsg = {
          message_sn: sIndex,
          remsg_create_user: oItem.Userid,
          res_resmsgto_userid: sRemsgUid,
          res_resmsgto_user: sName,
          response_content: $('#response_textmsg_' + sIndex).val(),
          remsg_delete_flag: '0'
        };
      });

      // 刪除回覆的留言
      $('.qa_content > ul > li > .delmsg').click(function (e) {
        var bSureDelete = confirm("確定要刪除訊息?");
        if (bSureDelete != true) return;

        var sMsgIndex, sReMsgIndex;
        $(e.target).parents().map(function () {
          if ('qa_content' === $(this).attr('class')) {
            sMsgIndex = $(this).prev().attr('id');
            sMsgIndex = sIndex.substr(sIndex.indexOf('_') + 1);
            return;
          }
        });
        // console.log($(e.target).parent().parent());
        sReMsgIndex = $(e.target).parent().parent().attr('id');
        sReMsgIndex = sReMsgIndex.substr(sReMsgIndex.indexOf('_') + 1);

        var oResponse = {
          upload: 'DEL',
          message_sn: sReMsgIndex
        };

        $.ajax({
            url: './modules/message/uploadresponse.php',
            data: oResponse,
            method: "POST",
            success: function (sRtn) {

              var oRtn = JSON.parse(sRtn);
              if ('SUCCESS' === oRtn.STATUS) {
                location.reload();
              }
              else {
                alert(oRtn.MSG);
                return;
              }
            },
            error: function (jqXHR, textStatus, errorMessage) {
                alert('伺服器連線不穩定，請稍後再試!')
            }
        });
      });

      // 新增 檔案
      $('body').on('change', '.toolbar > li > i > input', function (e) {
        if (typeof this.files[0] === 'undefined') return;

        // 給後端驗證,故marked
        // var vPassType = ['png','jpg','jpeg','bmp','gif','doc','docx','xls','xlsx','ppt','pptx','txt','pdf'];
        var filename = this.files[0].name;
        var filetype = this.files[0].type;
        var filesize = this.files[0].size;

        if (iFileSizeLimit < filesize) {
          alert('您的檔案過大，檔案大小限制為3M');
          return;
        }
        if (1 < filename.split('.').length-1) {
          alert('您的檔案名稱不能含有特殊字元 .');
          return;
        }
        // if (-1 == $.inArray(filename.split('.').pop(), vPassType)) {
        //   alert('不接受此格式檔案!');
        //   return;
        // }
        var fileupload = $('#uplodefile').prop('files')[0];
        var oForm = new FormData();
        oForm.append('import_file', fileupload);

        $.ajax({
            url: './modules/message/checkfile.php',
            data: oForm,
            method: "POST",
            processData: false,
            contentType: false,
            success: function (sRtn) {
              var oRtn = JSON.parse(sRtn);
              if ('SUCCESS' === oRtn.STATUS) {
                var reader = new FileReader();
                reader.onload = function (e) {
                  $('.filebox').height('150px');
                  if (0 <= filetype.indexOf('image')) {
                    $('.filebox').append('<li id="' + oRtn.fileid + '" class="delete"><span class="del">&nbsp;</span><img src="' + e.target.result + '" /></li>');
                  }
                  else {
                    $('.filebox').append('<li id="' + oRtn.fileid + '" class="fileupload bd1 h125 delete"><span class="del">&nbsp;</span><div><img src="./images/toolbar/file.png" /><span>' + filename + '</span></div></li>');
                  }
                  $grid.masonry('reloadItems');
                  $grid.masonry('layout');
                }
                reader.readAsDataURL(fileupload);
              }
              else {
                alert('檔案上傳失敗' + ' ' + oRtn.MSG);
                return;
              }
            },
            error: function (jqXHR, textStatus, errorMessage) {
              alert('伺服器連線不穩定，請稍後再試!');
            }
        });
      })

      // 刪除 圖片/檔案/留言
      $('body').on('click', '.del', function (e) {

        // 刪除 圖片/檔案
        if ($(e.target).parent().is('li')) {
          sDelFile += '[' + $(e.target).parent().attr('id') + ']';
          $(e.target).parent().remove();

          // 如果沒有檔案, 畫面縮小
          if (0 === $('.filebox').children().length) {
            $('.filebox').height('0px');
            $grid.masonry('reloadItems');
            $grid.masonry('layout');
          }
        }

        // 刪除留言
        if ($(e.target).parent().is('section')) {
          var bSureDelete = confirm("確定要刪除訊息?\n留言和附檔都會一併被刪除。");
          if (bSureDelete != true) return;

          var sDelIndex = $(e.target).next().attr('id');
          sDelIndex = sDelIndex.substr(sDelIndex.indexOf('_') + 1);

          var oDelMessage = {
            upload: 'DEL',
            messageid: sDelIndex
          };

          $.ajax({
              url: './modules/message/uploadmessage.php',
              data: oDelMessage,
              method: "POST",
              success: function (sRtn) {
                var oRtn = JSON.parse(sRtn);
                if ('SUCCESS' === oRtn.STATUS) {
                  location.reload();
                }
                else {
                  alert(oRtn.MSG);
                  return;
                }
              },
              error: function (jqXHR, textStatus, errorMessage) {
                alert('伺服器連線不穩定，請稍後再試!');
              }
          });
        }
      });

      // textarea 自動高
      $('.auto-height').css('overflow', 'hidden').bind('keydown keyup', function(e) {

        if ($(e.target).hasClass('form-control')) {
          $(this).attr("style", "height:0px !important").attr("style", "height: " + $(this).prop('scrollHeight') + "px !important");
          $('#stamp').attr("style", "height: " + $(this).height() + "px !important");
        }
        else {
          $(this).height('0px').height($(this).prop('scrollHeight') + 'px');
          $('#stamp').height($(this).height());
        }

        $grid.masonry('reloadItems');
        $grid.masonry('layout');
      });

      // 打開留言
			$grid.on('click','.grid-item', function(e) {
        if ($(e.target).is('textarea') || $(e.target).closest('div').hasClass('qa_content') ||
            $(e.target).parent().hasClass('filename') || $(e.target).is('button') || $(e.target).hasClass('del')) {
          return;
        }

				$(this).toggleClass('open');
			  $grid.masonry('reloadItems');
			  $grid.masonry('layout');
        $(e.target).parents().map(function () {
          if ('qa_title' === $(this).attr('class') || 'qa_title delete' === $(this).attr('class')) {
            sLongIndex = $(this).attr('id');
            sIndex = sLongIndex.substr(sLongIndex.indexOf('_') + 1);

            var oMessage = {
              upload: 'MOD',
              messageid: sIndex
            };

            $.ajax({
                url: './modules/message/uploadmessage.php',
                data: oMessage,
                method: "POST",
                success: function (sRtn) {
                  var oRtn = JSON.parse(sRtn);
                  if ('SUCCESS' === oRtn.STATUS) {
                    // console.log(oRtn);
                  }
                  else {
                    alert(oRtn.MSG);
                    return;
                  }
                },
                error: function (jqXHR, textStatus, errorMessage) {
                  alert('伺服器連線不穩定，請稍後再試!');
                }
            });
            if ($('#' + sLongIndex + '_section').hasClass('open')) {
              $('.accordionPart > section.grid-item').fadeTo('fast',0.2);
              $('#response_textmsg_' + sIndex).focus();
              $('#' + sLongIndex + '_section').fadeTo('fast',1);
            }
            else {
              $('.accordionPart > section.grid-item').fadeTo('fast',1);
            }

            $grid.masonry('reloadItems');
            $grid.masonry();
          }
        });
			});
  });
});

function insertAtCursor(myField, myValue) {
  if ('@undefined' == myValue) return;

    //IE support
    if (document.selection) {
        myField.focus();
        sel = document.selection.createRange();
        sel.text = myValue;
    }
    //MOZILLA and others
    else if (myField.selectionStart || myField.selectionStart == '0') {
        var startPos = myField.selectionStart;
        var endPos = myField.selectionEnd;
        myField.value = myValue
          + myField.value.substring(0, startPos)
          + myField.value.substring(endPos, myField.value.length);
        myField.selectionStart = startPos + myValue.length;
        myField.selectionEnd = startPos + myValue.length;
    } else {
        myField.value += myValue;
    }
}
</script>
  <div class="content2-Box">
	  <div class="path">目前位置：親師互動</div>
      <div class="choice-box">
        <div class="choice-title">選單</div>
          <ul class="choice work-cholic">
        	  <li><a href="" class="current"><i class="fa fa-caret-right"></i>親師互動</a></li>
          </ul>
   		 </div>
 			<div class="right-box" style="width:100%;margin:0px auto;">
        <button id="stamp_btn" class="btn06 stamp-button" style="width:120px;"><?php echo $sButtonLable; ?></button>
        <article class="main_content">
          <div class="accordionPart grid">
						<section class="stamp">
              <ul class="toolbar">
                <li>留言<span class="messagetoico"></span></li>
                <li class="messageto">
<?php echo implode('', $vTo); ?>
                </li>
                <li><i class="file" title="上傳圖片/檔案"><input id="uplodefile" type="file" value="" /></i></li>
              </ul>
							<textarea id="edit_text" name="edit_text" class="auto-height"></textarea>
              <ul class="filebox"></ul>
							<button id="sumbit_btn" name="sumbit_btn" class="btn04" style="float:right;margin:0px;">確認</button>
						</section>
            <div id="msg_content" style="display:none;">
              <section v-for="(item, index) in Message" v-bind:id="index + '_section'" class="grid-item">
                <span class="del">&nbsp;</span>
                <div v-bind:id="index" class="qa_title" v-bind:class="[matchClass(item.create_userid) ? 'delete' : '']">
  								<ul>
  									<li><span class="name">{{item.create_user}}<span class="messagetoico"></span>{{item.touser_name}}</span><span class="time">{{item.create_time}}</span></li>
  									<li>
                      <span class="text" v-html="item.msg_content"></span>
  									</li>
                    <li v-if="item.msgimg">
                      <img v-for="oIMG in item.msgimg" v-bind:src="oIMG.filesrc" />
                    </li>
  									<li>
                      <span class="attachedfile">
                        <i class="ico" v-if="item.msgfile"></i>
                        <span class="filename" v-for="oFile in item.msgfile">
                          <span v-if="oFile.file_orgnialname"><a v-bind:href="oFile.filesrc" v-bind:download="oFile.file_orgnialname">{{oFile.file_orgnialname}}</a></span>
                        </span>
                        <span class="messageinfo" v-if="item.response_total">留言數({{item.response_total}})</span>
                      </span>
                    </li>
  								</ul>
  							</div>
                <div class="qa_content">
                  <ul v-for="(oReMsg, ReMsgIndx) in item.remsg">
                      <li v-bind:id="'resmsg_'+ReMsgIndx">
                      <span class="name">{{oReMsg.remsg_create_user}}</span>
                      <span class="text" v-html="showContent(oReMsg.response_content)"></span>
                      <span class="time">{{oReMsg.remsg_create_time}}</span>
                      <span class="resmsg" v-if="showResmsg(oReMsg.remsg_create_userid)"><ins id="res_resmsg" title="只有本人能看到回覆的訊息，一次只能回覆一名對象">回覆</ins></span>
                      <span class="delmsg" v-if="showDeleteresmsg(oReMsg.remsg_create_userid, oReMsg.remsg_create_time)"><ins id="res_delmsg">刪除</ins></span>
                    </li>
                  </ul>
                  <div class="input-group" style="padding:4px;">
                    <span class="input-group-btn">
                      <button id="toAuth" type="button" class="btn" title="只有本人能看到回覆的訊息，一次只能回覆一名對象" v-if="showResmsg(item.create_userid)">回覆作者</button>
                    </span>
                    <textarea v-bind:id="'response_text' + index" class="form-control auto-height" type="text" placeholder="留言‧‧‧‧‧" rows="1"></textarea>
                    <span class="input-group-btn">
                      <button id="send_msg" type="button" class="btn btn-success">送出</button>
                    </span>
                  </div>
                </div>
  						</section>
            </div>
          </article>
        </div>
	   </div>
	 </div>
</html>
