<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


// 테마가 지원하는 장치 설정
if(! defined('G5_THEME_DEVICE')) define('G5_THEME_DEVICE', 'pc');

// 테마에서 커뮤니티 지원여부 설정
if(! defined('G5_COMMUNITY_USE')) define('G5_COMMUNITY_USE', true);

// API 설정 파싱 (cf_9)
$api_config = array();
if (!empty($config['cf_9']) && (strpos($config['cf_9'], '{') === 0 || strpos($config['cf_9'], '[') === 0)) {
    $api_config = json_decode($config['cf_9'], true);
    if (!is_array($api_config)) {
        $api_config = array();
    }
}

// 테마 설정
$theme_config = array(
    'set_default_skin'              => false,
    'preview_board_skin'            => 'basic',
    'cf_member_skin'                => 'basic',
    'cf_new_skin'                   => 'basic',
    'cf_search_skin'                => 'basic',
    'cf_connect_skin'               => 'basic',
    'cf_faq_skin'                   => 'basic',
    'bo_gallery_cols'               => 4,
    'bo_gallery_width'              => 215,
    'bo_gallery_height'             => 215,
    'bo_image_width'                => 900,
    'qa_skin'                       => 'basic',
    'theme_version'                 => '1.0.0',
);

// API 키 설정 (DB에서 읽기)
$theme_config['kakao_js_key'] = isset($api_config['kakao_js_key']) && !empty($api_config['kakao_js_key'])
    ? $api_config['kakao_js_key']
    : '';

$theme_config['gemini_api_key'] = isset($api_config['gemini_api_key']) && !empty($api_config['gemini_api_key'])
    ? $api_config['gemini_api_key']
    : '';
?>