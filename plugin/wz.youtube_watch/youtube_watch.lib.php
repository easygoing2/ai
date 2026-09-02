<?php
if (!defined('_GNUBOARD_')) exit;

function wzy_json_response($success, $data = array(), $status = 200) {
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    }
    echo json_encode(array_merge(array('success' => (bool)$success), $data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function wzy_request_data() {
    $content_type = isset($_SERVER['CONTENT_TYPE']) ? strtolower((string)$_SERVER['CONTENT_TYPE']) : '';
    if (strpos($content_type, 'application/json') !== false) {
        $data = json_decode((string)file_get_contents('php://input'), true);
        return is_array($data) ? $data : array();
    }
    return $_POST;
}

function wzy_csrf_token() {
    $token = get_session('ss_wzy_csrf_token');
    if (!$token) {
        try {
            $token = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            $token = get_random_token_string(64);
        }
        set_session('ss_wzy_csrf_token', $token);
    }
    return $token;
}

function wzy_require_request() {
    if (!isset($_SERVER['REQUEST_METHOD']) || strtoupper((string)$_SERVER['REQUEST_METHOD']) !== 'POST') {
        wzy_json_response(false, array('message' => 'POST 요청만 허용됩니다.', 'code' => 'METHOD_NOT_ALLOWED'), 405);
    }
    if (isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 65536) {
        wzy_json_response(false, array('message' => '요청 데이터가 너무 큽니다.', 'code' => 'PAYLOAD_TOO_LARGE'), 413);
    }
    $data = wzy_request_data();
    $saved = get_session('ss_wzy_csrf_token');
    $token = isset($data['csrf_token']) ? (string)$data['csrf_token'] : '';
    if (!$saved || !$token || !hash_equals((string)$saved, $token)) {
        wzy_json_response(false, array('message' => '요청 토큰이 올바르지 않습니다. 페이지를 새로고침해 주세요.', 'code' => 'INVALID_TOKEN'), 403);
    }
    return $data;
}

function wzy_require_member() {
    global $member;
    if (empty($member['mb_id'])) {
        wzy_json_response(false, array('message' => '로그인이 필요합니다.', 'code' => 'LOGIN_REQUIRED'), 401);
    }
    return (string)$member['mb_id'];
}

function wzy_rate_limit($key, $limit = 120, $seconds = 60) {
    $session_key = 'ss_wzy_rate_'.preg_replace('/[^a-z0-9_]/i', '', (string)$key);
    $now = time();
    $history = get_session($session_key);
    if (!is_array($history)) $history = array();
    $history = array_values(array_filter($history, function($at) use ($now, $seconds) {
        return (int)$at > $now - (int)$seconds;
    }));
    if (count($history) >= (int)$limit) {
        wzy_json_response(false, array('message' => '시청 기록 요청이 너무 많습니다. 잠시 후 다시 시도해 주세요.', 'code' => 'RATE_LIMIT'), 429);
    }
    $history[] = $now;
    set_session($session_key, $history);
}

function wzy_schema_installed($refresh = false) {
    global $g5;
    static $installed = null;
    if (!$refresh && $installed !== null) return $installed;
    foreach (array($g5['wzy_watch_table'], $g5['wzy_config_table']) as $table) {
        $name = sql_escape_string($table);
        $row = sql_fetch("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$name}'", false);
        if (empty($row['TABLE_NAME'])) return $installed = false;
    }
    foreach (array('wyc_count_short_seek', 'wyc_calendar_percent') as $column) {
        if (!wzy_column_exists($g5['wzy_config_table'], $column)) return $installed = false;
    }
    return $installed = true;
}

function wzy_column_exists($table, $column) {
    $table_sql = sql_escape_string((string)$table);
    $column_sql = sql_escape_string((string)$column);
    $row = sql_fetch("SELECT COLUMN_NAME FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$table_sql}' AND COLUMN_NAME='{$column_sql}'", false);
    return !empty($row['COLUMN_NAME']);
}

function wzy_install_schema() {
    global $g5;
    $watch_sql = "CREATE TABLE IF NOT EXISTS `{$g5['wzy_watch_table']}` (
        `ww_ix` bigint unsigned NOT NULL AUTO_INCREMENT,
        `mb_id` varchar(20) NOT NULL,
        `bo_table` varchar(20) NOT NULL,
        `wr_id` bigint unsigned NOT NULL,
        `wy_video_id` varchar(20) NOT NULL,
        `ww_subject_snapshot` varchar(255) NOT NULL DEFAULT '',
        `ww_duration_seconds` decimal(12,3) NOT NULL DEFAULT '0.000',
        `ww_ranges_json` mediumtext NOT NULL,
        `ww_watched_seconds` decimal(12,3) NOT NULL DEFAULT '0.000',
        `ww_percent` tinyint unsigned NOT NULL DEFAULT '0',
        `ww_last_position` decimal(12,3) NOT NULL DEFAULT '0.000',
        `ww_status` varchar(20) NOT NULL DEFAULT 'watching',
        `ww_started_at` datetime NOT NULL,
        `ww_last_watched_at` datetime NOT NULL,
        `ww_completed_at` datetime DEFAULT NULL,
        `ww_calendar_event_id` bigint unsigned DEFAULT NULL,
        `ww_calendar_status` varchar(20) NOT NULL DEFAULT 'none',
        `ww_version` int unsigned NOT NULL DEFAULT '1',
        `ww_created_at` datetime NOT NULL,
        `ww_updated_at` datetime NOT NULL,
        PRIMARY KEY (`ww_ix`),
        UNIQUE KEY `member_post_video` (`mb_id`,`bo_table`,`wr_id`,`wy_video_id`),
        KEY `member_recent` (`mb_id`,`ww_last_watched_at`),
        KEY `calendar_event` (`ww_calendar_event_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $config_sql = "CREATE TABLE IF NOT EXISTS `{$g5['wzy_config_table']}` (
        `wyc_ix` tinyint unsigned NOT NULL DEFAULT '1',
        `wyc_use` tinyint(1) NOT NULL DEFAULT '1',
        `wyc_completion_percent` tinyint unsigned NOT NULL DEFAULT '90',
        `wyc_count_short_seek` tinyint(1) NOT NULL DEFAULT '1',
        `wyc_save_interval` tinyint unsigned NOT NULL DEFAULT '10',
        `wyc_show_list_badge` tinyint(1) NOT NULL DEFAULT '1',
        `wyc_calendar_use` tinyint(1) NOT NULL DEFAULT '1',
        `wyc_calendar_percent` tinyint unsigned NOT NULL DEFAULT '90',
        `wyc_schema_version` int unsigned NOT NULL DEFAULT '2',
        `wyc_updated_at` datetime NOT NULL,
        PRIMARY KEY (`wyc_ix`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    if (!sql_query($watch_sql, false) || !sql_query($config_sql, false)) return false;
    if (!wzy_column_exists($g5['wzy_config_table'], 'wyc_count_short_seek')) {
        if (!sql_query("ALTER TABLE `{$g5['wzy_config_table']}` ADD `wyc_count_short_seek` tinyint(1) NOT NULL DEFAULT '1' AFTER `wyc_completion_percent`", false)) return false;
    }
    if (!wzy_column_exists($g5['wzy_config_table'], 'wyc_calendar_percent')) {
        if (!sql_query("ALTER TABLE `{$g5['wzy_config_table']}` ADD `wyc_calendar_percent` tinyint unsigned NOT NULL DEFAULT '90' AFTER `wyc_calendar_use`", false)) return false;
    }
    sql_query("INSERT INTO `{$g5['wzy_config_table']}`
        (`wyc_ix`,`wyc_use`,`wyc_completion_percent`,`wyc_count_short_seek`,`wyc_save_interval`,`wyc_show_list_badge`,`wyc_calendar_use`,`wyc_calendar_percent`,`wyc_schema_version`,`wyc_updated_at`)
        VALUES (1,1,90,1,10,1,1,90,".(int)WZY_SCHEMA_VERSION.",NOW())
        ON DUPLICATE KEY UPDATE `wyc_schema_version`=GREATEST(`wyc_schema_version`, VALUES(`wyc_schema_version`))", false);
    return wzy_schema_installed(true);
}

function wzy_get_config() {
    global $g5;
    $defaults = array(
        'wyc_use' => 0,
        'wyc_completion_percent' => 90,
        'wyc_count_short_seek' => 1,
        'wyc_save_interval' => 10,
        'wyc_show_list_badge' => 1,
        'wyc_calendar_use' => 1,
        'wyc_calendar_percent' => 90,
        'wyc_schema_version' => 0
    );
    if (!wzy_schema_installed()) return $defaults;
    $row = sql_fetch("SELECT * FROM `{$g5['wzy_config_table']}` WHERE wyc_ix=1", false);
    return $row ? array_merge($defaults, $row) : $defaults;
}

function wzy_completion_threshold($config) {
    return max(50, min(100, (int)(isset($config['wyc_completion_percent']) ? $config['wyc_completion_percent'] : 90)));
}

function wzy_calendar_threshold($config) {
    return max(1, min(100, (int)(isset($config['wyc_calendar_percent']) ? $config['wyc_calendar_percent'] : 90)));
}

function wzy_extract_youtube_id($value) {
    $value = trim(html_entity_decode((string)$value, ENT_QUOTES, 'UTF-8'));
    if (preg_match('/^[A-Za-z0-9_-]{11}$/', $value)) return $value;
    if ($value === '') return '';

    $parts = @parse_url($value);
    if (!is_array($parts) || empty($parts['host'])) return '';
    $host = strtolower(preg_replace('/^(www\.|m\.)/', '', $parts['host']));
    $path = isset($parts['path']) ? trim((string)$parts['path'], '/') : '';
    $candidate = '';

    if ($host === 'youtu.be') {
        $candidate = explode('/', $path)[0];
    } elseif (in_array($host, array('youtube.com', 'youtube-nocookie.com'), true)) {
        if ($path === 'watch') {
            parse_str(isset($parts['query']) ? (string)$parts['query'] : '', $query);
            $candidate = isset($query['v']) ? (string)$query['v'] : '';
        } elseif (preg_match('#^(embed|shorts|live)/([^/?]+)#', $path, $matches)) {
            $candidate = $matches[2];
        }
    }
    return preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate) ? $candidate : '';
}

function wzy_post_video_ids($post) {
    $ids = array();
    foreach (array('wr_10', 'wr_link1') as $field) {
        $id = wzy_extract_youtube_id(isset($post[$field]) ? $post[$field] : '');
        if ($id && !in_array($id, $ids, true)) $ids[] = $id;
    }
    return $ids;
}

function wzy_get_post($bo_table, $wr_id) {
    global $g5;
    if (!preg_match('/^[a-zA-Z0-9_]{1,20}$/', (string)$bo_table)) return null;
    $bo_sql = sql_escape_string($bo_table);
    $board = sql_fetch("SELECT * FROM `{$g5['board_table']}` WHERE bo_table='{$bo_sql}'", false);
    if (empty($board['bo_table'])) return null;
    $group = sql_fetch("SELECT * FROM `{$g5['group_table']}` WHERE gr_id='".sql_escape_string($board['gr_id'])."'", false);
    $write_table = $g5['write_prefix'].$board['bo_table'];
    $post = sql_fetch("SELECT * FROM `{$write_table}` WHERE wr_id=".(int)$wr_id." AND wr_is_comment=0", false);
    if (empty($post['wr_id'])) return null;
    return array('board' => $board, 'group' => $group, 'post' => $post, 'write_table' => $write_table);
}

function wzy_can_read_post($bundle) {
    global $g5, $member, $is_admin, $config;
    if (!$bundle || empty($member['mb_id'])) return false;
    $board = $bundle['board'];
    $group = isset($bundle['group']) ? $bundle['group'] : array();
    $post = $bundle['post'];
    if ((int)$member['mb_level'] < (int)$board['bo_read_level']) return false;

    $admin = !empty($is_admin);
    if (!empty($group['gr_use_access']) && !$admin) {
        $gr = sql_escape_string($board['gr_id']);
        $mb = sql_escape_string($member['mb_id']);
        $row = sql_fetch("SELECT COUNT(*) AS cnt FROM `{$g5['group_member_table']}` WHERE gr_id='{$gr}' AND mb_id='{$mb}'", false);
        if (empty($row['cnt'])) return false;
    }

    if (!empty($board['bo_use_cert']) && !empty($config['cf_cert_use']) && !$admin) {
        $cert = (string)$board['bo_use_cert'];
        if (($cert === 'cert' || $cert === 'hp-cert') && empty($member['mb_certify'])) return false;
        if (($cert === 'adult' || $cert === 'hp-adult') && empty($member['mb_adult'])) return false;
    }

    if (strpos((string)$post['wr_option'], 'secret') !== false && !$admin && (string)$post['mb_id'] !== (string)$member['mb_id']) {
        $is_owner = false;
        if (!empty($post['wr_reply'])) {
            $root = sql_fetch("SELECT mb_id FROM `{$bundle['write_table']}` WHERE wr_num=".(int)$post['wr_num']." AND wr_reply='' AND wr_is_comment=0 LIMIT 1", false);
            $is_owner = !empty($root['mb_id']) && (string)$root['mb_id'] === (string)$member['mb_id'];
        }
        if (!$is_owner && !get_session('ss_secret_'.$board['bo_table'].'_'.$post['wr_num'])) return false;
    }
    return true;
}

function wzy_plain_subject($subject) {
    $subject = trim(strip_tags((string)$subject));
    if (mb_strlen($subject, 'UTF-8') > 230) $subject = mb_substr($subject, 0, 230, 'UTF-8');
    return $subject;
}

function wzy_merge_ranges($ranges, $duration) {
    $duration = (float)$duration;
    $clean = array();
    foreach ((array)$ranges as $range) {
        if (!is_array($range) || !array_key_exists(0, $range) || !array_key_exists(1, $range) || !is_numeric($range[0]) || !is_numeric($range[1])) continue;
        $start = max(0, min($duration, (float)$range[0]));
        $end = max(0, min($duration, (float)$range[1]));
        if (!is_finite($start) || !is_finite($end) || $end - $start < 0.2) continue;
        $clean[] = array(round($start, 3), round($end, 3));
    }
    usort($clean, function($a, $b) {
        if ($a[0] == $b[0]) return $a[1] <=> $b[1];
        return $a[0] <=> $b[0];
    });
    $merged = array();
    foreach ($clean as $range) {
        $last = count($merged) - 1;
        if ($last >= 0 && $range[0] <= $merged[$last][1] + 0.75) {
            $merged[$last][1] = max($merged[$last][1], $range[1]);
        } else {
            $merged[] = $range;
        }
    }
    return $merged;
}

function wzy_ranges_are_valid($ranges, $duration) {
    $duration = (float)$duration;
    if (!is_array($ranges) || !$ranges || count($ranges) > 50 || !is_finite($duration) || $duration < 1) return false;
    foreach ($ranges as $range) {
        if (!is_array($range) || count($range) !== 2 || !array_key_exists(0, $range) || !array_key_exists(1, $range) || !is_numeric($range[0]) || !is_numeric($range[1])) return false;
        $start = (float)$range[0];
        $end = (float)$range[1];
        if (!is_finite($start) || !is_finite($end) || $start < 0 || $end > $duration + 0.5 || $end - $start < 0.2) return false;
    }
    return true;
}

function wzy_watched_seconds($ranges) {
    $seconds = 0.0;
    foreach ((array)$ranges as $range) $seconds += max(0, (float)$range[1] - (float)$range[0]);
    return round($seconds, 3);
}

function wzy_watch_public_data($row, $include_ranges = false) {
    $data = array(
        'percent' => isset($row['ww_percent']) ? (int)$row['ww_percent'] : 0,
        'watched_seconds' => isset($row['ww_watched_seconds']) ? (float)$row['ww_watched_seconds'] : 0,
        'duration' => isset($row['ww_duration_seconds']) ? (float)$row['ww_duration_seconds'] : 0,
        'last_position' => isset($row['ww_last_position']) ? (float)$row['ww_last_position'] : 0,
        'status' => isset($row['ww_status']) ? $row['ww_status'] : 'watching',
        'calendar_status' => isset($row['ww_calendar_status']) ? $row['ww_calendar_status'] : 'none'
    );
    if ($include_ranges) {
        $ranges = json_decode(isset($row['ww_ranges_json']) ? $row['ww_ranges_json'] : '[]', true);
        $data['ranges'] = is_array($ranges) ? $ranges : array();
    }
    return $data;
}

function wzy_calendar_event_title($subject, $percent, $completed = false) {
    $subject = wzy_plain_subject($subject);
    $prefix = $completed ? '[시청완료] ' : '['.max(0, min(100, (int)$percent)).'%] ';
    return $prefix.$subject;
}

function wzy_create_calendar_event($mb_id, $post, $watch_id, $percent, $completed = false) {
    global $g5;
    if (!defined('WZC_PLUGIN_PATH')) {
        $config_file = G5_PLUGIN_PATH.'/wz.calendar/config.php';
        if (is_file($config_file)) include_once($config_file);
    }
    $lib_file = G5_PLUGIN_PATH.'/wz.calendar/calendar.lib.php';
    if (is_file($lib_file)) include_once($lib_file);
    if (!function_exists('wzc_schema_installed') || !wzc_schema_installed()) return array('success' => false, 'code' => 'CALENDAR_NOT_INSTALLED');
    $calendar_config = wzc_get_config();
    if (empty($calendar_config['wcf_use'])) return array('success' => false, 'code' => 'CALENDAR_DISABLED');
    if (!function_exists('wzc_create_member_event')) return array('success' => false, 'code' => 'CALENDAR_UNAVAILABLE');

    $subject = wzy_plain_subject($post['wr_subject']);
    $title = wzy_calendar_event_title($subject, $percent, $completed);
    $bo_table = isset($post['_bo_table']) ? $post['_bo_table'] : '';
    $link = html_entity_decode(get_pretty_url($bo_table, (int)$post['wr_id']), ENT_QUOTES, 'UTF-8');
    $recorded_at = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
    $date = substr($recorded_at, 0, 10);
    $content = $completed
        ? "유튜브 영상 시청률 {$percent}%로 시청 완료\n완료 시각: {$recorded_at}"
        : "유튜브 영상 시청률 {$percent}%에서 캘린더 자동등록 기준 도달\n등록 시각: {$recorded_at}";

    return wzc_create_member_event($mb_id, array(
        'title' => $title,
        'content' => $content,
        'location' => '',
        'category_id' => 0,
        'start_date' => $date,
        'end_date' => $date,
        'all_day' => 1,
        'start_time' => '',
        'end_time' => '',
        'link_url' => $link
    ), array('source_type' => 'youtube_watch', 'source_id' => (int)$watch_id));
}

function wzy_backfill_calendar_events($limit = 200) {
    global $g5;
    if (!wzy_schema_installed()) return 0;
    $config = wzy_get_config();
    if (empty($config['wyc_use']) || empty($config['wyc_calendar_use'])) return 0;

    $threshold = wzy_calendar_threshold($config);
    $limit = max(1, min(1000, (int)$limit));
    $candidates = sql_query("SELECT ww_ix FROM `{$g5['wzy_watch_table']}`
        WHERE ww_percent>={$threshold} AND ww_calendar_event_id IS NULL
          AND ww_calendar_status IN ('none','pending')
        ORDER BY ww_updated_at DESC LIMIT {$limit}", false);
    if (!$candidates) return 0;

    $watch_ids = array();
    while ($candidate = sql_fetch_array($candidates)) $watch_ids[] = (int)$candidate['ww_ix'];
    $created_count = 0;
    foreach ($watch_ids as $watch_id) {
        sql_query('START TRANSACTION', false);
        $watch = sql_fetch("SELECT * FROM `{$g5['wzy_watch_table']}` WHERE ww_ix={$watch_id} FOR UPDATE", false);
        if (!$watch || (int)$watch['ww_percent'] < $threshold || !empty($watch['ww_calendar_event_id']) || !in_array($watch['ww_calendar_status'], array('none', 'pending'), true)) {
            sql_query('COMMIT', false);
            continue;
        }

        $bundle = wzy_get_post($watch['bo_table'], (int)$watch['wr_id']);
        if (!$bundle || !in_array($watch['wy_video_id'], wzy_post_video_ids($bundle['post']), true)) {
            sql_query("UPDATE `{$g5['wzy_watch_table']}` SET ww_calendar_status='unavailable', ww_updated_at=NOW() WHERE ww_ix={$watch_id}", false);
            sql_query('COMMIT', false);
            continue;
        }

        $bundle['post']['_bo_table'] = $watch['bo_table'];
        $calendar = wzy_create_calendar_event($watch['mb_id'], $bundle['post'], $watch_id, (int)$watch['ww_percent'], $watch['ww_status'] === 'completed');
        $event_id = !empty($calendar['success']) ? (int)$calendar['event_id'] : 0;
        if ($event_id) {
            $calendar_status = 'created';
        } else {
            $code = isset($calendar['code']) ? $calendar['code'] : '';
            $calendar_status = in_array($code, array('MAX_EVENTS', 'CALENDAR_UNAVAILABLE', 'INSERT_FAILED', 'ORDER_FAILED'), true) ? 'pending' : 'unavailable';
        }
        $updated = sql_query("UPDATE `{$g5['wzy_watch_table']}` SET ww_calendar_event_id=".($event_id ? $event_id : 'NULL').",
            ww_calendar_status='".sql_escape_string($calendar_status)."', ww_updated_at=NOW() WHERE ww_ix={$watch_id}", false);
        if (!$updated || !sql_query('COMMIT', false)) {
            sql_query('ROLLBACK', false);
        } elseif ($event_id) {
            $created_count++;
        }
    }
    return $created_count;
}