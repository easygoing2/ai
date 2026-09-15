<?php
if (!defined('_GNUBOARD_')) exit;

include_once(__DIR__.'/study.lib.php');
$study = uxc_study_data($widget_layout, $_GET);
$study_calendar = $study['calendar'];
$study_escape = function($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
$study_url = function($year, $month, $day = '') {
    return G5_URL.'/?'.http_build_query(array('study_year' => $year, 'study_month' => $month, 'study_day' => $day)).'#studySidebar';
};
$study_calendar_url = WZC_BOARD_URL.'&sch_year='.$study_calendar->year.'&sch_month='.$study_calendar->month.'&sch_day='.$study_calendar->selected;
$study_login_url = G5_BBS_URL.'/login.php?url='.urlencode(G5_URL.'/');
?>
<aside class="study-sidebar" id="studySidebar" aria-label="나의 학습" style="--study-feed-rows:<?php echo (int)$study_feed_rows; ?>">
    <section class="study-card study-calendar" aria-labelledby="studyCalendarTitle">
        <header class="study-card__header">
            <h2 id="studyCalendarTitle"><i class="bx bx-calendar" aria-hidden="true"></i>학습 캘린더</h2>
            <nav class="study-calendar__nav" aria-label="학습 캘린더 월 이동">
                <a data-study-date href="<?php echo $study_escape($study_url($study_calendar->prev_year, $study_calendar->prev_month)); ?>" aria-label="이전 달"><i class="bx bx-chevron-left" aria-hidden="true"></i></a>
                <a data-study-date href="<?php echo $study_escape($study_url($study_calendar->next_year, $study_calendar->next_month)); ?>" aria-label="다음 달"><i class="bx bx-chevron-right" aria-hidden="true"></i></a>
            </nav>
        </header>
        <div class="study-calendar__month">
            <strong><?php echo $study_calendar->year; ?>년 <?php echo $study_calendar->month; ?>월</strong>
            <?php if ($study_calendar->selected !== G5_TIME_YMD) { ?>
            <a data-study-date href="<?php echo $study_escape($study_url((int)date('Y', G5_SERVER_TIME), (int)date('n', G5_SERVER_TIME), G5_TIME_YMD)); ?>">오늘</a>
            <?php } ?>
        </div>
        <div class="study-calendar__weekdays" aria-hidden="true"><span>일</span><span>월</span><span>화</span><span>수</span><span>목</span><span>금</span><span>토</span></div>
        <div class="study-calendar__days" aria-label="학습 날짜 선택">
            <?php foreach ($study_calendar->cells as $cell) {
                $selected = $cell['date'] === $study_calendar->selected;
                $recorded = isset($study['days'][$cell['date']]);
                $classes = 'study-calendar__day'.(!$cell['current_month'] ? ' is-outside' : '').($selected ? ' is-selected' : '').($recorded ? ' has-record' : '');
            ?>
            <a data-study-date class="<?php echo $classes; ?>" href="<?php echo $study_escape($study_url((int)substr($cell['date'], 0, 4), (int)substr($cell['date'], 5, 2), $cell['date'])); ?>"
                aria-label="<?php echo $cell['date'].($recorded ? ' 학습 기록 있음' : '').($selected ? ' 선택됨' : ''); ?>"<?php if ($cell['today']) echo ' aria-current="date"'; ?>><?php echo $cell['day']; ?></a>
            <?php } ?>
        </div>
    </section>

    <section class="study-card study-lessons" aria-labelledby="studyLessonsTitle">
        <header class="study-card__header">
            <h2 id="studyLessonsTitle"><i class="bx bx-play-circle" aria-hidden="true"></i><?php echo $study_calendar->selected === G5_TIME_YMD ? '오늘의 학습' : (int)substr($study_calendar->selected, 5, 2).'월 '.(int)substr($study_calendar->selected, 8, 2).'일 학습'; ?></h2>
            <span class="study-lessons__count"><?php echo count($study['lessons']); ?>개</span>
        </header>
        <?php if ($study['lessons']) { ?>
        <ul class="study-lessons__list">
            <?php foreach ($study['lessons'] as $lesson) { ?>
            <li><a class="study-lesson" href="<?php echo $study_escape($lesson['url']); ?>">
                <span class="study-lesson__thumbnail"><i class="bx bx-play-circle" aria-hidden="true"></i><img src="<?php echo $study_escape($lesson['thumbnail']); ?>" alt="" loading="lazy"></span>
                <span class="study-lesson__body">
                    <span class="study-lesson__heading"><strong><?php echo $study_escape($lesson['title']); ?></strong><?php if ($lesson['completed']) { ?><i class="bx bxs-check-circle" aria-label="학습 완료" role="img"></i><?php } ?></span>
                    <span class="study-lesson__category"><?php echo $study_escape($lesson['category']); ?></span>
                    <span class="study-lesson__progress"><progress max="100" value="<?php echo $lesson['percent']; ?>" aria-label="<?php echo $study_escape($lesson['title']); ?> 시청률"><?php echo $lesson['percent']; ?>%</progress><span><?php echo $lesson['percent']; ?>%</span></span>
                </span>
            </a></li>
            <?php } ?>
        </ul>
        <?php } else { ?>
        <div class="study-empty">
            <i class="bx bx-play-circle" aria-hidden="true"></i>
            <?php if (empty($member['mb_id'])) { ?>
            <p>로그인하고 나의 학습 기록을 확인하세요.</p>
            <a class="study-login-button" href="<?php echo $study_escape($study_login_url); ?>"><i class="bx bx-log-in-circle" aria-hidden="true"></i><span>로그인하기</span></a>
            <?php } elseif (!$study['enabled']) { ?>
            <p>학습 기록 기능을 준비하고 있습니다.</p>
            <?php } else { ?>
            <p><?php echo $study_calendar->selected === G5_TIME_YMD ? '오늘은 아직 학습한 강의가 없어요.' : '선택한 날짜의 학습 기록이 없습니다.'; ?></p><span>관심 있는 강의를 시청해 보세요.</span>
            <?php } ?>
        </div>
        <?php } ?>
        <?php if (!empty($member['mb_id'])) { ?><a class="study-lessons__more" href="<?php echo $study_escape($study_calendar_url); ?>">학습 캘린더 전체보기 <i class="bx bx-chevron-right" aria-hidden="true"></i></a><?php } ?>
    </section>

    <section class="study-upload" aria-labelledby="studyUploadTitle">
        <i class="bx bx-cloud-upload study-upload__icon" aria-hidden="true"></i>
        <h2 id="studyUploadTitle">새 강의 업로드</h2>
        <p>학습할 강의 파일을 업로드하세요</p>
        <?php if (empty($member['mb_id'])) { ?>
        <a class="study-upload__button" href="<?php echo $study_escape($study_login_url); ?>">강의 업로드</a>
        <?php } elseif ($study['boards']) { ?>
        <details class="study-upload__chooser">
            <summary class="study-upload__button">강의 업로드</summary>
            <div class="study-upload__boards"><p>강의를 등록할 게시판을 선택하세요.</p>
                <?php foreach ($study['boards'] as $study_board) { ?>
                <a href="<?php echo $study_escape(G5_BBS_URL.'/write.php?bo_table='.rawurlencode($study_board['bo_table'])); ?>"><?php echo $study_escape($study_board['bo_subject']); ?><i class="bx bx-chevron-right" aria-hidden="true"></i></a>
                <?php } ?>
            </div>
        </details>
        <?php } else { ?><p class="study-upload__unavailable">등록 가능한 강의 게시판이 없습니다.</p><?php } ?>
    </section>
    <p class="study-sidebar__status sound_only" role="status" aria-live="polite"></p>
</aside>
