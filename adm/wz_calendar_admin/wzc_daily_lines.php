<?php
$sub_menu = '790150';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');
$g5['title'] = '오늘의 한 줄 관리';

if (!wzc_schema_installed() || !wzc_daily_line_schema_installed()) {
    include_once(G5_ADMIN_PATH.'/admin.head.php');
?>
<div class="local_desc01 local_desc"><p>오늘의 한 줄 DB 설치가 필요합니다. 설치 상태 복구를 실행해 주세요.</p></div>
<div class="btn_fixed_top"><a href="./wzc_install.php" class="btn_submit btn">설치 상태 복구</a></div>
<?php
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

$year = isset($_REQUEST['year']) ? (int)$_REQUEST['year'] : (int)date('Y');
$month = isset($_REQUEST['month']) ? (int)$_REQUEST['month'] : (int)date('n');
if ($year < 2000 || $year > 2100) $year = (int)date('Y');
if ($month < 1 || $month > 12) $month = (int)date('n');

$month_start = sprintf('%04d-%02d-01', $year, $month);
$days_in_month = (int)date('t', strtotime($month_start));
$month_end = sprintf('%04d-%02d-%02d', $year, $month, $days_in_month);
$config = wzc_get_config();
$max_length = max(50, min(100, (int)$config['wcf_daily_line_max_length']));

if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    auth_check_menu($auth, $sub_menu, 'w');
    check_demo();
    check_admin_token();

    $posted_lines = isset($_POST['daily_line']) && is_array($_POST['daily_line']) ? $_POST['daily_line'] : array();
    $admin_id = isset($member['mb_id']) ? sql_escape_string($member['mb_id']) : '';
    $save_ok = true;

    sql_query('START TRANSACTION', false);
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $content = isset($posted_lines[$date]) ? trim(preg_replace('/\s+/u', ' ', strip_tags((string)$posted_lines[$date]))) : '';
        if (mb_strlen($content, 'UTF-8') > $max_length) {
            sql_query('ROLLBACK', false);
            alert($date.' 문구는 최대 '.$max_length.'자까지 입력할 수 있습니다.');
        }

        $date_sql = sql_escape_string($date);
        if ($content === '') {
            $result = sql_query("DELETE FROM `{$g5['wzc_daily_line_table']}` WHERE wdl_date='{$date_sql}'", false);
        } else {
            $content_sql = sql_escape_string($content);
            $result = sql_query("INSERT INTO `{$g5['wzc_daily_line_table']}`
                (wdl_date, wdl_content, wdl_created_by, wdl_created_at, wdl_updated_at)
                VALUES ('{$date_sql}', '{$content_sql}', '{$admin_id}', NOW(), NOW())
                ON DUPLICATE KEY UPDATE wdl_content=VALUES(wdl_content), wdl_created_by=VALUES(wdl_created_by), wdl_updated_at=NOW()", false);
        }
        if (!$result) {
            $save_ok = false;
            break;
        }
    }

    sql_query($save_ok ? 'COMMIT' : 'ROLLBACK', false);
    if (!$save_ok) alert('저장 중 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.');
    alert($year.'년 '.$month.'월 오늘의 한 줄을 저장했습니다.', './wzc_daily_lines.php?year='.$year.'&month='.$month);
}

$daily_lines = wzc_get_daily_lines($month_start, $month_end);
$first_weekday = (int)date('w', strtotime($month_start));
$prev_time = strtotime($month_start.' -1 month');
$next_time = strtotime($month_start.' +1 month');
$prev_url = './wzc_daily_lines.php?year='.date('Y', $prev_time).'&amp;month='.date('n', $prev_time);
$next_url = './wzc_daily_lines.php?year='.date('Y', $next_time).'&amp;month='.date('n', $next_time);
$today_url = './wzc_daily_lines.php?year='.date('Y').'&amp;month='.date('n');

add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/wz_calendar_admin/wzc_daily_lines.css?v='.(int)@filemtime(G5_ADMIN_PATH.'/wz_calendar_admin/wzc_daily_lines.css').'">', 0);
include_once(G5_ADMIN_PATH.'/admin.head.php');
?>
<div class="local_desc01 local_desc">
    <p>날짜별 문구를 입력한 뒤 월간 저장을 누르면 메인 화면의 AI Agent 영역 위에 표시됩니다. 비어 있는 날짜에는 영역이 노출되지 않습니다.</p>
    <p>문구는 한 줄로 정리되어 저장되며, 현재 최대 <?php echo $max_length; ?>자입니다. 글자 수는 <a href="./wzc_config.php">환경설정</a>에서 50~100자로 조절할 수 있습니다.</p>
</div>

<form name="fwzcdailylines" method="post" action="./wzc_daily_lines.php">
    <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
    <input type="hidden" name="year" value="<?php echo $year; ?>">
    <input type="hidden" name="month" value="<?php echo $month; ?>">

    <div class="wzc-admin-toolbar">
        <a href="<?php echo $prev_url; ?>" class="btn btn_02" aria-label="이전 달">&lsaquo; 이전 달</a>
        <div class="wzc-admin-month">
            <strong><?php echo $year; ?>년 <?php echo $month; ?>월</strong>
            <a href="<?php echo $today_url; ?>">오늘</a>
        </div>
        <a href="<?php echo $next_url; ?>" class="btn btn_02" aria-label="다음 달">다음 달 &rsaquo;</a>
    </div>

    <div class="wzc-admin-calendar-wrap">
        <div class="wzc-admin-calendar" role="grid" aria-label="<?php echo $year; ?>년 <?php echo $month; ?>월 오늘의 한 줄 입력">
            <?php
            $weekdays = array('일', '월', '화', '수', '목', '금', '토');
            foreach ($weekdays as $weekday_index => $weekday) {
                $weekday_class = $weekday_index === 0 ? ' is-sunday' : ($weekday_index === 6 ? ' is-saturday' : '');
            ?>
            <div class="wzc-admin-weekday<?php echo $weekday_class; ?>" role="columnheader"><?php echo $weekday; ?></div>
            <?php } ?>

            <?php for ($blank = 0; $blank < $first_weekday; $blank++) { ?>
            <div class="wzc-admin-day is-empty" role="gridcell" aria-hidden="true"></div>
            <?php } ?>

            <?php for ($day = 1; $day <= $days_in_month; $day++) {
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $weekday = (int)date('w', strtotime($date));
                $value = isset($daily_lines[$date]) ? $daily_lines[$date] : '';
                $classes = 'wzc-admin-day';
                if ($weekday === 0) $classes .= ' is-sunday';
                if ($weekday === 6) $classes .= ' is-saturday';
                if ($date === date('Y-m-d')) $classes .= ' is-today';
            ?>
            <div class="<?php echo $classes; ?>" role="gridcell">
                <div class="wzc-admin-date">
                    <label for="daily_line_<?php echo $date; ?>"><?php echo $day; ?>일</label>
                    <span data-count-for="daily_line_<?php echo $date; ?>"><?php echo mb_strlen($value, 'UTF-8'); ?>/<?php echo $max_length; ?></span>
                </div>
                <textarea name="daily_line[<?php echo $date; ?>]" id="daily_line_<?php echo $date; ?>" rows="4" maxlength="<?php echo $max_length; ?>" placeholder="오늘의 한 줄을 입력하세요" data-daily-line><?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <?php } ?>

            <?php
            $rendered_cells = $first_weekday + $days_in_month;
            $trailing_cells = (7 - ($rendered_cells % 7)) % 7;
            for ($blank = 0; $blank < $trailing_cells; $blank++) {
            ?>
            <div class="wzc-admin-day is-empty" role="gridcell" aria-hidden="true"></div>
            <?php } ?>
        </div>
    </div>

    <div class="btn_fixed_top">
        <a href="./wzc_config.php" class="btn btn_02">환경설정</a>
        <a href="<?php echo G5_URL; ?>" class="btn btn_03" target="_blank" rel="noopener">메인 화면</a>
        <button type="submit" class="btn_submit btn">월간 저장</button>
    </div>
</form>

<script>
(function () {
    'use strict';
    var fields = document.querySelectorAll('[data-daily-line]');
    for (var i = 0; i < fields.length; i++) {
        fields[i].addEventListener('input', function () {
            var counter = document.querySelector('[data-count-for="' + this.id + '"]');
            if (counter) counter.textContent = Array.from(this.value).length + '/' + this.maxLength;
        });
    }
}());
</script>
<?php include_once(G5_ADMIN_PATH.'/admin.tail.php'); ?>
