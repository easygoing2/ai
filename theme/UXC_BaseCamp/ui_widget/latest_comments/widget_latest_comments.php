<?php
if (!defined('_GNUBOARD_')) exit;

// CSS 파일 로드
add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/ui_widget/latest_comments/style.css">', 0);

// 위젯 설정
$list_count = 5; // 고정값
$title = isset($col['widget']['title']) ? $col['widget']['title'] : '최신 댓글';

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

// 최신 댓글 가져오기
$list = array();
$cache_file = G5_DATA_PATH.'/cache/widget_latest_comments_'.md5($g5['title']).'.php';

// 캐시 확인 (10분)
if(file_exists($cache_file) && (time() - filemtime($cache_file)) < 600) {
    include($cache_file);
} else {
    // 전체 게시판에서 최신 댓글 가져오기
    $sql = "SELECT DISTINCT bn.bo_table, bn.wr_id, bn.wr_parent 
            FROM g5_board_new bn
            INNER JOIN {$g5['board_table']} b ON bn.bo_table = b.bo_table
            WHERE bn.wr_id != bn.wr_parent 
            AND b.bo_use_search = 1
            ORDER BY bn.bn_id DESC 
            LIMIT " . ($list_count * 2); // 여분으로 더 가져옴

    $result = sql_query($sql);
    $list = array();

    // 각 게시판에서 실제 댓글 데이터 가져오기
    while ($row = sql_fetch_array($result)) {
        $write_table = $g5['write_prefix'] . $row['bo_table'];
        
        // 해당 게시판 테이블이 존재하는지 확인
        $sql_check = "SHOW TABLES LIKE '{$write_table}'";
        $result_check = sql_query($sql_check, false);
        if (!sql_num_rows($result_check)) continue;
        
        // 댓글 정보 가져오기
        $sql_comment = "SELECT '{$row['bo_table']}' as bo_table, wr_id, wr_parent, wr_content, wr_datetime, mb_id, wr_name 
                        FROM {$write_table} 
                        WHERE wr_id = '{$row['wr_id']}' 
                        AND wr_is_comment = 1";
        $comment = sql_fetch($sql_comment);
        
        if ($comment) {
            // 원글 정보 가져오기
            $sql_parent = "SELECT wr_subject 
                          FROM {$write_table} 
                          WHERE wr_id = '{$comment['wr_parent']}' 
                          AND wr_is_comment = 0";
            $parent = sql_fetch($sql_parent);
            
            if ($parent) {
                $board = get_board_db($comment['bo_table'], true);
                if (!$board) continue;
                
                // 댓글 내용 정리
                $comment_text = strip_tags($comment['wr_content']);
                $comment_text = str_replace(array("\r", "\n"), ' ', $comment_text);
                $comment_text = trim($comment_text);
                if (mb_strlen($comment_text, 'utf-8') > 50) {
                    $comment_text = mb_substr($comment_text, 0, 50, 'utf-8') . '...';
                }
                
                $comment['href'] = get_pretty_url($comment['bo_table'], $comment['wr_parent']) . '#c_' . $comment['wr_id'];
                $comment['board_name'] = $board['bo_subject'];
                $comment['parent_subject'] = $parent['wr_subject'];
                $comment['content_text'] = $comment_text;
                $comment['datetime'] = strtotime($comment['wr_datetime']);
                
                // 시간 표시 처리
                $time_diff = time() - $comment['datetime'];
                if ($time_diff < 60) {
                    $comment['date'] = '방금 전';
                } elseif ($time_diff < 3600) {
                    $comment['date'] = floor($time_diff / 60) . '분 전';
                } elseif ($time_diff < 86400) {
                    $comment['date'] = floor($time_diff / 3600) . '시간 전';
                } elseif ($time_diff < 604800) {
                    $comment['date'] = floor($time_diff / 86400) . '일 전';
                } else {
                    $comment['date'] = date('m.d', $comment['datetime']);
                }
                
                $comment['is_new'] = ($time_diff < 86400); // 24시간 이내
                
                // 작성자 정보
                if ($comment['mb_id']) {
                    $mb = get_member($comment['mb_id']);
                    $comment['name'] = $mb['mb_nick'] ? $mb['mb_nick'] : $mb['mb_name'];
                } else {
                    $comment['name'] = $comment['wr_name'];
                }
                
                $list[] = $comment;
            }
        }
        
        if (count($list) >= $list_count) break;
    }
    
    // 캐시 저장
    $cache_content = '<?php $list = ' . var_export($list, true) . '; ?>';
    file_put_contents($cache_file, $cache_content);
}
?>

<div class="widget-box latest-comments-widget">
    <span class="widget-period">최신 댓글</span>
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
                        <span class="comment-text"><?php echo get_text($item['content_text']); ?></span>
                    </a>
                    <div class="item-meta">
                        <div class="meta-left">
                            <span class="board-name"><?php echo $item['board_name']; ?></span>
                            <span class="divider">·</span>
                            <span class="author"><i class="bx bx-user"></i> <?php echo $item['name']; ?></span>
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
            <p>최신 댓글이 없습니다.</p>
        </div>
        <?php endif; ?>
    </div>
</div>