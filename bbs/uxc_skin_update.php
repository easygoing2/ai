<?php
include_once('./_common.php');

// 관리자만 접근 가능
if ($is_admin != 'super') {
    die(json_encode(['success' => false, 'message' => '최고관리자만 접근 가능합니다.']));
}

header('Content-Type: application/json');

$page_type = isset($_POST['page_type']) ? preg_replace('/[^a-z_]/', '', $_POST['page_type']) : '';
$skin_name = isset($_POST['skin_name']) ? preg_replace('/[^a-z0-9_\-]/', '', $_POST['skin_name']) : '';

if(!$page_type || !$skin_name) {
    die(json_encode(['success' => false, 'message' => '필수 값이 누락되었습니다.']));
}

// 허용된 페이지 타입 확인
$allowed_types = array('mypage', 'dashboard');
if(!in_array($page_type, $allowed_types)) {
    die(json_encode(['success' => false, 'message' => '허용되지 않은 페이지 타입입니다.']));
}

// 스킨 디렉토리 존재 여부 확인
$skin_path = G5_THEME_PATH.'/skin/'.$page_type.'/'.$skin_name;
if(!is_dir($skin_path)) {
    die(json_encode(['success' => false, 'message' => '존재하지 않는 스킨입니다.']));
}

// 테이블 존재 여부 확인
$table_check = sql_fetch("SHOW TABLES LIKE 'g5_theme_skin_config'");
if(!$table_check) {
    die(json_encode(['success' => false, 'message' => '스킨 설정 테이블이 없습니다. 슬라이더 관리에서 테이블을 먼저 생성해주세요.']));
}

// 스킨 설정 업데이트
$sql = "INSERT INTO g5_theme_skin_config (page_type, skin_name) 
        VALUES ('$page_type', '$skin_name') 
        ON DUPLICATE KEY UPDATE skin_name = '$skin_name'";

if(sql_query($sql)) {
    die(json_encode(['success' => true, 'message' => '스킨이 변경되었습니다.']));
} else {
    die(json_encode(['success' => false, 'message' => '스킨 설정 저장에 실패했습니다.']));
}
?>