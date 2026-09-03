<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

/**
 * UXC 테마 새 글 확인 함수 모음
 * 
 * 메뉴에 새 글 표시 기능
 * get_menu_db는 이미 lib/get_data.lib.php에 정의되어 있음
 */

/**
 * 메뉴 링크에 새 글이 있는지 확인하는 함수
 */
if (!function_exists('menu_has_new_post')) {
function menu_has_new_post($me_link) {
    global $g5, $config;
    
    if (!$me_link) return false;
    
    // 링크에서 bo_table 추출
    $bo_table = '';
    
    // URL 파싱
    $parsed = parse_url($me_link);
    
    // 쿼리 스트링에서 bo_table 추출
    if (isset($parsed['query'])) {
        parse_str($parsed['query'], $query_params);
        if (isset($query_params['bo_table'])) {
            $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $query_params['bo_table']);
        }
    }
    
    // 경로에서 bo_table 추출 (짧은 주소 형식)
    if (!$bo_table && isset($parsed['path'])) {
        // /bbs/board.php?bo_table=notice 형식
        if (strpos($parsed['path'], '/bbs/board.php') !== false && isset($parsed['query'])) {
            parse_str($parsed['query'], $query_params);
            if (isset($query_params['bo_table'])) {
                $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $query_params['bo_table']);
            }
        }
        // /notice 같은 짧은 주소 형식
        else {
            $path_parts = explode('/', trim($parsed['path'], '/'));
            if (count($path_parts) > 0) {
                $last_part = preg_replace('/[^a-zA-Z0-9_]/', '', end($path_parts));
                if ($last_part) {
                    // 게시판 테이블 존재 여부 확인
                    $sql = " SELECT bo_table FROM {$g5['board_table']} 
                             WHERE bo_table = '".sql_escape_string($last_part)."' ";
                    $board = sql_fetch($sql);
                    if ($board) {
                        $bo_table = $board['bo_table'];
                    }
                }
            }
        }
    }
    
    // 게시판 테이블이 없으면 false 반환
    if (!$bo_table) return false;
    
    // 게시판 테이블 유효성 검사
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $bo_table)) {
        return false;
    }
    
    // 새 글 체크 기준 시간 (기본 24시간)
    $new_time = isset($config['cf_new']) ? (int)$config['cf_new'] : 24;
    if ($new_time <= 0) $new_time = 24;
    
    $date_gap = date("Y-m-d H:i:s", G5_SERVER_TIME - ($new_time * 3600));
    
    // 테이블명 생성
    $write_table = $g5['write_prefix'] . $bo_table;
    
    // 새 글이 있는지 확인 (LIMIT 1로 빠른 체크)
    $sql = " SELECT 1 
             FROM {$write_table}
             WHERE wr_is_comment = 0 
               AND wr_datetime >= '{$date_gap}' 
             LIMIT 1 ";
    
    $row = sql_fetch($sql);
    
    return $row ? true : false;
}
}

/**
 * 게시판의 새 글 개수를 반환하는 함수
 */
if (!function_exists('get_board_new_count')) {
function get_board_new_count($bo_table, $hours = 24) {
    global $g5;
    
    if (!$bo_table) return 0;
    
    // 게시판 테이블명 검증
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $bo_table)) {
        return 0;
    }
    
    $hours = (int)$hours;
    if ($hours <= 0) $hours = 24;
    
    $date_gap = date("Y-m-d H:i:s", G5_SERVER_TIME - ($hours * 3600));
    
    // 테이블명 생성
    $write_table = $g5['write_prefix'] . $bo_table;
    
    $sql = " SELECT COUNT(*) as cnt 
             FROM {$write_table}
             WHERE wr_is_comment = 0 
               AND wr_datetime >= '{$date_gap}' ";
    
    $row = sql_fetch($sql);
    
    return ($row && isset($row['cnt'])) ? (int)$row['cnt'] : 0;
}
}

/**
 * 게시판의 최신 글 작성 시간을 반환하는 함수
 */
if (!function_exists('get_board_latest_datetime')) {
function get_board_latest_datetime($bo_table) {
    global $g5;
    
    if (!$bo_table) return '';
    
    // 게시판 테이블명 검증
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $bo_table)) {
        return '';
    }
    
    // 테이블명 생성
    $write_table = $g5['write_prefix'] . $bo_table;
    
    $sql = " SELECT MAX(wr_datetime) as latest_time 
             FROM {$write_table}
             WHERE wr_is_comment = 0 ";
    
    $row = sql_fetch($sql);
    
    return ($row && isset($row['latest_time']) && $row['latest_time']) ? $row['latest_time'] : '';
}
}

/**
 * 메뉴에 표시할 아이콘 클래스를 반환하는 함수
 */
if (!function_exists('get_menu_icon_class')) {
function get_menu_icon_class($icon_name) {
    if (!$icon_name) return '';
    
    // 허용된 문자만 남기기
    $icon_name = preg_replace('/[^a-zA-Z0-9\-_]/', '', $icon_name);
    
    if (!$icon_name) return '';
    
    // bx- 접두어가 없으면 추가
    if (strpos($icon_name, 'bx-') !== 0) {
        $icon_name = 'bx-' . $icon_name;
    }
    
    return $icon_name;
}
}

/**
 * 현재 페이지가 특정 메뉴와 일치하는지 확인하는 함수
 */
if (!function_exists('is_menu_active')) {
function is_menu_active($me_link) {
    if (!$me_link) return false;

    $current_url = $_SERVER['REQUEST_URI'];
    $menu_url = parse_url($me_link);

    // 쿼리 스트링 비교
    if (isset($menu_url['query'])) {
        parse_str($menu_url['query'], $menu_params);

        // bo_table 비교
        if (isset($menu_params['bo_table']) && isset($_GET['bo_table'])) {
            $menu_table = preg_replace('/[^a-zA-Z0-9_]/', '', $menu_params['bo_table']);
            $current_table = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['bo_table']);
            return ($menu_table === $current_table);
        }
    }

    // 경로 비교
    if (isset($menu_url['path'])) {
        $menu_path = trim($menu_url['path'], '/');
        $current_path = trim(parse_url($current_url, PHP_URL_PATH), '/');

        if ($menu_path === $current_path) {
            return true;
        }
    }

    return false;
}
}

/**
 * 현재 페이지가 속한 메뉴 그룹(대메뉴) 이름을 반환하는 함수
 * breadcrumbs 표시 등에 사용
 *
 * @param string $bo_table 게시판 테이블명 (선택)
 * @param string $co_id 내용관리 ID (선택)
 * @return string 메뉴 그룹명 (없으면 빈 문자열)
 */
if (!function_exists('uxc_get_current_menu_group')) {
function uxc_get_current_menu_group($bo_table = '', $co_id = '') {
    // 파라미터가 없으면 전역 변수에서 가져오기
    if (!$bo_table && isset($GLOBALS['bo_table'])) {
        $bo_table = $GLOBALS['bo_table'];
    }
    if (!$co_id && isset($GLOBALS['co_id'])) {
        $co_id = $GLOBALS['co_id'];
    }

    // 메뉴 데이터 가져오기
    $menu_datas = get_menu_db(0, true);
    if (!is_array($menu_datas) || count($menu_datas) == 0) {
        return '';
    }

    // 현재 페이지가 속한 메뉴 그룹 찾기
    $menu_group_name = '';

    foreach ($menu_datas as $row) {
        if (empty($row)) continue;

        // 서브메뉴에서 현재 페이지 찾기
        if (isset($row['sub']) && is_array($row['sub'])) {
            foreach ($row['sub'] as $row2) {
                if (empty($row2) || !isset($row2['me_link'])) continue;

                // bo_table로 찾기
                if (!empty($bo_table) && strpos($row2['me_link'], $bo_table) !== false) {
                    $menu_group_name = isset($row['me_name']) ? $row['me_name'] : '';
                    break 2;
                }

                // co_id로 찾기
                if (!empty($co_id) && strpos($row2['me_link'], $co_id) !== false) {
                    $menu_group_name = isset($row['me_name']) ? $row['me_name'] : '';
                    break 2;
                }
            }
        }

        // 메인메뉴에서도 확인
        if (empty($menu_group_name) && isset($row['me_link'])) {
            // bo_table로 찾기
            if (!empty($bo_table) && strpos($row['me_link'], $bo_table) !== false) {
                $menu_group_name = isset($row['me_name']) ? $row['me_name'] : '';
                break;
            }
            // co_id로 찾기
            if (!empty($co_id) && strpos($row['me_link'], $co_id) !== false) {
                $menu_group_name = isset($row['me_name']) ? $row['me_name'] : '';
                break;
            }
        }
    }

    return $menu_group_name;
}
}

/**
 * 현재 페이지가 속한 메뉴 그룹(대메뉴)의 아이콘을 반환하는 함수
 *
 * @param string $bo_table 게시판 테이블명 (선택)
 * @param string $co_id 내용관리 ID (선택)
 * @return string 메뉴 그룹 아이콘 클래스 (없으면 빈 문자열)
 */
if (!function_exists('uxc_get_current_menu_group_icon')) {
function uxc_get_current_menu_group_icon($bo_table = '', $co_id = '') {
    // 파라미터가 없으면 전역 변수에서 가져오기
    if (!$bo_table && isset($GLOBALS['bo_table'])) {
        $bo_table = $GLOBALS['bo_table'];
    }
    if (!$co_id && isset($GLOBALS['co_id'])) {
        $co_id = $GLOBALS['co_id'];
    }

    // 메뉴 데이터 가져오기
    $menu_datas = get_menu_db(0, true);
    if (!is_array($menu_datas) || count($menu_datas) == 0) {
        return '';
    }

    // 현재 페이지가 속한 메뉴 그룹의 아이콘 찾기
    $menu_group_icon = '';

    foreach ($menu_datas as $row) {
        if (empty($row)) continue;

        // 서브메뉴에서 현재 페이지 찾기
        if (isset($row['sub']) && is_array($row['sub'])) {
            foreach ($row['sub'] as $row2) {
                if (empty($row2) || !isset($row2['me_link'])) continue;

                // bo_table로 찾기
                if (!empty($bo_table) && strpos($row2['me_link'], $bo_table) !== false) {
                    $menu_group_icon = (isset($row['me_icon']) && !empty(trim($row['me_icon']))) ? trim($row['me_icon']) : '';
                    break 2;
                }

                // co_id로 찾기
                if (!empty($co_id) && strpos($row2['me_link'], $co_id) !== false) {
                    $menu_group_icon = (isset($row['me_icon']) && !empty(trim($row['me_icon']))) ? trim($row['me_icon']) : '';
                    break 2;
                }
            }
        }

        // 메인메뉴에서도 확인
        if (empty($menu_group_icon) && isset($row['me_link'])) {
            // bo_table로 찾기
            if (!empty($bo_table) && strpos($row['me_link'], $bo_table) !== false) {
                $menu_group_icon = (isset($row['me_icon']) && !empty(trim($row['me_icon']))) ? trim($row['me_icon']) : '';
                break;
            }
            // co_id로 찾기
            if (!empty($co_id) && strpos($row['me_link'], $co_id) !== false) {
                $menu_group_icon = (isset($row['me_icon']) && !empty(trim($row['me_icon']))) ? trim($row['me_icon']) : '';
                break;
            }
        }
    }

    return $menu_group_icon;
}
}