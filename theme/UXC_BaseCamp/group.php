<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/group.php');
    return;
}

if(!$is_admin && $group['gr_device'] == 'mobile')
    alert($group['gr_subject'].' 그룹은 모바일에서만 접근할 수 있습니다.');

$g5['title'] = $group['gr_subject'];
include_once(G5_THEME_PATH.'/head.php');
include_once(G5_LIB_PATH.'/latest.lib.php');

// 그룹 내 게시판 수 계산
$sql = "select count(*) as cnt from {$g5['board_table']}
        where gr_id = '{$gr_id}'
        and bo_list_level <= '{$member['mb_level']}'
        and bo_device <> 'mobile' ";
if(!$is_admin) $sql .= " and bo_use_cert = '' ";
$row = sql_fetch($sql);
$board_count = $row['cnt'];
?>

<style>
/* Group Header */
.groupHeader {position:relative; margin-bottom:35px; padding:30px; overflow:hidden; border:1px solid var(--layout-gray-300); border-radius:var(--layout-radius-lg); background:var(--layout-white); color:var(--layout-gray-900);}
.groupHeader::before {content:''; position:absolute; top:-50%; right:-10%; width:400px; height:400px; border-radius:50%; background:var(--layout-gray-50);}
.groupHeader::after {content:''; position:absolute; left:-5%; bottom:-30%; width:300px; height:300px; border-radius:50%; background:var(--layout-gray-50);}
.groupHeader .inner {position:relative; z-index:1;}
.groupHeader h1 {display:flex; align-items:center; margin:0 0 12px 0; color:var(--layout-gray-900); font-size:32px; font-weight:700; gap:12px;}
.groupHeader h1 i {color:var(--layout-gray-600); font-size:38px;}
.groupHeader p {margin:0 0 16px 0; color:var(--layout-gray-700); font-size:15px; line-height:1.6;}
.groupHeader p i {margin-right:5px; color:var(--layout-gray-500);}
.groupStats {display:flex; margin-top:20px; gap:20px;}
.groupStats .stat {display:flex; align-items:center; padding:12px 20px; border:1px solid var(--layout-gray-200); border-radius:var(--layout-radius-lg); background:var(--layout-gray-50); gap:10px;}
.groupStats .stat i {color:var(--layout-gray-600); font-size:24px;}
.groupStats .stat .label {color:var(--layout-gray-600); font-size:12px;}
.groupStats .stat .value {color:var(--layout-gray-900); font-size:18px; font-weight:700;}
/* Board Grid Layout */
.boardGridWrap {display:grid; margin-bottom:40px; grid-template-columns:repeat(auto-fill, minmax(380px, 1fr));gap:25px;}
.boardCard {overflow:hidden; border:1px solid var(--layout-gray-300); border-radius:var(--layout-radius-lg); background:var(--layout-white);}
.boardCard .cardHeader {padding:16px; border-bottom:1px solid var(--layout-gray-300); background:var(--layout-gray-100);}
.boardCard .cardHeader h3 {display:flex; align-items:center; margin:0; color:var(--layout-gray-900); font-size:17px; font-weight:700; gap:10px;}
.boardCard .cardHeader h3 i {color:var(--layout-gray-600); font-size:22px;}
.boardCard .cardBody {padding:0 20px;}
/* Latest List Styling */
.boardCard .latest_wr {padding:0;}
/* Empty State */
.emptyState {padding:60px 20px; color:var(--layout-gray-500); text-align:center;}
.emptyState i {margin-bottom:16px; opacity:0.4; color:var(--layout-gray-400); font-size:52px;}
.emptyState p {margin:0; color:var(--layout-gray-600); font-size:15px;}
/* Responsive */
@media (max-width:1200px) {
    .boardGridWrap {grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:20px;}
    .groupHeader {padding:40px 28px;}
    .groupHeader h1 {font-size:28px;}
    .groupHeader h1 i {font-size:34px;}
}
@media (max-width:768px) {
    .boardGridWrap {grid-template-columns:1fr;gap:18px;}
    .groupHeader {padding:35px 22px; border-radius:var(--layout-radius);}
    .groupHeader h1 {font-size:24px;}
    .groupHeader h1 i {font-size:28px;}
    .groupHeader p {font-size:14px;}
    .groupStats {flex-direction:column; gap:12px;}
    .groupStats .stat {width:100%;}
}
@media (max-width:480px) {
    .groupHeader {padding:28px 18px;}
    .groupHeader h1 {font-size:20px; gap:8px;}
    .groupHeader h1 i {font-size:24px;}
    .boardCard .cardHeader {padding:16px;}
    .boardCard .cardHeader h3 {font-size:15px;}
    .boardCard .cardHeader h3 i {font-size:20px;}
}
</style>

<!-- Group Header -->
<div class="groupHeader">
    <div class="inner">
        <h1>
            <i class='bx bxs-folder-open'></i>
            <?php echo $group['gr_subject'] ?>
        </h1>
        <?php if ($group['gr_admin']) { ?>
            <p>
                <i class='bx bx-user-circle'></i>
                그룹 관리자: <?php echo $group['gr_admin'] ?>
            </p>
        <?php } ?>

        <div class="groupStats">
            <div class="stat">
                <i class='bx bxs-dashboard'></i>
                <div>
                    <div class="label">게시판</div>
                    <div class="value"><?php echo number_format($board_count) ?>개</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Board Grid -->
<div class="boardGridWrap">
<?php
//  최신글
$sql = " select bo_table, bo_subject
            from {$g5['board_table']}
            where gr_id = '{$gr_id}'
              and bo_list_level <= '{$member['mb_level']}'
              and bo_device <> 'mobile' ";
if(!$is_admin)
    $sql .= " and bo_use_cert = '' ";
$sql .= " order by bo_order ";
$result = sql_query($sql);
$board_exists = false;
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $board_exists = true;
?>
    <div class="boardCard">
        <div class="cardHeader">
            <h3>
                <i class='bx bxs-message-square-detail'></i>
                <?php echo $row['bo_subject'] ?>
            </h3>
        </div>
        <div class="cardBody">
            <?php
            // 이 함수가 바로 최신글을 추출하는 역할을 합니다.
            // 사용방법 : latest(스킨, 게시판아이디, 출력라인, 글자수);
            echo latest('theme/uxc_webzine', $row['bo_table'], 4, 25);
            ?>
        </div>
    </div>
<?php
}

// 게시판이 없는 경우
if (!$board_exists) {
?>
    <div class="emptyState" style="grid-column: 1 / -1;">
        <i class='bx bx-folder-open'></i>
        <p>등록된 게시판이 없습니다.</p>
    </div>
<?php
}
?>
</div>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
