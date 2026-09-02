<?php
include_once('./_common.php');

$mb_id = wzc_require_member(true);
if (!wzc_schema_installed()) wzc_json_response(false, array('message' => '캘린더가 아직 설치되지 않았습니다.', 'code' => 'NOT_INSTALLED'), 503);
wzc_require_enabled_json();
$data = wzc_require_post_json();
wzc_rate_limit('event_save', 40, 60);

$event_id = isset($data['event_id']) ? (int)$data['event_id'] : 0;
$version = isset($data['version']) ? (int)$data['version'] : 0;
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

if ($title === '') wzc_json_response(false, array('message' => '일정 제목을 입력해 주세요.', 'field' => 'title'), 422);
if (mb_strlen($content, 'UTF-8') > 60000) wzc_json_response(false, array('message' => '일정 메모는 60,000자 이내로 입력해 주세요.', 'field' => 'content'), 422);
if (!wzc_valid_date($start_date) || !wzc_valid_date($end_date)) wzc_json_response(false, array('message' => '일정 날짜가 올바르지 않습니다.', 'field' => 'start_date'), 422);
if ((int)substr($start_date, 0, 4) < 1970 || (int)substr($end_date, 0, 4) > 2100) wzc_json_response(false, array('message' => '일정은 1970년부터 2100년 사이에 등록할 수 있습니다.', 'field' => 'start_date'), 422);
if ($start_date > $end_date) wzc_json_response(false, array('message' => '종료일은 시작일보다 빠를 수 없습니다.', 'field' => 'end_date'), 422);
if (wzc_event_day_count($start_date, $end_date) > 366) wzc_json_response(false, array('message' => '일정 기간은 최대 366일까지 등록할 수 있습니다.'), 422);
if (!$all_day && (!wzc_valid_time($start_time) || !wzc_valid_time($end_time))) wzc_json_response(false, array('message' => '시간 형식이 올바르지 않습니다.'), 422);
if (!$all_day && $start_date === $end_date && $start_time && $end_time && $start_time > $end_time) wzc_json_response(false, array('message' => '종료 시간은 시작 시간보다 빠를 수 없습니다.', 'field' => 'end_time'), 422);
if ($link_url === false) wzc_json_response(false, array('message' => '외부 링크는 http 또는 https 주소만 입력할 수 있습니다.', 'field' => 'link_url'), 422);
if (!wzc_category_owned($mb_id, $category_id)) wzc_json_response(false, array('message' => '사용할 수 없는 분류입니다.', 'field' => 'category_id'), 422);

$config_row = wzc_get_config();
if (!$event_id) {
    $mb_sql = sql_escape_string($mb_id);
    $count_row = sql_fetch("SELECT COUNT(*) AS cnt FROM `{$g5['wzc_event_table']}` WHERE mb_id='{$mb_sql}' AND we_deleted_at IS NULL", false);
    if ((int)$count_row['cnt'] >= (int)$config_row['wcf_max_events']) wzc_json_response(false, array('message' => '등록할 수 있는 개인 일정 개수를 초과했습니다.'), 409);
}

$old_event = $event_id ? wzc_event_for_member($mb_id, $event_id) : null;
if ($event_id && !$old_event) wzc_json_response(false, array('message' => '일정을 찾을 수 없습니다.', 'code' => 'NOT_FOUND'), 404);
if ($event_id && $version !== (int)$old_event['we_version']) wzc_json_response(false, array('message' => '다른 기기에서 일정이 변경되었습니다. 새로고침 후 다시 시도해 주세요.', 'code' => 'VERSION_CONFLICT'), 409);
$creating = !$event_id;

$mb_sql = sql_escape_string($mb_id);
$title_sql = sql_escape_string($title);
$content_sql = sql_escape_string($content);
$location_sql = sql_escape_string($location);
$link_sql = sql_escape_string((string)$link_url);
$start_sql = sql_escape_string($start_date);
$end_sql = sql_escape_string($end_date);
$category_sql = $category_id ? (string)$category_id : 'NULL';
$start_time_sql = (!$all_day && $start_time) ? "'".sql_escape_string($start_time).":00'" : 'NULL';
$end_time_sql = (!$all_day && $end_time) ? "'".sql_escape_string($end_time).":00'" : 'NULL';

sql_query('START TRANSACTION', false);
if ($event_id) {
    $sql = "UPDATE `{$g5['wzc_event_table']}` SET
        wc_ix={$category_sql}, we_title='{$title_sql}', we_content='{$content_sql}',
        we_start_date='{$start_sql}', we_end_date='{$end_sql}', we_all_day={$all_day},
        we_start_time={$start_time_sql}, we_end_time={$end_time_sql},
        we_location='{$location_sql}', we_link_url='{$link_sql}',
        we_version=we_version+1, we_updated_at=NOW()
        WHERE we_ix={$event_id} AND mb_id='{$mb_sql}' AND we_version={$version} AND we_deleted_at IS NULL";
    if (!sql_query($sql, false) || mysqli_affected_rows($GLOBALS['connect_db']) !== 1) {
        sql_query('ROLLBACK', false);
        wzc_json_response(false, array('message' => '일정 저장 중 충돌이 발생했습니다.', 'code' => 'VERSION_CONFLICT'), 409);
    }
} else {
    $created = wzc_create_member_event($mb_id, array(
        'title' => $title,
        'content' => $content,
        'location' => $location,
        'category_id' => $category_id,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'all_day' => $all_day,
        'start_time' => $start_time,
        'end_time' => $end_time,
        'link_url' => $link_url
    ));
    if (empty($created['success'])) {
        sql_query('ROLLBACK', false);
        if (isset($created['code']) && $created['code'] === 'MAX_EVENTS') {
            wzc_json_response(false, array('message' => '등록할 수 있는 개인 일정 개수를 초과했습니다.', 'code' => 'MAX_EVENTS'), 409);
        }
        wzc_json_response(false, array('message' => '일정을 저장하지 못했습니다.'), 500);
    }
    $event_id = (int)$created['event_id'];
}

if (!$creating) {
    $dates_to_sort = array();
    if ($old_event) for ($date = $old_event['we_start_date']; $date <= $old_event['we_end_date']; $date = wzc_date_add($date, 1)) $dates_to_sort[$date] = true;
    for ($date = $start_date; $date <= $end_date; $date = wzc_date_add($date, 1)) $dates_to_sort[$date] = true;
    $date_orders = array();
    foreach (array_keys($dates_to_sort) as $date) $date_orders[$date] = wzc_valid_event_ids_for_date($mb_id, $date);
    if (!sql_query("DELETE FROM `{$g5['wzc_event_order_table']}` WHERE mb_id='{$mb_sql}' AND we_ix={$event_id}", false)) {
        sql_query('ROLLBACK', false);
        wzc_json_response(false, array('message' => '일정 순서를 저장하지 못했습니다.'), 500);
    }
    foreach (array_keys($dates_to_sort) as $date) {
        if (!wzc_save_date_order($mb_id, $date, $date_orders[$date])) {
            sql_query('ROLLBACK', false);
            wzc_json_response(false, array('message' => '일정 순서를 저장하지 못했습니다.'), 500);
        }
    }
}
if (!sql_query('COMMIT', false)) {
    sql_query('ROLLBACK', false);
    wzc_json_response(false, array('message' => '일정 저장을 완료하지 못했습니다.'), 500);
}

$saved = wzc_event_for_member($mb_id, $event_id);
wzc_json_response(true, array('message' => '일정을 저장했습니다.', 'event' => wzc_event_public_data($saved)));
