<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<!-- 답변 등록 폼 -->
<div class="qaAnswerFormWrap">
    <?php
    if($is_admin) // 관리자이면 답변등록
    {
    ?>
    <div class="answerFormHeader">
        <h2><i class='bx bx-comment-add'></i> 답변등록</h2>
    </div>

    <form name="fanswer" method="post" action="./qawrite_update.php" onsubmit="return fwrite_submit(this);" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="qa_id" value="<?php echo $view['qa_id']; ?>">
    <input type="hidden" name="w" value="a">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="stx" value="<?php echo $stx; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
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

    <div class="answerFormBody">
        <div class="writeBox bg-wh round-m padding-m">
            
            <div class="item">
                <label for="qa_subject" class="sound_only">제목<strong class="sound_only">필수</strong></label>
                <input type="text" name="qa_subject" value="" id="qa_subject" required class="required" maxlength="255" placeholder="답변 제목을 입력하세요">
            </div>

            <?php if ($option) { ?>
            <div class="item option">
                <?php echo $option ?>
            </div>
            <?php } ?>

            <div class="item">
                <label for="qa_content" class="sound_only">내용<strong class="sound_only">필수</strong></label>
                <?php echo $editor_html; // 에디터 사용시는 에디터로, 아니면 textarea 로 노출 ?>
            </div>

        </div>
        
        <!-- insertBox -->
        <div class="boardWrite insertBox bg-wh round-m padding-m">
            
            <div class="item title">
                <strong>파일 첨부</strong>
            </div>
            <div class="helpText">첨부파일은 최대 <?php echo isset($upload_max_filesize) ? $upload_max_filesize : '2MB'; ?> 이하만 업로드 가능합니다.</div>

            <div class="item file">
                        <label for="bf_file_1" class="lb_icon"><i class="bx bx-upload"></i><span class="sound_only"> 파일 #1</span></label>
                        <input type="file" name="bf_file[1]" id="bf_file_1" title="파일첨부 1 : 용량 <?php echo $upload_max_filesize; ?> 이하만 업로드 가능" class="frm_file">
                        <?php if($w == 'u' && $write['qa_file1']) { ?>
                        <div class="fileDel" style="padding-top: 8px;">
                            <input type="checkbox" id="bf_file_del1" name="bf_file_del[1]" value="1" class="magic-checkbox"> 
                            <label for="bf_file_del1"><span class="text" style="color:#ff6666"><?php echo $write['qa_source1']; ?></span> 파일 삭제</label>
                        </div>
                        <?php } ?>
                    </div>

            <div class="item file">
                <label for="bf_file_2" class="lb_icon"><i class="bx bx-upload"></i><span class="sound_only"> 파일 #2</span></label>
                <input type="file" name="bf_file[2]" id="bf_file_2" title="파일첨부 2 : 용량 <?php echo isset($upload_max_filesize) ? $upload_max_filesize : '2MB'; ?> 이하만 업로드 가능" class="frm_file">
                <?php if(isset($answer) && isset($answer['qa_file2']) && $answer['qa_file2']) { ?>
                <div class="fileDel" style="padding-top: 8px;">
                    <input type="checkbox" id="bf_file_del2" name="bf_file_del[2]" value="1" class="magic-checkbox"> 
                    <label for="bf_file_del2"><span class="text" style="color:#ff6666"><?php echo isset($answer['qa_source2']) ? $answer['qa_source2'] : ''; ?></span> 파일 삭제</label>
                </div>
                <?php } ?>
            </div>

            <div class="btnWrap">
                <button type="submit" id="btn_submit" accesskey="s" class="button bg-pr color-wh round-m bxicon mBtn">
                    <i class='bx bx-check'></i> 답변등록
                </button>
                <button type="button" class="button bg-wh shadowline-de round-m bxicon mBtn" onclick="location.href='<?php echo $list_href; ?>';">
                    <i class='bx bx-x'></i> 취소
                </button>
            </div>

        </div>

    </div>
    </form>

    <script>
    function html_auto_br(obj)
    {
        if (obj.checked) {
            result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
            if (result)
                obj.value = "2";
            else
                obj.value = "1";
        }
        else
            obj.value = "";
    }

    function fwrite_submit(f)
    {
        <?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>

        var subject = "";
        var content = "";
        $.ajax({
            url: g5_bbs_url+"/ajax.filter.php",
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
            alert("제목에 금지단어('"+subject+"')가 포함되어있습니다");
            f.qa_subject.focus();
            return false;
        }

        if (content) {
            alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
            if (typeof(ed_qa_content) != "undefined")
                ed_qa_content.returnFalse();
            else
                f.qa_content.focus();
            return false;
        }

        $.ajax({
            type: "POST",
            url: g5_bbs_url+"/ajax.write.token.php",
            data: { 'token_case' : 'qa_write' },
            cache: false,
            async: false,
            dataType: "json",
            success: function(data) {
                if (typeof data.token !== "undefined") {
                    token = data.token;
                    if(typeof f.token === "undefined")
                        $(f).prepend('<input type="hidden" name="token" value="">');
                    $(f).find("input[name=token]").val(token);
                }
            }
        });

        document.getElementById("btn_submit").disabled = "disabled";

        return true;
    }
    </script>
    <?php
    }
    else
    {
    ?>
    <div class="answerWaitingBox">
        <p class="waitingMsg">
            <i class='bx bx-time-five'></i>
            고객님의 문의에 대한 답변을 준비 중입니다.
        </p>
    </div>
    <?php
    }
    ?>
</div>