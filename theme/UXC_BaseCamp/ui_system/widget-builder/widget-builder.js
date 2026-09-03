// 위젯 빌더 JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const gridBuilderArea = document.getElementById('gridBuilderArea');
    const addGridRowBtn = document.getElementById('addGridRow');
    const saveLayoutBtn = document.getElementById('saveLayout');
    const gridTemplateModal = document.getElementById('gridTemplateModal');
    const closeModalBtn = document.querySelector('.close');
    
    let currentEditingRow = null;
    
    // 그리드 행 추가 버튼 클릭
    addGridRowBtn.addEventListener('click', function() {
        currentEditingRow = null;
        gridTemplateModal.style.display = 'block';
    });
    
    // 모달 닫기
    closeModalBtn.addEventListener('click', function() {
        gridTemplateModal.style.display = 'none';
    });
    
    // 모달 외부 클릭시 닫기
    window.addEventListener('click', function(e) {
        if (e.target == gridTemplateModal) {
            gridTemplateModal.style.display = 'none';
        }
    });
    
    // 템플릿 선택
    document.querySelectorAll('.template-item').forEach(item => {
        item.addEventListener('click', function() {
            const template = this.dataset.template;
            
            if (currentEditingRow !== null) {
                // 기존 행 수정
                updateGridRow(currentEditingRow, template);
            } else {
                // 새로운 행 추가
                addGridRow(template);
            }
            
            gridTemplateModal.style.display = 'none';
        });
    });
    
    // 그리드 행 추가
    function addGridRow(template) {
        const rowIndex = document.querySelectorAll('.grid-row-wrapper').length;
        const columns = getColumnsFromTemplate(template);
        
        const rowHtml = `
            <div class="grid-row-wrapper" data-row-index="${rowIndex}">
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
                    ${columns.map((col, index) => `
                        <div class="grid-col grid-col-${col} widget-drop-zone" 
                             data-row="${rowIndex}" 
                             data-col="${index}">
                            <div class="drop-zone-placeholder">
                                위젯을 여기에 드롭하세요
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        
        // 빈 상태 메시지 제거
        const emptyState = gridBuilderArea.querySelector('.empty-state');
        if (emptyState) {
            emptyState.remove();
        }
        
        gridBuilderArea.insertAdjacentHTML('beforeend', rowHtml);
        attachRowControls();
        
        // 편집 모드에서 드롭존 이벤트 설정
        if (typeof window.setupDropZones === 'function') {
            window.setupDropZones();
        }
    }
    
    // 그리드 행 수정
    function updateGridRow(rowIndex, template) {
        const rowWrapper = document.querySelector(`.grid-row-wrapper[data-row-index="${rowIndex}"]`);
        const rowElement = rowWrapper.querySelector('.grid-row');
        const columns = getColumnsFromTemplate(template);
        
        // Remove existing columns but keep controls
        const controls = rowWrapper.querySelector('.grid-row-controls');
        rowElement.innerHTML = '';
        if (controls) rowWrapper.insertBefore(controls, rowElement);
        
        // Add new columns
        rowElement.insertAdjacentHTML('beforeend', columns.map((col, index) => `
            <div class="grid-col grid-col-${col} widget-drop-zone" 
                 data-row="${rowIndex}" 
                 data-col="${index}">
                <div class="drop-zone-placeholder">
                    위젯을 여기에 드롭하세요
                </div>
            </div>
        `).join(''));
    }
    
    // 템플릿에서 컬럼 정보 추출
    function getColumnsFromTemplate(template) {
        const templates = {
            '100': ['100'],
            '50-50': ['50', '50'],
            '70-30': ['70', '30'],
            '30-70': ['30', '70'],
            '60-40': ['60', '40'],
            '40-60': ['40', '60']
        };
        
        return templates[template] || ['100'];
    }
    
    // 행 컨트롤 이벤트 연결
    function attachRowControls() {
        // 삭제 버튼
        document.querySelectorAll('.btn-delete-row').forEach(btn => {
            btn.removeEventListener('click', deleteRow);
            btn.addEventListener('click', deleteRow);
        });
        
        // 수정 버튼
        document.querySelectorAll('.btn-edit-row').forEach(btn => {
            btn.removeEventListener('click', editRow);
            btn.addEventListener('click', editRow);
        });
        
        // 위로 이동 버튼
        document.querySelectorAll('.btn-move-up').forEach(btn => {
            btn.removeEventListener('click', moveRowUp);
            btn.addEventListener('click', moveRowUp);
        });
        
        // 아래로 이동 버튼
        document.querySelectorAll('.btn-move-down').forEach(btn => {
            btn.removeEventListener('click', moveRowDown);
            btn.addEventListener('click', moveRowDown);
        });
    }
    
    // 행 삭제
    function deleteRow(e) {
        if (confirm('이 그리드 행을 삭제하시겠습니까?')) {
            const rowElement = e.target.closest('.grid-row-wrapper');
            rowElement.remove();
            
            // 인덱스 재정렬
            updateRowIndexes();
            
            // 빈 상태 확인
            if (gridBuilderArea.querySelectorAll('.grid-row-wrapper').length === 0) {
                gridBuilderArea.innerHTML = `
                    <div class="empty-state">
                        <i class='bx bx-layout'></i>
                        <p>그리드를 추가하여 레이아웃을 만들어보세요</p>
                        <button type="button" class="button bg-pr color-wh round-m mBtn" onclick="document.getElementById('addGridRow').click()">
                            첫 그리드 추가하기
                        </button>
                    </div>
                `;
            }
        }
    }
    
    // 행 수정
    function editRow(e) {
        const rowElement = e.target.closest('.grid-row-wrapper');
        currentEditingRow = rowElement.dataset.rowIndex;
        gridTemplateModal.style.display = 'block';
    }
    
    // 행 위로 이동
    function moveRowUp(e) {
        const rowElement = e.target.closest('.grid-row-wrapper');
        const previousRow = rowElement.previousElementSibling;
        
        if (previousRow && previousRow.classList.contains('grid-row-wrapper')) {
            rowElement.parentNode.insertBefore(rowElement, previousRow);
            updateRowIndexes();
        }
    }
    
    // 행 아래로 이동
    function moveRowDown(e) {
        const rowElement = e.target.closest('.grid-row-wrapper');
        const nextRow = rowElement.nextElementSibling;
        
        if (nextRow && nextRow.classList.contains('grid-row-wrapper')) {
            rowElement.parentNode.insertBefore(nextRow, rowElement);
            updateRowIndexes();
        }
    }
    
    // 행 인덱스 업데이트
    function updateRowIndexes() {
        document.querySelectorAll('.grid-row-wrapper').forEach((row, index) => {
            row.dataset.rowIndex = index;
            row.querySelectorAll('.widget-drop-zone').forEach((zone, colIndex) => {
                zone.dataset.row = index;
                zone.dataset.col = colIndex;
            });
        });
    }
    
    // 레이아웃 저장 함수 (중복 코드 제거)
    function saveLayout() {
        const gridRows = [];
        
        document.querySelectorAll('.grid-row-wrapper').forEach((row) => {
            const columns = [];
            
            row.querySelector('.grid-row').querySelectorAll('[class*="grid-col"]').forEach((col) => {
                const classList = Array.from(col.classList);
                const widthClass = classList.find(c => c.startsWith('grid-col-'));
                const width = widthClass ? widthClass.replace('grid-col-', '') : '100';
                
                // 위젯 데이터 찾기
                let widgetData = null;
                if (col.dataset.widget) {
                    try {
                        widgetData = JSON.parse(col.dataset.widget);
                    } catch (e) {
                        console.error('위젯 데이터 파싱 오류:', e);
                    }
                }
                
                columns.push({
                    width: width,
                    widget: widgetData
                });
            });
            
            gridRows.push({
                columns: columns
            });
        });
        
        const layoutData = {
            grid_rows: gridRows,
            updated_at: new Date().toISOString()
        };
        
        // AJAX로 서버에 저장
        fetch(THEME_URL + '/ui_system/widget-builder/save-layout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': typeof ADMIN_TOKEN !== 'undefined' ? ADMIN_TOKEN : ''
            },
            body: JSON.stringify(layoutData)
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
                    alert('레이아웃이 저장되었습니다.');
                    if (typeof EDIT_MODE !== 'undefined' && EDIT_MODE) {
                        window.location.href = '/';
                    }
                } else {
                    alert('저장 중 오류가 발생했습니다: ' + data.message);
                }
            } catch (e) {
                console.error('JSON 파싱 오류:', e, 'Response:', text);
                alert('저장 중 오류가 발생했습니다: 서버 응답 오류');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('저장 중 오류가 발생했습니다.');
        });
    }
    
    // 레이아웃 저장 버튼 클릭 이벤트
    saveLayoutBtn.addEventListener('click', saveLayout);
    
    // 초기 행 컨트롤 연결
    attachRowControls();
});

// 미리보기 함수
function previewLayout() {
    window.open(THEME_URL + '/../../index.php?preview=1', '_blank');
}

// 스킨 표시 이름 가져오기
function getSkinDisplayName(skinName) {
    // 폴더명 그대로 반환
    return skinName;
}

// 전역 변수로 draggedWidget 선언
let draggedWidget = null;

// 위젯 드래그앤드롭 기능
document.addEventListener('DOMContentLoaded', function() {
    // 편집 모드이거나 위젯빌더 페이지인 경우
    const isWidgetBuilder = window.location.pathname.includes('widget-builder');
    if ((typeof EDIT_MODE !== 'undefined' && EDIT_MODE) || isWidgetBuilder) {
        
        // 현재 레이아웃 데이터 저장 (기존 위젯 정보 유지용)
        fetch(THEME_URL + '/ui_system/widget-builder/widget-layout.json')
            .then(response => response.json())
            .then(data => {
                window.currentLayoutData = data;
            })
            .catch(error => {
                window.currentLayoutData = { grid_rows: [] };
            });
    
    // 초기 드래그 이벤트 설정은 동적 로드 후에 처리됨
    
    // 드롭존 이벤트
    function setupDropZones() {
        document.querySelectorAll('.widget-drop-zone').forEach(zone => {
            zone.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'copy';
                this.classList.add('drag-over');
            });
            
            zone.addEventListener('dragleave', function() {
                this.classList.remove('drag-over');
            });
            
            zone.addEventListener('drop', function(e) {
                console.log('=== [디버그] 드롭 이벤트 발생 ===');
                e.preventDefault();
                this.classList.remove('drag-over');

                console.log('[디버그] draggedWidget 존재 여부:', !!draggedWidget);
                console.log('[디버그] draggedWidget 내용:', draggedWidget);

                if (draggedWidget) {
                    const rowIndex = this.dataset.row;
                    const colIndex = this.dataset.col;

                    console.log('[디버그] rowIndex:', rowIndex);
                    console.log('[디버그] colIndex:', colIndex);
                    console.log('[디버그] showWidgetConfigModal 호출 직전');

                    // 위젯 설정 모달 표시
                    showWidgetConfigModal(draggedWidget, rowIndex, colIndex);

                    console.log('[디버그] showWidgetConfigModal 호출 완료');
                } else {
                    console.log('[디버그] draggedWidget이 null이라 모달을 열 수 없음');
                }
            });
        });
    }
    
    // 위젯 설정 모달
    function showWidgetConfigModal(widget, rowIndex, colIndex, isEdit = false) {
        // 기존 모달이 있으면 제거
        const existingModal = document.querySelector('.widget-config-modal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // 편집 모드가 아닐 때만 draggedWidget 업데이트
        if (!isEdit) {
            draggedWidget = widget;
            } else {
            }
        
        const modal = document.createElement('div');
        modal.className = 'widget-config-modal';
        modal.innerHTML = `
            <div class="modal-backdrop"></div>
            <div class="modal-dialog">
                <div class="modal-header">
                    <h3>위젯 설정</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>위젯 타이틀</label>
                        <input type="text" id="widget-title" class="form-control" placeholder="타이틀을 입력하세요 (선택사항)">
                    </div>
                    <div class="form-group">
                        <label>게시판 선택 (복수 선택 가능)</label>
                        <select id="widget-board" class="form-control" multiple="multiple">
                            ${window.availableBoards ? window.availableBoards.map(board => 
                                `<option value="${board.bo_table}">${board.bo_subject}</option>`
                            ).join('') : '<option value="free">자유게시판</option>'}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>최신글 스킨 선택</label>
                        <select id="widget-skin" class="form-control">
                            <option value="">스킨을 선택하세요</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>출력 개수</label>
                        <input type="number" id="widget-rows" class="form-control" value="5" min="1" max="20">
                    </div>
                    <div class="form-group">
                        <label>제목 길이</label>
                        <input type="number" id="widget-subject-len" class="form-control" value="40" min="10" max="100">
                    </div>
                    <div class="form-group">
                        <div class="opt">
                            <input type="checkbox" id="widget-show-more" name="widget-show-more" checked>
                            <label for="widget-show-more">
                                <span class="text">더보기 버튼 표시</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="button bg-gr color-wh round-m mBtn" onclick="this.closest('.widget-config-modal').remove()">취소</button>
                    <button class="button bg-pr color-wh round-m mBtn" onclick="saveWidgetConfig(${rowIndex}, ${colIndex})">저장</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // 모달 닫기 이벤트
        modal.querySelector('.modal-close').addEventListener('click', () => modal.remove());
        modal.querySelector('.modal-backdrop').addEventListener('click', () => modal.remove());
        
        // 커스텀 위젯인 경우 config schema에 따라 동적으로 생성
        if (widget.type !== 'latest') {
            console.log('=== [디버그] 커스텀 위젯 설정 모달 생성 시작 ===');
            console.log('[디버그] widget 전체 객체:', widget);
            console.log('[디버그] widget.type:', widget.type);
            console.log('[디버그] window.customWidgets 존재 여부:', !!window.customWidgets);
            console.log('[디버그] window.customWidgets 내용:', window.customWidgets);

            // window.customWidgets에서 해당 위젯 찾기
            const widgetData = window.customWidgets ? window.customWidgets.find(w => w.filename === widget.type) : null;

            console.log('[디버그] widgetData 찾기 결과:', widgetData);
            console.log('[디버그] widgetData.config 존재 여부:', widgetData ? !!widgetData.config : 'widgetData가 null');
            console.log('[디버그] widgetData.config 내용:', widgetData ? widgetData.config : 'widgetData가 null');

            let formHTML = '';

            // config schema가 있으면 동적으로 폼 생성
            if (widgetData && widgetData.config) {
                console.log('[디버그] config schema 있음 - 동적 폼 생성 시작');
                console.log('[디버그] config 키 목록:', Object.keys(widgetData.config));

                Object.keys(widgetData.config).forEach(key => {
                    const config = widgetData.config[key];
                    const currentValue = widget.widget && widget.widget[key] !== undefined ? widget.widget[key] : config.default;

                    console.log(`[디버그] 필드 생성: ${key}`, {
                        type: config.type,
                        label: config.label,
                        currentValue: currentValue
                    });

                    formHTML += `<div class="form-group">`;
                    formHTML += `<label>${config.label}</label>`;

                    if (config.type === 'select') {
                        formHTML += `<select id="widget-${key}" class="form-control">`;
                        config.options.forEach(opt => {
                            const selected = currentValue == opt.value ? 'selected' : '';
                            formHTML += `<option value="${opt.value}" ${selected}>${opt.label}</option>`;
                        });
                        formHTML += `</select>`;
                    } else if (config.type === 'number') {
                        const min = config.min !== undefined ? `min="${config.min}"` : '';
                        const max = config.max !== undefined ? `max="${config.max}"` : '';
                        formHTML += `<input type="number" id="widget-${key}" class="form-control" value="${currentValue}" ${min} ${max}>`;
                    } else if (config.type === 'text') {
                        const placeholder = config.placeholder || '';
                        formHTML += `<input type="text" id="widget-${key}" class="form-control" value="${currentValue || ''}" placeholder="${placeholder}">`;
                    }

                    formHTML += `</div>`;
                });

                console.log('[디버그] 생성된 formHTML 길이:', formHTML.length);
                console.log('[디버그] 생성된 formHTML 일부:', formHTML.substring(0, 200));
            } else {
                console.log('[디버그] config schema 없음 - 기본 타이틀 폼만 생성');
                // config schema가 없으면 기본 타이틀만 표시
                formHTML = `
                    <div class="form-group">
                        <label>위젯 타이틀</label>
                        <input type="text" id="widget-title" class="form-control" placeholder="타이틀을 입력하세요 (선택사항)" value="${widget.title || ''}">
                    </div>
                `;
            }

            console.log('[디버그] 최종 formHTML 설정 전');
            modal.querySelector('.modal-body').innerHTML = formHTML;
            console.log('[디버그] modal-body innerHTML 설정 완료');
            console.log('[디버그] 실제 적용된 HTML:', modal.querySelector('.modal-body').innerHTML.substring(0, 200));
            console.log('=== [디버그] 커스텀 위젯 설정 모달 생성 완료 ===');
        } else {
            // 기존 값이 있으면 불러오기
            if (widget.title) document.getElementById('widget-title').value = widget.title;
            if (widget.rows) document.getElementById('widget-rows').value = widget.rows;
            if (widget.subject_len) document.getElementById('widget-subject-len').value = widget.subject_len;
            if (widget.show_more !== undefined) document.getElementById('widget-show-more').checked = widget.show_more;
            
            // 스킨 드롭다운 채우기
            const skinSelect = document.getElementById('widget-skin');
            if (skinSelect && window.availableSkins) {
                skinSelect.innerHTML = '<option value="">스킨을 선택하세요</option>';
                window.availableSkins.forEach(skin => {
                    const option = document.createElement('option');
                    option.value = skin.path;
                    option.textContent = skin.display_name;
                    // 기존 스킨 선택
                    if (widget.skin && widget.skin === skin.path) {
                        option.selected = true;
                    }
                    skinSelect.appendChild(option);
                });
            }
            
            // 멀티셀렉트 초기화
            setTimeout(function() {
                var $select = $('#widget-board');
                
                console.log('Initializing multiselect...');
                console.log('Select element exists:', $select.length > 0);
                console.log('Options count:', $select.find('option').length);
                
                // 기존 값이 있으면 먼저 선택
                if (widget.boards) {
                    console.log('Loading boards:', widget.boards);
                    // boards가 배열인 경우
                    if (Array.isArray(widget.boards)) {
                        widget.boards.forEach(function(board) {
                            $select.find('option[value="' + board + '"]').prop('selected', true);
                        });
                    }
                } else if (widget.board) {
                    console.log('Loading board (legacy):', widget.board);
                    // 이전 버전 호환성 (단일 게시판)
                    if (widget.board.includes(',')) {
                        // 쉼표로 구분된 경우
                        var boards = widget.board.split(',');
                        boards.forEach(function(board) {
                            $select.find('option[value="' + board.trim() + '"]').prop('selected', true);
                        });
                    } else {
                        $select.find('option[value="' + widget.board + '"]').prop('selected', true);
                    }
                }
                
                // 이미 초기화되어 있으면 destroy
                if ($select.next('.ms-options-wrap').length > 0) {
                    console.log('Removing existing multiselect wrapper');
                    $select.next('.ms-options-wrap').remove();
                }
                
                // 새로 초기화
                try {
                    $select.multiselect({
                        placeholder: '게시판을 선택하세요',
                        selectAll: true,
                        minHeight: 200,
                        maxHeight: 300,
                        showCheckbox: true,
                        onLoad: function(element) {
                            console.log('MultiSelect loaded successfully');
                        },
                        afterSelect: function(values) {
                            // 선택된 게시판 개수 확인
                            var selectedCount = $select.find('option:selected').length;
                            var $showMoreCheckbox = $('#widget-show-more');
                            var $showMoreLabel = $('label[for="widget-show-more"]');
                            
                            if (selectedCount > 1) {
                                // 여러 게시판 선택시 더보기 버튼 비활성화
                                $showMoreCheckbox.prop('checked', false);
                                $showMoreCheckbox.prop('disabled', true);
                                $showMoreLabel.css('opacity', '0.5');
                                $showMoreLabel.attr('title', '여러 게시판 선택시 더보기 버튼을 사용할 수 없습니다');
                            } else {
                                // 단일 게시판 선택시 더보기 버튼 활성화
                                $showMoreCheckbox.prop('disabled', false);
                                $showMoreLabel.css('opacity', '1');
                                $showMoreLabel.removeAttr('title');
                            }
                        },
                        afterDeselect: function(values) {
                            // 선택 해제시에도 동일한 로직 적용
                            var selectedCount = $select.find('option:selected').length;
                            var $showMoreCheckbox = $('#widget-show-more');
                            var $showMoreLabel = $('label[for="widget-show-more"]');
                            
                            if (selectedCount > 1) {
                                $showMoreCheckbox.prop('checked', false);
                                $showMoreCheckbox.prop('disabled', true);
                                $showMoreLabel.css('opacity', '0.5');
                                $showMoreLabel.attr('title', '여러 게시판 선택시 더보기 버튼을 사용할 수 없습니다');
                            } else {
                                $showMoreCheckbox.prop('disabled', false);
                                $showMoreLabel.css('opacity', '1');
                                $showMoreLabel.removeAttr('title');
                            }
                        }
                    });
                    console.log('Multiselect initialized');
                    
                    // 초기 로드시 체크
                    var selectedCount = $select.find('option:selected').length;
                    if (selectedCount > 1) {
                        $('#widget-show-more').prop('checked', false).prop('disabled', true);
                        $('label[for="widget-show-more"]').css('opacity', '0.5').attr('title', '여러 게시판 선택시 더보기 버튼을 사용할 수 없습니다');
                    }
                } catch(e) {
                    console.error('Error initializing multiselect:', e);
                }
            }, 300);
        }
    }
    
    // 위젯 설정 저장
    window.saveWidgetConfig = function(rowIndex, colIndex) {
        const modal = document.querySelector('.widget-config-modal');
        const skinSelect = document.getElementById('widget-skin');
        const selectedSkin = skinSelect ? skinSelect.value : draggedWidget.skin;
        
        const widgetConfig = {
            type: draggedWidget.type,
            skin: selectedSkin || draggedWidget.skin  // 선택된 스킨 사용, 없으면 원래 스킨
        };
        
        if (draggedWidget.type === 'latest') {
            // 멀티 선택된 게시판 가져오기
            const $select = $('#widget-board');
            const selectedBoards = [];
            
            // 선택된 옵션들을 수동으로 가져오기
            $select.find('option:selected').each(function() {
                selectedBoards.push($(this).val());
            });
            
            console.log('Selected boards:', selectedBoards);
            
            widgetConfig.boards = selectedBoards; // 배열로 저장
            widgetConfig.board = selectedBoards.length > 0 ? selectedBoards.join(',') : ''; // 쉼표로 구분된 문자열로도 저장 (호환성)
            widgetConfig.rows = document.getElementById('widget-rows').value;
            widgetConfig.subject_len = document.getElementById('widget-subject-len').value;
            widgetConfig.title = document.getElementById('widget-title').value;
            
            // 여러 게시판 선택시 더보기 버튼 강제로 false
            if (selectedBoards.length > 1) {
                widgetConfig.show_more = false;
            } else {
                widgetConfig.show_more = document.getElementById('widget-show-more').checked;
            }
        } else {
            console.log('=== [디버그] 커스텀 위젯 config 저장 시작 ===');
            console.log('[디버그] draggedWidget.type:', draggedWidget.type);

            // 커스텀 위젯 config 저장
            const widgetData = window.customWidgets ? window.customWidgets.find(w => w.filename === draggedWidget.type) : null;

            console.log('[디버그] widgetData 찾기 결과:', widgetData);

            if (widgetData && widgetData.config) {
                console.log('[디버그] config schema 있음 - 값 수집 시작');
                console.log('[디버그] config 키 목록:', Object.keys(widgetData.config));

                // config schema가 있으면 동적으로 값 수집
                widgetConfig.widget = {};
                Object.keys(widgetData.config).forEach(key => {
                    const input = document.getElementById(`widget-${key}`);
                    console.log(`[디버그] 필드 값 읽기: ${key}`, {
                        inputExists: !!input,
                        value: input ? input.value : 'input 없음'
                    });

                    if (input) {
                        widgetConfig.widget[key] = input.value;
                    }
                });

                console.log('[디버그] 수집된 widgetConfig.widget:', widgetConfig.widget);
            } else {
                console.log('[디버그] config schema 없음 - 기본 타이틀만 저장');
                // config schema가 없으면 기본 타이틀만 저장
                const titleInput = document.getElementById('widget-title');
                if (titleInput) {
                    widgetConfig.title = titleInput.value;
                }
            }

            console.log('[디버그] 최종 widgetConfig:', widgetConfig);
            console.log('=== [디버그] 커스텀 위젯 config 저장 완료 ===');
        }
        
        // 드롭존에 위젯 표시
        const dropZone = document.querySelector(`.widget-drop-zone[data-row="${rowIndex}"][data-col="${colIndex}"]`);
        
        // 위젯 정보 생성
        let widgetInfo = '';
        if (draggedWidget.type !== 'latest') {
            // 커스텀 위젯 정보 표시
            const widgetData = window.customWidgets ? window.customWidgets.find(w => w.filename === draggedWidget.type) : null;
            let defaultTitle = draggedWidget.type;

            if (widgetData) {
                defaultTitle = widgetData.name;

                // config가 있고 widget 설정값이 있으면 상세 정보 표시
                if (widgetData.config && widgetConfig.widget) {
                    let detailInfo = [];

                    Object.keys(widgetData.config).forEach(key => {
                        const config = widgetData.config[key];
                        const value = widgetConfig.widget[key];

                        if (config.type === 'select' && config.options) {
                            const option = config.options.find(opt => opt.value == value);
                            if (option) {
                                detailInfo.push(`${config.label}: ${option.label}`);
                            }
                        } else if (config.type === 'number') {
                            detailInfo.push(`${config.label}: ${value}개`);
                        } else if (config.type === 'text' && value) {
                            detailInfo.push(`${config.label}: ${value}`);
                        }
                    });

                    widgetInfo = `
                        <div class="widget-detail-info">
                            <div class="widget-main-info">
                                <strong>${defaultTitle}</strong>
                            </div>
                            ${detailInfo.length > 0 ? `<div class="widget-sub-info">${detailInfo.map(info => `<span>${info}</span>`).join('')}</div>` : ''}
                        </div>
                    `;
                } else {
                    widgetInfo = `<div class="widget-detail-info"><strong>${widgetConfig.title || defaultTitle}</strong></div>`;
                }
            } else {
                widgetInfo = `<div class="widget-detail-info"><strong>${widgetConfig.title || defaultTitle}</strong></div>`;
            }
        } else {
            const skinName = widgetConfig.skin ? widgetConfig.skin.split('/').pop() : '';
            
            // 멀티 게시판 이름 처리
            let boardNames = [];
            if (widgetConfig.boards && Array.isArray(widgetConfig.boards)) {
                widgetConfig.boards.forEach(function(boardTable) {
                    const boardInfo = window.availableBoards ? 
                        window.availableBoards.find(b => b.bo_table === boardTable) : null;
                    boardNames.push(boardInfo ? boardInfo.bo_subject : boardTable);
                });
            } else if (widgetConfig.board) {
                // 이전 버전 호환성
                const boardInfo = window.availableBoards ? 
                    window.availableBoards.find(b => b.bo_table === widgetConfig.board) : null;
                boardNames.push(boardInfo ? boardInfo.bo_subject : widgetConfig.board);
            }
            
            const boardNameDisplay = boardNames.length > 0 ? boardNames.join(', ') : '게시판 미선택';
            const boardCount = boardNames.length;
            
            widgetInfo = `
                <div class="widget-detail-info">
                    <div class="widget-main-info">
                        <strong>${widgetConfig.title || boardNameDisplay}</strong>
                        <span class="widget-type">${getSkinDisplayName(skinName)}</span>
                    </div>
                    <div class="widget-sub-info">
                        <span><i class='bx bx-clipboard'></i> ${boardCount}개 게시판</span>
                        <span title="${boardNameDisplay}"><i class='bx bx-list-ul'></i> ${widgetConfig.rows}개</span>
                        ${widgetConfig.show_more ? '<span><i class="bx bx-link-external"></i> 더보기</span>' : ''}
                    </div>
                </div>
            `;
        }
        
        dropZone.innerHTML = `
            <div class="widget-placeholder">
                ${widgetInfo}
                <div class="widget-actions">
                    <button class="btn-widget-edit" onclick="editWidget(${rowIndex}, ${colIndex})">
                        <i class='bx bx-edit'></i>
                    </button>
                    <button class="btn-widget-remove" onclick="removeWidget(${rowIndex}, ${colIndex})">
                        <i class='bx bx-trash'></i>
                    </button>
                </div>
            </div>
        `;
        
        // 위젯 정보를 데이터 속성에 저장
        dropZone.dataset.widget = JSON.stringify(widgetConfig);
        
        modal.remove();
    };
    
    // 위젯 제거
    window.removeWidget = function(rowIndex, colIndex) {
        if (confirm('이 위젯을 제거하시겠습니까?')) {
            const dropZone = document.querySelector(`.widget-drop-zone[data-row="${rowIndex}"][data-col="${colIndex}"]`);
            dropZone.innerHTML = '<div class="drop-zone-placeholder">위젯을 여기에 드롭하세요</div>';
            delete dropZone.dataset.widget;
        }
    };
    
    // 위젯 편집
    window.editWidget = function(rowIndex, colIndex) {
        const dropZone = document.querySelector(`.widget-drop-zone[data-row="${rowIndex}"][data-col="${colIndex}"]`);
        let widgetData;
        try {
            widgetData = JSON.parse(dropZone.dataset.widget);
        } catch (e) {
            console.error('위젯 데이터 파싱 오류:', e);
            return;
        }
        // 편집 시에는 기존 데이터를 draggedWidget에 설정
        draggedWidget = widgetData;
        showWidgetConfigModal(widgetData, rowIndex, colIndex, true); // isEdit = true
    };
    
    
    // 초기 드롭존 설정
    setupDropZones();
    
    // setupDropZones를 전역에서 접근 가능하도록 설정
    window.setupDropZones = setupDropZones;
    
    // 동적으로 추가되는 드롭존 처리 
    const gridBuilderAreaForObserver = document.querySelector('#gridBuilderArea, .grid-builder-area');
    if (gridBuilderAreaForObserver) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    setupDropZones();
                }
            });
        });
        
        observer.observe(gridBuilderAreaForObserver, {
            childList: true,
            subtree: true
        });
    }
    }
});