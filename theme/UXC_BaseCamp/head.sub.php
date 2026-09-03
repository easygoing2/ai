<?php
// 이 파일은 새로운 파일 생성시 반드시 포함되어야 함
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!isset($g5['title'])) {
    $g5['title'] = $config['cf_title'];
    $g5_head_title = $g5['title'];
}
else {
    // 상태바에 표시될 제목
    $g5_head_title = implode(' | ', array_filter(array($g5['title'], $config['cf_title'])));
}

$g5['title'] = strip_tags($g5['title']);
$g5_head_title = strip_tags($g5_head_title);

// 현재 접속자
// 게시판 제목에 ' 포함되면 오류 발생
$g5['lo_location'] = addslashes($g5['title']);
if (!$g5['lo_location'])
    $g5['lo_location'] = addslashes(clean_xss_tags($_SERVER['REQUEST_URI']));
$g5['lo_url'] = addslashes(clean_xss_tags($_SERVER['REQUEST_URI']));
if (strstr($g5['lo_url'], '/'.G5_ADMIN_DIR.'/') || $is_admin == 'super') $g5['lo_url'] = '';

?>
<!doctype html>
<html lang="ko">

<head>
  <meta charset="utf-8">
  <?php
    echo '<meta name="viewport" id="meta_viewport" content="width=device-width,initial-scale=1.0,minimum-scale=0,maximum-scale=1,user-scalable=yes">'.PHP_EOL;
    echo '<meta http-equiv="imagetoolbar" content="no">'.PHP_EOL;
    echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">'.PHP_EOL;

if($config['cf_add_meta'])
    echo $config['cf_add_meta'].PHP_EOL;
?>
  <title><?php echo $g5_head_title; ?></title>
  <?php
echo '<link rel="stylesheet" href="'.run_replace('head_css_url', G5_THEME_CSS_URL.'/ui.css?ver='.G5_CSS_VER, G5_THEME_URL).'">'.PHP_EOL;
echo '<link rel="stylesheet" href="'.run_replace('head_css_url', G5_THEME_CSS_URL.'/layout.css?ver='.G5_CSS_VER, G5_THEME_URL).'">'.PHP_EOL;
// 컬러셋 설정 적용 (cf_3 필드 사용)
$colorset_file = 'default_6f48ff.css'; // 기본값 설정

// cf_3 값이 있고, .css 확장자를 포함한 유효한 파일명인 경우에만 사용
if (isset($config['cf_3']) && !empty($config['cf_3']) && strpos($config['cf_3'], '.css') !== false) {
    // color_set 디렉토리에 실제로 파일이 존재하는지 확인
    $colorset_path = G5_THEME_PATH.'/css/color_set/'.$config['cf_3'];
    if (file_exists($colorset_path)) {
        $colorset_file = $config['cf_3'];
    }
}

echo '<link rel="stylesheet" href="'.run_replace('head_css_url', G5_THEME_CSS_URL.'/color_set/'.$colorset_file.'?ver='.G5_CSS_VER, G5_THEME_URL).'">'.PHP_EOL;

// 폰트 설정 적용 (head_theme_option.php에서 파싱된 값 사용)
$fontset = 'Pretendard'; // 기본값

// head_theme_option.php가 아직 로드되지 않았을 수 있으므로 직접 파싱
if (!empty($config['cf_1'])) {
    // JSON 형식인지 확인
    if (strpos($config['cf_1'], '{') === 0) {
        $cf1_data = json_decode($config['cf_1'], true);
        if (is_array($cf1_data) && isset($cf1_data['font'])) {
            $fontset = $cf1_data['font'];
        }
    }
}

// 폰트 CSS 파일 적용
$font_css_path = G5_THEME_PATH.'/css/font/'.$fontset.'.css';
if (file_exists($font_css_path)) {
    echo '<link rel="stylesheet" href="'.G5_THEME_URL.'/css/font/'.$fontset.'.css?ver='.G5_CSS_VER.'">'.PHP_EOL;
} else {
    // 기본 폰트 파일이 없을 경우 Pretendard.css 사용
    echo '<link rel="stylesheet" href="'.G5_THEME_URL.'/css/font/Pretendard.css?ver='.G5_CSS_VER.'">'.PHP_EOL;
}

// 쇼핑몰 전용 CSS 추가
if (defined('_SHOP_')) {
    echo '<link rel="stylesheet" href="'.run_replace('head_css_url', G5_THEME_CSS_URL.'/shop_layout.css?ver='.G5_CSS_VER, G5_THEME_URL).'">'.PHP_EOL;
    echo '<link rel="stylesheet" href="'.run_replace('head_css_url', G5_THEME_CSS_URL.'/shop_ui.css?ver='.G5_CSS_VER, G5_THEME_URL).'">'.PHP_EOL;
}

echo '<link rel="stylesheet" href="'.run_replace('head_css_url', G5_THEME_CSS_URL.'/board.css?ver='.G5_CSS_VER, G5_THEME_URL).'">'.PHP_EOL;
echo '<link rel="stylesheet" href="'.run_replace('head_css_url', G5_THEME_CSS_URL.'/custome.css?ver='.G5_CSS_VER, G5_THEME_URL).'">'.PHP_EOL;
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">'.PHP_EOL;
?>
  <!--[if lte IE 8]>
<script src="<?php echo G5_JS_URL ?>/html5.js"></script>
<![endif]-->
  <script>
  // 자바스크립트에서 사용하는 전역변수 선언
  var g5_url = "<?php echo G5_URL ?>";
  var g5_bbs_url = "<?php echo G5_BBS_URL ?>";
  var g5_is_member = "<?php echo isset($is_member)?$is_member:''; ?>";
  var g5_is_admin = "<?php echo isset($is_admin)?$is_admin:''; ?>";
  var g5_is_mobile = "<?php echo G5_IS_MOBILE ?>";
  var g5_bo_table = "<?php echo isset($bo_table)?$bo_table:''; ?>";
  var g5_sca = "<?php echo isset($sca)?$sca:''; ?>";
  var g5_editor = "<?php echo ($config['cf_editor'] && ((isset($board['bo_use_dhtml_editor']) && $board['bo_use_dhtml_editor']) || (isset($qaconfig['qa_use_editor']) && $qaconfig['qa_use_editor']))) ? $config['cf_editor'] : ''; ?>";
  var g5_cookie_domain = "<?php echo G5_COOKIE_DOMAIN ?>";
  <?php if (defined('G5_USE_SHOP') && G5_USE_SHOP) { ?>
  var g5_theme_shop_url = "<?php echo G5_THEME_SHOP_URL; ?>";
  var g5_shop_url = "<?php echo G5_SHOP_URL; ?>";
  <?php } ?>
  <?php if(defined('G5_IS_ADMIN')) { ?>
  var g5_admin_url = "<?php echo G5_ADMIN_URL; ?>";
  <?php } ?>
  </script>
  <?php
add_javascript('<script src="'.G5_JS_URL.'/jquery-1.12.4.min.js"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/jquery-migrate-1.4.1.min.js"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/common.js?ver='.G5_JS_VER.'"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/wrest.js?ver='.G5_JS_VER.'"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/placeholders.min.js"></script>', 0);
add_javascript('<script src="'.G5_THEME_JS_URL.'/common.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/font-awesome/css/font-awesome.min.css">', 0);
add_javascript('<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>', 0);

if(!defined('G5_IS_ADMIN'))
    echo $config['cf_add_script'];
?>
  <?php
// 테마 옵션 스크립트 (다크모드, 사이드메뉴, Google Translate)
include_once(G5_THEME_PATH.'/include/head_sub_theme_option.php');
?>

</head>
<body<?php echo isset($g5['body_script']) ? $g5['body_script'] : ''; ?>>
  <?php
// 로그인 상태 표시는 새로운 테마에서 별도로 처리
?>
