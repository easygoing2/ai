<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<div class="loginBoxWrap">

    <div class="loginWrap cardBox bg-wh padding-l round-x">
        <!-- loginBox -->
        <div class="loginBox">
    
            <div class="memberBox">
                <h1><?php echo $g5['title'] ?></h1>
                <div class="loginForm">
                    <form name="flogin" action="<?php echo $login_action_url ?>" onsubmit="return flogin_submit(this);" method="post">
                    <input type="hidden" name="url" value="<?php echo $login_url ?>">
                        <fieldset id="login_fs">
                            <legend>login</legend>
                            <div class="inputBox">
                                <label for="login_id" class="sr-only">회원아이디<strong class="sr-only"> 필수</strong></label>
                                <input type="text" name="mb_id" id="login_id" required class="required" size="20" maxLength="20" placeholder="아이디">
                            </div>
                            <div class="inputBox">
                                <label for="login_pw" class="sr-only">비밀번호<strong class="sr-only"></strong> 필수</strong></label>
                                <input type="password" name="mb_password" id="login_pw" required class="required" size="20" maxLength="20" placeholder="비밀번호">
                            </div>
                            <div class="persistent">
                                <div class="opt">
                                    <input type="checkbox" name="auto_login" id="login_auto_login" class="selec_chk">
                                    <label for="login_auto_login"><span class="text">자동로그인</span></label>
                                </div>
                                <a href="<?php echo G5_BBS_URL ?>/password_lost.php">ID/PW 찾기</a>  
                            </div>
                            <div class="buttonBox">
                                <button type="submit" class="button bg-bl color-wh round-m mBtn fw">로그인</button>
                            </div>
                        </fieldset>
                    </form>
                    <div class="buttonWrap">
                        <a href="<?php echo G5_BBS_URL ?>/register.php" class="button shadowline-de round-m bxicon mBtn fw">
                            <i class="bx bx-user-plus"></i>
                            <span class="text">회원가입</span>
                        </a>
                    </div>
                    <div class="infoText"><i class='bx bx-error-circle'></i>회원이 되시면 모든 컨텐츠를 이용하실 수 있습니다.</div>
    
                    <!-- social -->
                    <?php @include_once(get_social_skin_path() . '/social_login.skin.php'); // 소셜로그인 버튼 ?>
    
                    <section class="historyBack">
                        <button type="button" onclick="history.back()"><i class='bx bxs-left-arrow-circle'></i> 이전 화면으로</button>
                    </section>
                </div>
            </div>
    
        </div>

    
    </div>
</div>


<script>
jQuery(function($){
    $("#login_auto_login").click(function(){
        if (this.checked) {
            this.checked = confirm("자동로그인을 사용하시면 다음부터 회원아이디와 비밀번호를 입력하실 필요가 없습니다.\n\n공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?");
        }
    });
});

function flogin_submit(f)
{
    if( $( document.body ).triggerHandler( 'login_sumit', [f, 'flogin'] ) !== false ){
        return true;
    }
    return false;
}
</script>
<!-- } 로그인 끝 -->
