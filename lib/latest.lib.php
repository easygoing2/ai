<?php
if (!defined('_GNUBOARD_')) exit;
@include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// 최신글 추출
// $cache_time 캐시 갱신시간
function latest($skin_dir='', $bo_table='', $rows=10, $subject_len=40, $cache_time=1, $options='')
{
    global $g5;

    if (!$skin_dir) $skin_dir = 'basic';
    
    $time_unit = 3600;  // 1시간으로 고정

    if(preg_match('#^theme/(.+)$#', $skin_dir, $match)) {
        if (G5_IS_MOBILE) {
            $latest_skin_path = G5_THEME_MOBILE_PATH.'/'.G5_SKIN_DIR.'/latest/'.$match[1];
            if(!is_dir($latest_skin_path))
                $latest_skin_path = G5_THEME_PATH.'/'.G5_SKIN_DIR.'/latest/'.$match[1];
            $latest_skin_url = str_replace(G5_PATH, G5_URL, $latest_skin_path);
        } else {
            $latest_skin_path = G5_THEME_PATH.'/'.G5_SKIN_DIR.'/latest/'.$match[1];
            $latest_skin_url = str_replace(G5_PATH, G5_URL, $latest_skin_path);
        }
        $skin_dir = $match[1];
    } else {
        if(G5_IS_MOBILE) {
            $latest_skin_path = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/latest/'.$skin_dir;
            $latest_skin_url  = G5_MOBILE_URL.'/'.G5_SKIN_DIR.'/latest/'.$skin_dir;
        } else {
            $latest_skin_path = G5_SKIN_PATH.'/latest/'.$skin_dir;
            $latest_skin_url  = G5_SKIN_URL.'/latest/'.$skin_dir;
        }
    }

    $caches = false;

    if(G5_USE_CACHE) {
        $cache_file_name = "latest-{$bo_table}-{$skin_dir}-{$rows}-{$subject_len}-".g5_cache_secret_key();
        $caches = g5_get_cache($cache_file_name, (int) $time_unit * (int) $cache_time);
        $cache_list = isset($caches['list']) ? $caches['list'] : array();
        g5_latest_cache_data($bo_table, $cache_list);
    }

    if( $caches === false ){

        $list = array();

        $board = get_board_db($bo_table, true);

        if( ! $board ){
            return '';
        }

        $bo_subject = get_text($board['bo_subject']);

        $tmp_write_table = $g5['write_prefix'] . $bo_table; // 게시판 테이블 전체이름
        $sql = " select * from {$tmp_write_table} where wr_is_comment = 0 order by wr_num limit 0, {$rows} ";
        
        $result = sql_query($sql);
        for ($i=0; $row = sql_fetch_array($result); $i++) {
            try {
                unset($row['wr_password']);     //패스워드 저장 안함( 아예 삭제 )
            } catch (Exception $e) {
            }
            $row['wr_email'] = '';              //이메일 저장 안함
            if (strstr($row['wr_option'], 'secret')){           // 비밀글일 경우 내용, 링크, 파일 저장 안함
                $row['wr_content'] = '';
                for ($j=1; $j<=G5_LINK_COUNT; $j++) {
                    $row['wr_link'.$j] = '';
                }
                $row['file'] = array('count'=>0);
            }
            $list[$i] = get_list($row, $board, $latest_skin_url, $subject_len);

            $list[$i]['first_file_thumb'] = (isset($row['wr_file']) && $row['wr_file']) ? get_board_file_db($bo_table, $row['wr_id'], 'bf_file, bf_content', "and bf_type in (1, 2, 3, 18) ", true) : array('bf_file'=>'', 'bf_content'=>'');
            $list[$i]['bo_table'] = $bo_table;
            // 썸네일 추가
            if($options && is_string($options)) {
                $options_arr = explode(',', $options);
                $thumb_width = $options_arr[0];
                $thumb_height = $options_arr[1];
                $thumb = get_list_thumbnail($bo_table, $row['wr_id'], $thumb_width, $thumb_height, false, true);
                // 이미지 썸네일
                if($thumb['src']) {
                    $img_content = '<img src="'.$thumb['src'].'" alt="'.$thumb['alt'].'" width="'.$thumb_width.'" height="'.$thumb_height.'">';
                    $list[$i]['img_thumbnail'] = '<a href="'.$list[$i]['href'].'" class="lt_img">'.$img_content.'</a>';
                // } else {
                //     $img_content = '<img src="'. G5_IMG_URL.'/no_img.png'.'" alt="'.$thumb['alt'].'" width="'.$thumb_width.'" height="'.$thumb_height.'" class="no_img">';
                }
            }

            if(! isset($list[$i]['icon_file'])) $list[$i]['icon_file'] = '';
        }
        g5_latest_cache_data($bo_table, $list);

        if(G5_USE_CACHE) {

            $caches = array(
                'list' => $list,
                'bo_subject' => sql_escape_string($bo_subject),
            );

            g5_set_cache($cache_file_name, $caches, (int) $time_unit * (int) $cache_time);
        }
    } else {
        $list = $cache_list;
        $bo_subject = (is_array($caches) && isset($caches['bo_subject'])) ? $caches['bo_subject'] : '';
    }

    ob_start();
    include $latest_skin_path.'/latest.skin.php';
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

// 최신글 추출 (멀티 게시판)
// $bo_tables 는 게시판 테이블명을 콤마(,)로 구분하여
// $cache_time 캐시 갱신시간
function latest_all($skin_dir = '', $bo_tables, $rows = 10, $subject_len = 40, $cache_time = 1, $options = '', $only_notice = false)
{
    global $g5;

    if (!$skin_dir) $skin_dir = 'basic';

    if (preg_match('#^theme/(.+)$#', $skin_dir, $match)) {
        if (G5_IS_MOBILE) {
            $latest_skin_path = G5_THEME_MOBILE_PATH . '/' . G5_SKIN_DIR . '/latest/' . $match[1];
            if (!is_dir($latest_skin_path))
                $latest_skin_path = G5_THEME_PATH . '/' . G5_SKIN_DIR . '/latest/' . $match[1];
            $latest_skin_url = str_replace(G5_PATH, G5_URL, $latest_skin_path);
        } else {
            $latest_skin_path = G5_THEME_PATH . '/' . G5_SKIN_DIR . '/latest/' . $match[1];
            $latest_skin_url = str_replace(G5_PATH, G5_URL, $latest_skin_path);
        }
        $skin_dir = $match[1];
    } else {
        if (G5_IS_MOBILE) {
            $latest_skin_path = G5_MOBILE_PATH . '/' . G5_SKIN_DIR . '/latest/' . $skin_dir;
            $latest_skin_url  = G5_MOBILE_URL . '/' . G5_SKIN_DIR . '/latest/' . $skin_dir;
        } else {
            $latest_skin_path = G5_SKIN_PATH . '/latest/' . $skin_dir;
            $latest_skin_url  = G5_SKIN_URL . '/latest/' . $skin_dir;
        }
    }

    $list = array();

    // only_notice 옵션에 따라 SQL 다르게 구성
    if ($only_notice) {
        $notice_where = array();

        $bo_tables_arr = explode(',', $bo_tables);
        foreach ($bo_tables_arr as $bo_table) {
            $bo_table = trim($bo_table);
            if (!$bo_table) continue;

            $board = sql_fetch("SELECT * FROM {$g5['board_table']} WHERE bo_table = '{$bo_table}'");
            if (!$board) continue;

            if ($board['bo_notice']) {
                $notice_ids = array_map('trim', explode(',', $board['bo_notice']));
                foreach ($notice_ids as $nid) {
                    if ($nid) {
                        $notice_where[] = "(a.bo_table = '{$bo_table}' AND a.wr_id = '{$nid}')";
                    }
                }
            }
        }

        if (!empty($notice_where)) {
            $sql_common = " FROM {$g5['board_new_table']} a WHERE (" . implode(' OR ', $notice_where) . ") ";
            $sql_common .= " AND a.wr_id = a.wr_parent ";
        } else {
            // 공지글이 하나도 없으면 빈 쿼리 처리
            return '';
        }
    } else {
        $sql_common = " FROM {$g5['board_new_table']} a WHERE find_in_set(a.bo_table, '{$bo_tables}') ";
        $sql_common .= " AND a.wr_id = a.wr_parent ";
    }

    $sql_order = " ORDER BY a.bn_id DESC ";
    $sql = " SELECT a.* {$sql_common} {$sql_order} LIMIT 0, {$rows}";

    $result = sql_query($sql);

    for ($i = 0; $row = sql_fetch_array($result); $i++) {
        $sql = "SELECT * FROM {$g5['board_table']} WHERE bo_table = '{$row['bo_table']}' ";
        $board = sql_fetch($sql);

        $tmp_write_table = $g5['write_prefix'] . $row['bo_table'];
        $row2 = sql_fetch("SELECT * FROM {$tmp_write_table} WHERE wr_id = '{$row['wr_id']}'");

        // get_list 함수가 올바른 bo_table을 사용하도록 임시 설정
        $temp_bo_table = $GLOBALS['bo_table'];
        $GLOBALS['bo_table'] = $row['bo_table'];
        
        $list[$i] = get_list($row2, $board, $latest_skin_url, $subject_len);
        
        // bo_table 정보 추가 (멀티 게시판 지원)
        $list[$i]['bo_subject'] = $board['bo_subject'];
        $list[$i]['bo_table'] = $row['bo_table'];
        
        // href가 제대로 생성되지 않았다면 직접 생성
        if (empty($list[$i]['href'])) {
            $list[$i]['href'] = G5_BBS_URL . '/board.php?bo_table=' . $row['bo_table'] . '&wr_id=' . $row2['wr_id'];
        }
        
        // 원래 bo_table 복원
        $GLOBALS['bo_table'] = $temp_bo_table;
    }

    ob_start();
    include $latest_skin_path . '/latest.skin.php';
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}



?>
