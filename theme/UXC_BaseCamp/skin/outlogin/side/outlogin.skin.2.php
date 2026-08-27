<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$outlogin_skin_url.'/style.css">', 0);
?>

<div class="sideUserMenu">
    <div class="sideUserInfo">
        <!-- 로그인 상태 -->
        <span class="profile_img">
            <?php echo get_member_profile_img($member['mb_id']); ?>
        </span>
        <strong class="nickname"><?php echo $nick ?>님</strong>
    </div>

    <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=register_form.php" title="정보수정">
        <i class="bx bx-user-circle"></i>정보수정
    </a>
    <a href="<?php echo G5_BBS_URL ?>/uxc_mypage.php" title="마이페이지">
        <i class="bx bx-user-circle"></i>마이페이지
    </a>
    <a href="<?php echo G5_BBS_URL ?>/point.php" target="_blank" class="win_point">
        <i class="fa fa-database" aria-hidden="true"></i>
        <strong class="color-pr"><?php echo $point; ?></strong>
    </a>
    <a href="<?php echo G5_BBS_URL ?>/memo.php" target="_blank" class="win_memo">
        <i class="fa fa-envelope-o" aria-hidden="true"></i><span class="sound_only">안 읽은 </span>쪽지
        <strong class="color-pr"><?php echo $memo_not_read; ?></strong>
    </a>
    <a href="<?php echo G5_BBS_URL ?>/scrap.php" target="_blank" class="win_scrap">
        <i class="fa fa-thumb-tack" aria-hidden="true"></i>스크랩
        <strong class="color-pr"><?php echo $mb_scrap_cnt; ?></strong>
    </a>
    <a href="<?php echo G5_BBS_URL ?>/logout.php">
        <i class="bx bx-log-out"></i>로그아웃
    </a>
    <?php if ($is_admin == 'super' || $is_auth) {  ?>
    <a href="<?php echo correct_goto_url(G5_ADMIN_URL); ?>" class="adminLink" title="관리자" target="_blank">
        <i class="bx bx-cog"></i>관리자
    </a>
    <?php }  ?>

</div>

