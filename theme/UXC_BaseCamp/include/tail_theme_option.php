<?php
/**
 * tail.php에서 사용하는 UI 모듈 로드
 * head_theme_option.php에서 파싱된 변수 사용
 */

if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// UI 모듈 JavaScript 로드
// head_theme_option.php에서 파싱된 변수 사용
// $other_config는 이미 로드됨

// 사용된 모듈의 JavaScript 파일 로드
$loaded_modules = array();

// ui_mainSlider (메인 슬라이더 - index 페이지에서만)
if (defined('_INDEX_')) {
    $loaded_modules[] = 'ui_mainSlider';
}

// 중복 제거 및 JavaScript 파일 로드
$loaded_modules = array_unique($loaded_modules);
foreach ($loaded_modules as $module) {
    $js_file = G5_THEME_PATH . '/ui_module/' . $module . '/script.js';
    if (file_exists($js_file)) {
        echo '<script src="' . G5_THEME_URL . '/ui_module/' . $module . '/script.js"></script>' . "\n";
    }
}
?>
