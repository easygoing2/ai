<?php
// 에러 출력 방지
error_reporting(0);
ini_set('display_errors', 0);

// 그누보드 common.php 포함
$common_path = '';
$check_paths = array(
    '../../../common.php',           // 기본 경로
    '../../../../common.php',        // 서브디렉토리 설치
    '../../../../../common.php',     // 더 깊은 서브디렉토리
    '../../../../../../common.php'   // 매우 깊은 서브디렉토리
);

foreach ($check_paths as $path) {
    if (file_exists($path)) {
        $common_path = $path;
        break;
    }
}

if (!$common_path) {
    die(json_encode(['success' => false, 'message' => 'Configuration file not found']));
}

include_once($common_path);

// JSON 응답 헤더
header('Content-Type: application/json; charset=utf-8');

try {
    // 로그인 체크
    if (!$is_member) {
        throw new Exception('로그인이 필요합니다.');
    }

    // CSRF 토큰 검증
    $upload_token = isset($_POST['upload_token']) ? $_POST['upload_token'] : '';
    $session_token = get_session('ss_editor_upload_token');
    if (!$upload_token || !$session_token || $upload_token !== $session_token) {
        throw new Exception('잘못된 접근입니다.');
    }

    // 파일 업로드 체크
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('파일 업로드에 실패했습니다.');
    }

    $file = $_FILES['image'];
    $filename = $file['name'];
    $filesize = $file['size'];
    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // 허용된 확장자
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');

    if (!in_array($file_ext, $allowed_ext)) {
        throw new Exception('허용되지 않은 파일 형식입니다. (JPG, PNG, GIF, WEBP만 가능)');
    }

    // 게시판 테이블 파라미터 받기
    $bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/i', '', $_POST['bo_table']) : '';

    // 게시판 설정 가져오기
    $max_filesize = 5 * 1024 * 1024; // 기본값 5MB
    if ($bo_table) {
        $board = sql_fetch("SELECT bo_upload_size FROM {$g5['board_table']} WHERE bo_table = '{$bo_table}'");
        if ($board && $board['bo_upload_size'] > 0) {
            $max_filesize = $board['bo_upload_size'];
        }
    }

    // 파일 크기 제한
    if ($filesize > $max_filesize) {
        $max_mb = number_format($max_filesize / 1024 / 1024, 1);
        throw new Exception("파일 크기가 {$max_mb}MB를 초과합니다.");
    }

    // MIME 타입 체크
    $allowed_mime = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_mime)) {
        throw new Exception('올바른 이미지 파일이 아닙니다.');
    }

    // 실제 이미지인지 확인
    $image_info = @getimagesize($file['tmp_name']);
    if ($image_info === false) {
        throw new Exception('이미지 파일이 아닙니다.');
    }

    // 업로드 디렉토리 설정
    $editor_path = G5_DATA_PATH.'/editor';
    if (!is_dir($editor_path)) {
        @mkdir($editor_path, G5_DIR_PERMISSION);
        @chmod($editor_path, G5_DIR_PERMISSION);
    }
    
    $upload_dir = $editor_path.'/'.date('ym');
    $upload_url = G5_DATA_URL.'/editor/'.date('ym');

    // 디렉토리 생성
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, G5_DIR_PERMISSION);
        @chmod($upload_dir, G5_DIR_PERMISSION);
    }

    // 파일명 생성 (중복 방지)
    $chars_array = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
    shuffle($chars_array);
    $shuffle = implode('', $chars_array);
    $upload_name = abs(ip2long($_SERVER['REMOTE_ADDR'])).'_'.substr($shuffle, 0, 8).'_'.time().'.'.$file_ext;

    $upload_file = $upload_dir.'/'.$upload_name;
    $upload_file_url = $upload_url.'/'.$upload_name;

    // 파일 이동
    if (!move_uploaded_file($file['tmp_name'], $upload_file)) {
        throw new Exception('파일 저장에 실패했습니다.');
    }

    // 파일 권한 설정
    @chmod($upload_file, G5_FILE_PERMISSION);

    // 성공 응답
    echo json_encode([
        'success' => true,
        'url' => $upload_file_url,
        'alt' => basename($filename, '.'.$file_ext)
    ]);

} catch (Exception $e) {
    // 에러 응답
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;