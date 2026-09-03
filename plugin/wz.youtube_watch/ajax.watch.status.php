<?php
include_once('./_common.php');

$mb_id = wzy_require_member();
if (!wzy_schema_installed()) wzy_json_response(false, array('message' => '시청률 기능이 아직 설치되지 않았습니다.', 'code' => 'NOT_INSTALLED'), 503);
$wzy_config = wzy_get_config();
if (empty($wzy_config['wyc_use'])) wzy_json_response(false, array('message' => '현재 시청률 기능을 사용할 수 없습니다.', 'code' => 'WATCH_DISABLED'), 503);
$data = wzy_require_request();
wzy_rate_limit('status', 60, 60);

$items = isset($data['items']) && is_array($data['items']) ? array_slice($data['items'], 0, 100) : array();
$result = array();
$mb_sql = sql_escape_string($mb_id);

foreach ($items as $item) {
    $bo_table = isset($item['bo_table']) ? (string)$item['bo_table'] : '';
    $wr_id = isset($item['wr_id']) ? (int)$item['wr_id'] : 0;
    $video_id = isset($item['video_id']) ? (string)$item['video_id'] : '';
    if (!$wr_id || !preg_match('/^[A-Za-z0-9_-]{11}$/', $video_id)) continue;
    $bundle = wzy_get_post($bo_table, $wr_id);
    if (!$bundle || !wzy_can_read_post($bundle) || !in_array($video_id, wzy_post_video_ids($bundle['post']), true)) continue;
    $bo_sql = sql_escape_string($bo_table);
    $video_sql = sql_escape_string($video_id);
    $row = sql_fetch("SELECT * FROM `{$g5['wzy_watch_table']}` WHERE mb_id='{$mb_sql}' AND bo_table='{$bo_sql}' AND wr_id={$wr_id} AND wy_video_id='{$video_sql}'", false);
    $key = $bo_table.':'.$wr_id.':'.$video_id;
    $result[$key] = $row ? wzy_watch_public_data($row, true) : array(
        'percent' => 0,
        'watched_seconds' => 0,
        'duration' => 0,
        'last_position' => 0,
        'status' => 'watching',
        'calendar_status' => 'none',
        'ranges' => array()
    );
}

wzy_json_response(true, array('items' => $result));
