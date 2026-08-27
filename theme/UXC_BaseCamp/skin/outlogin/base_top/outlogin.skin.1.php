<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$outlogin_skin_url.'/style.css">', 0);
?>

<div class="outloginUser">
    <div class="userInfo">
        <a href="<?php echo G5_BBS_URL ?>/login.php" class="tnbLink" title="로그인">
            <i class="bx bx-log-in"></i>로그인
        </a>
        <a href="<?php echo G5_BBS_URL ?>/register.php" class="tnbLink" title="회원가입">
            <i class="bx bx-user-plus"></i>회원가입
        </a>
    </div>
    <!-- } 로그인 전 아웃로그인 끝 -->
</div>