<?php
if (!defined('_GNUBOARD_')) exit;

// CSS 파일 로드
add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/ui_widget/latest_posts/style.css">', 0);

// 위젯 설정
$list_count = 5; // 고정값
$title = isset($col['widget']['title']) ? $col['widget']['title'] : '최신 글';

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

// 최신 게시글 가져오기
$list = array();
$cache_file = G5_DATA_PATH.'/cache/widget_latest_posts_'.md5($g5['title']).'.php';

// 캐시 확인 (10분)
if(file_exists($cache_file) && (time() - filemtime($cache_file)) < 600) {
    include($cache_file);
} else {
    // 전체 게시판에서 최신글 가져오기
    $sql = "SELECT DISTINCT bn.bo_table, bn.wr_id 
            FROM g5_board_new bn
            INNER JOIN {$g5['board_table']} b ON bn.bo_table = b.bo_table
            WHERE bn.wr_id = bn.wr_parent 
            AND b.bo_use_search = 1
            ORDER BY bn.bn_id DESC 
            LIMIT " . $list_count;

    $result = sql_query($sql);
    $list = array();

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
            
            // 시간 표시 처리
            $time_diff = time() - $post['datetime'];
            if ($time_diff < 60) {
                $post['date'] = '방금 전';
            } elseif ($time_diff < 3600) {
                $post['date'] = floor($time_diff / 60) . '분 전';
            } elseif ($time_diff < 86400) {
                $post['date'] = floor($time_diff / 3600) . '시간 전';
            } elseif ($time_diff < 604800) {
                $post['date'] = floor($time_diff / 86400) . '일 전';
            } else {
                $post['date'] = date('m.d', $post['datetime']);
            }
            
            $post['is_new'] = ($time_diff < 86400); // 24시간 이내
            
            $list[] = $post;
        }
        
        if (count($list) >= $list_count) break;
    }
    
    // 캐시 저장
    $cache_content = '<?php $list = ' . var_export($list, true) . '; ?>';
    file_put_contents($cache_file, $cache_content);
}
?>

<div class="widget-box latest-posts-widget">
    <span class="widget-period">최신 글</span>
    <div class="widget-content">
        <?php if (count($list) > 0): ?>
        <ul class="best-list">
            <?php foreach ($list as $idx => $item): ?>
            <li>
                <div class="item-content">
                    <a href="<?php echo $item['href']; ?>" class="item-title">
                        <?php if ($item['is_new']): ?>
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
                                <?php if ($item['wr_good'] > 0): ?>
                                <i class='bx bx-like'></i> <?php echo number_format($item['wr_good']); ?>
                                <?php endif; ?>
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
            <p>최신 게시글이 없습니다.</p>
        </div>
        <?php endif; ?>
    </div>
</div>