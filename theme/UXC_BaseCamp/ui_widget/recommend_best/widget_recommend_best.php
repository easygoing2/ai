<?php
if (!defined('_GNUBOARD_')) exit;

// CSS 파일 로드
add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/ui_widget/recommend_best/style.css">', 0);

// 위젯 설정
$list_count = 5; // 고정값
$title = isset($col['widget']['title']) ? $col['widget']['title'] : '추천 베스트';
$days = 30; // 최근 30일 고정

// 시작일 설정
$start_date = date('Y-m-d', strtotime("-{$days} days"));

// 전체 검색 사용 게시판만 가져오기
$sql_boards = "SELECT bo_table FROM {$g5['board_table']} WHERE bo_use_search = 1";
$result_boards = sql_query($sql_boards);
$search_boards = array();
while ($row = sql_fetch_array($result_boards)) {
    $search_boards[] = "'" . $row['bo_table'] . "'";
}

// 게시판 조건
$board_condition = '';
if (!empty($search_boards)) {
    $board_condition = " AND bo_table IN (" . implode(',', $search_boards) . ")";
}

// 추천 베스트 게시글 - g5_board_new에서 먼저 가져오기
$sql = "SELECT DISTINCT bn.bo_table, bn.wr_id 
        FROM g5_board_new bn
        INNER JOIN {$g5['board_table']} b ON bn.bo_table = b.bo_table
        WHERE bn.wr_id = bn.wr_parent 
        AND b.bo_use_search = 1
        ORDER BY bn.bn_id DESC 
        LIMIT 100";

$result = sql_query($sql);
$list = array();
$temp_list = array();

// 각 게시판에서 실제 데이터 가져오기
while ($row = sql_fetch_array($result)) {
    $write_table = $g5['write_prefix'] . $row['bo_table'];
    
    // 해당 게시판 테이블이 존재하는지 확인
    $sql_check = "SHOW TABLES LIKE '{$write_table}'";
    $result_check = sql_query($sql_check, false);
    if (!sql_num_rows($result_check)) continue;
    
    // 게시글 정보 가져오기
    $sql_post = "SELECT '{$row['bo_table']}' as bo_table, wr_id, wr_subject, wr_hit, wr_good, wr_comment, wr_datetime, mb_id, wr_name 
                 FROM {$write_table} 
                 WHERE wr_id = '{$row['wr_id']}' 
                 AND wr_is_comment = 0";
    $post = sql_fetch($sql_post);
    
    if ($post && $post['wr_good'] > 0) {
        // 최근 30일 이내 게시글인지 확인
        $post_time = strtotime($post['wr_datetime']);
        if ($post_time >= strtotime($start_date)) {
            $board = get_board_db($post['bo_table'], true);
            if (!$board) continue;
            
            $post['href'] = get_pretty_url($post['bo_table'], $post['wr_id']);
            $post['board_name'] = $board['bo_subject'];
            $post['datetime'] = $post_time;
            $post['date'] = date('m.d', $post['datetime']);
            
            // 추천수에 따른 등급 설정
            if ($post['wr_good'] >= 50) {
                $post['grade'] = 'hot';
                $post['grade_text'] = 'HOT';
            } elseif ($post['wr_good'] >= 20) {
                $post['grade'] = 'best';
                $post['grade_text'] = 'BEST';
            } else {
                $post['grade'] = 'good';
                $post['grade_text'] = 'GOOD';
            }
            
            $temp_list[] = $post;
        }
    }
}

// 추천수 기준으로 정렬 후 상위 5개만 선택
usort($temp_list, function($a, $b) {
    return $b['wr_good'] - $a['wr_good'];
});

$list = array_slice($temp_list, 0, $list_count);
?>

<div class="widget-box recommend-best-widget">
    <span class="widget-period">최근 <?php echo $days; ?>일</span>
    <div class="widget-content">
        <?php if (count($list) > 0): ?>
        <ul class="best-list">
            <?php foreach ($list as $idx => $item): ?>
            <li>
                <span class="rank"><?php echo $idx + 1; ?></span>
                <div class="item-content">
                    <a href="<?php echo $item['href']; ?>" class="item-title">
                        <span class="grade-badge <?php echo $item['grade']; ?>"><?php echo $item['grade_text']; ?></span>
                        <?php echo get_text($item['wr_subject']); ?>
                        <?php if ($item['wr_comment']): ?>
                        <span class="comment-count">[<?php echo $item['wr_comment']; ?>]</span>
                        <?php endif; ?>
                    </a>
                    <div class="item-info">
                        <div class="info-left">
                            <span class="board-name"><?php echo $item['board_name']; ?></span>
                            <span class="divider">·</span>
                            <span class="stats">
                                <i class='bx bx-show'></i> <?php echo number_format($item['wr_hit']); ?>
                                <span class="recommend-count">
                                    <i class='bx bx-like'></i> <?php echo number_format($item['wr_good']); ?>
                                </span>
                            </span>
                        </div>
                        <span class="date"><?php echo $item['date']; ?></span>
                    </div>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <div class="no-data">
            <i class='bx bx-info-circle'></i>
            <p>추천받은 게시글이 없습니다.</p>
        </div>
        <?php endif; ?>
    </div>
</div>