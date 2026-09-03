<?php
$sub_menu = '790200';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');
$g5['title'] = '개인 캘린더 이용현황';
include_once(G5_ADMIN_PATH.'/admin.head.php');

if (!wzc_schema_installed()) {
    echo '<div class="local_desc01 local_desc"><p>개인 캘린더 DB가 아직 설치되지 않았습니다.</p></div>';
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

$summary = sql_fetch("SELECT COUNT(*) AS event_count, COUNT(DISTINCT mb_id) AS member_count, SUM(CASE WHEN we_updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS recent_count FROM `{$g5['wzc_event_table']}` WHERE we_deleted_at IS NULL", false);
$category_count = sql_fetch("SELECT COUNT(*) AS cnt FROM `{$g5['wzc_category_table']}` WHERE wc_use=1", false);
$watch_summary = array('member_count' => 0, 'watch_count' => 0, 'completed_count' => 0);
if (wzy_schema_installed()) {
    $watch_summary = sql_fetch("SELECT COUNT(*) AS watch_count, COUNT(DISTINCT mb_id) AS member_count,
        SUM(CASE WHEN ww_status='completed' THEN 1 ELSE 0 END) AS completed_count FROM `{$g5['wzy_watch_table']}`", false);
}
?>
<div class="local_desc01 local_desc">
    <p>개인정보 보호를 위해 회원별 일정 제목, 메모, 장소는 관리자 화면에 표시하지 않습니다.</p>
</div>
<div class="local_ov01 local_ov">
    <span class="btn_ov01"><span class="ov_txt">이용 회원</span><span class="ov_num"><?php echo number_format((int)$summary['member_count']); ?>명</span></span>
    <span class="btn_ov01"><span class="ov_txt">전체 일정</span><span class="ov_num"><?php echo number_format((int)$summary['event_count']); ?>개</span></span>
    <span class="btn_ov01"><span class="ov_txt">최근 30일 변경</span><span class="ov_num"><?php echo number_format((int)$summary['recent_count']); ?>개</span></span>
    <span class="btn_ov01"><span class="ov_txt">사용 중 분류</span><span class="ov_num"><?php echo number_format((int)$category_count['cnt']); ?>개</span></span>
    <span class="btn_ov01"><span class="ov_txt">영상 시청 회원</span><span class="ov_num"><?php echo number_format((int)$watch_summary['member_count']); ?>명</span></span>
    <span class="btn_ov01"><span class="ov_txt">영상 시청 기록</span><span class="ov_num"><?php echo number_format((int)$watch_summary['watch_count']); ?>개</span></span>
    <span class="btn_ov01"><span class="ov_txt">시청 완료</span><span class="ov_num"><?php echo number_format((int)$watch_summary['completed_count']); ?>개</span></span>
</div>
<?php include_once(G5_ADMIN_PATH.'/admin.tail.php'); ?>
