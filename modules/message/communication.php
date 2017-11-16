<?php
  require_once('./include/config.php');
  require_once('./include/adp_API.php');

  if (!isset($_SESSION)) {
  	session_start();
  }

  // 取得 user 資料
  $vUserData = get_object_vars($_SESSION['user_data']);
  $sUserID = $vUserData['user_id'];

  $vToParent = getParents($vUserData['grade'], $vUserData['class_name']);
  $vMessageData = getMessage($sUserID);
  $vMessageData = handleData($vMessageData);

  // 整理資料, 統一變數傳至HTML
  $sJSOject = arraytoJS(array('Userid' => $sUserID,
                              'Message' => $vMessageData));

  function getMessage($sUserID) {
    global $dbh;

    $sSQLMessage = "SELECT * FROM message_master
      LEFT JOIN message_response ON message_master.msg_sn = message_response.message_sn
      LEFT JOIN message_fileattached ON message_master.msg_sn = message_fileattached.message_sn
      WHERE message_master.msg_type = '2'
      AND message_master.delete_flag = '0'
      AND (message_master.touser_id = '$sUserID' OR message_master.create_user ='$sUserID')
      UNION
      SELECT * FROM message_master
      RIGHT JOIN message_response ON message_master.msg_sn = message_response.message_sn
      RIGHT JOIN message_fileattached ON message_master.msg_sn = message_fileattached.message_sn
      WHERE message_master.msg_type = '2'
      AND message_master.delete_flag = '0'
      AND (message_master.touser_id = '$sUserID' OR message_master.create_user ='$sUserID') ORDER BY msg_sn DESC";
    $oMessage = $dbh->prepare($sSQLMessage);
    $oMessage->execute();
    $vMessageData = $oMessage->fetchAll(\PDO::FETCH_ASSOC);

    return $vMessageData;
  }

  function getParents($sGroup, $sClass) {
    global $dbh;

    $sSQLParents = "SELECT user_info.user_id, user_family.fuser_id FROM user_info
      LEFT JOIN user_family ON user_info.user_id = user_family.user_id
      WHERE user_info.grade = '6'
      AND user_info.class = '9'
      AND user_info.user_id LIKE '%ss%'
      AND LENGTH(user_family.user_id) > 4";
    $oParents = $dbh->prepare($sSQLParents);
    $oParents->execute();
    $vParentsData = $oParents->fetchAll(\PDO::FETCH_ASSOC);

    if (!empty($vParentsData) && is_array($vParentsData)) {
      $vToParent = array();
      $vToParent[] = '<select id="sel_parent">';
      $vToParent[] =   '<option value="">全部家長</option>';
      foreach($vParentsData as $vParent) {
        $sStudentName = id2uname($vParent['user_id']);
        $vToParent[] = '<option value="'.$vParent['fuser_id'].'">'.$sStudentName.'  家長</option>';
      }
      $vToParent[] = '</select>';
    }

    return $vToParent;
  }

  function handleData($vMessageData) {
    global $sUserID;

    $vNewData = array();
    foreach ($vMessageData as $key => $vMsg) {
      if (!isset($vNewData['msg_'.$vMsg['msg_sn']])) {
        $vNewData['msg_'.$vMsg['msg_sn']]['msg_sn'] = $vMsg['msg_sn'];
        $vNewData['msg_'.$vMsg['msg_sn']]['touser_name'] = id2uname($vMsg['touser_id']);
        $vNewData['msg_'.$vMsg['msg_sn']]['create_user'] = id2uname($vMsg['create_user']);
        $vNewData['msg_'.$vMsg['msg_sn']]['create_userid'] = $vMsg['create_user'];
        $vNewData['msg_'.$vMsg['msg_sn']]['create_time'] = substr($vMsg['create_time'], 0, 16);
        $vNewData['msg_'.$vMsg['msg_sn']]['msg_content'] = str_replace(array("\r", "\n", "\r\n", "\n\r"), '<br>', $vMsg['msg_content']);
        $vNewData['msg_'.$vMsg['msg_sn']]['attachefile'] = $vMsg['attachefile'];
        $vNewData['msg_'.$vMsg['msg_sn']]['read_mk'] = $vMsg['read_mk'];
        $vNewData['msg_'.$vMsg['msg_sn']]['delete_flag'] = $vMsg['delete_flag'];
        $vNewData['msg_'.$vMsg['msg_sn']]['response_total'] = 0;
      }
      if (isset($vMsg['file_sn'])) {
        switch($vMsg['filetype']) {
          case 'image':
            $vNewData['msg_'.$vMsg['msg_sn']]['msgimg'][$vMsg['file_sn']] = array('filesrc' => './data/message/'.$sUserID.'/'.$vMsg['file_replacename']);
            break;

          default:
            $vNewData['msg_'.$vMsg['msg_sn']]['msgfile'][$vMsg['file_sn']] = array('file_orgnialname' => $vMsg['file_orgnialname'],
                                                                                   'file_replacename' => $vMsg['file_replacename'],
                                                                                   'upload_time' => substr($vMsg['upload_time'], 0, 16),
                                                                                   'filesrc' => './data/message/'.$sUserID.'/'.$vMsg['file_replacename']);
            break;
        }
      }
      if ($vMsg['response_sn']) {
        $vNewData['msg_'.$vMsg['msg_sn']]['response_total']++;
        $vNewData['msg_'.$vMsg['msg_sn']]['remsgindex'] = $vMsg['response_sn'];
        $vNewData['msg_'.$vMsg['msg_sn']]['remsg'][$vMsg['response_sn']] = array('response_content' => str_replace(array("\r", "\n", "\r\n", "\n\r"), '<br>', $vMsg['response_content']),
                                                                                 'remsg_create_user' => id2uname($vMsg['remsg_create_user']),
                                                                                 'remsg_create_time' => substr($vMsg['remsg_create_time'], 0, 16));
      }
    }
    return $vNewData;
  }
?>
<!DOCTYPE HTML>
<html>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
<style>
  ul, li {margin:0px;padding:0px;list-style:none;}
  textarea {resize:none;}
  .main_content {height:700px;width:100%;overflow:hidden;overflow-y:auto;}

  .main_content::-webkit-scrollbar-track {-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);border-radius: 10px;background-color: #F5F5F5;}
  .main_content::-webkit-scrollbar {width: 10px;background-color: #F5F5F5;}
  .main_content::-webkit-scrollbar-thumb {border-radius: 10px;-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);background-color: #555;}
  .filebox::-webkit-scrollbar-track {-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);border-radius: 10px;background-color: #F5F5F5;}
  .filebox::-webkit-scrollbar {height:10px; width: 10px;background-color: #F5F5F5;}
  .filebox::-webkit-scrollbar-thumb {border-radius: 10px;-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);background-color: #555;}

  /* 留言浮動框 */
  .grid-item {position:relative;margin-bottom:5px;height:250;}
  .accordionPart > section {border:solid 1px #E3E3E3;}
  .accordionPart > section.grid-item {width:320px;float:left;}
  .accordionPart > section.open {width:100%;float:left;}
  .accordionPart > section.open > div.qa_content {display:inherit !important;background-color:#f6f7f9;}
  .accordionPart > section > div.qa_title {cursor:pointer;position:relative;}
  .accordionPart > section > div.delete::after {content:"";position:absolute;top:0px;right:0px;width:10px;height:10px;background-image: url("./images/toolbar/del.png");background-size:100%;background-position:center;background-repeat:no-repeat;}
  .accordionPart > section > div.qa_content {display:none;}

  /* 留言標題 */
  .qa_title > ul > li {position:relative;overflow:hidden;}
  .qa_title > ul > li > .name {color:rgb(0, 0, 255);}
  .qa_title > ul > li > .time {padding:0px 4px;font-size:14px;max-width:11em;color:#999;vertical-align:middle;white-space:nowrap;display:inline-block;text-overflow:ellipsis;overflow:hidden;}
  .qa_title > ul > li > .info {display:inline-block;height:25px;float:right;font-size:14px;margin-right:1em;}
  .qa_title > ul > li > .attachedfile {display:flex;height:25px;font-size:14px;vertical-align:middle;justify-content:flex-end;}
  .qa_title > ul > li > .attachedfile > .ico {display:inline-block;height:25px;width:25px;margin:0px 5px;background-size:80%;background-position:center;background-repeat:no-repeat;background-image: url("./images/toolbar/file.png");}
  .qa_title > ul > li > .attachedfile > .filename {flex:80;display:flex;justify-content:stretch;overflow:hidden;white-space: nowrap;}
  .qa_title > ul > li > .attachedfile > .filename > span {margin-right:4px;overflow:hidden;text-overflow:ellipsis;text-decoration:underline;}
  .qa_title > ul > li > .attachedfile > .filename > span:hover {color:rgb(0, 0, 255);}

  /* 留言回覆 */
  .qa_content > ul > li > .name {color:rgb(0, 0, 255);}
  .qa_content > ul > li > .time {font-size:14px;color:rgb(157, 157, 157);}
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
  .toolbar > li.messageto > select {height:28px;margin-top:5px;}

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

  /* RWD */
  @media screen and (min-width: 500px) {
    .accordionPart > section.grid-item {width:380px;float:left;}
    .accordionPart > section.open {width:100%;float:left;}
    /*.qa_title > ul > li > .time {max-width:11em;}*/
  }
</style>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.1/vue.js"></script>
<!-- masonry -->
<script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
<!-- Loading套件 -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@1.5.4/src/loadingoverlay.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@1.5.4/extras/loadingoverlay_progress/loadingoverlay_progress.min.js"></script>
<script>
  var iFileSizeLimit = 3072000; // 3M (1M = 1024000)
  var oItem = $.parseJSON('<?php echo $sJSOject; ?>');
  var sDelFile = '';

  console.log(oItem);
  $(function() {
		$.LoadingOverlay('show');

		$(document).ready(function() {
      // masonry
      var $grid = $('.grid').masonry({
			  itemSelector: '.grid-item',
			  columnWidth: $('#grid-item').width(),
				gutter: 5
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

      // Vue
      var vueMessage = new Vue({
        el: '#msg_content',
        data: oItem,
        mounted: function () {
          var oMsgQuestion = $('.grid-item');
          $grid.prepend(oMsgQuestion).masonry('prepended', oMsgQuestion);
        },
        methods: {
          matchClass: function(sMsgCreater) {
            return (sMsgCreater == oItem.Userid);
          },
          addremsg: function(oArg) {
            console.log(oArg);
            if (0 === this.Message[oArg.index].response_total) {
              // 留言資料
              this.Message[oArg.index].remsg = Object.assign({}, this.Message[oArg.index] .remsg, {
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
          }
        }
      })

      // 送出留言
      $('#sumbit_btn').click(function() {
        var oMessage = {
          attachefile: ($('.filebox').children().length > 0) ? '1': '0',
          create_user: oItem.Userid,
          delete_flag: '0',
          read_mk: '0',
          msg_content: $('#edit_text').val(),
          touser_id: $('#sel_parent').val(),
          deletefile: sDelFile
        };

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
              }
            },
            error: function (jqXHR, textStatus, errorMessage) {
              alert('伺服器連線不穩定，請稍後再試!');
            }
        });
      });

      // 回覆留言
      $('.qa_content > .input-group > .input-group-btn > button').click(function (e) {
        var sIndex;
        $(e.target).parents().map(function () {
          if ('qa_content' === $(this).attr('class')) {
            sIndex = $(this).prev().attr('id');
            sIndex = sIndex.substr(sIndex.indexOf('_') + 1);
          }
        });
        var oResponse = {
          message_sn: sIndex,
          remsg_create_user: oItem.Userid,
          response_content: $('#response_textmsg_' + sIndex).val(),
          remsg_delete_flag: '0'
        };

        if ('' === oResponse.response_content) return;

        $.ajax({
            url: './modules/message/uploadresponse.php',
            data: oResponse,
            method: "POST",
            success: function (sRtn) {

              var oRtn = JSON.parse(sRtn);
              if ('SUCCESS' === oRtn.STATUS) {
                vueMessage.addremsg({
                  index: 'msg_' + oResponse.message_sn,
                  data: {
                    remsg_create_time: oRtn.TIME,
                    remsg_create_user: oRtn.CREATEUSER,
                    response_content: oRtn.CONTENT
                  }
                });
                $('#response_textmsg_' + sIndex).val('');
                $grid.masonry('reloadItems');
              }
              else {
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

        var vPassType = ['png','jpg','jpeg','bmp','gif','doc','docx','xls','xlsx','ppt','pptx','txt','pdf'];
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
        if (-1 == $.inArray(filename.split('.').pop(), vPassType)) {
          alert('不接受此格式檔案!');
          return;
        }
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
          var sDelIndex = $(e.target).next().attr('id');
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
          if ('qa_title' === $(this).attr('class')) {
            window.location.href = '#' + $(this).attr('id');
          }
        });
			});

      $grid.masonry();
      $.LoadingOverlay('hide');
      $('#msg_content').css('display', '');
  });
});

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
        <button class="btn06 stamp-button" style="width:120px;">留言給家長</button>
        <article class="main_content">
          <div class="accordionPart grid">
						<section class="stamp">
              <ul class="toolbar">
                <li>留言<span class="messagetoico"></span></li>
                <li class="messageto">
<?php echo implode('', $vToParent); ?>
                </li>
                <li><i class="file" title="上傳圖片/檔案"><input id="uplodefile" type="file" value="" /></i></li>
              </ul>
							<textarea id="edit_text" name="edit_text" class="auto-height"></textarea>
              <ul class="filebox"></ul>
							<button id="sumbit_btn" name="sumbit_btn" class="btn04" style="float:right;margin:0px;">確認</button>
						</section>
            <div id="msg_content" style="display:none;">
              <section v-for="(item, index) in Message" class="grid-item">
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
                        <span class="info" v-if="item.response_total">留言數({{item.response_total}})</span>
                      </span>
                    </li>
  								</ul>
  							</div>
                <div class="qa_content">
                  <ul v-for="oReMsg in item.remsg">
                    <li>
                      <span class="name">{{oReMsg.remsg_create_user}}</span>
                      <span class="text" v-html="oReMsg.response_content"></span>
                      <span class="time">{{oReMsg.remsg_create_time}}</span>
                    </li>
                  </ul>
                  <div class="input-group" style="padding:4px;">
                    <textarea v-bind:id="'response_text' + index" class="form-control auto-height" type="text" placeholder="留言‧‧‧‧‧" rows="1"></textarea>
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-secondary">送出</button>
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
