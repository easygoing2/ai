<?php
if (!defined('_GNUBOARD_')) exit;

$daily_line_config_file = G5_PLUGIN_PATH.'/wz.calendar/config.php';
$daily_line_lib_file = G5_PLUGIN_PATH.'/wz.calendar/calendar.lib.php';
if (!is_file($daily_line_config_file) || !is_file($daily_line_lib_file)) return;

include_once($daily_line_config_file);
include_once($daily_line_lib_file);

$daily_line_date = defined('G5_TIME_YMD') ? G5_TIME_YMD : date('Y-m-d');
$daily_line_content = function_exists('wzc_get_daily_line') ? wzc_get_daily_line($daily_line_date) : '';
if ($daily_line_content === '') return;

add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/ui_module/ui_dailyLine/style.css?v='.(int)@filemtime(G5_THEME_PATH.'/ui_module/ui_dailyLine/style.css').'">', 0);
$daily_line_weekdays = array('일', '월', '화', '수', '목', '금', '토');
$daily_line_timestamp = strtotime($daily_line_date);
$daily_line_date_label = date('n월 j일', $daily_line_timestamp).' '.$daily_line_weekdays[(int)date('w', $daily_line_timestamp)].'요일';
?>
<section class="daily-line" aria-labelledby="dailyLineTitle">
    <div class="daily-line__label">
        <span class="daily-line__icon" aria-hidden="true"><i class="bx bxs-quote-left"></i></span>
        <h2 id="dailyLineTitle">오늘의 한 줄</h2>
    </div>
    <p class="daily-line__content"><?php echo htmlspecialchars($daily_line_content, ENT_QUOTES, 'UTF-8'); ?></p>
    <time class="daily-line__date" datetime="<?php echo $daily_line_date; ?>"><?php echo $daily_line_date_label; ?></time>
</section>
