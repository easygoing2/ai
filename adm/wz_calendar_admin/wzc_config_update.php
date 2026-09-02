<?php
$sub_menu = '790100';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'w');
check_demo();
check_admin_token();

if (!wzc_schema_installed()) alert('개인 캘린더를 먼저 설치해 주세요.', './wzc_install.php');

$use = isset($_POST['wcf_use']) && (int)$_POST['wcf_use'] === 1 ? 1 : 0;
$max_events = isset($_POST['wcf_max_events']) ? (int)$_POST['wcf_max_events'] : 5000;
$max_events = max(10, min(100000, $max_events));

sql_query("UPDATE `{$g5['wzc_config_table']}` SET wcf_use={$use}, wcf_max_events={$max_events}, wcf_updated_at=NOW() WHERE wcf_ix=1");

$calendar_backfilled = 0;
if (wzy_schema_installed()) {
    $watch_use = isset($_POST['wyc_use']) && (int)$_POST['wyc_use'] === 1 ? 1 : 0;
    $completion = isset($_POST['wyc_completion_percent']) ? (int)$_POST['wyc_completion_percent'] : 90;
    $completion = max(50, min(100, $completion));
    $count_short_seek = isset($_POST['wyc_count_short_seek']) && (int)$_POST['wyc_count_short_seek'] === 1 ? 1 : 0;
    $interval = isset($_POST['wyc_save_interval']) ? (int)$_POST['wyc_save_interval'] : 10;
    $interval = max(5, min(60, $interval));
    $show_badge = isset($_POST['wyc_show_list_badge']) && (int)$_POST['wyc_show_list_badge'] === 1 ? 1 : 0;
    $calendar_use = isset($_POST['wyc_calendar_use']) && (int)$_POST['wyc_calendar_use'] === 1 ? 1 : 0;
    $calendar_percent = isset($_POST['wyc_calendar_percent']) ? (int)$_POST['wyc_calendar_percent'] : 90;
    $calendar_percent = max(1, min(100, $calendar_percent));
    sql_query("UPDATE `{$g5['wzy_config_table']}` SET wyc_use={$watch_use}, wyc_completion_percent={$completion},
        wyc_count_short_seek={$count_short_seek}, wyc_save_interval={$interval}, wyc_show_list_badge={$show_badge},
        wyc_calendar_use={$calendar_use}, wyc_calendar_percent={$calendar_percent}, wyc_updated_at=NOW() WHERE wyc_ix=1");
    if ($watch_use && $calendar_use) $calendar_backfilled = wzy_backfill_calendar_events();
}
$message = '개인 캘린더 설정을 저장했습니다.';
if ($calendar_backfilled) $message .= "\n기준을 충족한 기존 시청 기록 {$calendar_backfilled}건을 캘린더에 등록했습니다.";
alert($message, './wzc_config.php');
