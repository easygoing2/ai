<?php
include_once('./_common.php');

$mb_id = wzc_require_member(true);
if (!wzc_schema_installed()) wzc_json_response(false, array('message' => '캘린더가 설치되지 않았습니다.'), 503);
wzc_require_enabled_json();
$data = wzc_require_post_json();
wzc_rate_limit('event_delete', 30, 60);

$event_id = isset($data['event_id']) ? (int)$data['event_id'] : 0;
$version = isset($data['version']) ? (int)$data['version'] : 0;
$event = wzc_event_for_member($mb_id, $event_id);
if (!$event) wzc_json_response(false, array('message' => '일정을 찾을 수 없습니다.'), 404);
if ($version !== (int)$event['we_version']) wzc_json_response(false, array('message' => '다른 기기에서 일정이 변경되었습니다.', 'code' => 'VERSION_CONFLICT'), 409);

$mb_sql = sql_escape_string($mb_id);
sql_query('START TRANSACTION', false);
$ok = sql_query("UPDATE `{$g5['wzc_event_table']}` SET we_deleted_at=NOW(), we_version=we_version+1, we_updated_at=NOW() WHERE we_ix={$event_id} AND mb_id='{$mb_sql}' AND we_version={$version} AND we_deleted_at IS NULL", false);
if (!$ok || mysqli_affected_rows($GLOBALS['connect_db']) !== 1) {
    sql_query('ROLLBACK', false);
    wzc_json_response(false, array('message' => '일정을 삭제하지 못했습니다.', 'code' => 'VERSION_CONFLICT'), 409);
}
sql_query("DELETE FROM `{$g5['wzc_event_order_table']}` WHERE mb_id='{$mb_sql}' AND we_ix={$event_id}", false);
for ($date = $event['we_start_date']; $date <= $event['we_end_date']; $date = wzc_date_add($date, 1)) wzc_save_date_order($mb_id, $date, array());
run_event('wzc_event_deleted', $mb_id, $event_id);
if (!sql_query('COMMIT', false)) {
    sql_query('ROLLBACK', false);
    wzc_json_response(false, array('message' => '일정 삭제를 완료하지 못했습니다.'), 500);
}
wzc_json_response(true, array('message' => '일정을 휴지통으로 이동했습니다.'));
