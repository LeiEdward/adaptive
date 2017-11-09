<?php
  require_once('./include/config.php');
  require_once('./include/adp_API.php');

  if (!isset($_SESSION)) {
  	session_start();
  }
  $sUserID = $_SESSION['user_id'];
  $sSQLMessage = "SELECT * FROM message_master
    LEFT JOIN message_response ON message_master.msg_sn = message_response.message_sn
    LEFT JOIN message_fileattached ON message_master.msg_sn = message_fileattached.message_sn
    WHERE message_master.msg_type='2'
    AND message_master.delete_flag='0'
    AND (message_master.touser_id = '$sUserID' OR message_master.create_user ='$sUserID')
    UNION
    SELECT * FROM message_master
    RIGHT JOIN message_response ON message_master.msg_sn = message_response.message_sn
    RIGHT JOIN message_fileattached ON message_master.msg_sn = message_fileattached.message_sn
    WHERE message_master.msg_type='2'
    AND message_master.delete_flag = '0'
    AND (message_master.touser_id = '$sUserID' OR message_master.create_user ='$sUserID')";
  $oMessage = $dbh->prepare($sSQLMessage);
  $oMessage->execute();
  $vMessageData = $oMessage->fetchAll(\PDO::FETCH_ASSOC);

  $vMessageData = handleData($vMessageData);

  // 整理資料, 統一變數傳至HTML
  $sJSOject = arraytoJS(array('Userid' => $sUserID,
                              'Message' => $vMessageData));

  function handleData($vMessageData) {
    $vNewData = array();
    foreach ($vMessageData as $key => $vMsg) {
      if (!isset($vNewData[$vMsg['msg_sn']])) {
        $vNewData[$vMsg['msg_sn']]['msg_sn'] = $vMsg['msg_sn'];
        $vNewData[$vMsg['msg_sn']]['touser_name'] = id2uname($vMsg['touser_id']);
        $vNewData[$vMsg['msg_sn']]['create_user'] = id2uname($vMsg['create_user']);
        $vNewData[$vMsg['msg_sn']]['create_time'] = substr($vMsg['create_time'], 0, 16);
        $vNewData[$vMsg['msg_sn']]['msg_content'] = str_replace(array("\r", "\n", "\r\n", "\n\r"), '<br>', $vMsg['msg_content']);
        $vNewData[$vMsg['msg_sn']]['attachefile'] = $vMsg['attachefile'];
        $vNewData[$vMsg['msg_sn']]['read_mk'] = $vMsg['read_mk'];
        $vNewData[$vMsg['msg_sn']]['delete_flag'] = $vMsg['delete_flag'];
        $vNewData[$vMsg['msg_sn']]['response_total'] = 0;
      }
      if (isset($vMsg['file_sn'])) {
        $vNewData[$vMsg['msg_sn']]['msgfile'][$vMsg['file_sn']] = array('file_orgnialname' => $vMsg['file_orgnialname'],
                                                                        'file_replacename' => $vMsg['file_replacename'],
                                                                        'upload_time' => substr($vMsg['upload_time'], 0, 16));

      }
      if ($vMsg['response_sn']) {
        $vNewData[$vMsg['msg_sn']]['response_total']++;
        $vNewData[$vMsg['msg_sn']]['remsg'][$vMsg['response_sn']] = array('response_content' => $vMsg['response_content'],
                                                                          'remsg_create_user' => $vMsg['remsg_create_user'],
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
  .accordionPart > section {border:solid 1px #E3E3E3;}
  .accordionPart > section.grid-item {width:320px;float:left;}
  .accordionPart > section.open {width:100%;float:left;}
  .accordionPart > section.open > div.qa_content {display:inherit !important;background-color:#f6f7f9;}
  .accordionPart > section > div.qa_title {cursor: pointer;}
  .accordionPart > section > div.qa_content {display:none;}

  /* 留言標題 */
  .qa_title > ul > li {position:relative;overflow:hidden;}
  .qa_title > ul > li > .name {color:rgb(0, 0, 255);}
  .qa_title > ul > li > .time {padding:0px 4px;font-size:14px;max-width:8em;vertical-align:middle;white-space:nowrap;display:inline-block;text-overflow:ellipsis;overflow:hidden;}
  /*.qa_title > ul > li > .text {padding-left:1em;}*/
  .qa_title > ul > li > .info {display:inline-block;height:25px;float:right;font-size:14px;margin-right:1em;}
  .qa_title > ul > li > .attachedfile {display:flex;height:25px;font-size:14px;vertical-align:middle;}
  .qa_title > ul > li > .attachedfile > .ico {display:inline-block;height:25px;width:25px;margin:0px 5px;background-size:80%;background-position:center;background-repeat:no-repeat;background-image: url("./images/toolbar/file.png");}
  .qa_title > ul > li > .attachedfile > .filename {flex:80;display:flex;justify-content:stretch;overflow:hidden;white-space: nowrap;}
  .qa_title > ul > li > .attachedfile > .filename > span {margin-right:4px;overflow:hidden;text-overflow:ellipsis;text-decoration:underline;}
  .qa_title > ul > li > .attachedfile > .filename > span:hover {color:rgb(0, 0, 255);}

  /* 留言回覆 */
  .qa_content > ul > li > .name {color:rgb(0, 0, 255);}
  .qa_content > ul > li > .time {font-size:14px;color:rgb(157, 157, 157);}
  .qa_content .input-group textarea {height: 38px !important;overflow: hidden !important;}

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

  /* RWD */
  @media screen and (min-width: 500px) {
    .accordionPart > section.grid-item {width:380px;float:left;}
    .accordionPart > section.open {width:100%;float:left;}
    .qa_title > ul > li > .time {max-width:11em;}
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
        beforeUpdate: function () {
        },
        Update: function () {
        }
      })

      // 送出留言
      $('#sumbit_btn').click(function() {
        var dt = new Date();
        var oMessage = {
          attachefile: '0',
          create_time: dt.getFullYear() + '-' + dt.getMonth() + '-' + dt.getDate() + '-' + dt.getHours() + '-' + dt.getMinutes() + '-' + dt.getSeconds(),
          create_user: oItem.Userid,
          delete_flag: '0',
          read_mk: '0',
          msg_content: $('#edit_text').val(),
          touser_id: 'yuanhsuanch@gmail.com',
          touser_name: 'dhc'
        };
        // oItem.Message.push(oMessage);

        $.ajax({
            url: './modules/message/uploadmessage.php',
            data: oMessage,
            method: "POST",
            success: function (sRtn) {
              location.reload();
              var oRtn = JSON.parse(sRtn);
              if ('SUCCESS' === oRtn.STATUS) {
              }
              else {
                // alert('檔案上傳失敗' + ' ' + oRtn.MSG);
              }
            },
            error: function (jqXHR, textStatus, errorMessage) {
                alert('伺服器連線不穩定，請稍後再試!')
            }
        });
      });

      // 回覆留言
      $('.qa_content > div > input').keydown(function (e) {
        if (13 == event.which) {
          // send
        }
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
              console.log(sRtn);
              var oRtn = JSON.parse(sRtn);
              if ('SUCCESS' === oRtn.STATUS) {
                var reader = new FileReader();
                reader.onload = function (e) {
                  $('.filebox').height('150px');
                  if (0 <= filetype.indexOf('image')) {
                    $('.filebox').append('<li class="imgupload delete"><img src="' + e.target.result + '" /></li>');
                  }
                  else {
                    $('.filebox').append('<li class="fileupload bd1 h125 delete"><div><img src="./images/toolbar/file.png" /><span>' + filename + '</span></div></li>');
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
                alert('伺服器連線不穩定，請稍後再試!')
            }
        });
      })

      // 刪除 圖片/檔案
      $('body').on('click', '.filebox > li', function (e) {
        var sX = $(this).position().left;
        var sY = $(this).position().top;
        if (196 <= (e.pageX - sX) && 306 >= (e.pageY - sY) && $(e.target).is('li')) {
          e.target.remove();

          // 如果沒有檔案, 畫面縮小
          if (0 === $('.filebox').children().length) {
            $('.filebox').height('0px');
            $grid.masonry('reloadItems');
            $grid.masonry('layout');
          }
        }
      });

      // textarea 自動高
      $('.auto-height').css('overflow', 'hidden').bind('keydown keyup', function(e) {
        // if (e.ctrlKey && e.keyCode == 13 && $(e.target).parent().is('div')) {
        //   var val = this.value;
        //     console.log('val',  val);
        //     if (typeof this.selectionStart == "number" && typeof this.selectionEnd == "number") {
        //         var start = this.selectionStart;
        //         this.value = val.slice(0, start) + "\n" + val.slice(this.selectionEnd);
        //         this.selectionStart = this.selectionEnd = start + 1;
        //         console.log(start ,this.selectionStart, this.selectionEnd);
        //     } else if (document.selection && document.selection.createRange) {
        //         this.focus();
        //         var range = document.selection.createRange();
        //         range.text = "\r\n";
        //         range.collapse(false);
        //         range.select();
        //     }
        // }
        // if (e.keyCode == 13 && $(e.target).parent().is('div')) {
          // return false;
        // }

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
      }).keydown();

      // 留言區塊
			$grid.on('click','.grid-item', function(e) {
        if ($(e.target).is('textarea') || $(e.target).closest('div').hasClass('qa_content') || $(e.target).parent().hasClass('filename')) {
          return;
        }

				$(this).toggleClass('open');
			  $grid.masonry('reloadItems');
			  $grid.masonry('layout');

        var ranId = Math.random();
        $(e.target).attr('id', ranId);
        // window.location.href = '#' + ranId;
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
                  <select>
                    <option value="">全部家長</option>
                    <option value="">小明家長</option>
                    <option value="">小美家長</option>
                  <select>
                </li>
                <li><i class="file" title="上傳圖片/檔案"><input id="uplodefile" type="file" value="" /></i></li>
              </ul>
							<textarea id="edit_text" name="edit_text" class="auto-height"></textarea>
              <ul class="filebox"></ul>
							<button id="sumbit_btn" name="sumbit_btn" class="btn04" style="float:right;margin:0px;">確認</button>
						</section>
            <div id="msg_content" style="display:none;">
              <section v-for="item in Message" class="grid-item">
                <!-- {{item}} -->
                <div class="qa_title">
  								<ul>
  									<li><span class="name">{{item.create_user}}<span class="messagetoico"></span>{{item.touser_name}}</span><span class="time">2017-10-23 14:00</span></li>
  									<li>
                      <span class="text" v-html="item.msg_content"></span>
  									</li>
                    <li v-if="item.img">
                      <img class="qaimg" src="./include/srcoe.jpg" />
                    </li>
  									<li>
                      <span class="attachedfile">
                        <i class="ico" v-if="item.msgfile"></i>
                        <span class="filename" v-for="oFile in item.msgfile">
                          <span v-if="oFile.file_orgnialname">{{oFile.file_orgnialname}}</span>
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
                      <span class="text">{{oReMsg.response_content}}</span>
                      <span class="time">{{oReMsg.remsg_create_time}}</span>
                    </li>
                  </ul>
                  <div class="input-group" style="padding:4px;">
                    <textarea class="form-control auto-height" type="text" placeholder="留言‧‧‧‧‧" rows="1"></textarea>
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
