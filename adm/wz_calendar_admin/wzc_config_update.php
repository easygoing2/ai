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
alert('개인 캘린더 설정을 저장했습니다.', './wzc_config.php');
