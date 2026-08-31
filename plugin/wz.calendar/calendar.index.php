<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_PLUGIN_PATH.'/wz.calendar/config.php');
include_once(G5_PLUGIN_PATH.'/wz.calendar/calendar.lib.php');

$mb_id = wzc_require_member(false);

add_stylesheet('<link rel="stylesheet" href="'.WZC_PLUGIN_URL.'/style.css?v='.WZC_VERSION.'">', 0);
add_javascript('<script src="'.WZC_PLUGIN_URL.'/vendor/sortable/Sortable.min.js?v=1.15.7"></script>', 20);
add_javascript('<script src="'.WZC_PLUGIN_URL.'/calendar.js?v='.WZC_VERSION.'"></script>', 21);

if (!wzc_schema_installed()) {
    echo '<section class="wzc-system-message"><h2>내 캘린더 준비 중</h2><p>캘린더 DB 설치가 필요합니다.</p>';
    if (isset($is_admin) && $is_admin === 'super') echo '<a class="wzc-btn wzc-btn-primary" href="'.G5_ADMIN_URL.'/wz_calendar_admin/wzc_install.php">캘린더 설치하기</a>';
    echo '</section>';
    return;
}

$wzc_config = wzc_get_config();
if (empty($wzc_config['wcf_use'])) {
    echo '<section class="wzc-system-message"><h2>내 캘린더</h2><p>현재 캘린더 기능을 사용할 수 없습니다.</p></section>';
    return;
}

wzc_ensure_default_categories($mb_id);
$sch_year = isset($_GET['sch_year']) ? (int)$_GET['sch_year'] : 0;
$sch_month = isset($_GET['sch_month']) ? (int)$_GET['sch_month'] : 0;
$sch_day = isset($_GET['sch_day']) ? (string)$_GET['sch_day'] : '';
$calendar = new WzcCalendar($sch_year, $sch_month, $sch_day);
$selected_day = $calendar->selected;
if ($selected_day < $calendar->month_start || $selected_day > $calendar->month_end) $selected_day = $calendar->month_start;
foreach ($calendar->cells as &$calendar_cell) $calendar_cell['selected'] = $calendar_cell['date'] === $selected_day;
unset($calendar_cell);

$categories = wzc_get_categories($mb_id);
$preference = wzc_get_preference($mb_id);
$events = wzc_get_events($mb_id, $calendar->month_start, $calendar->month_end);
$events_by_date = wzc_events_by_date($mb_id, $events, $calendar->month_start, $calendar->month_end);
$events_per_day = max(1, min(10, (int)$preference['wp_events_per_day']));
$default_category = (int)$preference['wp_default_category'];
if ($default_category && !wzc_category_owned($mb_id, $default_category)) $default_category = 0;
$csrf_token = wzc_csrf_token();

$calendar_url = WZC_BOARD_URL;
$month_url = function($year, $month, $day = '') use ($calendar_url) {
    $url = $calendar_url.'&sch_year='.(int)$year.'&sch_month='.(int)$month;
    if ($day) $url .= '&sch_day='.urlencode($day);
    return $url;
};
$event_json = array();
foreach ($events as $event) $event_json[(int)$event['we_ix']] = wzc_event_public_data($event);

$render_event = function($event, $index) use ($events_per_day) {
    $data = wzc_event_public_data($event);
    $json = htmlspecialchars(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
    $color = preg_match('/^#[0-9a-fA-F]{6}$/', $data['color']) ? $data['color'] : '#6f48ff';
    $overflow = $index >= $events_per_day ? ' is-overflow' : '';
    $time = !$data['all_day'] && $data['start_time'] ? '<span class="wzc-event-time">'.htmlspecialchars($data['start_time'], ENT_QUOTES, 'UTF-8').'</span>' : '';
    echo '<article class="wzc-event'.$overflow.'" data-event-id="'.(int)$data['id'].'" data-category-id="'.(int)$data['category_id'].'" data-event="'.$json.'" style="--wzc-event-color:'.htmlspecialchars($color, ENT_QUOTES, 'UTF-8').'">';
    echo '<button type="button" class="wzc-drag-handle" aria-label="'.htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8').' 일정 이동"><i class="bx bx-grid-vertical"></i></button>';
    echo '<button type="button" class="wzc-event-open">'.$time.'<span class="wzc-event-title">'.htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8').'</span></button>';
    echo '</article>';
};
?>

<section class="wzc-calendar" id="wzcCalendar" aria-labelledby="wzcCalendarTitle">
    <header class="wzc-heading">
        <div>
            <p class="wzc-eyebrow">PRIVATE CALENDAR</p>
            <h2 id="wzcCalendarTitle">내 캘린더</h2>
            <p>나만 볼 수 있는 개인 일정입니다. 일정을 드래그해 날짜와 순서를 바꿀 수 있습니다.</p>
        </div>
        <div class="wzc-heading-actions">
            <button type="button" class="wzc-btn wzc-btn-ghost" data-open-modal="settings"><i class="bx bx-slider-alt"></i><span>설정</span></button>
            <button type="button" class="wzc-btn wzc-btn-primary" data-add-event="<?php echo htmlspecialchars($selected_day, ENT_QUOTES, 'UTF-8'); ?>"><i class="bx bx-plus"></i><span>일정 추가</span></button>
        </div>
    </header>

    <div class="wzc-toolbar">
        <a class="wzc-nav-button" href="<?php echo $month_url($calendar->prev_year, $calendar->prev_month); ?>" aria-label="이전 달"><i class="bx bx-chevron-left"></i></a>
        <strong class="wzc-month-title"><?php echo (int)$calendar->year; ?>년 <?php echo htmlspecialchars($calendar->month_text, ENT_QUOTES, 'UTF-8'); ?>월</strong>
        <a class="wzc-nav-button" href="<?php echo $month_url($calendar->next_year, $calendar->next_month); ?>" aria-label="다음 달"><i class="bx bx-chevron-right"></i></a>
        <a class="wzc-today-button" href="<?php echo $month_url((int)substr($calendar->today, 0, 4), (int)substr($calendar->today, 5, 2), $calendar->today); ?>">오늘</a>
    </div>

    <div class="wzc-filter" aria-label="일정 분류 필터">
        <button type="button" class="wzc-filter-chip is-active" data-category-filter="0">전체</button>
        <?php foreach ($categories as $category) { ?>
        <button type="button" class="wzc-filter-chip" data-category-filter="<?php echo (int)$category['wc_ix']; ?>">
            <span class="wzc-color-dot" style="--wzc-category-color:<?php echo htmlspecialchars($category['wc_color'], ENT_QUOTES, 'UTF-8'); ?>"></span>
            <?php echo htmlspecialchars($category['wc_name'], ENT_QUOTES, 'UTF-8'); ?>
        </button>
        <?php } ?>
        <span class="wzc-filter-note" id="wzcFilterNote" hidden>필터를 해제하면 일정을 이동할 수 있습니다.</span>
    </div>

    <div class="wzc-weekdays" aria-hidden="true">
        <span class="is-sunday">일</span><span>월</span><span>화</span><span>수</span><span>목</span><span>금</span><span class="is-saturday">토</span>
    </div>

    <div class="wzc-grid" role="grid" aria-label="<?php echo (int)$calendar->year; ?>년 <?php echo (int)$calendar->month; ?>월 개인 일정">
        <?php foreach ($calendar->cells as $cell) {
            $classes = array('wzc-day');
            if (!$cell['current_month']) $classes[] = 'is-outside';
            if ($cell['today']) $classes[] = 'is-today';
            if ($cell['selected']) $classes[] = 'is-selected';
            if ($cell['weekday'] === 0) $classes[] = 'is-sunday';
            if ($cell['weekday'] === 6) $classes[] = 'is-saturday';
            $date_events = isset($events_by_date[$cell['date']]) ? $events_by_date[$cell['date']] : array();
        ?>
        <section class="<?php echo implode(' ', $classes); ?>" role="gridcell" data-date="<?php echo htmlspecialchars($cell['date'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php if ($cell['current_month']) { ?>
            <div class="wzc-day-header">
                <a href="<?php echo $month_url($calendar->year, $calendar->month, $cell['date']); ?>" class="wzc-day-number" aria-label="<?php echo htmlspecialchars($cell['date'], ENT_QUOTES, 'UTF-8'); ?> 일정 보기"><?php echo (int)$cell['day']; ?></a>
                <?php if ($cell['today']) { ?><span class="wzc-today-label">오늘</span><?php } ?>
                <button type="button" class="wzc-day-add" data-add-event="<?php echo htmlspecialchars($cell['date'], ENT_QUOTES, 'UTF-8'); ?>" aria-label="<?php echo htmlspecialchars($cell['date'], ENT_QUOTES, 'UTF-8'); ?> 일정 추가"><i class="bx bx-plus"></i></button>
            </div>
            <div class="wzc-event-list" data-date="<?php echo htmlspecialchars($cell['date'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php foreach ($date_events as $index => $event) $render_event($event, $index); ?>
            </div>
            <?php if (count($date_events) > $events_per_day) { ?>
            <button type="button" class="wzc-more" data-more-count="<?php echo count($date_events) - $events_per_day; ?>">+<?php echo count($date_events) - $events_per_day; ?>개 더보기</button>
            <?php } ?>
            <?php } ?>
        </section>
        <?php } ?>
    </div>

    <section class="wzc-day-agenda" aria-labelledby="wzcAgendaTitle">
        <div class="wzc-agenda-heading">
            <div>
                <p>선택한 날짜</p>
                <h3 id="wzcAgendaTitle"><?php echo htmlspecialchars($selected_day, ENT_QUOTES, 'UTF-8'); ?></h3>
            </div>
            <button type="button" class="wzc-btn wzc-btn-primary wzc-btn-small" data-add-event="<?php echo htmlspecialchars($selected_day, ENT_QUOTES, 'UTF-8'); ?>"><i class="bx bx-plus"></i> 일정 추가</button>
        </div>
        <div class="wzc-agenda-list">
            <?php $selected_events = isset($events_by_date[$selected_day]) ? $events_by_date[$selected_day] : array(); ?>
            <?php if (!$selected_events) { ?>
                <div class="wzc-empty"><i class="bx bx-calendar"></i><p>등록된 일정이 없습니다.</p></div>
            <?php } else { foreach ($selected_events as $event) { $data = wzc_event_public_data($event); ?>
                <button type="button" class="wzc-agenda-item wzc-event-open" data-event-id="<?php echo (int)$data['id']; ?>" style="--wzc-event-color:<?php echo htmlspecialchars($data['color'], ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="wzc-agenda-time"><?php echo $data['all_day'] ? '종일' : htmlspecialchars($data['start_time'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="wzc-agenda-main"><strong><?php echo htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8'); ?></strong><small><?php echo htmlspecialchars($data['category_name'] ?: '분류 없음', ENT_QUOTES, 'UTF-8'); ?><?php echo $data['location'] ? ' · '.htmlspecialchars($data['location'], ENT_QUOTES, 'UTF-8') : ''; ?></small></span>
                    <i class="bx bx-chevron-right"></i>
                </button>
            <?php }} ?>
        </div>
    </section>

    <p class="wzc-drag-guide"><i class="bx bx-info-circle"></i> PC에서는 이동 손잡이를 드래그하고, 모바일에서는 손잡이를 길게 누른 뒤 이동하세요.</p>
</section>

<div class="wzc-modal" id="wzcEventModal" aria-hidden="true">
    <div class="wzc-modal-backdrop" data-close-modal></div>
    <section class="wzc-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="wzcEventModalTitle">
        <header class="wzc-modal-header">
            <h3 id="wzcEventModalTitle">일정 추가</h3>
            <button type="button" class="wzc-modal-close" data-close-modal aria-label="닫기"><i class="bx bx-x"></i></button>
        </header>
        <form id="wzcEventForm" class="wzc-form">
            <input type="hidden" name="event_id" value="0">
            <input type="hidden" name="version" value="0">
            <div class="wzc-field wzc-field-full">
                <label for="wzcTitle">일정 제목 <strong>필수</strong></label>
                <input type="text" name="title" id="wzcTitle" maxlength="255" required placeholder="일정 제목을 입력하세요">
            </div>
            <div class="wzc-field">
                <label for="wzcCategory">분류</label>
                <select name="category_id" id="wzcCategory">
                    <option value="0">분류 없음</option>
                    <?php foreach ($categories as $category) { ?><option value="<?php echo (int)$category['wc_ix']; ?>" <?php echo $default_category === (int)$category['wc_ix'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['wc_name'], ENT_QUOTES, 'UTF-8'); ?></option><?php } ?>
                </select>
            </div>
            <div class="wzc-field wzc-check-field">
                <label><input type="checkbox" name="all_day" value="1" checked> 종일 일정</label>
            </div>
            <div class="wzc-field">
                <label for="wzcStartDate">시작일</label>
                <input type="date" name="start_date" id="wzcStartDate" required>
            </div>
            <div class="wzc-field">
                <label for="wzcEndDate">종료일</label>
                <input type="date" name="end_date" id="wzcEndDate" required>
            </div>
            <div class="wzc-field wzc-time-field">
                <label for="wzcStartTime">시작 시간</label>
                <input type="time" name="start_time" id="wzcStartTime">
            </div>
            <div class="wzc-field wzc-time-field">
                <label for="wzcEndTime">종료 시간</label>
                <input type="time" name="end_time" id="wzcEndTime">
            </div>
            <div class="wzc-field wzc-field-full">
                <label for="wzcLocation">장소</label>
                <input type="text" name="location" id="wzcLocation" maxlength="255" placeholder="장소를 입력하세요">
            </div>
            <div class="wzc-field wzc-field-full">
                <label for="wzcContent">메모</label>
                <textarea name="content" id="wzcContent" rows="5" placeholder="일정에 필요한 내용을 입력하세요"></textarea>
            </div>
            <div class="wzc-field wzc-field-full">
                <label for="wzcLinkUrl">외부 링크</label>
                <input type="url" name="link_url" id="wzcLinkUrl" maxlength="500" placeholder="https://">
            </div>
            <div class="wzc-form-message" role="alert" hidden></div>
            <div class="wzc-order-actions" id="wzcOrderActions" hidden aria-label="일정 순서 변경">
                <span>선택 날짜 순서</span>
                <button type="button" class="wzc-btn wzc-btn-ghost wzc-btn-small" data-order-direction="up"><i class="bx bx-up-arrow-alt"></i> 위로</button>
                <button type="button" class="wzc-btn wzc-btn-ghost wzc-btn-small" data-order-direction="down"><i class="bx bx-down-arrow-alt"></i> 아래로</button>
                <button type="button" class="wzc-btn wzc-btn-ghost wzc-btn-small" data-order-direction="top"><i class="bx bx-chevrons-up"></i> 맨 위로</button>
            </div>
            <footer class="wzc-modal-actions">
                <button type="button" class="wzc-btn wzc-btn-danger" id="wzcDeleteEvent" hidden>삭제</button>
                <span class="wzc-action-spacer"></span>
                <button type="button" class="wzc-btn wzc-btn-ghost" data-close-modal>취소</button>
                <button type="submit" class="wzc-btn wzc-btn-primary">저장</button>
            </footer>
        </form>
    </section>
</div>

<div class="wzc-modal" id="wzcSettingsModal" aria-hidden="true">
    <div class="wzc-modal-backdrop" data-close-modal></div>
    <section class="wzc-modal-dialog wzc-settings-dialog" role="dialog" aria-modal="true" aria-labelledby="wzcSettingsTitle">
        <header class="wzc-modal-header">
            <h3 id="wzcSettingsTitle">내 캘린더 설정</h3>
            <button type="button" class="wzc-modal-close" data-close-modal aria-label="닫기"><i class="bx bx-x"></i></button>
        </header>
        <div class="wzc-settings-body">
            <section class="wzc-settings-section">
                <h4>개인 분류</h4>
                <div class="wzc-category-list">
                    <?php foreach ($categories as $category) { ?>
                    <form class="wzc-category-form" data-category-id="<?php echo (int)$category['wc_ix']; ?>">
                        <input type="color" name="color" value="<?php echo htmlspecialchars($category['wc_color'], ENT_QUOTES, 'UTF-8'); ?>" aria-label="분류 색상">
                        <input type="text" name="name" value="<?php echo htmlspecialchars($category['wc_name'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="50" aria-label="분류명">
                        <button type="submit" class="wzc-icon-btn" aria-label="분류 저장"><i class="bx bx-check"></i></button>
                        <button type="button" class="wzc-icon-btn is-danger" data-delete-category aria-label="분류 삭제"><i class="bx bx-trash"></i></button>
                    </form>
                    <?php } ?>
                    <form class="wzc-category-form is-new" data-category-id="0">
                        <input type="color" name="color" value="#6f48ff" aria-label="새 분류 색상">
                        <input type="text" name="name" value="" maxlength="50" placeholder="새 분류" aria-label="새 분류명">
                        <button type="submit" class="wzc-icon-btn" aria-label="분류 추가"><i class="bx bx-plus"></i></button>
                    </form>
                </div>
            </section>
            <section class="wzc-settings-section">
                <h4>보기 및 조작</h4>
                <form id="wzcPreferenceForm" class="wzc-preference-form">
                    <label>기본 일정 분류
                        <select name="default_category">
                            <option value="0">분류 없음</option>
                            <?php foreach ($categories as $category) { ?><option value="<?php echo (int)$category['wc_ix']; ?>" <?php echo $default_category === (int)$category['wc_ix'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['wc_name'], ENT_QUOTES, 'UTF-8'); ?></option><?php } ?>
                        </select>
                    </label>
                    <label>날짜 셀 일정 표시
                        <select name="events_per_day">
                            <?php for ($i = 1; $i <= 10; $i++) { ?><option value="<?php echo $i; ?>" <?php echo $events_per_day === $i ? 'selected' : ''; ?>>최대 <?php echo $i; ?>개</option><?php } ?>
                        </select>
                    </label>
                    <label class="wzc-switch-row"><span>모바일 드래그 사용</span><input type="checkbox" name="touch_drag_use" value="1" <?php echo !empty($preference['wp_touch_drag_use']) ? 'checked' : ''; ?>></label>
                    <button type="submit" class="wzc-btn wzc-btn-primary">개인 설정 저장</button>
                </form>
            </section>
        </div>
    </section>
</div>

<div class="wzc-toast" id="wzcToast" role="status" aria-live="polite" hidden>
    <span class="wzc-toast-message"></span>
    <button type="button" class="wzc-toast-action" hidden>실행 취소</button>
    <button type="button" class="wzc-toast-close" aria-label="알림 닫기"><i class="bx bx-x"></i></button>
</div>

<script type="application/json" id="wzcRuntimeConfig"><?php echo json_encode(array(
    'csrfToken' => $csrf_token,
    'baseUrl' => WZC_PLUGIN_URL,
    'calendarUrl' => WZC_BOARD_URL,
    'year' => $calendar->year,
    'month' => $calendar->month,
    'selectedDay' => $selected_day,
    'touchDragUse' => !empty($preference['wp_touch_drag_use']),
    'events' => $event_json
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP); ?></script>
