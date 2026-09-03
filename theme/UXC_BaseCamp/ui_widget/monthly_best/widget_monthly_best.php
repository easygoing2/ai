<?php
if (!defined('_GNUBOARD_')) exit;

// CSS 파일 로드
add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/ui_widget/monthly_best/style.css">', 0);

// 위젯 설정
$list_count = 5; // 고정값
$title = isset($col['widget']['title']) ? $col['widget']['title'] : '월간 베스트';

// 30일 기준으로 가중치 계산을 위한 기준일
$weight_base_date = date('Y-m-d', strtotime('-30 days'));

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

// 월간 베스트 게시글 가져오기
$list = array();
$cache_file = G5_DATA_PATH.'/cache/widget_monthly_best_'.md5($g5['title']).'.php';

// 캐시 확인 (1시간)
if(file_exists($cache_file) && (time() - filemtime($cache_file)) < 3600) {
    include($cache_file);
} else {
    // 전체 게시판에서 가져오기 (최근 6개월)
    $sql = "SELECT bn.bo_table, bn.wr_id, bn.wr_parent, bo.bo_subject
            FROM {$g5['board_new_table']} bn
            LEFT JOIN {$g5['board_table']} bo ON bn.bo_table = bo.bo_table
            WHERE bn.wr_id = bn.wr_parent 
            AND bo.bo_use_search = 1
            ORDER BY bn.bn_id DESC
            LIMIT 500";
    
    $result = sql_query($sql);
    $temp_list = array();
    
    while ($row = sql_fetch_array($result)) {
        $write_table = $g5['write_prefix'] . $row['bo_table'];
        
        // 게시글 정보 가져오기
        $write = sql_fetch("SELECT * FROM {$write_table} WHERE wr_id = '{$row['wr_id']}' AND wr_is_comment = 0");
        
        if ($write) {
            $write['bo_table'] = $row['bo_table'];
            $write['bo_subject'] = $row['bo_subject'];
            $write['href'] = get_pretty_url($row['bo_table'], $write['wr_id']);
            $write['datetime'] = strtotime($write['wr_datetime']);
            $write['date'] = date('m.d', $write['datetime']);
            
            // 시간 가중치 계산 (월간: 지속적 인기도 반영)
            $days_ago = (time() - $write['datetime']) / 86400;
            if ($days_ago <= 30) {
                // 30일 이내: 1.0 ~ 0.6 사이의 가중치 (완만한 감소)
                $time_weight = 1 - ($days_ago / 30 * 0.4);
            } elseif ($days_ago <= 60) {
                // 30-60일: 0.6 ~ 0.4 사이
                $time_weight = 0.6 - (($days_ago - 30) / 30 * 0.2);
            } elseif ($days_ago <= 90) {
                // 60-90일: 0.4 ~ 0.3 사이
                $time_weight = 0.4 - (($days_ago - 60) / 30 * 0.1);
            } else {
                // 90일 이후: 천천히 감소
                $time_weight = max(0.2, 0.3 - (($days_ago - 90) / 180));
            }
            
            // 점수 계산 (조회수 + 추천수*2) * 시간 가중치
            $base_score = $write['wr_hit'] + ($write['wr_good'] * 2);
            $write['score'] = $base_score * $time_weight;
            $write['board_name'] = $row['bo_subject'];
            
            $temp_list[] = $write;
        }
    }
    
    // 점수순 정렬
    usort($temp_list, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    $list = array_slice($temp_list, 0, $list_count);
    
    // 캐시 저장
    $cache_content = '<?php $list = ' . var_export($list, true) . '; ?>';
    file_put_contents($cache_file, $cache_content);
}
?>

<div class="widget-box monthly-best-widget">
    <span class="widget-period">인기글</span>
    <div class="widget-content">
        <?php if (count($list) > 0): ?>
        <!-- 디버깅: <?php echo count($list); ?>개 게시글 -->
        <ul class="best-list">
            <?php foreach ($list as $idx => $item): ?>
            <li>
                <span class="rank"><?php echo $idx + 1; ?></span>
                <div class="item-content">
                    <a href="<?php echo $item['href']; ?>" class="item-title">
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
            <p>이번 달 게시글이 없습니다.</p>
        </div>
        <?php endif; ?>
    </div>
</div>