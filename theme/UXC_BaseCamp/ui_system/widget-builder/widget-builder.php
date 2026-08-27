<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 이 파일은 index.php에서 편집 모드일 때만 include됩니다

// CSRF 토큰 자동 생성 (토큰이 없으면 생성)
if (empty($_SESSION['ss_token']) || !isset($_SESSION['ss_token'])) {
    $_SESSION['ss_token'] = get_token();
}
?>

<!-- 편집 모드 UI -->
<div class="widget-edit-mode-header">
    <div class="edit-mode-container">
        <div class="edit-mode-info">
            <i class='bx bx-edit-alt'></i>
            <span>위젯 편집 모드</span>
        </div>
        <div class="edit-mode-actions">
            <button type="button" class="button bg-pr color-wh-only round-s bxicon sBtn" id="addGridRow">
                <i class='bx bx-plus'></i> 그리드 행 추가
            </button>
            <button type="button" class="button bg-su color-wh-only round-s bxicon sBtn" id="saveLayout">
                <i class='bx bx-save'></i> 저장
            </button>
            <a href="<?php echo htmlspecialchars(G5_URL); ?>" class="button bg-gr color-wh round-s bxicon sBtn">
                <i class='bx bx-x'></i> 편집 종료
            </a>
        </div>
    </div>
</div>

<!-- 위젯 라이브러리 사이드바 -->
<div class="widget-library-sidebar">
    <h3>위젯 라이브러리</h3>
    <div class="widget-tabs">
        <button class="widget-tab active" data-tab="latest">
            <i class='bx bx-list-ul'></i> 최신글
        </button>
        <button class="widget-tab" data-tab="widget">
            <i class='bx bx-widget'></i> 위젯
        </button>
    </div>
    <div class="widget-tab-content">
        <div class="widget-items active" id="latestWidgetItems" data-tab="latest">
            <!-- 최신글 위젯 동적으로 로드됨 -->
            <div class="widget-loading">
                <i class='bx bx-loader-alt bx-spin'></i>
                <span>최신글 위젯을 불러오는 중...</span>
            </div>
        </div>
        <div class="widget-items" id="customWidgetItems" data-tab="widget">
            <!-- 커스텀 위젯 동적으로 로드됨 -->
            <div class="widget-loading">
                <i class='bx bx-loader-alt bx-spin'></i>
                <span>위젯을 불러오는 중...</span>
            </div>
        </div>
    </div>
</div>

<!-- 그리드 템플릿 모달 -->
<div id="gridTemplateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>그리드 템플릿 선택</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div class="grid-templates">
                <div class="template-item" data-template="100">
                    <div class="template-preview">
                        <div class="col" style="width: 100%">100%</div>
                    </div>
                </div>
                <div class="template-item" data-template="50-50">
                    <div class="template-preview">
                        <div class="col" style="width: 50%">50%</div>
                        <div class="col" style="width: 50%">50%</div>
                    </div>
                </div>
                <div class="template-item" data-template="70-30">
                    <div class="template-preview">
                        <div class="col" style="width: 70%">70%</div>
                        <div class="col" style="width: 30%">30%</div>
                    </div>
                </div>
                <div class="template-item" data-template="30-70">
                    <div class="template-preview">
                        <div class="col" style="width: 30%">30%</div>
                        <div class="col" style="width: 70%">70%</div>
                    </div>
                </div>
                <div class="template-item" data-template="60-40">
                    <div class="template-preview">
                        <div class="col" style="width: 60%">60%</div>
                        <div class="col" style="width: 40%">40%</div>
                    </div>
                </div>
                <div class="template-item" data-template="40-60">
                    <div class="template-preview">
                        <div class="col" style="width: 40%">40%</div>
                        <div class="col" style="width: 60%">60%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 편집 모드에서의 그리드 빌더 영역 -->
<div id="gridBuilderArea" class="grid-container edit-mode">
    <?php if (!empty($widget_layout['grid_rows'])): ?>
        <?php foreach ($widget_layout['grid_rows'] as $rowIndex => $row): ?>
        <div class="grid-row-wrapper" data-row-index="<?php echo (int)$rowIndex; ?>">
            <div class="grid-row-controls">
                <button class="btn-move-up" title="위로 이동">
                    <i class='bx bx-chevron-up'></i>
                </button>
                <button class="btn-move-down" title="아래로 이동">
                    <i class='bx bx-chevron-down'></i>
                </button>
                <button class="btn-edit-row" title="그리드 수정">
                    <i class='bx bx-edit'></i>
                </button>
                <button class="btn-delete-row" title="그리드 삭제">
                    <i class='bx bx-trash'></i>
                </button>
            </div>
            <div class="grid-row">
                <?php foreach ($row['columns'] as $colIndex => $col): ?>
                <div class="grid-col-<?php echo (int)$col['width']; ?> widget-drop-zone" 
                     data-row="<?php echo (int)$rowIndex; ?>" 
                     data-col="<?php echo (int)$colIndex; ?>"
                     <?php if (!empty($col['widget'])): ?>data-widget='<?php echo htmlspecialchars(json_encode($col['widget']), ENT_QUOTES, 'UTF-8'); ?>'<?php endif; ?>>
                    <?php if (!empty($col['widget'])): ?>
                        <!-- 편집 모드에서는 위젯 플레이스홀더 표시 -->
                        <div class="widget-placeholder">
                            <?php if ($col['widget']['type'] === 'latest'): ?>
                                <?php 
                                $skinName = isset($col['widget']['skin']) ? basename($col['widget']['skin']) : '';
                                $displayName = $skinName;
                                ?>
                                <div class="widget-detail-info">
                                    <div class="widget-main-info">
                                        <strong><?php echo !empty($col['widget']['title']) ? htmlspecialchars($col['widget']['title']) : (isset($col['widget']['board']) ? htmlspecialchars($col['widget']['board']) . ' 게시판' : '최신글'); ?></strong>
                                        <span class="widget-type"><?php echo htmlspecialchars($displayName); ?></span>
                                    </div>
                                    <div class="widget-sub-info">
                                        <?php if (isset($col['widget']['boards']) && is_array($col['widget']['boards'])): ?>
                                        <span><i class='bx bx-clipboard'></i> <?php echo count($col['widget']['boards']); ?>개 게시판</span>
                                        <?php elseif (isset($col['widget']['board'])): ?>
                                        <span><i class='bx bx-clipboard'></i> <?php echo htmlspecialchars($col['widget']['board']); ?></span>
                                        <?php endif; ?>
                                        <?php if (isset($col['widget']['rows'])): ?>
                                        <span><i class='bx bx-list-ul'></i> <?php echo (int)$col['widget']['rows']; ?>개</span>
                                        <?php endif; ?>
                                        <?php 
                                        // 여러 게시판 선택시 더보기 버튼 숨김
                                        $boardCount = 0;
                                        if (isset($col['widget']['boards']) && is_array($col['widget']['boards'])) {
                                            $boardCount = count($col['widget']['boards']);
                                        } elseif (isset($col['widget']['board']) && strpos($col['widget']['board'], ',') !== false) {
                                            $boardCount = count(explode(',', $col['widget']['board']));
                                        } elseif (isset($col['widget']['board']) && !empty($col['widget']['board'])) {
                                            $boardCount = 1;
                                        }
                                        
                                        if (!empty($col['widget']['show_more']) && $boardCount <= 1): 
                                        ?>
                                        <span><i class='bx bx-link-external'></i> 더보기</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php
                                // 분산형 위젯 시스템: 개별 widget.json에서 위젯 정보 가져오기
                                $widget_display_name = $col['widget']['type'];
                                $widget_def = null;

                                // 위젯 폴더 경로에서 widget.json 읽기
                                $widget_type = $col['widget']['type'];
                                $widget_folder = explode('/', $widget_type)[0];
                                $widget_json_path = G5_THEME_PATH . '/ui_widget/' . $widget_folder . '/widget.json';

                                if (file_exists($widget_json_path)) {
                                    $widget_json = @file_get_contents($widget_json_path);
                                    if ($widget_json) {
                                        $widget_def = json_decode($widget_json, true);
                                        if ($widget_def && isset($widget_def['name'])) {
                                            $widget_display_name = $widget_def['name'];
                                        }
                                    }
                                }
                                ?>
                                <div class="widget-detail-info">
                                    <div class="widget-main-info">
                                        <strong><?php echo htmlspecialchars($widget_display_name); ?></strong>
                                    </div>

                                    <?php if ($widget_def && isset($widget_def['config']) && isset($col['widget']['widget'])): ?>
                                    <div class="widget-sub-info">
                                        <?php foreach ($widget_def['config'] as $key => $config): ?>
                                            <?php if (isset($col['widget']['widget'][$key])): ?>
                                                <?php
                                                $value = $col['widget']['widget'][$key];
                                                $label = $config['label'];

                                                // select 타입은 label 표시
                                                if ($config['type'] === 'select' && isset($config['options'])) {
                                                    foreach ($config['options'] as $opt) {
                                                        if ($opt['value'] == $value) {
                                                            $display_value = $opt['label'];
                                                            break;
                                                        }
                                                    }
                                                } elseif ($config['type'] === 'number') {
                                                    $display_value = $value . '개';
                                                } elseif ($config['type'] === 'text' && !empty($value)) {
                                                    $display_value = $value;
                                                } else {
                                                    $display_value = null;
                                                }

                                                if ($display_value):
                                                ?>
                                                <span><i class='bx bx-info-circle'></i> <?php echo htmlspecialchars($label); ?>: <?php echo htmlspecialchars($display_value); ?></span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="widget-actions">
                                <button class="btn-widget-edit" onclick="editWidget(<?php echo (int)$rowIndex; ?>, <?php echo (int)$colIndex; ?>)">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button class="btn-widget-remove" onclick="removeWidget(<?php echo (int)$rowIndex; ?>, <?php echo (int)$colIndex; ?>)">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="drop-zone-placeholder">
                            위젯을 여기에 드롭하세요
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class='bx bx-layout'></i>
            <p>그리드를 추가하여 레이아웃을 만들어보세요</p>
            <button type="button" class="button bg-pr color-wh-only round-m mBtn" onclick="document.getElementById('addGridRow').click()">
                첫 그리드 추가하기
            </button>
        </div>
    <?php endif; ?>
</div>

<script>
// 전역 변수 선언
var THEME_URL = '<?php echo G5_THEME_URL; ?>';
var EDIT_MODE = true;

// CSRF 토큰 설정
var ADMIN_TOKEN = '<?php echo isset($_SESSION['ss_token']) ? $_SESSION['ss_token'] : ''; ?>';

// CSRF 토큰 함수
function get_admin_token() {
    return ADMIN_TOKEN;
}
</script>
<!-- jQuery MultiSelect 라이브러리 -->
<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/ui_system/widget-builder/assets/jquery.multiselect.css">
<script src="<?php echo G5_THEME_URL; ?>/ui_system/widget-builder/assets/jquery.multiselect.js"></script>
<script src="<?php echo G5_THEME_URL; ?>/ui_system/widget-builder/widget-builder.js"></script>
<script>
// 게시판 목록 가져오기
$(document).ready(function() {
    // 게시판 목록 로드
    fetch(THEME_URL + '/ui_system/widget-builder/get-boards.php', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': ADMIN_TOKEN
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.availableBoards = data.boards;
            }
        })
        .catch(error => console.error('게시판 목록 로드 실패:', error));
    
    // 스킨 목록 로드
    fetch(THEME_URL + '/ui_system/widget-builder/get-skins.php', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': ADMIN_TOKEN
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    window.availableSkins = data.skins; // 전역 변수에 스킨 목록 저장
                    loadWidgetLibrary(data.skins);
                } else {
                    console.error('스킨 목록 로드 실패:', data.message);
                    // 실패 시 에러 메시지 표시
                    loadDefaultWidgetLibrary();
                }
            } catch (e) {
                console.error('JSON 파싱 오류:', e, 'Response:', text);
                // 파싱 실패 시 에러 메시지 표시
                loadDefaultWidgetLibrary();
            }
        })
        .catch(error => {
            console.error('스킨 목록 로드 실패:', error);
            // 네트워크 오류 시 에러 메시지 표시
            loadDefaultWidgetLibrary();
        });
        
    // 커스텀 위젯 목록 로드
    loadCustomWidgets();
});

// 위젯 라이브러리 로드
function loadWidgetLibrary(skins) {
    const container = document.getElementById('latestWidgetItems');
    container.innerHTML = '';
    
    // 최신글 위젯들
    skins.forEach(skin => {
        const widgetItem = document.createElement('div');
        widgetItem.className = 'widget-item';
        widgetItem.draggable = true;
        widgetItem.setAttribute('data-widget', 'latest');
        widgetItem.setAttribute('data-skin', skin.path);
        
        let iconClass = 'bx-widget';
        if (skin.name.includes('slide')) iconClass = 'bx-image';
        else if (skin.name.includes('Gallery')) iconClass = 'bx-images';
        else if (skin.name.includes('news')) iconClass = 'bx-news';
        else if (skin.name.includes('basic')) iconClass = 'bx-list-ul';
        
        widgetItem.innerHTML = `
            ${skin.screenshot ? `<img src="${skin.screenshot}" alt="${skin.display_name}">` : `<i class='bx ${iconClass}'></i>`}
            <span>${skin.display_name}</span>
        `;
        
        container.appendChild(widgetItem);
    });
    
    // 커스텀 위젯은 별도로 로드
    
    
    // 드래그 이벤트 다시 설정
    setupWidgetDragEvents();
}

// 위젯 드래그 이벤트 설정
function setupWidgetDragEvents() {
    console.log('[디버그] setupWidgetDragEvents 시작');
    const widgetItems = document.querySelectorAll('.widget-item');
    console.log('[디버그] 드래그 이벤트 설정할 위젯 개수:', widgetItems.length);

    document.querySelectorAll('.widget-item').forEach(item => {
        item.addEventListener('dragstart', function(e) {
            console.log('=== [디버그] 위젯 드래그 시작 ===');
            console.log('[디버그] this.dataset.widget:', this.dataset.widget);
            console.log('[디버그] this.dataset.skin:', this.dataset.skin);

            // widget-builder.js에서 전역으로 선언된 draggedWidget 사용
            draggedWidget = {
                type: this.dataset.widget,
                skin: this.dataset.skin
            };

            console.log('[디버그] draggedWidget 설정:', draggedWidget);
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'copy';
        });
        
        item.addEventListener('dragend', function() {
            this.classList.remove('dragging');
        });
    });
}

// 기본 위젯 라이브러리 로드 (오류 시 대체)
function loadDefaultWidgetLibrary() {
    const container = document.getElementById('latestWidgetItems');
    container.innerHTML = `
        <div class="widget-error">
            <i class='bx bx-error-circle'></i>
            <p>스킨 디렉토리를 읽어오지 못했습니다.</p>
        </div>
    `;
}

// 커스텀 위젯 로드 (분산형 시스템)
function loadCustomWidgets() {
    console.log('=== [디버그] loadCustomWidgets 시작 (분산형) ===');
    console.log('[디버그] THEME_URL:', THEME_URL);
    console.log('[디버그] API URL:', THEME_URL + '/ui_system/widget-builder/get-widgets.php');

    fetch(THEME_URL + '/ui_system/widget-builder/get-widgets.php', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': ADMIN_TOKEN
        }
    })
        .then(response => {
            console.log('[디버그] fetch 응답 받음:', response);
            console.log('[디버그] response.ok:', response.ok);
            console.log('[디버그] response.status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('[디버그] JSON 파싱 성공');
            console.log('[디버그] API 응답:', data);

            if (data.success) {
                const widgets = data.widgets;
                console.log('[디버그] 로드된 위젯 개수:', widgets.length);
                console.log('[디버그] 로드된 위젯 목록:', widgets);

                // 전역 변수에 저장
                window.customWidgets = widgets;
                console.log('[디버그] window.customWidgets에 저장 완료');

                // shop_products 위젯 찾기
                const shopWidget = widgets.find(w => w.filename === 'shop_products/widget_shop_products');
                console.log('[디버그] shop_products 위젯 찾기 결과:', shopWidget);
                if (shopWidget) {
                    console.log('[디버그] shop_products.config:', shopWidget.config);
                }

                const container = document.getElementById('customWidgetItems');
                container.innerHTML = '';

                widgets.forEach(widget => {
                    console.log('[디버그] 위젯 아이템 생성:', widget.name, '/', widget.filename);
                    const widgetItem = document.createElement('div');
                    widgetItem.className = 'widget-item';
                    widgetItem.draggable = true;
                    widgetItem.setAttribute('data-widget', widget.filename);
                    widgetItem.innerHTML = `
                        <i class='bx ${widget.icon}'></i>
                        <span>${widget.name}</span>
                    `;
                    container.appendChild(widgetItem);
                });

                console.log('[디버그] 모든 위젯 아이템 생성 완료');

                // 드래그 이벤트 다시 설정
                setupWidgetDragEvents();
                console.log('=== [디버그] loadCustomWidgets 완료 (분산형) ===');
            } else {
                throw new Error(data.message || '위젯 로드 실패');
            }
        })
        .catch(error => {
            console.error('=== [디버그] 위젯 목록 로드 실패 ===');
            console.error('[디버그] 에러 내용:', error);
            const container = document.getElementById('customWidgetItems');
            container.innerHTML = `
                <div class="widget-error">
                    <i class='bx bx-error-circle'></i>
                    <p>위젯 목록을 불러오지 못했습니다.</p>
                    <small>${error.message}</small>
                </div>
            `;
        });
}

// 탭 전환 이벤트
document.querySelectorAll('.widget-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // 모든 탭과 컨텐츠 비활성화
        document.querySelectorAll('.widget-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.widget-items').forEach(c => c.classList.remove('active'));
        
        // 클릭한 탭과 해당 컨텐츠 활성화
        this.classList.add('active');
        const tabName = this.getAttribute('data-tab');
        document.querySelector(`.widget-items[data-tab="${tabName}"]`).classList.add('active');
    });
});
</script>