<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 포인트 랭킹 위젯
// 상위 10명의 회원 포인트 랭킹을 표시

// CSS 파일 로드
add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/ui_widget/pointranking/style.css">', 0);

// 포인트 랭킹 조회
$sql = " select mb_id, mb_nick, mb_point, mb_level
          from {$g5['member_table']}
          where mb_point > 0
            and mb_leave_date = ''
            and mb_intercept_date = ''
          order by mb_point desc
          limit 10 ";
$result = sql_query($sql);

$rank_list = array();
$rank = 1;
while ($row = sql_fetch_array($result)) {
    $row['rank'] = $rank++;
    $row['mb_nick'] = get_text($row['mb_nick']);
    $row['point_format'] = number_format($row['mb_point']);
    
    $rank_list[] = $row;
}

// 배열 안전성 체크
$rank_count = (is_array($rank_list) && $rank_list) ? count($rank_list) : 0;
?>

<div class="widget_pointranking" data-widget="pointranking">
    <?php if ($rank_count > 0) { ?>
    <ul class="point_ranking_list">
        <?php foreach ($rank_list as $member) { ?>
        <li class="ranking_item">
            <span class="rank_number">
                <?php if ($member['rank'] <= 3) { ?>
                    <i class="bx bx-medal rank-<?php echo $member['rank']; ?>"></i>
                <?php } else { ?>
                    <?php echo $member['rank']; ?>
                <?php } ?>
            </span>
            
            <div class="member_info">
                <strong class="member_nick"><?php echo $member['mb_nick']; ?></strong>
                <span class="member_level">
                    <i class="bx bx-star"></i>
                    LV.<?php echo $member['mb_level']; ?>
                </span>
            </div>
            
            <div class="point_value">
                <strong><?php echo $member['point_format']; ?></strong>
                <span class="point_unit">P</span>
            </div>
        </li>
        <?php } ?>
    </ul>
    <?php } else { ?>
    <div class="empty_ranking">
        <i class="bx bx-info-circle"></i>
        <p>아직 포인트 랭킹이 없습니다.</p>
    </div>
    <?php } ?>
</div>