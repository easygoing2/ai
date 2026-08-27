<?php
// common.php 로드
$common_paths = [
    './../../../../../common.php',
    './../../../../common.php',
    __DIR__ . '/../../../../../common.php'
];

$common_loaded = false;
foreach ($common_paths as $path) {
    if (file_exists($path)) {
        include_once($path);
        $common_loaded = true;
        break;
    }
}

if (!$common_loaded || !defined('_GNUBOARD_')) {
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode(['success' => false, 'message' => 'System initialization failed']));
}

// 관리자 권한 확인
if (!$is_admin) {
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode(['success' => false, 'message' => 'Permission denied']));
}

// AJAX 요청 확인 (경고만, 차단 안함)
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

/**
 * 분산형 위젯 로딩 시스템
 * 각 위젯 폴더에서 widget.json 파일을 자동으로 스캔하여 로드
 */
function loadDistributedWidgets() {
    $widgets = [];
    $widget_base_path = G5_THEME_PATH . '/ui_widget';

    // 캐시 파일 경로
    $cache_file = G5_DATA_PATH . '/cache/widgets_cache.json';
    $cache_duration = 3600; // 1시간

    // 개발 모드 체크 (config.php에서 설정 가능)
    $dev_mode = defined('G5_WIDGET_DEV_MODE') && G5_WIDGET_DEV_MODE;

    // 캐시 사용 (개발 모드가 아닐 때만)
    if (!$dev_mode && file_exists($cache_file)) {
        $cache_mtime = filemtime($cache_file);
        $current_time = time();

        // 캐시가 유효한 경우
        if (($current_time - $cache_mtime) < $cache_duration) {
            $cached_data = @file_get_contents($cache_file);
            if ($cached_data) {
                $decoded = json_decode($cached_data, true);
                if ($decoded !== null) {
                    return $decoded;
                }
            }
        }
    }

    // 위젯 폴더 스캔
    if (!is_dir($widget_base_path)) {
        return ['success' => false, 'message' => 'Widget directory not found'];
    }

    $widget_dirs = glob($widget_base_path . '/*', GLOB_ONLYDIR);

    foreach ($widget_dirs as $widget_dir) {
        $widget_json_path = $widget_dir . '/widget.json';

        // widget.json 파일이 존재하는지 확인
        if (file_exists($widget_json_path)) {
            $json_content = @file_get_contents($widget_json_path);

            if ($json_content) {
                $widget_data = json_decode($json_content, true);

                // JSON 디코딩 성공 여부 확인
                if ($widget_data !== null && json_last_error() === JSON_ERROR_NONE) {
                    // 필수 필드 검증
                    if (isset($widget_data['filename']) &&
                        isset($widget_data['name']) &&
                        isset($widget_data['icon'])) {
                        $widgets[] = $widget_data;
                    }
                }
            }
        }
    }

    // 캐시 저장 (개발 모드가 아닐 때만)
    if (!$dev_mode && !empty($widgets)) {
        $cache_dir = dirname($cache_file);
        if (!is_dir($cache_dir)) {
            @mkdir($cache_dir, 0755, true);
        }
        @file_put_contents($cache_file, json_encode($widgets, JSON_UNESCAPED_UNICODE));
    }

    return $widgets;
}

// 에러 핸들링
try {
    // 위젯 목록 로드
    $widgets = loadDistributedWidgets();

    // 배열이 아닌 경우 처리
    if (!is_array($widgets)) {
        throw new Exception('위젯 목록을 불러올 수 없습니다.');
    }

    // 응답 반환
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'widgets' => $widgets,
        'count' => count($widgets)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // 에러 로그
    error_log('Widget API Error: ' . $e->getMessage());

    // 에러 응답
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'widgets' => [],
        'count' => 0
    ], JSON_UNESCAPED_UNICODE);
}
