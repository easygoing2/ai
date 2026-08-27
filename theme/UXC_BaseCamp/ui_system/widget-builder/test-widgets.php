<?php
// 디버그 전용 파일 - 관리자만 접근 가능
include_once('./../../../../../common.php');

if (!$is_admin) {
    die('관리자만 접근 가능합니다.');
}

echo '<h2>위젯 API 디버그</h2>';
echo '<hr>';

// 1. 경로 확인
echo '<h3>1. 경로 확인</h3>';
echo 'G5_THEME_PATH: ' . G5_THEME_PATH . '<br>';
echo 'Widget 폴더: ' . G5_THEME_PATH . '/ui_widget<br>';
echo '폴더 존재: ' . (is_dir(G5_THEME_PATH . '/ui_widget') ? 'YES' : 'NO') . '<br>';
echo '<hr>';

// 2. 위젯 폴더 스캔
echo '<h3>2. 위젯 폴더 스캔</h3>';
$widget_base_path = G5_THEME_PATH . '/ui_widget';
$widget_dirs = glob($widget_base_path . '/*', GLOB_ONLYDIR);
echo '발견된 폴더 수: ' . count($widget_dirs) . '<br><br>';

foreach ($widget_dirs as $dir) {
    $folder_name = basename($dir);
    $json_file = $dir . '/widget.json';

    echo '<strong>' . $folder_name . '</strong><br>';
    echo '- JSON 파일 존재: ' . (file_exists($json_file) ? 'YES' : 'NO') . '<br>';

    if (file_exists($json_file)) {
        $content = file_get_contents($json_file);
        $json = json_decode($content, true);

        echo '- JSON 유효: ' . (json_last_error() === JSON_ERROR_NONE ? 'YES' : 'NO') . '<br>';

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo '- JSON 에러: ' . json_last_error_msg() . '<br>';
        } else {
            echo '- 위젯 이름: ' . ($json['name'] ?? 'N/A') . '<br>';
            echo '- 파일명: ' . ($json['filename'] ?? 'N/A') . '<br>';
        }
    }

    echo '<br>';
}
echo '<hr>';

// 3. API 함수 테스트
echo '<h3>3. loadDistributedWidgets() 함수 테스트</h3>';

function loadDistributedWidgets() {
    $widgets = [];
    $widget_base_path = G5_THEME_PATH . '/ui_widget';

    if (!is_dir($widget_base_path)) {
        return ['error' => 'Widget directory not found'];
    }

    $widget_dirs = glob($widget_base_path . '/*', GLOB_ONLYDIR);

    foreach ($widget_dirs as $widget_dir) {
        $widget_json_path = $widget_dir . '/widget.json';

        if (file_exists($widget_json_path)) {
            $json_content = @file_get_contents($widget_json_path);

            if ($json_content) {
                $widget_data = json_decode($json_content, true);

                if ($widget_data !== null && json_last_error() === JSON_ERROR_NONE) {
                    if (isset($widget_data['filename']) &&
                        isset($widget_data['name']) &&
                        isset($widget_data['icon'])) {
                        $widgets[] = $widget_data;
                    }
                }
            }
        }
    }

    return $widgets;
}

$result = loadDistributedWidgets();

if (isset($result['error'])) {
    echo '<strong style="color: red;">에러: ' . $result['error'] . '</strong><br>';
} else {
    echo '로드된 위젯 수: ' . count($result) . '<br><br>';
    echo '<pre>';
    print_r($result);
    echo '</pre>';
}

echo '<hr>';

// 4. JSON 응답 테스트
echo '<h3>4. JSON 응답 미리보기</h3>';
echo '<pre>';
echo json_encode([
    'success' => true,
    'widgets' => $result,
    'count' => count($result)
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo '</pre>';
