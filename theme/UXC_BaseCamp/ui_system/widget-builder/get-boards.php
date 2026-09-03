<?php
include_once('../../../../common.php');

// 관리자만 접근 가능
if ($is_admin != 'super') {
    die(json_encode(['success' => false, 'message' => '최고관리자만 접근 가능합니다.']));
}

// AJAX 요청 확인
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die(json_encode(['success' => false, 'message' => '잘못된 접근입니다.']));
}

// CSRF 토큰 검증 (유연한 처리)
$token = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : 
         (isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '');

// 토큰이 없거나 세션 토큰과 일치하지 않는 경우
if(!$token || !isset($_SESSION['ss_token']) || $token !== $_SESSION['ss_token']) {
    // 디버깅 정보 (개발 시에만 사용)
    error_log('CSRF Token Debug - Received: ' . $token . ', Session: ' . ($_SESSION['ss_token'] ?? 'not set'));
    die(json_encode(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.']));
}

// 게시판 목록 가져오기
$sql = "SELECT bo_table, bo_subject FROM {$g5['board_table']} ORDER BY bo_subject";
$result = sql_query($sql);

$boards = [];
while($row = sql_fetch_array($result)) {
    $boards[] = [
        'bo_table' => clean_xss_tags($row['bo_table']),
        'bo_subject' => clean_xss_tags($row['bo_subject'])
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'boards' => $boards
]);
?>