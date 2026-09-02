<?php
if (!defined('_GNUBOARD_')) exit;

function wzc_json_response($success, $data = array(), $status = 200) {
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    }
    echo json_encode(array_merge(array('success' => (bool)$success), $data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function wzc_request_data() {
    $content_type = isset($_SERVER['CONTENT_TYPE']) ? strtolower($_SERVER['CONTENT_TYPE']) : '';
    if (strpos($content_type, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : array();
    }
    return $_POST;
}

function wzc_require_member($json = false) {
    global $member;
    if (!isset($member['mb_id']) || !$member['mb_id']) {
        if ($json) wzc_json_response(false, array('message' => '로그인이 필요합니다.', 'code' => 'LOGIN_REQUIRED'), 401);
        $return_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : WZC_BOARD_URL;
        alert('로그인 후 이용해 주세요.', G5_BBS_URL.'/login.php?url='.urlencode($return_url));
    }
    return $member['mb_id'];
}

function wzc_csrf_token() {
    $token = get_session('ss_wzc_csrf_token');
    if (!$token) {
        try {
            $token = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            $token = get_random_token_string(64);
        }
        set_session('ss_wzc_csrf_token', $token);
    }
    return $token;
}

function wzc_check_csrf($token) {
    $saved = get_session('ss_wzc_csrf_token');
    return $saved && is_string($token) && hash_equals($saved, $token);
}

function wzc_require_post_json() {
    if (!isset($_SERVER['REQUEST_METHOD']) || strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
        wzc_json_response(false, array('message' => 'POST 요청만 허용됩니다.', 'code' => 'METHOD_NOT_ALLOWED'), 405);
    }
    $data = wzc_request_data();
    $token = isset($data['csrf_token']) ? (string)$data['csrf_token'] : '';
    if (!wzc_check_csrf($token)) {
        wzc_json_response(false, array('message' => '요청 토큰이 올바르지 않습니다. 페이지를 새로고침해 주세요.', 'code' => 'INVALID_TOKEN'), 403);
    }
    return $data;
}

function wzc_rate_limit($key, $limit = 60, $seconds = 60) {
    $session_key = 'ss_wzc_rate_'.preg_replace('/[^a-z0-9_]/i', '', $key);
    $now = time();
    $history = get_session($session_key);
    if (!is_array($history)) $history = array();
    $history = array_values(array_filter($history, function($at) use ($now, $seconds) {
        return (int)$at > ($now - $seconds);
    }));
    if (count($history) >= $limit) {
        wzc_json_response(false, array('message' => '요청이 너무 많습니다. 잠시 후 다시 시도해 주세요.', 'code' => 'RATE_LIMIT'), 429);
    }
    $history[] = $now;
    set_session($session_key, $history);
}

function wzc_valid_date($date) {
    if (!is_string($date) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m)) return false;
    return checkdate((int)$m[2], (int)$m[3], (int)$m[1]);
}

function wzc_valid_time($time) {
    if ($time === '' || $time === null) return true;
    if (!preg_match('/^(\d{2}):(\d{2})$/', $time, $m)) return false;
    return (int)$m[1] >= 0 && (int)$m[1] <= 23 && (int)$m[2] >= 0 && (int)$m[2] <= 59;
}

function wzc_date_add($date, $days) {
    $dt = new DateTimeImmutable($date);
    return $dt->modify(((int)$days >= 0 ? '+' : '').(int)$days.' days')->format('Y-m-d');
}

function wzc_date_diff_days($from, $to) {
    $a = new DateTimeImmutable($from);
    $b = new DateTimeImmutable($to);
    return (int)$a->diff($b)->format('%r%a');
}

function wzc_event_day_count($start, $end) {
    return wzc_date_diff_days($start, $end) + 1;
}

function wzc_plain_text($value, $max = 0) {
    $value = trim(strip_tags((string)$value));
    if ($max > 0 && mb_strlen($value, 'UTF-8') > $max) $value = mb_substr($value, 0, $max, 'UTF-8');
    return $value;
}

function wzc_clean_url($url) {
    $url = trim((string)$url);
    if ($url === '') return '';
    if (!filter_var($url, FILTER_VALIDATE_URL)) return false;
    $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
    return in_array($scheme, array('http', 'https'), true) ? $url : false;
}

function wzc_schema_installed() {
    global $g5;
    $tables = array(
        $g5['wzc_category_table'],
        $g5['wzc_event_table'],
        $g5['wzc_event_order_table'],
        $g5['wzc_preference_table'],
        $g5['wzc_config_table']
    );
    foreach ($tables as $table) {
        $table_sql = sql_escape_string($table);
        $row = sql_fetch("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$table_sql}'", false);
        if (empty($row['TABLE_NAME'])) return false;
    }
    return true;
}

function wzc_install_schema() {
    global $g5;
    $queries = array();
    $queries[] = "CREATE TABLE IF NOT EXISTS `{$g5['wzc_category_table']}` (
        `wc_ix` int unsigned NOT NULL AUTO_INCREMENT,
        `mb_id` varchar(20) NOT NULL,
        `wc_name` varchar(50) NOT NULL,
        `wc_color` char(7) NOT NULL DEFAULT '#6f48ff',
        `wc_use` tinyint(1) NOT NULL DEFAULT '1',
        `wc_sort` smallint NOT NULL DEFAULT '0',
        `wc_created_at` datetime NOT NULL,
        `wc_updated_at` datetime NOT NULL,
        PRIMARY KEY (`wc_ix`),
        UNIQUE KEY `mb_name` (`mb_id`,`wc_name`),
        KEY `mb_use_sort` (`mb_id`,`wc_use`,`wc_sort`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $queries[] = "CREATE TABLE IF NOT EXISTS `{$g5['wzc_event_table']}` (
        `we_ix` bigint unsigned NOT NULL AUTO_INCREMENT,
        `mb_id` varchar(20) NOT NULL,
        `wc_ix` int unsigned DEFAULT NULL,
        `we_title` varchar(255) NOT NULL,
        `we_content` text NOT NULL,
        `we_start_date` date NOT NULL,
        `we_end_date` date NOT NULL,
        `we_all_day` tinyint(1) NOT NULL DEFAULT '1',
        `we_start_time` time DEFAULT NULL,
        `we_end_time` time DEFAULT NULL,
        `we_location` varchar(255) NOT NULL DEFAULT '',
        `we_link_url` varchar(500) NOT NULL DEFAULT '',
        `we_version` int unsigned NOT NULL DEFAULT '1',
        `we_deleted_at` datetime DEFAULT NULL,
        `we_created_at` datetime NOT NULL,
        `we_updated_at` datetime NOT NULL,
        PRIMARY KEY (`we_ix`),
        KEY `mb_date` (`mb_id`,`we_deleted_at`,`we_start_date`,`we_end_date`),
        KEY `mb_category` (`mb_id`,`wc_ix`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $queries[] = "CREATE TABLE IF NOT EXISTS `{$g5['wzc_event_order_table']}` (
        `wo_ix` bigint unsigned NOT NULL AUTO_INCREMENT,
        `mb_id` varchar(20) NOT NULL,
        `we_ix` bigint unsigned NOT NULL,
        `wo_date` date NOT NULL,
        `wo_sort` int NOT NULL DEFAULT '0',
        `wo_updated_at` datetime NOT NULL,
        PRIMARY KEY (`wo_ix`),
        UNIQUE KEY `mb_event_date` (`mb_id`,`we_ix`,`wo_date`),
        KEY `mb_date_sort` (`mb_id`,`wo_date`,`wo_sort`),
        KEY `we_ix` (`we_ix`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $queries[] = "CREATE TABLE IF NOT EXISTS `{$g5['wzc_preference_table']}` (
        `mb_id` varchar(20) NOT NULL,
        `wp_events_per_day` tinyint unsigned NOT NULL DEFAULT '3',
        `wp_default_category` int unsigned DEFAULT NULL,
        `wp_touch_drag_use` tinyint(1) NOT NULL DEFAULT '1',
        `wp_updated_at` datetime NOT NULL,
        PRIMARY KEY (`mb_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $queries[] = "CREATE TABLE IF NOT EXISTS `{$g5['wzc_config_table']}` (
        `wcf_ix` tinyint unsigned NOT NULL DEFAULT '1',
        `wcf_use` tinyint(1) NOT NULL DEFAULT '1',
        `wcf_max_events` int unsigned NOT NULL DEFAULT '5000',
        `wcf_schema_version` int unsigned NOT NULL DEFAULT '1',
        `wcf_updated_at` datetime NOT NULL,
        PRIMARY KEY (`wcf_ix`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    foreach ($queries as $query) {
        if (!sql_query($query, false)) return false;
    }
    sql_query("INSERT INTO `{$g5['wzc_config_table']}` (`wcf_ix`,`wcf_use`,`wcf_max_events`,`wcf_schema_version`,`wcf_updated_at`)
        VALUES (1,1,5000,".(int)WZC_SCHEMA_VERSION.",NOW())
        ON DUPLICATE KEY UPDATE `wcf_schema_version`=GREATEST(`wcf_schema_version`, VALUES(`wcf_schema_version`))", false);
    return wzc_schema_installed();
}

function wzc_sql_value($value) {
    if ($value === null) return 'NULL';
    return "'".sql_escape_string((string)$value)."'";
}

function wzc_install_board() {
    global $g5;

    $board_table = sql_escape_string(WZC_BOARD_TABLE);
    $exists = sql_fetch("SELECT bo_table FROM `{$g5['board_table']}` WHERE bo_table='{$board_table}'", false);
    if (!$exists) {
        $source = sql_fetch("SELECT * FROM `{$g5['board_table']}` WHERE bo_table='reservation'", false);
        if (!$source) $source = sql_fetch("SELECT * FROM `{$g5['board_table']}` ORDER BY bo_order, bo_table LIMIT 1", false);
        if (!$source) return false;

        $group = sql_fetch("SELECT gr_id FROM `{$g5['group_table']}` WHERE gr_id='community'", false);
        if (!$group) $group = sql_fetch("SELECT gr_id FROM `{$g5['group_table']}` ORDER BY gr_id LIMIT 1", false);
        if (!$group || empty($group['gr_id'])) return false;

        $source['bo_table'] = WZC_BOARD_TABLE;
        $source['gr_id'] = $group['gr_id'];
        $source['bo_subject'] = '내 캘린더';
        $source['bo_mobile_subject'] = '내 캘린더';
        $source['bo_device'] = 'both';
        $source['bo_admin'] = '';
        $source['bo_list_level'] = 1;
        $source['bo_read_level'] = 1;
        $source['bo_write_level'] = 10;
        $source['bo_reply_level'] = 10;
        $source['bo_comment_level'] = 10;
        $source['bo_upload_level'] = 10;
        $source['bo_download_level'] = 10;
        $source['bo_skin'] = 'calendar';
        $source['bo_mobile_skin'] = 'calendar';
        $source['bo_use_category'] = 0;
        $source['bo_category_list'] = '';
        $source['bo_use_dhtml_editor'] = 0;
        $source['bo_use_rss_view'] = 0;
        $source['bo_use_search'] = 0;
        $source['bo_use_sns'] = 0;
        $source['bo_use_captcha'] = 0;
        $source['bo_count_write'] = 0;
        $source['bo_count_comment'] = 0;
        $source['bo_notice'] = '';
        $source['bo_order'] = 20;

        $columns = array();
        $values = array();
        foreach ($source as $column => $value) {
            if (!preg_match('/^bo_[a-z0-9_]+$/', $column) && $column !== 'gr_id') continue;
            $columns[] = '`'.$column.'`';
            $values[] = wzc_sql_value($value);
        }
        if (!sql_query("INSERT INTO `{$g5['board_table']}` (".implode(',', $columns).") VALUES (".implode(',', $values).")", false)) return false;
    } else {
        sql_query("UPDATE `{$g5['board_table']}` SET bo_subject='내 캘린더', bo_mobile_subject='내 캘린더', bo_device='both', bo_list_level=1, bo_read_level=1, bo_skin='calendar', bo_mobile_skin='calendar', bo_use_search=0, bo_use_rss_view=0 WHERE bo_table='{$board_table}'", false);
    }

    $write_table = $g5['write_prefix'].WZC_BOARD_TABLE;
    $write_exists = sql_fetch("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='".sql_escape_string($write_table)."'", false);
    if (!$write_exists) {
        $sql_file = G5_ADMIN_PATH.'/sql_write.sql';
        if (!is_file($sql_file)) return false;
        $sql_lines = file($sql_file);
        if (function_exists('get_db_create_replace')) $sql_lines = get_db_create_replace($sql_lines);
        $create_sql = implode("\n", $sql_lines);
        $create_sql = preg_replace(array('/__TABLE_NAME__/', '/;/'), array($write_table, ''), $create_sql);
        if (!sql_query($create_sql, false)) return false;
    }

    $board_path = G5_DATA_PATH.'/file/'.WZC_BOARD_TABLE;
    if (!is_dir($board_path)) @mkdir($board_path, G5_DIR_PERMISSION, true);
    if (is_dir($board_path)) {
        @chmod($board_path, G5_DIR_PERMISSION);
        $index_file = $board_path.'/index.php';
        if (!is_file($index_file)) {
            $handle = @fopen($index_file, 'w');
            if ($handle) {
                @fwrite($handle, '');
                @fclose($handle);
                @chmod($index_file, G5_FILE_PERMISSION);
            }
        }
    }

    return true;
}

function wzc_install_menu() {
    global $g5;

    $link = G5_URL.'/calendar';
    $link_sql = sql_escape_string($link);
    $row = sql_fetch("SELECT me_id FROM `{$g5['menu_table']}` WHERE me_link='{$link_sql}' OR me_name='내 캘린더' LIMIT 1", false);
    if ($row) {
        sql_query("UPDATE `{$g5['menu_table']}` SET me_name='내 캘린더', me_link='{$link_sql}', me_target='self', me_use=1, me_mobile_use=1 WHERE me_id=".(int)$row['me_id'], false);
        return true;
    }

    $code = '';
    for ($number = 80; $number <= 99; $number++) {
        $candidate = (string)$number;
        $used = sql_fetch("SELECT me_id FROM `{$g5['menu_table']}` WHERE me_code='".sql_escape_string($candidate)."' LIMIT 1", false);
        if (!$used) {
            $code = $candidate;
            break;
        }
    }
    if ($code === '') return false;

    return (bool)sql_query("INSERT INTO `{$g5['menu_table']}` SET me_code='".sql_escape_string($code)."', me_name='내 캘린더', me_link='{$link_sql}', me_target='self', me_order=16, me_use=1, me_mobile_use=1", false);
}

function wzc_install_all() {
    return wzc_install_schema() && wzc_install_board() && wzc_install_menu();
}

function wzc_get_config() {
    global $g5;
    if (!wzc_schema_installed()) return array('wcf_use' => 0, 'wcf_max_events' => 5000, 'wcf_schema_version' => 0);
    $row = sql_fetch("SELECT * FROM `{$g5['wzc_config_table']}` WHERE wcf_ix=1", false);
    return $row ?: array('wcf_use' => 1, 'wcf_max_events' => 5000, 'wcf_schema_version' => WZC_SCHEMA_VERSION);
}

function wzc_require_enabled_json() {
    $config = wzc_get_config();
    if (empty($config['wcf_use'])) {
        wzc_json_response(false, array('message' => '현재 캘린더 기능을 사용할 수 없습니다.', 'code' => 'CALENDAR_DISABLED'), 503);
    }
    return $config;
}

function wzc_get_preference($mb_id) {
    global $g5;
    $mb = sql_escape_string($mb_id);
    $row = sql_fetch("SELECT * FROM `{$g5['wzc_preference_table']}` WHERE mb_id='{$mb}'", false);
    if (!$row) return array('mb_id' => $mb_id, 'wp_events_per_day' => 3, 'wp_default_category' => null, 'wp_touch_drag_use' => 1);
    return $row;
}

function wzc_ensure_default_categories($mb_id) {
    global $g5;
    $mb = sql_escape_string($mb_id);
    $row = sql_fetch("SELECT COUNT(*) AS cnt FROM `{$g5['wzc_category_table']}` WHERE mb_id='{$mb}'", false);
    if ((int)$row['cnt'] > 0) return;
    $defaults = array(array('개인', '#6f48ff'), array('업무', '#2563eb'), array('약속', '#f97316'));
    foreach ($defaults as $sort => $item) {
        $name = sql_escape_string($item[0]);
        $color = sql_escape_string($item[1]);
        sql_query("INSERT IGNORE INTO `{$g5['wzc_category_table']}` SET mb_id='{$mb}', wc_name='{$name}', wc_color='{$color}', wc_use=1, wc_sort=".(int)$sort.", wc_created_at=NOW(), wc_updated_at=NOW()", false);
    }
}

function wzc_get_categories($mb_id, $include_disabled = false) {
    global $g5;
    $mb = sql_escape_string($mb_id);
    $where = $include_disabled ? '' : ' AND wc_use=1';
    $result = sql_query("SELECT * FROM `{$g5['wzc_category_table']}` WHERE mb_id='{$mb}'{$where} ORDER BY wc_sort, wc_ix", false);
    $rows = array();
    if ($result) while ($row = sql_fetch_array($result)) $rows[] = $row;
    return $rows;
}

function wzc_category_owned($mb_id, $category_id) {
    global $g5;
    if (!(int)$category_id) return true;
    $mb = sql_escape_string($mb_id);
    $id = (int)$category_id;
    $row = sql_fetch("SELECT wc_ix FROM `{$g5['wzc_category_table']}` WHERE wc_ix={$id} AND mb_id='{$mb}' AND wc_use=1", false);
    return !empty($row['wc_ix']);
}

function wzc_event_for_member($mb_id, $event_id, $include_deleted = false) {
    global $g5;
    $mb = sql_escape_string($mb_id);
    $id = (int)$event_id;
    $deleted = $include_deleted ? '' : ' AND we_deleted_at IS NULL';
    return sql_fetch("SELECT * FROM `{$g5['wzc_event_table']}` WHERE we_ix={$id} AND mb_id='{$mb}'{$deleted}", false);
}

function wzc_get_events($mb_id, $month_start, $month_end) {
    global $g5;
    $mb = sql_escape_string($mb_id);
    $start = sql_escape_string($month_start);
    $end = sql_escape_string($month_end);
    $sql = "SELECT e.*, c.wc_name, c.wc_color
        FROM `{$g5['wzc_event_table']}` e
        LEFT JOIN `{$g5['wzc_category_table']}` c ON c.wc_ix=e.wc_ix AND c.mb_id=e.mb_id
        WHERE e.mb_id='{$mb}' AND e.we_deleted_at IS NULL
          AND e.we_start_date<='{$end}' AND e.we_end_date>='{$start}'
        ORDER BY e.we_start_date, e.we_start_time, e.we_ix";
    $result = sql_query($sql, false);
    $events = array();
    if ($result) while ($row = sql_fetch_array($result)) $events[(int)$row['we_ix']] = $row;
    return $events;
}

function wzc_events_by_date($mb_id, $events, $month_start, $month_end) {
    global $g5;
    $by_date = array();
    $order_map = array();
    $mb = sql_escape_string($mb_id);
    $start = sql_escape_string($month_start);
    $end = sql_escape_string($month_end);
    $order_result = sql_query("SELECT we_ix, wo_date, wo_sort FROM `{$g5['wzc_event_order_table']}` WHERE mb_id='{$mb}' AND wo_date BETWEEN '{$start}' AND '{$end}'", false);
    if ($order_result) while ($row = sql_fetch_array($order_result)) $order_map[$row['wo_date']][(int)$row['we_ix']] = (int)$row['wo_sort'];
    foreach ($events as $event) {
        $from = max($event['we_start_date'], $month_start);
        $to = min($event['we_end_date'], $month_end);
        for ($date = $from; $date <= $to; $date = wzc_date_add($date, 1)) {
            $copy = $event;
            $copy['_display_date'] = $date;
            $copy['_sort'] = isset($order_map[$date][(int)$event['we_ix']]) ? $order_map[$date][(int)$event['we_ix']] : 100000;
            $by_date[$date][] = $copy;
        }
    }
    foreach ($by_date as &$items) {
        usort($items, function($a, $b) {
            if ((int)$a['_sort'] !== (int)$b['_sort']) return (int)$a['_sort'] <=> (int)$b['_sort'];
            $a_time = $a['we_all_day'] ? '00:00:00' : (string)$a['we_start_time'];
            $b_time = $b['we_all_day'] ? '00:00:00' : (string)$b['we_start_time'];
            if ($a_time !== $b_time) return strcmp($a_time, $b_time);
            return (int)$a['we_ix'] <=> (int)$b['we_ix'];
        });
    }
    unset($items);
    return $by_date;
}

/**
 * Keep an event on the same visual lane while it crosses dates in a week.
 *
 * The calendar still renders one draggable event node per date, but the shared
 * lane and segment flags let CSS join those nodes into a single continuous bar.
 */
function wzc_event_layout_by_date($events_by_date, $cells) {
    $layout = array();
    $cell_count = count($cells);

    for ($week_offset = 0; $week_offset < $cell_count; $week_offset += 7) {
        $week_cells = array_slice($cells, $week_offset, 7);
        if (count($week_cells) < 7) break;

        $week_start = $week_cells[0]['date'];
        $week_end = $week_cells[6]['date'];
        $week_events = array();

        foreach ($week_cells as $cell) {
            $date = $cell['date'];
            $date_events = isset($events_by_date[$date]) ? $events_by_date[$date] : array();
            foreach ($date_events as $position => $event) {
                $event_id = (int)$event['we_ix'];
                if (isset($week_events[$event_id])) continue;

                $segment_start = max($event['we_start_date'], $week_start);
                $segment_end = min($event['we_end_date'], $week_end);
                $start_column = wzc_date_diff_days($week_start, $segment_start);
                $end_column = wzc_date_diff_days($week_start, $segment_end);
                $week_events[$event_id] = array(
                    'event' => $event,
                    'start_column' => $start_column,
                    'end_column' => $end_column,
                    'sort' => isset($event['_sort']) ? (int)$event['_sort'] : 100000,
                    'position' => (int)$position
                );
            }
        }

        $week_events = array_values($week_events);
        usort($week_events, function($a, $b) {
            if ($a['sort'] !== $b['sort']) return $a['sort'] <=> $b['sort'];
            if ($a['position'] !== $b['position']) return $a['position'] <=> $b['position'];
            $a_span = $a['end_column'] - $a['start_column'];
            $b_span = $b['end_column'] - $b['start_column'];
            if ($a_span !== $b_span) return $b_span <=> $a_span;
            if ($a['start_column'] !== $b['start_column']) return $a['start_column'] <=> $b['start_column'];
            return (int)$a['event']['we_ix'] <=> (int)$b['event']['we_ix'];
        });

        $occupied = array();
        foreach ($week_events as $item) {
            $lane = 0;
            while (true) {
                $available = true;
                for ($column = $item['start_column']; $column <= $item['end_column']; $column++) {
                    if (!empty($occupied[$lane][$column])) {
                        $available = false;
                        break;
                    }
                }
                if ($available) break;
                $lane++;
            }

            for ($column = $item['start_column']; $column <= $item['end_column']; $column++) {
                $occupied[$lane][$column] = true;
                $date = $week_cells[$column]['date'];
                $copy = $item['event'];
                $copy['_display_date'] = $date;
                $copy['_layout_lane'] = $lane;
                $copy['_segment_start'] = $column === $item['start_column'];
                $copy['_segment_end'] = $column === $item['end_column'];
                $copy['_event_start'] = $date === $copy['we_start_date'];
                $copy['_event_end'] = $date === $copy['we_end_date'];
                $copy['_show_title'] = $column === $item['start_column'];
                $layout[$date][] = $copy;
            }
        }
    }

    foreach ($layout as &$items) {
        usort($items, function($a, $b) {
            return (int)$a['_layout_lane'] <=> (int)$b['_layout_lane'];
        });
    }
    unset($items);

    return $layout;
}

function wzc_valid_event_ids_for_date($mb_id, $date) {
    global $g5;
    $mb = sql_escape_string($mb_id);
    $date_sql = sql_escape_string($date);
    $sql = "SELECT e.we_ix, COALESCE(o.wo_sort,100000) AS wo_sort
        FROM `{$g5['wzc_event_table']}` e
        LEFT JOIN `{$g5['wzc_event_order_table']}` o ON o.we_ix=e.we_ix AND o.mb_id=e.mb_id AND o.wo_date='{$date_sql}'
        WHERE e.mb_id='{$mb}' AND e.we_deleted_at IS NULL
          AND e.we_start_date<='{$date_sql}' AND e.we_end_date>='{$date_sql}'
        ORDER BY wo_sort, e.we_start_time, e.we_ix";
    $result = sql_query($sql, false);
    $ids = array();
    if ($result) while ($row = sql_fetch_array($result)) $ids[] = (int)$row['we_ix'];
    return $ids;
}

function wzc_save_date_order($mb_id, $date, $submitted_ids) {
    global $g5;
    if (!wzc_valid_date($date)) return false;
    $valid_ids = wzc_valid_event_ids_for_date($mb_id, $date);
    $valid_map = array_fill_keys($valid_ids, true);
    $ordered = array();
    foreach ((array)$submitted_ids as $raw_id) {
        $id = (int)$raw_id;
        if ($id && isset($valid_map[$id]) && !in_array($id, $ordered, true)) $ordered[] = $id;
        elseif ($id && !isset($valid_map[$id])) return false;
    }
    foreach ($valid_ids as $id) if (!in_array($id, $ordered, true)) $ordered[] = $id;
    $mb = sql_escape_string($mb_id);
    $date_sql = sql_escape_string($date);
    if (!sql_query("DELETE FROM `{$g5['wzc_event_order_table']}` WHERE mb_id='{$mb}' AND wo_date='{$date_sql}'", false)) return false;
    foreach ($ordered as $sort => $id) {
        if (!sql_query("INSERT INTO `{$g5['wzc_event_order_table']}` SET mb_id='{$mb}', we_ix=".(int)$id.", wo_date='{$date_sql}', wo_sort=".(int)$sort.", wo_updated_at=NOW()", false)) return false;
    }
    return true;
}

/**
 * Create a member-owned event without producing an HTTP response.
 *
 * This is used by trusted internal integrations such as the YouTube watch
 * tracker. The caller may already have an open transaction, so this function
 * deliberately does not start, commit, or roll back one.
 */
function wzc_create_member_event($mb_id, $data, $source = array()) {
    global $g5;
    if (!$mb_id || !wzc_schema_installed()) return array('success' => false, 'code' => 'NOT_INSTALLED');
    $calendar_config = wzc_get_config();
    if (empty($calendar_config['wcf_use'])) return array('success' => false, 'code' => 'CALENDAR_DISABLED');

    $title = wzc_plain_text(isset($data['title']) ? $data['title'] : '', 255);
    $content = trim((string)(isset($data['content']) ? $data['content'] : ''));
    $location = wzc_plain_text(isset($data['location']) ? $data['location'] : '', 255);
    $category_id = isset($data['category_id']) ? (int)$data['category_id'] : 0;
    $start_date = isset($data['start_date']) ? (string)$data['start_date'] : '';
    $end_date = isset($data['end_date']) ? (string)$data['end_date'] : $start_date;
    $all_day = !empty($data['all_day']) ? 1 : 0;
    $start_time = isset($data['start_time']) ? substr((string)$data['start_time'], 0, 5) : '';
    $end_time = isset($data['end_time']) ? substr((string)$data['end_time'], 0, 5) : '';
    $link_url = wzc_clean_url(isset($data['link_url']) ? $data['link_url'] : '');

    if ($title === '') return array('success' => false, 'code' => 'INVALID_TITLE');
    if (mb_strlen($content, 'UTF-8') > 60000) return array('success' => false, 'code' => 'INVALID_CONTENT');
    if (!wzc_valid_date($start_date) || !wzc_valid_date($end_date) || $start_date > $end_date) return array('success' => false, 'code' => 'INVALID_DATE');
    if ((int)substr($start_date, 0, 4) < 1970 || (int)substr($end_date, 0, 4) > 2100) return array('success' => false, 'code' => 'INVALID_DATE');
    if (wzc_event_day_count($start_date, $end_date) > 366) return array('success' => false, 'code' => 'INVALID_RANGE');
    if (!$all_day && (!wzc_valid_time($start_time) || !wzc_valid_time($end_time))) return array('success' => false, 'code' => 'INVALID_TIME');
    if (!$all_day && $start_date === $end_date && $start_time && $end_time && $start_time > $end_time) return array('success' => false, 'code' => 'INVALID_TIME');
    if ($link_url === false) return array('success' => false, 'code' => 'INVALID_LINK');
    if (!wzc_category_owned($mb_id, $category_id)) return array('success' => false, 'code' => 'INVALID_CATEGORY');

    $mb_sql = sql_escape_string($mb_id);
    $count = sql_fetch("SELECT COUNT(*) AS cnt FROM `{$g5['wzc_event_table']}` WHERE mb_id='{$mb_sql}' AND we_deleted_at IS NULL", false);
    if ((int)$count['cnt'] >= (int)$calendar_config['wcf_max_events']) return array('success' => false, 'code' => 'MAX_EVENTS');

    $title_sql = sql_escape_string($title);
    $content_sql = sql_escape_string($content);
    $location_sql = sql_escape_string($location);
    $link_sql = sql_escape_string((string)$link_url);
    $start_sql = sql_escape_string($start_date);
    $end_sql = sql_escape_string($end_date);
    $category_sql = $category_id ? (string)$category_id : 'NULL';
    $start_time_sql = (!$all_day && $start_time) ? "'".sql_escape_string($start_time).":00'" : 'NULL';
    $end_time_sql = (!$all_day && $end_time) ? "'".sql_escape_string($end_time).":00'" : 'NULL';

    $insert = "INSERT INTO `{$g5['wzc_event_table']}` SET
        mb_id='{$mb_sql}', wc_ix={$category_sql}, we_title='{$title_sql}', we_content='{$content_sql}',
        we_start_date='{$start_sql}', we_end_date='{$end_sql}', we_all_day={$all_day},
        we_start_time={$start_time_sql}, we_end_time={$end_time_sql}, we_location='{$location_sql}',
        we_link_url='{$link_sql}', we_version=1, we_created_at=NOW(), we_updated_at=NOW()";
    if (!sql_query($insert, false)) return array('success' => false, 'code' => 'INSERT_FAILED');
    $event_id = (int)sql_insert_id();

    for ($date = $start_date; $date <= $end_date; $date = wzc_date_add($date, 1)) {
        $ordered_ids = wzc_valid_event_ids_for_date($mb_id, $date);
        if (!wzc_save_date_order($mb_id, $date, $ordered_ids)) {
            sql_query("DELETE FROM `{$g5['wzc_event_order_table']}` WHERE mb_id='{$mb_sql}' AND we_ix={$event_id}", false);
            sql_query("DELETE FROM `{$g5['wzc_event_table']}` WHERE mb_id='{$mb_sql}' AND we_ix={$event_id}", false);
            return array('success' => false, 'code' => 'ORDER_FAILED');
        }
    }

    return array(
        'success' => true,
        'code' => 'CREATED',
        'event_id' => $event_id,
        'source_type' => isset($source['source_type']) ? (string)$source['source_type'] : '',
        'source_id' => isset($source['source_id']) ? (int)$source['source_id'] : 0
    );
}

function wzc_event_public_data($event) {
    return array(
        'id' => (int)$event['we_ix'],
        'category_id' => isset($event['wc_ix']) ? (int)$event['wc_ix'] : 0,
        'category_name' => isset($event['wc_name']) ? $event['wc_name'] : '',
        'color' => isset($event['wc_color']) && $event['wc_color'] ? $event['wc_color'] : '#6f48ff',
        'title' => $event['we_title'],
        'content' => $event['we_content'],
        'start_date' => $event['we_start_date'],
        'end_date' => $event['we_end_date'],
        'all_day' => (int)$event['we_all_day'],
        'start_time' => $event['we_start_time'] ? substr($event['we_start_time'], 0, 5) : '',
        'end_time' => $event['we_end_time'] ? substr($event['we_end_time'], 0, 5) : '',
        'location' => $event['we_location'],
        'link_url' => $event['we_link_url'],
        'source_type' => isset($event['_source_type']) ? (string)$event['_source_type'] : '',
        'version' => (int)$event['we_version']
    );
}

class WzcCalendar {
    public $year;
    public $month;
    public $month_text;
    public $month_start;
    public $month_end;
    public $prev_year;
    public $prev_month;
    public $next_year;
    public $next_month;
    public $today;
    public $selected;
    public $cells = array();

    public function __construct($year = 0, $month = 0, $selected = '') {
        $today = new DateTimeImmutable(G5_TIME_YMD);
        $year = (int)$year;
        $month = (int)$month;
        if ($year < 1970 || $year > 2100 || $month < 1 || $month > 12) {
            $year = (int)$today->format('Y');
            $month = (int)$today->format('n');
        }
        $first = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $this->year = $year;
        $this->month = $month;
        $this->month_text = sprintf('%02d', $month);
        $this->month_start = $first->format('Y-m-d');
        $this->month_end = $first->modify('last day of this month')->format('Y-m-d');
        $prev = $first->modify('-1 month');
        $next = $first->modify('+1 month');
        $this->prev_year = (int)$prev->format('Y');
        $this->prev_month = (int)$prev->format('n');
        $this->next_year = (int)$next->format('Y');
        $this->next_month = (int)$next->format('n');
        $this->today = $today->format('Y-m-d');
        $this->selected = wzc_valid_date($selected) ? $selected : $this->today;
        $grid_start = $first->modify('-'.(int)$first->format('w').' days');
        for ($i = 0; $i < 42; $i++) {
            $date = $grid_start->modify('+'.$i.' days');
            $date_text = $date->format('Y-m-d');
            $this->cells[] = array(
                'date' => $date_text,
                'day' => (int)$date->format('j'),
                'weekday' => (int)$date->format('w'),
                'current_month' => (int)$date->format('n') === $month,
                'today' => $date_text === $this->today,
                'selected' => $date_text === $this->selected
            );
        }
    }
}
