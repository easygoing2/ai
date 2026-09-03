<?php
/**
 * 테마 옵션 설정
 * head.php에서 사용하는 테마 옵션 파싱 및 기본값 설정
 */

if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 테마 설정값 가져오기
// cf_1: 로고 설정 JSON (아이콘, 텍스트, 폰트)
// cf_2: 로고 이미지 파일명
$logo_config = array();
if (!empty($config['cf_1']) && (strpos($config['cf_1'], '{') === 0 || strpos($config['cf_1'], '[') === 0)) {
    // JSON 형식인 경우
    $logo_config = json_decode($config['cf_1'], true);
    if (!is_array($logo_config)) {
        $logo_config = array();
    }
} else {
    // 기존 형식인 경우 마이그레이션
    $logo_config = array(
        'logo_icon' => $config['cf_1'] ?: 'bx-sun',
        'logo_text' => 'BaseCamp',
        'font' => 'Pretendard'
    );
}

// 기본값 설정
$theme_logo_icon = isset($logo_config['logo_icon']) ? $logo_config['logo_icon'] : 'bx-sun';
$theme_logo_text = isset($logo_config['logo_text']) ? $logo_config['logo_text'] : 'BaseCamp';

// 회원 통계
$row = sql_fetch( "select count(*) as cnt from {$g5['member_table']}");
$cnt_total = $row['cnt']; //전체회원수

$mb_date = date('Y-m-d 00:00:00');
$row = sql_fetch( "select count(*) as cnt from {$g5['member_table']} where mb_datetime>='{$mb_date}' ");
$cnt_today = $row['cnt']; //오늘 가입자수

$mb_date = date('Y-m-01 00:00:00');
$row = sql_fetch( "select count(*) as cnt from {$g5['member_table']} where mb_datetime>='{$mb_date}' ");
$cnt_month = $row['cnt']; //이달 가입자수
?>
