<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 쪽지 보내기 시작 { -->
<div class="memoFormWrap memoListWrap">
    <div class="memoHeader">
        <h2><i class='bx bx-message-square-dots'></i> <?php echo $g5['title'] ?></h2>
        <button type="button" class="button shadowline-de round-s bxicon color-gray-800 sBtn" onclick="window.close();">
            <i class='bx bx-x'></i>
            <span class="text">닫기</span>
        </button>
    </div>
    
    <div class="memoTabWrap">
        <ul class="memoTab">
            <li>
                <a href="./memo.php?kind=recv">
                    <i class='bx bx-inbox'></i> 받은쪽지
                </a>
            </li>
            <li>
                <a href="./memo.php?kind=send">
                    <i class='bx bx-send'></i> 보낸쪽지
                </a>
            </li>
            <li class="active">
                <a href="./memo_form.php">
                    <i class='bx bx-edit'></i> 쪽지쓰기
                </a>
            </li>
        </ul>
    </div>

    <form name="fmemoform" action="<?php echo $memo_action_url; ?>" onsubmit="return fmemoform_submit(this);" method="post" autocomplete="off">
    <div class="memoFormContent">
        <div class="memoFormBox bg-wh round-m">
            <div class="formWrapper">
                <div class="formGroup">
                    <label for="me_recv_mb_id" class="formLabel">
                        <i class='bx bx-user'></i> 받는 회원아이디
                        <strong class="required">*</strong>
                    </label>
                    <div class="formControl">
                        <input type="text" name="me_recv_mb_id" value="<?php echo $me_recv_mb_id; ?>" id="me_recv_mb_id" required class="formInput" placeholder="받는 회원아이디를 입력하세요">
                        <div class="formHelp">
                            <i class='bx bx-info-circle'></i> 
                            <span>여러 회원에게 보낼때는 컴마(,)로 구분하세요.</span>
                        </div>
                        <?php if ($config['cf_memo_send_point']) { ?>
                        <div class="formHelp warning">
                            <i class='bx bx-coin'></i> 
                            <span>쪽지 보낼때 회원당 <strong><?php echo number_format($config['cf_memo_send_point']); ?>점</strong>의 포인트를 차감합니다.</span>
                        </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="formGroup">
                    <label for="me_memo" class="formLabel">
                        <i class='bx bx-message-square-detail'></i> 내용
                        <strong class="required">*</strong>
                    </label>
                    <div class="formControl">
                        <textarea name="me_memo" id="me_memo" required class="formTextarea" placeholder="메시지를 입력하세요"><?php echo $content ?></textarea>
                    </div>
                </div>

                <div class="formGroup">
                    <label class="formLabel">
                        <i class='bx bx-shield'></i> 자동등록방지
                    </label>
                    <div class="formControl">
                        <div class="captchaBox">
                            <?php echo captcha_html(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="memoFormFooter">
            <button type="submit" id="btn_submit" class="button fw bg-pr color-wh-only round-m bxicon mBtn">
                <i class='bx bx-send'></i> 
                <span class="text">보내기</span>
            </button>
        </div>
    </div>
    </form>
</div>

<script>
function fmemoform_submit(f)
{
    <?php echo chk_captcha_js();  ?>

    return true;
}
</script>
<!-- } 쪽지 보내기 끝 -->