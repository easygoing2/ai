<?php
include_once('./_common.php');

$mb_id = wzc_require_member(true);
if (!wzc_schema_installed()) wzc_json_response(false, array('message' => '캘린더가 설치되지 않았습니다.'), 503);
wzc_require_enabled_json();
$data = wzc_require_post_json();
wzc_rate_limit('category_save', 30, 60);

$action = isset($data['action']) ? (string)$data['action'] : 'save';
$category_id = isset($data['category_id']) ? (int)$data['category_id'] : 0;
$mb_sql = sql_escape_string($mb_id);

if ($action === 'delete') {
    $row = sql_fetch("SELECT wc_ix FROM `{$g5['wzc_category_table']}` WHERE wc_ix={$category_id} AND mb_id='{$mb_sql}'", false);
    if (!$row) wzc_json_response(false, array('message' => '분류를 찾을 수 없습니다.'), 404);
    sql_query('START TRANSACTION', false);
    $events_updated = sql_query("UPDATE `{$g5['wzc_event_table']}` SET wc_ix=NULL, we_version=we_version+1, we_updated_at=NOW() WHERE mb_id='{$mb_sql}' AND wc_ix={$category_id}", false);
    $preference_updated = sql_query("UPDATE `{$g5['wzc_preference_table']}` SET wp_default_category=NULL, wp_updated_at=NOW() WHERE mb_id='{$mb_sql}' AND wp_default_category={$category_id}", false);
    $category_deleted = sql_query("DELETE FROM `{$g5['wzc_category_table']}` WHERE wc_ix={$category_id} AND mb_id='{$mb_sql}'", false);
    if (!$events_updated || !$preference_updated || !$category_deleted) {
        sql_query('ROLLBACK', false);
        wzc_json_response(false, array('message' => '분류를 삭제하지 못했습니다.'), 500);
    }
    sql_query('COMMIT', false);
    wzc_json_response(true, array('message' => '분류를 삭제했습니다. 기존 일정은 분류 없음으로 변경했습니다.'));
}

$name = wzc_plain_text(isset($data['name']) ? $data['name'] : '', 50);
$color = isset($data['color']) ? trim((string)$data['color']) : '#6f48ff';
if ($name === '') wzc_json_response(false, array('message' => '분류명을 입력해 주세요.'), 422);
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) wzc_json_response(false, array('message' => '분류 색상이 올바르지 않습니다.'), 422);
$name_sql = sql_escape_string($name);
$color_sql = sql_escape_string(strtolower($color));

if ($category_id) {
    $owned = sql_fetch("SELECT wc_ix FROM `{$g5['wzc_category_table']}` WHERE wc_ix={$category_id} AND mb_id='{$mb_sql}'", false);
    if (!$owned) wzc_json_response(false, array('message' => '분류를 찾을 수 없습니다.'), 404);
    $ok = sql_query("UPDATE `{$g5['wzc_category_table']}` SET wc_name='{$name_sql}', wc_color='{$color_sql}', wc_updated_at=NOW() WHERE wc_ix={$category_id} AND mb_id='{$mb_sql}'", false);
} else {
    $count = sql_fetch("SELECT COUNT(*) AS cnt FROM `{$g5['wzc_category_table']}` WHERE mb_id='{$mb_sql}'", false);
    if ((int)$count['cnt'] >= 20) wzc_json_response(false, array('message' => '개인 분류는 최대 20개까지 만들 수 있습니다.'), 409);
    $ok = sql_query("INSERT INTO `{$g5['wzc_category_table']}` SET mb_id='{$mb_sql}', wc_name='{$name_sql}', wc_color='{$color_sql}', wc_use=1, wc_sort=".(int)$count['cnt'].", wc_created_at=NOW(), wc_updated_at=NOW()", false);
}
if (!$ok) wzc_json_response(false, array('message' => '같은 이름의 분류가 있거나 저장하지 못했습니다.'), 409);
wzc_json_response(true, array('message' => '분류를 저장했습니다.'));
