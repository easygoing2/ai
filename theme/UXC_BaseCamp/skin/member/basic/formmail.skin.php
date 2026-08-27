<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 폼메일 시작 { -->
<div class="formmailWrap">
    <div class="formmailHeader">
        <h2><i class='bx bx-envelope'></i> <?php echo $name ?>님께 메일보내기</h2>
        <button type="button" class="button shadowline-de round-s bxicon color-gray-800 sBtn" onclick="window.close();">
            <i class='bx bx-x'></i>
            <span class="text">닫기</span>
        </button>
    </div>

    <form name="fformmail" action="./formmail_send.php" onsubmit="return fformmail_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="to" value="<?php echo $email ?>">
    <input type="hidden" name="attach" value="2">
    <?php if ($is_member) { // 회원이면  ?>
    <input type="hidden" name="fnick" value="<?php echo get_text($member['mb_nick']) ?>">
    <input type="hidden" name="fmail" value="<?php echo $member['mb_email'] ?>">
    <?php }  ?>

    <div class="formmailFormBox">
        <?php if (!$is_member) {  ?>
        <div class="formmailSection bg-wh round-m padding-m">
            <div class="formmailItem">
                <div class="formmailItemInfo">
                    <div class="formmailLabel">
                        <i class='bx bx-user'></i> 이름 <span class="required">*</span>
                    </div>
                    <div class="formmailInput">
                        <input type="text" name="fnick" id="fnick" required class="frm_input" placeholder="이름을 입력하세요">
                    </div>
                </div>
            </div>
            <div class="formmailItem">
                <div class="formmailItemInfo">
                    <div class="formmailLabel">
                        <i class='bx bx-envelope'></i> 이메일 <span class="required">*</span>
                    </div>
                    <div class="formmailInput">
                        <input type="email" name="fmail" id="fmail" required class="frm_input" placeholder="이메일을 입력하세요">
                    </div>
                </div>
            </div>
        </div>
        <?php }  ?>

        <div class="formmailSection bg-wh round-m padding-m">
            <div class="formmailItem">
                <div class="formmailItemInfo">
                    <div class="formmailLabel">
                        <i class='bx bx-edit'></i> 제목 <span class="required">*</span>
                    </div>
                    <div class="formmailInput">
                        <input type="text" name="subject" id="subject" required class="frm_input" placeholder="제목을 입력하세요">
                    </div>
                </div>
            </div>
            
            <div class="formmailItem">
                <div class="formmailItemInfo">
                    <div class="formmailLabel">
                        <i class='bx bx-list-ul'></i> 메일 형식
                    </div>
                    <div class="formmailRadio">
                        <div class="opt">
                            <input type="radio" name="type" value="0" id="type_text" checked class="selec_chk">
                            <label for="type_text">
                                <span></span>
                                <span class="text">텍스트</span>
                            </label>
                        </div>
                        <div class="opt">
                            <input type="radio" name="type" value="1" id="type_html" class="selec_chk">
                            <label for="type_html">
                                <span></span>
                                <span class="text">HTML</span>
                            </label>
                        </div>
                        <div class="opt">
                            <input type="radio" name="type" value="2" id="type_both" class="selec_chk">
                            <label for="type_both">
                                <span></span>
                                <span class="text">텍스트+HTML</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="formmailItem">
                <div class="formmailItemInfo">
                    <div class="formmailLabel">
                        <i class='bx bx-message-detail'></i> 내용 <span class="required">*</span>
                    </div>
                    <div class="formmailInput">
                        <textarea name="content" id="content" required class="frm_input" rows="10" placeholder="메일 내용을 입력하세요"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="formmailSection bg-wh round-m padding-m">
            <div class="formmailItem">
                <div class="formmailItemInfo">
                    <div class="formmailLabel">
                        <i class='bx bx-paperclip'></i> 첨부 파일
                    </div>
                    <div class="formmailFile">
                        <div class="fileItem">
                            <label for="file1" class="fileLabel">
                                <i class='bx bx-upload'></i>
                                <span>파일 선택 1</span>
                            </label>
                            <input type="file" name="file1" id="file1" class="fileInput">
                        </div>
                        <div class="fileItem">
                            <label for="file2" class="fileLabel">
                                <i class='bx bx-upload'></i>
                                <span>파일 선택 2</span>
                            </label>
                            <input type="file" name="file2" id="file2" class="fileInput">
                        </div>
                    </div>
                </div>
            </div>
            <div class="formmailNote">
                <i class='bx bx-info-circle'></i>
                첨부 파일은 누락될 수 있으므로 메일을 보낸 후 파일이 첨부 되었는지 반드시 확인해 주시기 바랍니다.
            </div>
        </div>

        <div class="formmailSection bg-wh round-m padding-m">
            <div class="formmailItem">
                <div class="formmailItemInfo">
                    <div class="formmailLabel">
                        <i class='bx bx-shield'></i> 자동등록방지
                    </div>
                    <div class="formmailCaptcha">
                        <?php echo captcha_html(); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="formmailButtons">
            <button type="submit" id="btn_submit" class="button bg-pr color-wh round-m mBtn">
                <i class='bx bx-send'></i>
                <span class="text">메일 발송</span>
            </button>
            <button type="button" onclick="window.close();" class="button shadowline-de round-m mBtn">
                <span class="text">취소</span>
            </button>
        </div>
    </div>

    </form>
</div>

<script>
with (document.fformmail) {
    if (typeof fname != "undefined")
        fname.focus();
    else if (typeof subject != "undefined")
        subject.focus();
}

function fformmail_submit(f)
{
    <?php echo chk_captcha_js();  ?>

    if (f.file1.value || f.file2.value) {
        // 4.00.11
        if (!confirm("첨부파일의 용량이 큰경우 전송시간이 오래 걸립니다.\n\n메일보내기가 완료되기 전에 창을 닫거나 새로고침 하지 마십시오."))
            return false;
    }

    document.getElementById('btn_submit').disabled = true;

    return true;
}

// 파일 선택 시 라벨 텍스트 변경
document.getElementById('file1').addEventListener('change', function() {
    var fileName = this.files[0] ? this.files[0].name : '파일 선택 1';
    this.previousElementSibling.querySelector('span').textContent = fileName;
});

document.getElementById('file2').addEventListener('change', function() {
    var fileName = this.files[0] ? this.files[0].name : '파일 선택 2';
    this.previousElementSibling.querySelector('span').textContent = fileName;
});
</script>
<!-- } 폼메일 끝 -->