<?php
include_once('./_common.php');

$mb_id = wzc_require_member(true);
if (!wzc_schema_installed()) wzc_json_response(false, array('message' => '캘린더가 설치되지 않았습니다.'), 503);
wzc_require_enabled_json();
$data = wzc_require_post_json();
wzc_rate_limit('event_order', 80, 60);

$date = isset($data['date']) ? (string)$data['date'] : '';
$ids = isset($data['event_ids']) && is_array($data['event_ids']) ? $data['event_ids'] : array();
if (!wzc_valid_date($date)) wzc_json_response(false, array('message' => '날짜가 올바르지 않습니다.'), 422);

sql_query('START TRANSACTION', false);
if (!wzc_save_date_order($mb_id, $date, $ids)) {
    sql_query('ROLLBACK', false);
    wzc_json_response(false, array('message' => '일정 순서를 저장할 수 없습니다.', 'code' => 'INVALID_EVENT'), 422);
}
sql_query('COMMIT', false);
wzc_json_response(true, array('message' => '일정 순서를 저장했습니다.'));
