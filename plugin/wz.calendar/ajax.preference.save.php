<?php
include_once('./_common.php');

$mb_id = wzc_require_member(true);
if (!wzc_schema_installed()) wzc_json_response(false, array('message' => '캘린더가 설치되지 않았습니다.'), 503);
wzc_require_enabled_json();
$data = wzc_require_post_json();
wzc_rate_limit('preference_save', 20, 60);

$events_per_day = isset($data['events_per_day']) ? (int)$data['events_per_day'] : 5;
$default_category = isset($data['default_category']) ? (int)$data['default_category'] : 0;
$touch_drag = !empty($data['touch_drag_use']) ? 1 : 0;
if ($events_per_day < 1 || $events_per_day > 10) wzc_json_response(false, array('message' => '날짜 셀 표시 개수는 1~10 사이여야 합니다.'), 422);
if (!wzc_category_owned($mb_id, $default_category)) wzc_json_response(false, array('message' => '기본 분류를 사용할 수 없습니다.'), 422);
$mb_sql = sql_escape_string($mb_id);
$category_sql = $default_category ? (string)$default_category : 'NULL';
$sql = "INSERT INTO `{$g5['wzc_preference_table']}` SET mb_id='{$mb_sql}', wp_events_per_day={$events_per_day}, wp_default_category={$category_sql}, wp_touch_drag_use={$touch_drag}, wp_updated_at=NOW()
    ON DUPLICATE KEY UPDATE wp_events_per_day=VALUES(wp_events_per_day), wp_default_category=VALUES(wp_default_category), wp_touch_drag_use=VALUES(wp_touch_drag_use), wp_updated_at=NOW()";
if (!sql_query($sql, false)) wzc_json_response(false, array('message' => '개인 설정을 저장하지 못했습니다.'), 500);
wzc_json_response(true, array('message' => '개인 설정을 저장했습니다.'));
