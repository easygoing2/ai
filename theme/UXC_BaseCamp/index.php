<?php
if (!defined('_INDEX_')) define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_THEME_PATH.'/head.php');

// 편집 모드 확인
$edit_mode = (isset($_GET['mode']) && $_GET['mode'] == 'edit' && $is_admin) ? true : false;

// 위젯 레이아웃 파일 경로
$widget_layout_file = G5_THEME_PATH.'/ui_system/widget-builder/widget-layout.json';
$use_widget_builder = false;
$widget_layout = [];

// 위젯 빌더 레이아웃이 있으면 사용
if (file_exists($widget_layout_file)) {
    $widget_layout = json_decode(file_get_contents($widget_layout_file), true);
    if (!empty($widget_layout['grid_rows'])) {
        $use_widget_builder = true;
    }
}

// 편집 모드일 때 필요한 CSS/JS 추가
if ($edit_mode) {
    echo '<link rel="stylesheet" href="'.G5_THEME_URL.'/ui_system/widget-builder/widget-builder.css">';
}
?>

<!-- Swiper JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<?php if ($edit_mode): ?>
    <!-- 편집 모드일 때 -->    
    <?php include_once(G5_THEME_PATH.'/ui_system/widget-builder/widget-builder.php'); ?>
<?php elseif ($use_widget_builder): ?>
    <!-- 일반 모드일 때 위젯 출력 -->
    <div class="grid-container">
        <?php if (!empty($widget_layout['grid_rows'])): ?>
            <?php foreach ($widget_layout['grid_rows'] as $rowIndex => $row): ?>
            <div class="grid-row">
                <?php foreach ($row['columns'] as $colIndex => $col): ?>
                <div class="grid-col-<?php echo $col['width']; ?>">
                    <?php if (!empty($col['widget'])): ?>
                        <div class="widget-wrapper">
                            <?php 
                            if ($col['widget']['type'] == 'latest') {
                                // 스킨 경로 검증 - 동적으로 스킨 확인
                                $skin_path = G5_THEME_PATH . '/skin/latest/' . str_replace('theme/', '', $col['widget']['skin']);
                                $skin = is_dir($skin_path) ? $col['widget']['skin'] : 'theme/basic';
                                
                                // 멀티 게시판 처리
                                $boards_to_use = '';
                                
                                // 디버깅용 (나중에 제거)
                                if ($is_admin) {
                                    echo "<!-- Widget Data: " . htmlspecialchars(json_encode($col['widget'])) . " -->\n";
                                }
                                
                                if (!empty($col['widget']['boards']) && is_array($col['widget']['boards'])) {
                                    // 배열로 저장된 멀티 게시판
                                    $valid_boards = [];
                                    foreach ($col['widget']['boards'] as $bo) {
                                        if (preg_match('/^[a-zA-Z0-9_]+$/', $bo)) {
                                            $valid_boards[] = $bo;
                                        }
                                    }
                                    $boards_to_use = implode(',', $valid_boards);
                                } else if (!empty($col['widget']['board'])) {
                                    // 이전 버전 호환성 (쉼표로 구분된 문자열 또는 단일 게시판)
                                    $boards_to_use = preg_match('/^[a-zA-Z0-9_,]+$/', $col['widget']['board']) ? $col['widget']['board'] : 'free';
                                } else {
                                    $boards_to_use = 'free';
                                }
                                
                                // 디버깅용 (나중에 제거)
                                if ($is_admin) {
                                    echo "<!-- Boards to use: " . htmlspecialchars($boards_to_use) . " -->\n";
                                }
                                
                                // 숫자 값 검증
                                $rows = max(1, min(20, intval($col['widget']['rows'])));
                                $subject_len = max(10, min(100, intval($col['widget']['subject_len'])));
                                
                                // 타이틀과 더보기 버튼 표시
                                if (!empty($col['widget']['title']) || !empty($col['widget']['show_more'])) {
                                    echo '<div class="widget-header">';
                                    if (!empty($col['widget']['title'])) {
                                        echo '<h3 class="widget-title">' . htmlspecialchars($col['widget']['title']) . '</h3>';
                                    }
                                    if (!empty($col['widget']['show_more'])) {
                                        // 멀티 게시판일 경우 첫 번째 게시판으로 링크
                                        $board_array = explode(',', $boards_to_use);
                                        $first_board = trim($board_array[0]);
                                        // 게시판 테이블명 검증
                                        if (preg_match('/^[a-zA-Z0-9_]+$/', $first_board)) {
                                            echo '<a href="' . G5_BBS_URL . '/board.php?bo_table=' . $first_board . '" class="widget-more">더보기 <i class="bx bx-chevron-right"></i></a>';
                                        }
                                    }
                                    echo '</div>';
                                }
                                
                                // 멀티 게시판 지원 확인
                                if (strpos($boards_to_use, ',') !== false) {
                                    // 멀티 게시판인 경우 latest_all 함수 사용
                                    echo latest_all($skin, $boards_to_use, $rows, $subject_len);
                                } else {
                                    // 단일 게시판인 경우 기존 latest 함수 사용
                                    echo latest($skin, $boards_to_use, $rows, $subject_len);
                                }
                            } else {
                                // 동적 위젯 처리
                                $widget_file = G5_THEME_PATH.'/ui_widget/'.$col['widget']['type'].'.php';
                                if (file_exists($widget_file)) {
                                    // 위젯별 타이틀 처리 (필요한 경우)
                                    if (!empty($col['widget']['title'])) {
                                        echo '<div class="widget-header">';
                                        echo '<h3 class="widget-title">' . htmlspecialchars($col['widget']['title']) . '</h3>';
                                        echo '</div>';
                                    }
                                    include $widget_file;
                                } else {
                                    echo '<div class="widget-error">위젯 파일을 찾을 수 없습니다: ' . htmlspecialchars($col['widget']['type']) . '</div>';
                                }
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if ($is_admin): ?>
        <div class="fb_xy edit-mode">
            <a href="?mode=edit" class="button bg-pr color-wh-only round-s bxicon mBtn">
                <i class='bx bx-edit'></i> 위젯 편집
            </a>
        </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <!-- 위젯 빌더를 사용하지 않을 때 -->
    <div class="grid-container">
        <?php if ($is_admin): ?>
        <div class="fb_xy edit-mode">
            <a href="?mode=edit" class="button bg-pr color-wh round-s bxicon mBtn">
                <i class='bx bx-edit'></i> 위젯 편집
            </a>
        </div>
        <?php else: ?>
        <div class="fb_xy">
            <p>콘텐츠가 준비 중입니다.</p>
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>



<?php
include_once(G5_THEME_PATH.'/tail.php');
?>