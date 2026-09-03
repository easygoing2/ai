<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$qa_skin_url.'/style.css">', 0);
?>

<!-- QA 글쓰기 래퍼 -->
<div class="qaWriteWrap boardWriteWrap">
  <!-- fwrite -->
  <form name="fwrite" id="fwrite" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);"
    method="post" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="qa_id" value="<?php echo $qa_id ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="<?php echo $token ?>">

    <?php
        $option = '';
        $option_hidden = '';

        if ($is_dhtml_editor) {
            $option_hidden .= '<input type="hidden" name="qa_html" value="1">';
        } else {
            $option .= PHP_EOL.'<div class="opt"><input type="checkbox" id="qa_html" name="qa_html" onclick="html_auto_br(this);" class="magic-checkbox" value="'.$html_value.'" '.$html_checked.'>'.PHP_EOL.'<label for="qa_html"><span class="text">HTML</span></label></div>';
        }
        echo $option_hidden;
        ?>

    <div id="bo_w" class="boardWrite">

      <!-- boardWriteBody -->
      <div class="boardWriteBody">
        <!-- writeBox -->
        <div class="writeBox bg-wh round-m padding-m">

          <?php if ($category_option) { ?>
          <div class="item">
            <label for="qa_category" class="sound_only">분류<strong>필수</strong></label>
            <select name="qa_category" id="qa_category" required class="select_w w40">
              <option value="">분류를 선택하세요</option>
              <?php echo $category_option ?>
            </select>
          </div>
          <?php } ?>

          <div class="item">
            <label for="qa_subject" class="sound_only">제목<strong class="sound_only">필수</strong></label>
            <input type="text" name="qa_subject" value="<?php echo get_text($write['qa_subject']); ?>" id="qa_subject"
              required class="required" maxlength="255" placeholder="제목을 입력하세요">
          </div>

          <?php if ($option) { ?>
          <div class="item option">
            <?php echo $option ?>
          </div>
          <?php } ?>

          <?php if ($is_email) { ?>
          <div class="item">
            <label for="qa_email" class="sound_only">이메일</label>
            <input type="text" name="qa_email" value="<?php echo get_text($write['qa_email']); ?>" id="qa_email"
              <?php echo $req_email; ?> class="<?php echo $req_email.' '; ?>email" maxlength="100" placeholder="이메일 주소">
            <div class="opt" style="margin-top: 8px;">
              <input type="checkbox" name="qa_email_recv" id="qa_email_recv" value="1"
                <?php if($write['qa_email_recv']) echo 'checked="checked"'; ?> class="magic-checkbox">
              <label for="qa_email_recv"><span class="text">답변받기</span></label>
            </div>
          </div>
          <?php } ?>

          <?php if ($is_hp) { ?>
          <div class="item">
            <label for="qa_hp" class="sound_only">휴대폰</label>
            <input type="text" name="qa_hp" value="<?php echo get_text($write['qa_hp']); ?>" id="qa_hp"
              <?php echo $req_hp; ?> class="<?php echo $req_hp; ?>" placeholder="휴대폰 번호">
            <?php if($qaconfig['qa_use_sms']) { ?>
            <div class="opt" style="margin-top: 8px;">
              <input type="checkbox" name="qa_sms_recv" id="qa_sms_recv" value="1"
                <?php if($write['qa_sms_recv']) echo 'checked="checked"'; ?> class="magic-checkbox">
              <label for="qa_sms_recv"><span class="text">답변등록 SMS알림 수신</span></label>
            </div>
            <?php } ?>
          </div>
          <?php } ?>

          <div class="item">
            <label for="qa_content" class="sound_only">내용<strong class="sound_only">필수</strong></label>
            <?php echo $editor_html; // 에디터 사용시는 에디터로, 아니면 textarea 로 노출 ?>
          </div>

        </div>

        <!-- insertBox -->
        <div class="insertBox bg-wh round-m padding-m">

          <div class="item title">
            <strong>파일 첨부</strong>
          </div>
          <div class="helpText">첨부파일은 최대 <?php echo $upload_max_filesize; ?> 이하만 업로드 가능합니다.</div>

          <div class="item file">
            <label for="bf_file_1" class="lb_icon"><i class="bx bx-upload"></i><span class="sound_only"> 파일
                #1</span></label>
            <input type="file" name="bf_file[1]" id="bf_file_1"
              title="파일첨부 1 : 용량 <?php echo $upload_max_filesize; ?> 이하만 업로드 가능" class="frm_file">
            <?php if($w == 'u' && $write['qa_file1']) { ?>
            <div class="fileDel" style="padding-top: 8px;">
              <input type="checkbox" id="bf_file_del1" name="bf_file_del[1]" value="1" class="magic-checkbox">
              <label for="bf_file_del1"><span class="text"
                  style="color:#ff6666"><?php echo $write['qa_source1']; ?></span> 파일 삭제</label>
            </div>
            <?php } ?>
          </div>

          <div class="item file">
            <label for="bf_file_2" class="lb_icon"><i class="bx bx-upload"></i><span class="sound_only"> 파일
                #2</span></label>
            <input type="file" name="bf_file[2]" id="bf_file_2"
              title="파일첨부 2 : 용량 <?php echo $upload_max_filesize; ?> 이하만 업로드 가능" class="frm_file">
            <?php if($w == 'u' && $write['qa_file2']) { ?>
            <div class="fileDel" style="padding-top: 8px;">
              <input type="checkbox" id="bf_file_del2" name="bf_file_del[2]" value="1" class="magic-checkbox">
              <label for="bf_file_del2"><span class="text"
                  style="color:#ff6666"><?php echo $write['qa_source2']; ?></span> 파일 삭제</label>
            </div>
            <?php } ?>
          </div>

          <div class="btnWrap">
            <button type="submit" id="btn_submit" accesskey="s" class="button bg-pr color-wh round-m bxicon mBtn">
              <i class='bx bx-check'></i> 작성완료
            </button>
            <button type="button" class="button bg-wh shadowline-de round-m bxicon mBtn"
              onclick="location.href='<?php echo $list_href; ?>';">
              <i class='bx bx-x'></i> 취소
            </button>
          </div>

        </div>

      </div>
    </div>
  </form>
</div>

<script>
function html_auto_br(obj) {
  if (obj.checked) {
    result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
    if (result)
      obj.value = "2";
    else
      obj.value = "1";
  } else
    obj.value = "";
}

function fwrite_submit(f) {
  <?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>

  var subject = "";
  var content = "";
  $.ajax({
    url: g5_bbs_url + "/ajax.filter.php",
    type: "POST",
    data: {
      "subject": f.qa_subject.value,
      "content": f.qa_content.value
    },
    dataType: "json",
    async: false,
    cache: false,
    success: function(data, textStatus) {
      subject = data.subject;
      content = data.content;
    }
  });

  if (subject) {
    alert("제목에 금지단어('" + subject + "')가 포함되어있습니다");
    f.qa_subject.focus();
    return false;
  }

  if (content) {
    alert("내용에 금지단어('" + content + "')가 포함되어있습니다");
    if (typeof(ed_qa_content) != "undefined")
      ed_qa_content.returnFalse();
    else
      f.qa_content.focus();
    return false;
  }

  <?php if ($is_hp) { ?>
  var hp = f.qa_hp.value.replace(/[0-9\-]/g, "");
  if (hp.length > 0) {
    alert("휴대폰번호는 숫자, - 으로만 입력해 주십시오.");
    return false;
  }
  <?php } ?>

  $.ajax({
    type: "POST",
    url: g5_bbs_url + "/ajax.write.token.php",
    data: {
      'token_case': 'qa_write'
    },
    cache: false,
    async: false,
    dataType: "json",
    success: function(data) {
      if (typeof data.token !== "undefined") {
        token = data.token;

        if (typeof f.token === "undefined")
          $(f).prepend('<input type="hidden" name="token" value="">');

        $(f).find("input[name=token]").val(token);
      }
    }
  });

  document.getElementById("btn_submit").disabled = "disabled";

  return true;
}
</script>