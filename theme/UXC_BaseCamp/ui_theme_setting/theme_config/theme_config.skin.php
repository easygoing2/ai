<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/ui_theme_setting/theme_config/style.css">', 0);

// cf_1 로고 설정 데이터와 폰트 설정을 JSON으로 마이그레이션하거나 파싱
$logo_config = array();
$font_config = array();
if (!empty($config['cf_1']) && (strpos($config['cf_1'], '{') === 0 || strpos($config['cf_1'], '[') === 0)) {
    // 이미 JSON 형식인 경우
    $cf1_data = json_decode($config['cf_1'], true);
    if (is_array($cf1_data)) {
        // 로고 설정
        $logo_config['logo_icon'] = isset($cf1_data['logo_icon']) ? $cf1_data['logo_icon'] : 'bx-sun';
        $logo_config['logo_text'] = isset($cf1_data['logo_text']) ? $cf1_data['logo_text'] : 'BaseCamp';
        // 폰트 설정
        $font_config['font'] = isset($cf1_data['font']) ? $cf1_data['font'] : 'Pretendard';
    } else {
        $logo_config = array('logo_icon' => 'bx-sun', 'logo_text' => 'BaseCamp');
        $font_config = array('font' => 'Pretendard');
    }
} else {
    // 기존 형식인 경우 마이그레이션
    $logo_config = array(
        'logo_icon' => $config['cf_1'] ?: 'bx-sun',
        'logo_text' => 'BaseCamp'
    );
    $font_config = array('font' => 'Pretendard');
}

// 기본값 설정
$logo_config['logo_icon'] = isset($logo_config['logo_icon']) ? $logo_config['logo_icon'] : 'bx-sun';
$logo_config['logo_text'] = isset($logo_config['logo_text']) ? $logo_config['logo_text'] : 'BaseCamp';
$font_config['font'] = isset($font_config['font']) ? $font_config['font'] : 'Pretendard';

// cf_9 API 설정 JSON 파싱
$api_config = array();
if (!empty($config['cf_9']) && (strpos($config['cf_9'], '{') === 0 || strpos($config['cf_9'], '[') === 0)) {
    // JSON 형식인 경우
    $api_config = json_decode($config['cf_9'], true);
    if (!is_array($api_config)) {
        $api_config = array();
    }
}

// API 설정 기본값
$api_config['kakao_js_key'] = isset($api_config['kakao_js_key']) ? $api_config['kakao_js_key'] : '';
$api_config['gemini_api_key'] = isset($api_config['gemini_api_key']) ? $api_config['gemini_api_key'] : '';

// 현재 컬러셋 값 (JS로 전달)
$current_colorset = (isset($config['cf_3']) && !empty($config['cf_3']) && strpos($config['cf_3'], '.css') !== false) ? $config['cf_3'] : 'default_6f48ff.css';
?>

<body data-theme-url="<?php echo G5_THEME_URL; ?>" data-original-colorset="<?php echo htmlspecialchars($current_colorset); ?>">

<div class="themeConfigWrap">
    <!-- 탭 네비게이션 include -->
    <?php include_once(G5_THEME_PATH.'/ui_theme_setting/theme_tabs.php'); ?>

    <form name="fthemeconfig" id="fthemeconfig" action="<?php echo htmlspecialchars(G5_BBS_URL); ?>/uxc_theme_config.php" method="post" enctype="multipart/form-data" onsubmit="return fthemeconfig_submit(this);">
    <input type="hidden" name="w" value="u">
    <input type="hidden" name="cf_9" id="cf_9" value="">

    <div class="configContent">
        
        <!-- 기본 설정 -->
        <section class="configSection">
            <div class="sectionHeader">
                <h2 class="sectionTitle">
                    <i class='bx bx-layout'></i>
                    아이덴티티 설정
                </h2>
            </div>
            <div class="configBox">
                
                <div class="configItem">
                    <div class="configLabel">
                        <label for="logo_icon">로고 아이콘 선택</label>
                        <span class="configHelp">로고 아이콘을 선택합니다.</span>
                    </div>
                    <div class="configInput">
                        <div class="iconSelector">
                            <input type="hidden" name="logo_icon" id="logo_icon" value="<?php echo htmlspecialchars($logo_config['logo_icon']); ?>">
                            <button type="button" class="iconPreview" onclick="openIconSelector()">
                                <i class="bx <?php echo htmlspecialchars($logo_config['logo_icon']); ?>" id="selectedIcon"></i>
                                <span>아이콘 선택</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="configItem">
                    <div class="configLabel">
                        <label for="logo_text">로고텍스트 입력</label>
                        <span class="configHelp">로고 영역에 나타날 텍스트 입니다.</span>
                    </div>
                    <div class="configInput">
                        <input type="text" name="logo_text" id="logo_text" value="<?php echo htmlspecialchars($logo_config['logo_text']); ?>" class="frm_input full_input" placeholder="로고 텍스트를 입력하세요">
                    </div>
                </div>

                <div class="configItem">
                    <div class="configLabel">
                        <label for="font_select">폰트 선택</label>
                        <span class="configHelp">웹사이트의 폰트를 선택해 주세요.</span>
                    </div>
                    <div class="configInput">
                        <select name="font_select" id="font_select" class="frm_input full_input">
                            <?php
                            $font_dir = G5_THEME_PATH.'/css/font';
                            $font_files = array();
                            
                            if(is_dir($font_dir)) {
                                $dir = opendir($font_dir);
                                while($file = readdir($dir)) {
                                    if(preg_match('/^(.+)\.css$/', $file, $matches)) {
                                        $font_name = $matches[1];
                                        $selected = ($font_config['font'] == $font_name) ? 'selected' : '';
                                        echo '<option value="'.htmlspecialchars($font_name).'" '.$selected.'>'.htmlspecialchars($font_name).'</option>';
                                    }
                                }
                                closedir($dir);
                                
                                // 폰트 이름으로 정렬
                                sort($font_files);
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="configItem">
                    <div class="configLabel">
                        <label for="cf_3">컬러셋 선택</label>
                        <span class="configHelp">웹사이트의 컬러셋을 선택해 주세요.</span>
                    </div>
                    <div class="configInput">
                        <input type="hidden" name="cf_3" id="cf_3" value="<?php echo htmlspecialchars((isset($config['cf_3']) && !empty($config['cf_3']) && strpos($config['cf_3'], '.css') !== false) ? $config['cf_3'] : 'default_6f48ff.css'); ?>">
                        <div class="colorset-grid">
                            <?php
                            // color_set 폴더의 CSS 파일 읽기
                            $colorset_dir = G5_THEME_PATH.'/css/color_set';
                            $colorset_files = array();
                            
                            if(is_dir($colorset_dir)) {
                                $dir = opendir($colorset_dir);
                                while($file = readdir($dir)) {
                                    if(preg_match('/^(.+)_([0-9a-fA-F]{6})\.css$/', $file, $matches)) {
                                        $colorset_files[] = array(
                                            'file' => $file,
                                            'name' => $matches[1],
                                            'color' => '#'.$matches[2]
                                        );
                                    }
                                }
                                closedir($dir);
                            }
                            
                            // 파일명으로 정렬
                            sort($colorset_files);
                            
                            foreach($colorset_files as $colorset) {
                                // $current_colorset은 이미 위에서(라인 124) 정의됨
                                $is_active = ($current_colorset == $colorset['file']) ? 'active' : '';
                                ?>
                                <div class="colorset-item <?php echo $is_active; ?>" onclick="selectColorset('<?php echo htmlspecialchars($colorset['file']); ?>')">
                                    <div class="colorset-chip" style="background-color: <?php echo htmlspecialchars($colorset['color']); ?>;">
                                        <?php if($is_active) echo '<i class="bx bx-check"></i>'; ?>
                                    </div>
                                    <div class="colorset-name"><?php echo htmlspecialchars(ucfirst($colorset['name'])); ?></div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- API 설정 -->
        <section class="configSection">
            <div class="sectionHeader">
                <h2 class="sectionTitle">
                    <i class='bx bx-key'></i>
                    API 설정
                </h2>
            </div>
            <div class="configBox">

                <div class="configItem">
                    <div class="configLabel">
                        <label for="kakao_js_key">카카오 JavaScript 키</label>
                        <span class="configHelp">카카오 맵 등에 사용되는 JavaScript 키입니다.</span>
                    </div>
                    <div class="configInput">
                        <input type="text" name="kakao_js_key" id="kakao_js_key" value="<?php echo htmlspecialchars($api_config['kakao_js_key']); ?>" class="frm_input full_input" placeholder="JavaScript 키를 입력하세요">
                     </div>
                </div>

                <div class="configItem">
                    <div class="configLabel">
                        <label for="gemini_api_key">구글 제미나이 API 키</label>
                        <span class="configHelp">Google Gemini AI API 인증 키를 입력하세요.</span>
                    </div>
                    <div class="configInput">
                        <input type="text" name="gemini_api_key" id="gemini_api_key" value="<?php echo htmlspecialchars($api_config['gemini_api_key']); ?>" class="frm_input full_input" placeholder="Gemini API 키를 입력하세요">
                     </div>
                </div>

            </div>
        </section>

            <!-- 저장 버튼 -->
            <div class="configFooter">
                <button type="submit" class="button mBtn bg-pr color-wh-only round-m bxicon">
                    <i class='bx bx-save'></i>
                    <span class="text">설정 저장</span>
                </button>
                <a href="<?php echo htmlspecialchars(G5_URL); ?>" class="button mBtn bg-gray-200 color-gray-700 round-m bxicon">
                    <i class='bx bx-x'></i>
                    <span class="text">취소</span>
                </a>
        </div>
    </div>
    </form>
</div>

<!-- 아이콘 선택 팝업 -->
<div id="iconSelectorModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>아이콘 선택</h3>
            <button type="button" class="modal-close" onclick="closeIconSelector()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="icon-search">
                <input type="text" id="iconSearch" placeholder="아이콘 검색..." onkeyup="filterIcons()">
            </div>
            <div class="icon-grid" id="iconGrid">
                <?php
                // BoxIcons v2 Regular 아이콘 목록 (카테고리별 정리)
                $icons = [
                    // 비즈니스 & 상업
                    'bx-sun', 'bx-home', 'bx-store', 'bx-store-alt', 'bx-building', 'bx-buildings', 'bx-briefcase', 'bx-briefcase-alt', 'bx-briefcase-alt-2',
                    'bx-cart', 'bx-cart-alt', 'bx-cart-add', 'bx-cart-download', 'bx-shopping-bag', 'bx-basket', 'bx-purchase-tag', 'bx-purchase-tag-alt',
                    'bx-gift', 'bx-package', 'bx-box', 'bx-archive', 'bx-archive-in', 'bx-archive-out',
                    
                    // 사용자 & 계정
                    'bx-user', 'bx-user-plus', 'bx-user-minus', 'bx-user-x', 'bx-user-check', 'bx-user-circle', 'bx-user-pin', 'bx-user-voice',
                    'bx-group', 'bx-id-card', 'bx-face', 'bx-body', 'bx-male', 'bx-female', 'bx-male-female', 'bx-accessibility',
                    
                    // 감정 & 표정
                    'bx-smile', 'bx-laugh', 'bx-happy', 'bx-happy-alt', 'bx-happy-beaming', 'bx-happy-heart-eyes',
                    'bx-sad', 'bx-tired', 'bx-sleepy', 'bx-confused', 'bx-cool', 'bx-meh', 'bx-meh-alt', 'bx-meh-blank',
                    'bx-shocked', 'bx-dizzy', 'bx-angry', 'bx-wink-smile', 'bx-wink-tongue', 'bx-upside-down',
                    
                    // 커뮤니케이션
                    'bx-phone', 'bx-phone-call', 'bx-phone-incoming', 'bx-phone-outgoing', 'bx-phone-off',
                    'bx-envelope', 'bx-envelope-open', 'bx-mail-send', 'bx-at',
                    'bx-chat', 'bx-message', 'bx-message-square', 'bx-message-rounded', 'bx-message-dots', 'bx-message-square-dots', 'bx-message-rounded-dots',
                    'bx-conversation', 'bx-comment', 'bx-comment-dots', 'bx-comment-detail', 'bx-comment-add', 'bx-comment-minus', 'bx-comment-x',
                    
                    // 위치 & 지도
                    'bx-world', 'bx-globe', 'bx-globe-alt', 'bx-planet',
                    'bx-map', 'bx-map-alt', 'bx-map-pin', 'bx-location-plus', 'bx-current-location', 'bx-target-lock', 'bx-navigation', 'bx-compass',
                    
                    // 미디어 & 엔터테인먼트
                    'bx-camera', 'bx-camera-off', 'bx-camera-home', 'bx-camera-movie', 'bx-webcam', 'bx-screenshot',
                    'bx-image', 'bx-image-add', 'bx-image-alt', 'bx-images', 'bx-photo-album', 'bx-landscape',
                    'bx-video', 'bx-video-off', 'bx-video-plus', 'bx-video-recording', 'bx-movie', 'bx-movie-play', 'bx-film',
                    'bx-music', 'bx-headphone', 'bx-volume', 'bx-volume-full', 'bx-volume-low', 'bx-volume-mute', 'bx-speaker',
                    'bx-microphone', 'bx-microphone-off', 'bx-radio', 'bx-podcast', 'bx-play', 'bx-pause', 'bx-stop', 'bx-skip-next', 'bx-skip-previous',
                    
                    // 기기 & 하드웨어
                    'bx-laptop', 'bx-desktop', 'bx-mobile', 'bx-mobile-alt', 'bx-mobile-landscape', 'bx-mobile-vibration',
                    'bx-tab', 'bx-tv', 'bx-devices',
                    'bx-printer', 'bx-scan', 'bx-mouse', 'bx-mouse-alt', 'bx-joystick', 'bx-joystick-alt', 'bx-joystick-button',
                    'bx-usb', 'bx-hdd', 'bx-memory-card', 'bx-chip', 'bx-microchip',
                    
                    // 네트워크 & 연결
                    'bx-wifi', 'bx-wifi-off', 
                    'bx-bluetooth', 'bx-signal-4', 'bx-signal-5',
                    'bx-broadcast', 'bx-radar', 'bx-rss', 'bx-station', 'bx-cast', 'bx-network-chart',
                    
                    // 개발 & 코드
                    'bx-code', 'bx-code-alt', 'bx-code-curly', 'bx-code-block', 'bx-terminal', 'bx-command',
                    'bx-bug', 'bx-bug-alt', 'bx-test-tube', 'bx-git-branch', 'bx-git-merge', 'bx-git-pull-request', 'bx-git-commit', 'bx-git-compare',
                    'bx-bracket', 'bx-braille', 'bx-data', 'bx-server', 'bx-cloud', 'bx-cloud-upload', 'bx-cloud-download', 'bx-cloud-drizzle', 'bx-cloud-lightning', 'bx-cloud-rain', 'bx-cloud-snow',
                    
                    // 파일 & 폴더
                    'bx-folder', 'bx-folder-open', 'bx-folder-plus', 'bx-folder-minus',
                    'bx-file', 'bx-file-blank', 'bx-file-find', 'bx-copy', 'bx-copy-alt', 'bx-duplicate', 'bx-paste',
                    'bx-cabinet', 'bx-archive', 'bx-paperclip',
                    
                    // 도구 & 설정
                    'bx-cog', 'bx-wrench', 'bx-slider', 'bx-slider-alt',
                    'bx-equalizer', 'bx-filter', 'bx-filter-alt', 'bx-sort', 'bx-sort-alt-2', 'bx-sort-up', 'bx-sort-down', 'bx-sort-a-z', 'bx-sort-z-a',
                    
                    // 디자인 & 편집
                    'bx-palette', 'bx-paint', 'bx-paint-roll', 'bx-brush', 'bx-brush-alt', 'bx-spray-can',
                    'bx-pen', 'bx-pencil', 'bx-edit', 'bx-edit-alt', 'bx-eraser', 'bx-highlight',
                    'bx-color-fill', 'bx-font', 'bx-font-color', 'bx-font-family', 'bx-font-size',
                    'bx-text', 'bx-bold', 'bx-italic', 'bx-underline', 'bx-strikethrough', 'bx-align-left', 'bx-align-middle', 'bx-align-right', 'bx-align-justify',
                    
                    // 도형 & 기하학
                    'bx-shape-circle', 'bx-shape-square', 'bx-shape-triangle', 'bx-shape-polygon', 'bx-rectangle', 'bx-circle', 'bx-square', 'bx-square-rounded',
                    'bx-polygon', 'bx-cube', 'bx-cube-alt', 'bx-cuboid', 'bx-cylinder', 'bx-pyramid', 
                    'bx-grid', 'bx-grid-alt', 'bx-grid-horizontal', 'bx-grid-vertical', 'bx-grid-small',
                    
                    // 문서 & 콘텐츠
                    'bx-book', 'bx-book-open', 'bx-book-reader', 'bx-book-bookmark', 'bx-book-content', 'bx-book-add', 'bx-book-alt', 'bx-book-heart',
                    'bx-library', 'bx-bookmarks', 'bx-bookmark', 'bx-bookmark-plus', 'bx-bookmark-minus', 'bx-bookmark-alt', 'bx-bookmark-alt-plus', 'bx-bookmark-alt-minus', 'bx-bookmark-heart',
                    'bx-news', 'bx-note', 'bx-notepad', 'bx-clipboard', 'bx-spreadsheet', 'bx-receipt',
                    
                    // 시간 & 일정
                    'bx-calendar', 'bx-calendar-alt', 'bx-calendar-check', 'bx-calendar-x', 'bx-calendar-plus', 'bx-calendar-minus', 'bx-calendar-edit', 'bx-calendar-event', 'bx-calendar-exclamation', 'bx-calendar-heart', 'bx-calendar-star', 'bx-calendar-week',
                    'bx-time', 'bx-time-five', 'bx-timer', 'bx-alarm', 'bx-alarm-add', 'bx-alarm-off', 'bx-alarm-exclamation', 'bx-alarm-snooze',
                    'bx-stopwatch', 'bx-hourglass', 'bx-history',
                    
                    // 금융 & 화폐
                    'bx-dollar', 'bx-dollar-circle', 'bx-euro', 'bx-pound', 'bx-yen', 'bx-bitcoin', 'bx-lira', 'bx-ruble', 'bx-rupee', 'bx-shekel', 'bx-won',
                    'bx-credit-card', 'bx-credit-card-front', 'bx-credit-card-alt', 'bx-wallet', 'bx-wallet-alt', 'bx-money', 'bx-coin', 'bx-coin-stack', 
                    
                    // 차트 & 분석
                    'bx-chart', 'bx-bar-chart', 'bx-bar-chart-alt', 'bx-bar-chart-alt-2', 'bx-bar-chart-square', 'bx-line-chart', 'bx-line-chart-down',
                    'bx-pie-chart', 'bx-pie-chart-alt', 'bx-pie-chart-alt-2', 'bx-stats', 'bx-trending-up', 'bx-trending-down', 'bx-analyse', 'bx-candles',
                    
                    // 날씨 & 자연
                    'bx-sun', 'bx-moon', 'bx-cloud', 'bx-cloud-drizzle', 'bx-cloud-lightning', 'bx-cloud-light-rain', 'bx-cloud-rain', 'bx-cloud-snow',
                    'bx-wind', 'bx-water', 'bx-droplet', 'bx-leaf', 'bx-meteor',
                    
                    // 교통 & 이동
                    'bx-car', 'bx-bus', 'bx-train', 'bx-taxi', 'bx-walk', 'bx-run', 'bx-swim', 'bx-trip',
                    'bx-sun', 'bx-paper-plane', 'bx-gas-pump', 'bx-traffic-cone', 
                    
                    // 스포츠 & 게임
                    'bx-football', 'bx-basketball', 'bx-baseball', 'bx-tennis-ball', 'bx-cricket-ball', 'bx-bowling-ball', 
                    'bx-dumbbell', 'bx-cycling', 'bx-trophy', 'bx-medal', 'bx-target-lock', 'bx-dice-1', 'bx-dice-2', 'bx-dice-3', 'bx-dice-4', 'bx-dice-5', 'bx-dice-6',
                    
                    // 음식 & 음료
                    'bx-food-menu', 'bx-food-tag', 'bx-dish', 'bx-bowl-hot', 'bx-bowl-rice', 'bx-fridge',
                    'bx-drink', 'bx-coffee', 'bx-coffee-togo', 'bx-wine', 'bx-beer', 'bx-cookie', 'bx-cake', 'bx-baguette', 'bx-cheese', 'bx-popsicle', 'bx-lemon',
                    
                    // 의료 & 건강
                    'bx-first-aid', 'bx-health', 'bx-clinic', 'bx-plus-medical', 'bx-heart', 'bx-heart-circle', 'bx-heart-square',
                    'bx-pulse', 'bx-band-aid', 'bx-capsule', 'bx-injection', 'bx-dna', 'bx-atom', 'bx-test-tube', 'bx-vial', 
                    'bx-donate-heart', 'bx-donate-blood', 'bx-brain',
                    
                    // 보안 & 잠금
                    'bx-shield', 'bx-shield-alt', 'bx-shield-alt-2', 'bx-shield-quarter', 'bx-shield-x', 'bx-check-shield',
                    'bx-lock', 'bx-lock-open', 'bx-lock-alt', 'bx-lock-open-alt', 'bx-key',
                    
                    // 화살표 & 방향
                    'bx-up-arrow', 'bx-down-arrow', 'bx-left-arrow', 'bx-right-arrow', 'bx-up-arrow-alt', 'bx-down-arrow-alt', 'bx-left-arrow-alt', 'bx-right-arrow-alt',
                    'bx-chevron-up', 'bx-chevron-down', 'bx-chevron-left', 'bx-chevron-right', 'bx-chevrons-up', 'bx-chevrons-down', 'bx-chevrons-left', 'bx-chevrons-right',
                    'bx-caret-up', 'bx-caret-down', 'bx-caret-left', 'bx-caret-right', 'bx-arrow-back', 'bx-arrow-from-bottom', 'bx-arrow-from-left', 'bx-arrow-from-right', 'bx-arrow-from-top',
                    'bx-arrow-to-bottom', 'bx-arrow-to-left', 'bx-arrow-to-right', 'bx-arrow-to-top',
                    
                    // 액션 & UI
                    'bx-search', 'bx-search-alt', 'bx-search-alt-2', 'bx-zoom-in', 'bx-zoom-out',
                    'bx-plus', 'bx-plus-circle', 'bx-minus', 'bx-minus-circle', 'bx-x', 'bx-x-circle', 'bx-check', 'bx-check-circle', 'bx-check-double', 'bx-check-square',
                    'bx-menu', 'bx-menu-alt-left', 'bx-menu-alt-right', 'bx-dots-horizontal', 'bx-dots-horizontal-rounded', 'bx-dots-vertical', 'bx-dots-vertical-rounded',
                    'bx-home', 'bx-home-alt', 'bx-home-alt-2', 'bx-home-circle', 'bx-home-heart', 'bx-home-smile',
                    'bx-info-circle', 'bx-info-square', 'bx-help-circle', 'bx-error', 'bx-error-circle', 'bx-error-alt',
                    'bx-bell', 'bx-bell-off', 'bx-bell-plus', 'bx-bell-minus', 'bx-notification', 'bx-notification-off',
                    'bx-log-in', 'bx-log-in-circle', 'bx-log-out', 'bx-log-out-circle', 'bx-power-off',
                    'bx-refresh', 'bx-sync', 'bx-reset', 'bx-loader', 'bx-loader-alt', 'bx-loader-circle',
                    'bx-share', 'bx-share-alt', 'bx-repost', 'bx-export', 'bx-import', 'bx-download', 'bx-upload', 'bx-transfer', 'bx-transfer-alt',
                    'bx-link', 'bx-link-alt', 'bx-link-external', 'bx-unlink', 'bx-anchor',
                    'bx-trash', 'bx-trash-alt', 'bx-recycle', 'bx-save', 'bx-undo', 'bx-redo', 'bx-revision', 'bx-rewind', 'bx-fast-forward',
                    'bx-expand', 'bx-expand-alt', 'bx-collapse', 'bx-fullscreen', 'bx-exit-fullscreen', 'bx-move', 'bx-move-horizontal', 'bx-move-vertical',
                    
                    // 기타 유용한 아이콘
                    'bx-bulb', 'bx-crown', 'bx-badge', 'bx-badge-check', 'bx-award', 'bx-flag',
                    'bx-tag', 'bx-tag-alt', 'bx-label', 'bx-category', 'bx-category-alt',
                    'bx-qr', 'bx-qr-scan', 'bx-barcode', 'bx-barcode-reader', 'bx-id-card',
                    'bx-magnet', 'bx-hard-hat', 'bx-vector', 'bx-selection', 'bx-pointer', 'bx-crosshair',
                    'bx-mask', 'bx-ghost', 'bx-angry', 'bx-bomb', 'bx-game', 'bx-extension', 'bx-plug',
                    'bx-glasses', 'bx-glasses-alt', 'bx-cool', 'bx-abacus', 'bx-blanket', 'bx-bone'
                ];
                
                foreach($icons as $icon) {
                    echo '<div class="icon-item" onclick="selectIcon(\''.$icon.'\')">';
                    echo '<i class="bx '.$icon.'"></i>';
                    echo '<span>'.$icon.'</span>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo G5_THEME_URL; ?>/ui_theme_setting/theme_config/theme_config.js"></script>

</body>