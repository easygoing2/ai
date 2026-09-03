<?php
if (!defined('_GNUBOARD_')) exit;

// CSS 파일 로드
add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/ui_widget/weekly_best/style.css">', 0);

// 위젯 설정
$list_count = 5; // 고정값
$title = isset($col['widget']['title']) ? $col['widget']['title'] : '주간 베스트';

// 7일 기준으로 가중치 계산을 위한 기준일
$weight_base_date = date('Y-m-d', strtotime('-7 days'));

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

// 주간 베스트 게시글 가져오기
$list = array();
$cache_file = G5_DATA_PATH.'/cache/widget_weekly_best_'.md5($g5['title']).'.php';

// 캐시 확인 (30분)
if(file_exists($cache_file) && (time() - filemtime($cache_file)) < 1800) {
    include($cache_file);
} else {
    // 전체 게시판에서 가져오기 (최근 3개월)
    $sql = "SELECT DISTINCT bn.bo_table, bn.wr_id 
            FROM g5_board_new bn
            INNER JOIN {$g5['board_table']} b ON bn.bo_table = b.bo_table
            WHERE bn.wr_id = bn.wr_parent 
            AND b.bo_use_search = 1
            ORDER BY bn.bn_id DESC 
            LIMIT 300";

    $result = sql_query($sql);
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
        
        if ($post) {
            $board = get_board_db($post['bo_table'], true);
            if (!$board) continue;
            
            $post['href'] = get_pretty_url($post['bo_table'], $post['wr_id']);
            $post['board_name'] = $board['bo_subject'];
            $post['datetime'] = strtotime($post['wr_datetime']);
            $post['date'] = date('m.d', $post['datetime']);
            $post['is_today'] = (date('Y-m-d', $post['datetime']) == date('Y-m-d'));
            
            // 시간 가중치 계산 (주간: 최근 활동성 중시)
            $days_ago = (time() - $post['datetime']) / 86400;
            if ($days_ago <= 7) {
                // 7일 이내: 1.0 ~ 0.3 사이의 가중치 (급격한 감소)
                $time_weight = 1 - ($days_ago / 7 * 0.7);
            } elseif ($days_ago <= 14) {
                // 7-14일: 0.3 ~ 0.1 사이
                $time_weight = 0.3 - (($days_ago - 7) / 7 * 0.2);
            } else {
                // 14일 이후: 매우 낮은 가중치
                $time_weight = max(0.05, 0.1 - (($days_ago - 14) / 30));
            }
            
            // 점수 계산 (조회수 + 추천수*2) * 시간 가중치
            $base_score = $post['wr_hit'] + ($post['wr_good'] * 2);
            $post['score'] = $base_score * $time_weight;
            
            $temp_list[] = $post;
        }
    }

    // 점수 기준으로 정렬 후 상위 5개만 선택
    usort($temp_list, function($a, $b) {
        return $b['score'] - $a['score'];
    });

    $list = array_slice($temp_list, 0, $list_count);
    
    // 캐시 저장
    $cache_content = '<?php $list = ' . var_export($list, true) . '; ?>';
    file_put_contents($cache_file, $cache_content);
}
?>

<div class="widget-box weekly-best-widget">
    <span class="widget-period">주간 인기</span>
    <div class="widget-content">
        <?php if (count($list) > 0): ?>
        <ul class="best-list">
            <?php foreach ($list as $idx => $item): ?>
            <li>
                <span class="rank"><?php echo $idx + 1; ?></span>
                <div class="item-content">
                    <a href="<?php echo $item['href']; ?>" class="item-title">
                        <?php if ($item['is_today']): ?>
                        <span class="new-badge">N</span>
                        <?php endif; ?>
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
                                <i class='bx bx-like'></i> <?php echo number_format($item['wr_good']); ?>
                            </span>
                        </div>
                        <span class="date"><i class='bx bx-time'></i> <?php echo $item['date']; ?></span>
                    </div>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <div class="no-data">
            <i class='bx bx-info-circle'></i>
            <p>이번 주 게시글이 없습니다.</p>
        </div>
        <?php endif; ?>
    </div>
</div>