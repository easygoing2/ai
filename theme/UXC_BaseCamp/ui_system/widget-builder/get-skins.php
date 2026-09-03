<?php
include_once('../../../../common.php');

// JSON 헤더 설정
header('Content-Type: application/json');

// 관리자만 접근 가능
if ($is_admin != 'super') {
    echo json_encode(['success' => false, 'message' => '최고관리자만 접근 가능합니다.']);
    exit;
}

// AJAX 요청 확인
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    echo json_encode(['success' => false, 'message' => '잘못된 접근입니다.']);
    exit;
}

// CSRF 토큰 검증 (유연한 처리)
$token = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : 
         (isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '');

// 토큰이 없거나 세션 토큰과 일치하지 않는 경우
if(!$token || !isset($_SESSION['ss_token']) || $token !== $_SESSION['ss_token']) {
    // 디버깅 정보 (개발 시에만 사용)
    error_log('CSRF Token Debug - Received: ' . $token . ', Session: ' . ($_SESSION['ss_token'] ?? 'not set'));
    echo json_encode(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.']);
    exit;
}

// 스킨 디렉토리 경로
$skin_dir = G5_THEME_PATH . '/skin/latest';
$skin_url = G5_THEME_URL . '/skin/latest';

$skins = [];

// 디렉토리가 존재하는지 확인
if (is_dir($skin_dir)) {
    $directories = scandir($skin_dir);
    
    foreach ($directories as $dir) {
        // . 과 .. 디렉토리는 제외
        if ($dir == '.' || $dir == '..') continue;
        
        // 디렉토리명 검증 (path traversal 방지)
        $dir = basename($dir);
        if (preg_match('/[^a-zA-Z0-9_\-]/', $dir)) {
            continue;
        }
        
        $full_path = $skin_dir . '/' . $dir;
        
        // realpath를 통한 추가 보안 검증
        $real_path = realpath($full_path);
        $real_skin_dir = realpath($skin_dir);
        if (!$real_path || strpos($real_path, $real_skin_dir) !== 0) {
            continue;
        }
        
        // 디렉토리인지 확인
        if (is_dir($full_path)) {
            $skin_info = [
                'name' => clean_xss_tags($dir),
                'path' => 'theme/' . clean_xss_tags($dir),
                'display_name' => clean_xss_tags($dir),
                'screenshot' => null
            ];
            
            // 스크린샷 파일 확인
            $screenshot_path = $full_path . '/screenshot.png';
            if (file_exists($screenshot_path)) {
                $skin_info['screenshot'] = htmlspecialchars($skin_url . '/' . $dir . '/screenshot.png');
            }
            
            $skins[] = $skin_info;
        }
    }
}

// 결과 반환
echo json_encode([
    'success' => true,
    'skins' => $skins
], JSON_UNESCAPED_UNICODE);
exit;