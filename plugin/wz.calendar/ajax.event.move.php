<?php
include_once('./_common.php');

$mb_id = wzc_require_member(true);
if (!wzc_schema_installed()) wzc_json_response(false, array('message' => '캘린더가 설치되지 않았습니다.'), 503);
wzc_require_enabled_json();
$data = wzc_require_post_json();
wzc_rate_limit('event_move', 80, 60);

$event_id = isset($data['event_id']) ? (int)$data['event_id'] : 0;
$version = isset($data['version']) ? (int)$data['version'] : 0;
$source_date = isset($data['source_date']) ? (string)$data['source_date'] : '';
$target_date = isset($data['target_date']) ? (string)$data['target_date'] : '';
$source_ids = isset($data['source_ids']) && is_array($data['source_ids']) ? $data['source_ids'] : array();
$target_ids = isset($data['target_ids']) && is_array($data['target_ids']) ? $data['target_ids'] : array();

if (!wzc_valid_date($source_date) || !wzc_valid_date($target_date)) wzc_json_response(false, array('message' => '이동 날짜가 올바르지 않습니다.'), 422);
$event = wzc_event_for_member($mb_id, $event_id);
if (!$event) wzc_json_response(false, array('message' => '일정을 찾을 수 없습니다.'), 404);
if ($version !== (int)$event['we_version']) wzc_json_response(false, array('message' => '다른 기기에서 일정이 변경되었습니다.', 'code' => 'VERSION_CONFLICT'), 409);
if ($source_date < $event['we_start_date'] || $source_date > $event['we_end_date']) wzc_json_response(false, array('message' => '일정의 현재 날짜가 일치하지 않습니다.'), 422);

$delta = wzc_date_diff_days($source_date, $target_date);
$new_start = wzc_date_add($event['we_start_date'], $delta);
$new_end = wzc_date_add($event['we_end_date'], $delta);
if ((int)substr($new_start, 0, 4) < 1970 || (int)substr($new_end, 0, 4) > 2100) wzc_json_response(false, array('message' => '이동할 수 있는 날짜 범위를 벗어났습니다.'), 422);

$mb_sql = sql_escape_string($mb_id);
$new_start_sql = sql_escape_string($new_start);
$new_end_sql = sql_escape_string($new_end);
sql_query('START TRANSACTION', false);
$ok = sql_query("UPDATE `{$g5['wzc_event_table']}` SET we_start_date='{$new_start_sql}', we_end_date='{$new_end_sql}', we_version=we_version+1, we_updated_at=NOW() WHERE we_ix={$event_id} AND mb_id='{$mb_sql}' AND we_version={$version} AND we_deleted_at IS NULL", false);
if (!$ok || mysqli_affected_rows($GLOBALS['connect_db']) !== 1) {
    sql_query('ROLLBACK', false);
    wzc_json_response(false, array('message' => '다른 기기에서 일정이 변경되었습니다.', 'code' => 'VERSION_CONFLICT'), 409);
}

$dates = array();
for ($date = $event['we_start_date']; $date <= $event['we_end_date']; $date = wzc_date_add($date, 1)) $dates[$date] = true;
for ($date = $new_start; $date <= $new_end; $date = wzc_date_add($date, 1)) $dates[$date] = true;
$date_orders = array();
foreach (array_keys($dates) as $date) $date_orders[$date] = wzc_valid_event_ids_for_date($mb_id, $date);
if (!sql_query("DELETE FROM `{$g5['wzc_event_order_table']}` WHERE mb_id='{$mb_sql}' AND we_ix={$event_id}", false)) {
    sql_query('ROLLBACK', false);
    wzc_json_response(false, array('message' => '이동한 일정 순서를 저장하지 못했습니다.'), 500);
}
foreach (array_keys($dates) as $date) {
    $ordered = $date_orders[$date];
    if ($date === $source_date && $source_ids) $ordered = $source_ids;
    if ($date === $target_date && $target_ids) $ordered = $target_ids;
    if (!wzc_save_date_order($mb_id, $date, $ordered)) {
        sql_query('ROLLBACK', false);
        wzc_json_response(false, array('message' => '이동한 일정 순서를 저장하지 못했습니다.'), 422);
    }
}
sql_query('COMMIT', false);

$saved = wzc_event_for_member($mb_id, $event_id);
wzc_json_response(true, array(
    'message' => '일정을 이동했습니다.',
    'event' => wzc_event_public_data($saved),
    'undo' => array('source_date' => $target_date, 'target_date' => $source_date, 'version' => (int)$saved['we_version'])
));
