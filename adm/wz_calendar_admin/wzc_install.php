<?php
$sub_menu = '790100';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'w');
$g5['title'] = '개인 캘린더 설치';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_demo();
    check_admin_token();
    if (!wzc_install_all() || !wzy_install_schema()) alert('설치를 완료하지 못했습니다. DB 권한과 오류 로그를 확인해 주세요.');
    alert('개인 캘린더와 유튜브 시청률 기능 설치를 완료했습니다.', './wzc_config.php');
}

include_once(G5_ADMIN_PATH.'/admin.head.php');
?>
<div class="local_desc01 local_desc">
    <p>개인 일정, 분류, 날짜별 순서, 유튜브 시청률 테이블과 전용 게시판·메뉴를 설치합니다.</p>
    <p>이미 설치된 항목은 유지하므로 다시 실행해도 중복 생성되지 않습니다.</p>
</div>

<form method="post" action="./wzc_install.php" onsubmit="return confirm('개인 캘린더를 설치하시겠습니까?');">
    <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
    <div class="btn_fixed_top">
        <button type="submit" class="btn_submit btn">설치 실행</button>
    </div>
</form>
<?php include_once(G5_ADMIN_PATH.'/admin.tail.php'); ?>
