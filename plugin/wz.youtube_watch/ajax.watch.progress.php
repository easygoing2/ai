<?php
include_once('./_common.php');

$mb_id = wzy_require_member();
if (!wzy_schema_installed()) wzy_json_response(false, array('message' => '시청률 기능이 아직 설치되지 않았습니다.', 'code' => 'NOT_INSTALLED'), 503);
$wzy_config = wzy_get_config();
if (empty($wzy_config['wyc_use'])) wzy_json_response(false, array('message' => '현재 시청률 기능을 사용할 수 없습니다.', 'code' => 'WATCH_DISABLED'), 503);
$data = wzy_require_request();
wzy_rate_limit('progress', 180, 60);

$bo_table = isset($data['bo_table']) ? (string)$data['bo_table'] : '';
$wr_id = isset($data['wr_id']) ? (int)$data['wr_id'] : 0;
$video_id = isset($data['video_id']) ? (string)$data['video_id'] : '';
$duration = isset($data['duration']) && is_numeric($data['duration']) ? (float)$data['duration'] : 0;
$last_position = isset($data['last_position']) && is_numeric($data['last_position']) ? (float)$data['last_position'] : 0;
$new_ranges = isset($data['ranges']) && is_array($data['ranges']) ? $data['ranges'] : array();

if (!$wr_id || !preg_match('/^[A-Za-z0-9_-]{11}$/', $video_id)) {
    wzy_json_response(false, array('message' => '영상 식별 정보가 올바르지 않습니다.', 'code' => 'INVALID_VIDEO'), 422);
}
if (!is_finite($duration) || $duration < 1 || $duration > 604800) {
    wzy_json_response(false, array('message' => '영상 길이를 확인할 수 없습니다.', 'code' => 'INVALID_DURATION'), 422);
}
if (count($new_ranges) > 50) {
    wzy_json_response(false, array('message' => '한 번에 저장할 수 있는 시청 구간을 초과했습니다.', 'code' => 'TOO_MANY_RANGES'), 422);
}
if (!wzy_ranges_are_valid($new_ranges, $duration)) {
    wzy_json_response(false, array('message' => '시청 구간 형식이 올바르지 않습니다.', 'code' => 'INVALID_RANGES'), 422);
}

$bundle = wzy_get_post($bo_table, $wr_id);
if (!$bundle || !wzy_can_read_post($bundle)) wzy_json_response(false, array('message' => '게시물을 찾을 수 없습니다.', 'code' => 'NOT_FOUND'), 404);
if (!in_array($video_id, wzy_post_video_ids($bundle['post']), true)) {
    wzy_json_response(false, array('message' => '게시물의 유튜브 영상이 변경되었습니다.', 'code' => 'VIDEO_CHANGED'), 409);
}

$clean_new_ranges = wzy_merge_ranges($new_ranges, $duration);
if (!$clean_new_ranges) wzy_json_response(false, array('message' => '저장할 시청 구간이 없습니다.', 'code' => 'EMPTY_RANGES'), 422);

$mb_sql = sql_escape_string($mb_id);
$bo_sql = sql_escape_string($bo_table);
$video_sql = sql_escape_string($video_id);
$subject = wzy_plain_subject($bundle['post']['wr_subject']);
$subject_sql = sql_escape_string($subject);
$completed_now = false;
$calendar_event_id = 0;
$duration_sql = number_format($duration, 3, '.', '');

sql_query('START TRANSACTION', false);
$seed = "INSERT INTO `{$g5['wzy_watch_table']}` SET mb_id='{$mb_sql}', bo_table='{$bo_sql}', wr_id={$wr_id},
    wy_video_id='{$video_sql}', ww_subject_snapshot='{$subject_sql}', ww_duration_seconds={$duration_sql},
    ww_ranges_json='[]', ww_watched_seconds=0, ww_percent=0, ww_last_position=0,
    ww_status='watching', ww_started_at=NOW(), ww_last_watched_at=NOW(), ww_completed_at=NULL,
    ww_calendar_status='none', ww_version=1, ww_created_at=NOW(), ww_updated_at=NOW()
    ON DUPLICATE KEY UPDATE ww_ix=LAST_INSERT_ID(ww_ix)";
if (!sql_query($seed, false)) {
    sql_query('ROLLBACK', false);
    wzy_json_response(false, array('message' => '시청 기록을 준비하지 못했습니다.'), 500);
}
$watch_id = (int)sql_insert_id();
$row = sql_fetch("SELECT * FROM `{$g5['wzy_watch_table']}` WHERE ww_ix={$watch_id} AND mb_id='{$mb_sql}' FOR UPDATE", false);
if (!$row) {
    sql_query('ROLLBACK', false);
    wzy_json_response(false, array('message' => '시청 기록을 불러오지 못했습니다.'), 500);
}
$old_ranges = array();
$decoded = json_decode($row['ww_ranges_json'], true);
if (is_array($decoded)) $old_ranges = $decoded;
if ((float)$row['ww_duration_seconds'] > 0) {
    $old_duration = (float)$row['ww_duration_seconds'];
    if (abs($old_duration - $duration) > max(2, $old_duration * 0.02)) {
        sql_query('ROLLBACK', false);
        wzy_json_response(false, array('message' => '저장된 영상 길이와 일치하지 않습니다.', 'code' => 'DURATION_CHANGED'), 409);
    }
    $duration = $old_duration;
}
$ranges = wzy_merge_ranges(array_merge($old_ranges, $clean_new_ranges), $duration);
if (count($ranges) > 1000) {
    sql_query('ROLLBACK', false);
    wzy_json_response(false, array('message' => '시청 구간 데이터가 너무 많습니다.', 'code' => 'RANGE_LIMIT'), 422);
}
$watched_seconds = min($duration, wzy_watched_seconds($ranges));
$percent = max(0, min(100, (int)floor(($watched_seconds / $duration) * 100)));
$threshold = wzy_completion_threshold($wzy_config);
$calendar_threshold = wzy_calendar_threshold($wzy_config);
$was_completed = $row['ww_status'] === 'completed';
$status = ($was_completed || $percent >= $threshold) ? 'completed' : 'watching';
$completed_now = !$was_completed && $status === 'completed';
$calendar_status = (string)$row['ww_calendar_status'];
$calendar_event_id = (int)$row['ww_calendar_event_id'];
$ranges_sql = sql_escape_string(json_encode($ranges, JSON_UNESCAPED_SLASHES));
$duration_sql = number_format($duration, 3, '.', '');
$watched_sql = number_format($watched_seconds, 3, '.', '');
$position_sql = number_format(max(0, min($duration, is_finite($last_position) ? $last_position : 0)), 3, '.', '');

$completed_clause = $completed_now ? ', ww_completed_at=NOW()' : '';
$update = "UPDATE `{$g5['wzy_watch_table']}` SET ww_subject_snapshot='{$subject_sql}',
    ww_duration_seconds={$duration_sql}, ww_ranges_json='{$ranges_sql}', ww_watched_seconds={$watched_sql},
    ww_percent={$percent}, ww_last_position={$position_sql}, ww_status='{$status}',
    ww_last_watched_at=NOW(), ww_version=ww_version+1, ww_updated_at=NOW(){$completed_clause}
    WHERE ww_ix={$watch_id} AND mb_id='{$mb_sql}'";
if (!sql_query($update, false)) {
    sql_query('ROLLBACK', false);
    wzy_json_response(false, array('message' => '시청률을 저장하지 못했습니다.'), 500);
}

if ($percent >= $calendar_threshold && !$calendar_event_id && in_array($calendar_status, array('none', 'pending'), true)) {
    if (!empty($wzy_config['wyc_calendar_use'])) {
        $bundle['post']['_bo_table'] = $bo_table;
        $calendar_watch = $row;
        $calendar_watch['ww_last_watched_at'] = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        $calendar = wzy_create_calendar_event($mb_id, $bundle['post'], $watch_id, $percent, $status === 'completed', $calendar_watch);
        if (!empty($calendar['success'])) {
            $calendar_event_id = (int)$calendar['event_id'];
            $calendar_status = 'created';
        } else {
            $calendar_status = in_array(isset($calendar['code']) ? $calendar['code'] : '', array('MAX_EVENTS', 'CALENDAR_UNAVAILABLE', 'INSERT_FAILED', 'ORDER_FAILED'), true) ? 'pending' : 'unavailable';
        }
    } else {
        $calendar_status = 'unavailable';
    }
    if (!sql_query("UPDATE `{$g5['wzy_watch_table']}` SET ww_calendar_event_id=".($calendar_event_id ? $calendar_event_id : 'NULL').",
        ww_calendar_status='".sql_escape_string($calendar_status)."', ww_updated_at=NOW() WHERE ww_ix={$watch_id}", false)) {
        sql_query('ROLLBACK', false);
        wzy_json_response(false, array('message' => '캘린더 연결 상태를 저장하지 못했습니다.'), 500);
    }
}

if (!sql_query('COMMIT', false)) {
    sql_query('ROLLBACK', false);
    wzy_json_response(false, array('message' => '시청률 저장을 완료하지 못했습니다.'), 500);
}

$saved = sql_fetch("SELECT * FROM `{$g5['wzy_watch_table']}` WHERE ww_ix={$watch_id} AND mb_id='{$mb_sql}'", false);

if ($calendar_event_id && $calendar_status === 'created' && $saved) {
    // Synchronization runs after the watch transaction so a temporary calendar
    // conflict never rolls back valid progress.
    wzy_sync_calendar_event($mb_id, $calendar_event_id, $percent, $status === 'completed', $saved);
}

$public = wzy_watch_public_data($saved, true);
$public['completed_now'] = $completed_now;
$public['calendar_event_id'] = $calendar_event_id;
wzy_json_response(true, $public);
