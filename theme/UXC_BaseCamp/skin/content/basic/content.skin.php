<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

add_stylesheet('<link rel="stylesheet" href="'.$content_skin_url.'/style.css">', 0);

// 파일 기반 내용관리
$content_file = G5_THEME_PATH . '/page/' . $co_id . '.php';

if (is_file($content_file)) {
    // 파일이 존재하면 파일을 include
    include_once($content_file);
} else {
    // 파일이 없으면 기본 DB 출력
    echo '<div class="content">'.nl2br($co['co_content']).'</div>';
}
?>
