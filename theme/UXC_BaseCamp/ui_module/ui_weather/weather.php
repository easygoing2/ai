<?php
if (!defined('_GNUBOARD_')) exit;

$weather_google_api_key = defined('G5_WEATHER_GOOGLE_API_KEY') ? G5_WEATHER_GOOGLE_API_KEY : '';

add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/ui_module/ui_weather/style.css">', 1);
add_javascript('<script src="'.G5_THEME_URL.'/ui_module/ui_weather/weather.js"></script>', 1);
?>
<div class="weatherWidget" id="weatherWidget" data-google-api-key="<?php echo htmlspecialchars($weather_google_api_key, ENT_QUOTES, 'UTF-8'); ?>">
  <div class="weatherLoading">
    <i class="bx bx-loader-alt bx-spin" aria-hidden="true"></i>
    <span>날씨 정보 불러오는 중...</span>
  </div>
</div>
