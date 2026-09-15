<?php
if (!defined('_GNUBOARD_')) exit;

function uxc_study_data($layout, $params = array()) {
    global $g5, $member, $is_admin;

    include_once(G5_PLUGIN_PATH.'/wz.calendar/config.php');
    include_once(G5_PLUGIN_PATH.'/wz.calendar/calendar.lib.php');
    include_once(G5_PLUGIN_PATH.'/wz.youtube_watch/config.php');
    include_once(G5_PLUGIN_PATH.'/wz.youtube_watch/youtube_watch.lib.php');

    $year = isset($params['study_year']) && is_scalar($params['study_year']) ? (int)$params['study_year'] : 0;
    $month = isset($params['study_month']) && is_scalar($params['study_month']) ? (int)$params['study_month'] : 0;
    $day = isset($params['study_day']) && is_string($params['study_day']) ? $params['study_day'] : '';
    $calendar = new WzcCalendar($year, $month, $day);
    if ($calendar->selected < $calendar->month_start || $calendar->selected > $calendar->month_end) {
        $calendar->selected = $calendar->month_start;
    }
    // Show only the weeks needed by this month.
    if (!$calendar->cells[35]['current_month']) $calendar->cells = array_slice($calendar->cells, 0, 35);
    if (!$calendar->cells[28]['current_month']) $calendar->cells = array_slice($calendar->cells, 0, 28);
    $start = $calendar->cells[0]['date'];
    $end = $calendar->cells[count($calendar->cells) - 1]['date'];
    $next_day = (new DateTimeImmutable($end))->modify('+1 day')->format('Y-m-d');
    $data = array('calendar' => $calendar, 'days' => array(), 'lessons' => array(), 'boards' => array(), 'enabled' => false);

    // Offer the lecture boards already selected in the homepage gallery widgets.
    $board_ids = array();
    foreach ($layout['grid_rows'] ?? array() as $row) {
        foreach ($row['columns'] ?? array() as $column) {
            $widget = $column['widget'] ?? array();
            if (($widget['type'] ?? '') !== 'latest' || strpos($widget['skin'] ?? '', 'gallery') === false) continue;
            foreach ($widget['boards'] ?? explode(',', $widget['board'] ?? '') as $id) {
                if (is_string($id) && preg_match('/^[a-zA-Z0-9_]{1,20}$/', $id)) $board_ids[$id] = $id;
            }
        }
    }
    if ($board_ids) {
        $ids = implode(',', array_map(function($id) { return "'".sql_escape_string($id)."'"; }, $board_ids));
        $result = sql_query("SELECT b.bo_table, b.bo_subject, b.bo_write_level, b.bo_read_level, b.gr_id, g.gr_use_access
            FROM `{$g5['board_table']}` b LEFT JOIN `{$g5['group_table']}` g ON g.gr_id=b.gr_id
            WHERE b.bo_table IN ({$ids}) ORDER BY b.bo_order, b.bo_subject", false);
        while ($result && ($board = sql_fetch_array($result))) {
            if (empty($is_admin) && (int)($member['mb_level'] ?? 1) < (int)$board['bo_read_level']) continue;
            if (!empty($member['mb_id']) && empty($is_admin) && (int)$member['mb_level'] < (int)$board['bo_write_level']) continue;
            if (!empty($board['gr_use_access']) && empty($is_admin)) {
                $mb = sql_escape_string($member['mb_id'] ?? '');
                $gr = sql_escape_string($board['gr_id']);
                $access = sql_fetch("SELECT gm_id FROM `{$g5['group_member_table']}` WHERE mb_id='{$mb}' AND gr_id='{$gr}'", false);
                if (!$access) continue;
            }
            $data['boards'][] = $board;
        }
    }

    if (empty($member['mb_id'])) return $data;
    $watch_config = wzy_get_config();
    if (empty($watch_config['wyc_use'])) return $data;
    $data['enabled'] = true;
    $mb = sql_escape_string($member['mb_id']);
    $join = '';
    $fields = ', NULL AS event_start, NULL AS event_end';
    $event_range = '';
    $calendar_config = wzc_get_config();
    if (!empty($calendar_config['wcf_use']) && wzc_schema_installed()) {
        $join = " LEFT JOIN `{$g5['wzc_event_table']}` e ON e.we_ix=w.ww_calendar_event_id AND e.mb_id=w.mb_id AND e.we_deleted_at IS NULL";
        $fields = ', e.we_start_date AS event_start, e.we_end_date AS event_end';
        $event_range = " OR (e.we_start_date<='{$end}' AND e.we_end_date>='{$start}')";
    }
    // Existing calendar spans preserve past study records when a video is watched again.
    $result = sql_query("SELECT w.* {$fields} FROM `{$g5['wzy_watch_table']}` w {$join}
        WHERE w.mb_id='{$mb}' AND w.ww_percent>0 AND (
            (w.ww_last_watched_at>='{$start} 00:00:00' AND w.ww_last_watched_at<'{$next_day} 00:00:00')
            OR (w.ww_completed_at>='{$start} 00:00:00' AND w.ww_completed_at<'{$next_day} 00:00:00') {$event_range})
        ORDER BY w.ww_last_watched_at DESC, w.ww_ix DESC", false);
    $posts = array();
    while ($result && ($watch = sql_fetch_array($result))) {
        $key = $watch['bo_table'].':'.$watch['wr_id'];
        if (!array_key_exists($key, $posts)) {
            $bundle = wzy_get_post($watch['bo_table'], $watch['wr_id']);
            $posts[$key] = wzy_can_read_post($bundle) ? $bundle : null;
        }
        $bundle = $posts[$key];
        if (!$bundle || !in_array($watch['wy_video_id'], wzy_post_video_ids($bundle['post']), true)) continue;
        $dates = array(substr($watch['ww_last_watched_at'], 0, 10), substr($watch['ww_completed_at'] ?? '', 0, 10));
        foreach ($calendar->cells as $cell) {
            $date = $cell['date'];
            if (in_array($date, $dates, true) || ($watch['event_start'] && $date >= $watch['event_start'] && $date <= $watch['event_end'])) {
                $data['days'][$date] = true;
                if ($date === $calendar->selected) {
                    $data['lessons'][] = array(
                        'title' => wzy_plain_subject($bundle['post']['wr_subject']),
                        'category' => $bundle['board']['bo_subject'],
                        'url' => get_pretty_url($watch['bo_table'], $watch['wr_id']),
                        'thumbnail' => 'https://img.youtube.com/vi/'.rawurlencode($watch['wy_video_id']).'/mqdefault.jpg',
                        'percent' => max(0, min(100, (int)$watch['ww_percent'])),
                        'completed' => $watch['ww_status'] === 'completed'
                    );
                }
            }
        }
    }
    return $data;
}
