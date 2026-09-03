<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>
<div class="loginBoxWrap">
    <div class="loginWrap cardBox bg-wh padding-x round-x">
    
        <!-- loginBox -->
        <div class="loginBox">
    
            <div class="memberBox">
                <h1><?php echo $g5['title'] ?></h1>
                <div class="loginForm">
    
                    <form name="fmemberconfirm" action="<?php echo $url ?>" onsubmit="return fmemberconfirm_submit(this);" method="post">
                    <input type="hidden" name="mb_id" value="<?php echo $member['mb_id'] ?>">
                    <input type="hidden" name="w" value="u">
    
                        <fieldset>
                            <legend><?php echo $g5['title'] ?></legend>
                            <dl class="user">
                                <dt>회원아이디</dt>
                                <dd><?php echo $member['mb_id'] ?></dd>
                            </dl>
    
                            <div class="inputBox">
                                <label for="confirm_mb_password" class="sound_only">비밀번호<strong>필수</strong></label>
                                <input type="password" name="mb_password" id="confirm_mb_password" required class="required" size="15" maxLength="20" placeholder="비밀번호">
                            </div>
    
                            <div class="buttonBox">
                                <button type="submit" id="btn_submit" class="button bg-bl color-wh round-m mBtn fw">확인</button>
                            </div>
                            
    
                        </fieldset>
                    </form>
    
                    <div class="infoText">
                        <i class='bx bx-error-circle'></i>
                        <strong>비밀번호를 한번 더 입력해주세요.</strong>
                        <?php if ($url == 'member_leave.php') { ?>
                        비밀번호를 입력하시면 회원탈퇴가 완료됩니다.
                        <?php }else{ ?>
                        회원님의 정보를 안전하게 보호하기 위해 비밀번호를 한번 더 확인합니다.
                        <?php }  ?>
                    </div>
                    
                    <section class="historyBack">
                        <button type="button" onclick="history.back()"><i class='bx bxs-left-arrow-circle'></i> 이전 화면으로</button>
                    </section>
    
                </div>
    
            </div>
    
        </div>

    
    </div>
</div>


<script>
function fmemberconfirm_submit(f)
{
    document.getElementById("btn_submit").disabled = true;

    return true;
}
</script>
<!-- } 회원 비밀번호 확인 끝 -->