<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
// add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/css/style.css">', 0);

?>

<!-- boardWriteWrap -->
<div class="boardWriteWrap boardViewWrap">
    <!-- fwrite -->
    <form name="fwrite" id="fwrite" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off" style="width:<?php echo $width; ?>">
        <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
        <input type="hidden" name="w" value="<?php echo $w ?>">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
        <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
        <input type="hidden" name="sca" value="<?php echo $sca ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
        <input type="hidden" name="stx" value="<?php echo $stx ?>">
        <input type="hidden" name="spt" value="<?php echo $spt ?>">
        <input type="hidden" name="sst" value="<?php echo $sst ?>">
        <input type="hidden" name="sod" value="<?php echo $sod ?>">
        <input type="hidden" name="page" value="<?php echo $page ?>">
    
        <?php
        $option = '';
        $option_hidden = '';
        if ($is_notice || $is_html || $is_secret || $is_mail) { 
            $option = '';
            if ($is_notice) {
                $option .= PHP_EOL.'<div class="opt"><input type="checkbox" id="notice" name="notice"  class="magic-checkbox" value="1" '.$notice_checked.'>'.PHP_EOL.'<label for="notice"><span class="text">공지</span></label></div>';
            }
            if ($is_html) {
                if ($is_dhtml_editor) {
                    $option_hidden .= '<input type="hidden" value="html1" name="html">';
                } else {
                    $option .= PHP_EOL.'<div class="opt"><input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" class="magic-checkbox" value="'.$html_value.'" '.$html_checked.'>'.PHP_EOL.'<label for="html"><span class="text">html</span></label></div>';
                }
            }
            if ($is_secret) {
                if ($is_admin || $is_secret==1) {
                    $option .= PHP_EOL.'<div class="opt"><input type="checkbox" id="secret" name="secret"  class="magic-checkbox" value="secret" '.$secret_checked.'>'.PHP_EOL.'<label for="secret"><span class="text">비밀글</span></label></div>';
                } else {
                    $option_hidden .= '<input type="hidden" name="secret" value="secret">';
                }
            }
            if ($is_mail) {
                $option .= PHP_EOL.'<div class="opt"><input type="checkbox" id="mail" name="mail"  class="magic-checkbox" value="mail" '.$recv_email_checked.'>'.PHP_EOL.'<label for="mail"><span class="text">답변메일받기</span></label></div>';
            }
        }
        echo $option_hidden;
        ?>
    
        <div id="bo_w" class="boardWrite">
            <!-- boardWriteHeader -->
            <div class="boardWriteHeader">
                <h3><u>글쓰기</u></h3>
            </div>
            <!-- boardWriteBody -->
            <div class="boardWriteBody">
                <!-- writeBox -->
                <div class="writeBox">
                    
                    <?php if ($is_category) { ?>
                    <div class="item catagory">
                        <select name="ca_name" id="ca_name" required class="select_w w40">
                            <option value="">분류를 선택하세요</option>
                            <?php echo $category_option ?>
                        </select>
                    </div>
                    <?php } ?>
        
                    <div class="item">
                        <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="required" maxlength="255" placeholder="제목을 입력하세요.">
                    </div>
                    
                    <?php if ($option) { ?>
                    <div class="item option">
                        <?php echo $option ?>
                    </div>
                    <?php } ?>
        
                    <div class="item">
                        <?php echo $editor_html; // 에디터 사용시는 에디터로, 아니면 textarea 로 노출 ?>
                    </div>
                    
                    <div class="item">
                        <input type="text" name="wr_10" value="<?php echo htmlspecialchars($write['wr_10'] ?? '', ENT_QUOTES, 'UTF-8') ?>" id="wr_10" class="video_inp" placeholder="유튜브동영상 URL">
                        <div class="helpText">
                            유튜브 동영상 <b>URL</b> 을 입력하시면 동영상이 출력 되며, 썸네일이 자동등록 됩니다.
                        </div>
                    </div>
        
                </div>
                
                <!-- insertBox -->
                <div class="insertBox">
        
                    <?php if ($is_name) { ?>
                    <div class="item">
                        <input type="text" name="wr_name" id="wr_name" class="required" value="<?php echo $name ?>" placeholder="작성자 성함" required>
                    </div>
                    <?php } ?>
                    
                    <?php if ($is_password) { ?>
                    <div class="item">
                        <input type="password" name="wr_password" id="wr_password" <?php echo $password_required ?> class="<?php echo $password_required ?>" placeholder="비밀번호">
                    </div>
                    <?php } ?>
                    
                    <?php if($is_file) { ?>
<!-- Dropzone 파일 첨부 영역 -->
<div class="item title">
    <strong>파일 첨부</strong>
</div>

<div class="dropzone-file-info">
    <span class="info-item"><i class='bx bx-file'></i> 최대 <?php echo (int)$board['bo_upload_count']; ?>개</span>
    <span class="info-item"><i class='bx bx-data'></i> 파일당 <?php echo number_format($board['bo_upload_size'] / 1048576, 1); ?>MB 이하</span>
</div>

<!-- 슬롯 컨테이너 -->
<div id="file-slot-container" class="file-slot-container">
    <?php
    $upload_count = (int)$board['bo_upload_count'];
    for ($i = 0; $i < $upload_count; $i++) {
        $has_file = ($w == 'u' && isset($file[$i]['file']) && $file[$i]['file']);
        $is_image = $has_file && preg_match("/\.(jpg|jpeg|gif|png|bmp|webp)$/i", $file[$i]['file']);
        $file_path = $has_file ? G5_DATA_URL.'/file/'.$bo_table.'/'.$file[$i]['file'] : '';
    ?>
    <div class="file-slot <?php echo $has_file ? 'has-file' : 'empty'; ?>" data-slot="<?php echo $i; ?>">
        <div class="slot-number"><?php echo $i + 1; ?></div>
        <?php if ($has_file) { ?>
        <div class="slot-preview existing">
            <div class="preview-image">
                <?php if ($is_image) { ?>
                <img src="<?php echo $file_path; ?>" alt="<?php echo $file[$i]['source']; ?>">
                <?php } else { ?>
                <i class='bx bx-file'></i>
                <?php } ?>
            </div>
            <div class="preview-info">
                <span class="filename"><?php echo $file[$i]['source']; ?></span>
                <span class="filesize"><?php echo $file[$i]['size']; ?></span>
            </div>
            <button type="button" class="btn-remove" data-slot="<?php echo $i; ?>">삭제</button>
            <input type="checkbox" name="bf_file_del[<?php echo $i; ?>]" value="1" class="file-delete-check" style="display:none;">
        </div>
        <?php } else { ?>
        <div class="slot-empty">
            <i class='bx bx-plus'></i>
            <span>파일 추가</span>
        </div>
        <?php } ?>
    </div>
    <?php } ?>
    <!-- 멀티 드롭 박스 -->
    <div id="multi-drop-box" class="multi-drop-box">
        <i class='bx bx-cloud-upload'></i>
        <span>파일을 여기에<br>드래그하세요</span>
    </div>
</div>

<!-- 숨겨진 file input (각 슬롯별) -->
<div id="file-inputs-container" style="display:none;">
    <?php for ($i = 0; $i < $upload_count; $i++) { ?>
    <input type="file" name="bf_file[]" id="bf_file_<?php echo $i; ?>" data-slot="<?php echo $i; ?>">
    <?php } ?>
</div>

<?php } ?>
                    
                    <?php if($is_link) { ?>
                    <div class="item title">
                        <strong>링크</strong>
                        <!-- <i><img src="<?php echo $board_skin_url ?>/img/link.svg" class="fr"></i> -->
                    </div>
                    <?php } ?>
        
                    <?php for ($i=1; $is_link && $i<=G5_LINK_COUNT; $i++) { ?>
                    <div class="item link">
                        <input type="text" name="wr_link<?php echo $i ?>" value="<?php if($w=="u"){ echo $write['wr_link'.$i]; } ?>" id="wr_link<?php echo $i ?>" class=""  placeholder="http://포함">
                    </div>
                    <?php } ?>
        
        
                    <?php if ($is_use_captcha) { //자동등록방지  ?>
                    <div class="item title">
                        <strong>자동등록방지</strong>
                    </div>
                    <div>
                        <?php echo $captcha_html ?>
                    </div>
                    <?php } ?>
        
                    <div class="btnWrap">
                        <button type="submit" id="btn_submit" accesskey="s" class="button bg-pr color-wh-only round-m bxicon mBtn"><i class='bx bx-check'></i> 등록완료</button>
                        <a href="<?php echo get_pretty_url($bo_table); ?>" class="button bg-wh shadowline-de round-m bxicon mBtn"><i class='bx bx-x'></i> 취소</a>
                    </div>
        
                </div>

            </div>

    
        </div>
    </form>

</div>

<script>
    <?php if($write_min || $write_max) { ?>
    // 글자수 제한
    var char_min = parseInt(<?php echo (int)$write_min; ?>); // 최소
    var char_max = parseInt(<?php echo (int)$write_max; ?>); // 최대
    check_byte("wr_content", "char_count");

    $(function() {
        $("#wr_content").on("keyup", function() {
            check_byte("wr_content", "char_count");
        });
    });

    <?php } ?>

    function html_auto_br(obj) {
        if (obj.checked) {
            result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
            if (result)
                obj.value = "html2";
            else
                obj.value = "html1";
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
                "subject": f.wr_subject.value,
                "content": f.wr_content.value
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
            f.wr_subject.focus();
            return false;
        }

        if (content) {
            alert("내용에 금지단어('" + content + "')가 포함되어있습니다");
            if (typeof(ed_wr_content) != "undefined")
                ed_wr_content.returnFalse();
            else
                f.wr_content.focus();
            return false;
        }

        if (document.getElementById("char_count")) {
            if (char_min > 0 || char_max > 0) {
                var cnt = parseInt(check_byte("wr_content", "char_count"));
                if (char_min > 0 && char_min > cnt) {
                    alert("내용은 " + char_min + "글자 이상 쓰셔야 합니다.");
                    return false;
                } else if (char_max > 0 && char_max < cnt) {
                    alert("내용은 " + char_max + "글자 이하로 쓰셔야 합니다.");
                    return false;
                }
            }
        }

        <?php echo $captcha_js; // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함  ?>

        document.getElementById("btn_submit").disabled = "disabled";

        return true;
    }

    // Update the auto-write functionality
    $(function() {
        $("#btn_auto_write").on("click", function() {
            var subject = $("#ai_subject").val();
            if (subject.trim() === "") {
                alert("내용을 입력해주세요.");
                return;
            }

            $(".aiWriteText").addClass("loading"); // Show loading icon

            $.ajax({
                url: g5_bbs_url + "/ajax.gemini_write.php",
                type: "POST",
                data: {
                    "subject": subject
                },
                dataType: "json",
                success: function(data) {
                    $(".aiWriteText").removeClass("loading"); // Hide loading icon
                    $("#geminiLogo").hide(); // Hide loading icon
                    if (data.error) {
                        alert("오류가 발생했습니다: " + data.error);
                    } else {
                        // 에디터가 사용되는 경우와 아닌 경우를 구분하여 처리
                        if (typeof(ed_wr_content) != "undefined") {
                            ed_wr_content.setContents(data.content);
                        } else {
                            $("#ai_content").val(data.content);
                        }
                        // alert("내용이 생성되었습니다.");
                    }
                },
                error: function() {
                    $(".aiWriteText").removeClass("loading"); // Hide loading icon
                    $("#geminiLogo").hide(); // Hide loading icon
                    alert("서버 요청에 실패했습니다.");
                }
            });
        });
    });

    <?php
// Dropzone 초기화 설정
if ($is_file) {
    $existing_file_count = 0;
    if ($w == 'u' && is_array($file)) {
        foreach ($file as $f) {
            if (!empty($f['file'])) $existing_file_count++;
        }
    }
?>
var dzConfig = {
    maxFiles: <?php echo (int)$board['bo_upload_count']; ?>,
    maxFilesize: <?php echo $board['bo_upload_size'] / 1048576; ?>,
    existingFileCount: <?php echo $existing_file_count; ?>,
    uploadUrl: '<?php echo $action_url; ?>'
};
<?php } ?>

</script>
<?php if ($is_file) { ?>
<script src="<?php echo G5_THEME_URL; ?>/js/plugin/dropzone/js/dropzone-init.js"></script>
<?php } ?>