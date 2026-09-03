<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/ui_theme_setting/theme_slider/style.css">', 0);

// DB 테이블 존재 여부 확인
$table_exists = false;
$sql = " SHOW TABLES LIKE 'g5_theme_slider' ";
$result = sql_query($sql, false);
if($row = sql_fetch_array($result)) {
    $table_exists = true;
}
?>

<div class="sliderAdminWrap">
    <!-- 탭 네비게이션 include -->
    <?php include_once(G5_THEME_PATH.'/ui_theme_setting/theme_tabs.php'); ?>

    <?php if(!$table_exists) { ?>
    <!-- 테이블 생성 섹션 -->
    <div class="adminContent">
        <section class="adminSection">
            <div class="createTableBox">
                <div class="createIcon">
                    <i class='bx bx-data'></i>
                </div>
                <h3 class="createTitle">데이터베이스 테이블 생성 필요</h3>
                <p class="createDesc">
                    슬라이더 기능을 사용하려면 먼저 데이터베이스 테이블을 생성해야 합니다.
                </p>
                <button type="button" onclick="createSliderTable()" class="button mBtn bg-pr color-wh-only round-m bxicon">
                    <i class='bx bx-data'></i>
                    <span class="text">테이블 생성하기</span>
                </button>
            </div>
        </section>
    </div>
    <?php } else { 
        // 슬라이더 목록 조회
        $sql = " SELECT * FROM g5_theme_slider ORDER BY slide_order ASC, slide_id DESC ";
        $result = sql_query($sql);
    ?>
    
    <div class="adminContent">
        <!-- 슬라이더 헤더 -->
        <div class="sliderHeader">
            <div class="headerInfo">
                <h2 class="headerTitle">슬라이더 관리</h2>
                <p class="headerDesc">
                    메인 페이지에 표시되는 슬라이더를 관리합니다.
                </p>
            </div>
            <div class="headerActions">
                <button type="button" onclick="openSliderModal()" class="button mBtn bg-pr color-wh-only round-m bxicon">
                    <i class='bx bx-plus'></i>
                    <span class="text">슬라이더 추가</span>
                </button>
                <?php if($table_exists) { ?>
                <button type="button" onclick="openDeleteTableModal()" class="button mBtn bg-danger color-wh-only round-m bxicon" data-removedata="off" style="margin-right: -110px;">
                    <i class='bx bx-trash'></i>
                    <span class="text">테이블 삭제</span>
                </button>
                <?php } ?>
            </div>
        </div>
        
        <!-- 슬라이더 목록 -->
        <?php if(sql_num_rows($result) > 0) { 
            $total_count = sql_num_rows($result);
        ?>
        <div class="sliderList">
            <?php 
            $i = 0;
            while($row = sql_fetch_array($result)) { 
                $i++;
            ?>
            <div class="sliderItem" id="slider_<?php echo (int)$row['slide_id']; ?>">
                <div class="itemNumber"><?php echo $i; ?></div>
                <div class="itemPreview">
                    <?php if($row['slide_image']) { ?>
                        <img src="<?php echo G5_DATA_URL; ?>/mainkv/<?php echo htmlspecialchars($row['slide_image']); ?>" alt="">
                    <?php } else { ?>
                        <div class="noImage">
                            <i class='bx bx-image'></i>
                        </div>
                    <?php } ?>
                </div>
                <div class="itemContent">
                    <div class="contentHeader">
                        <h4 class="contentTitle"><?php echo $row['slide_title'] ? get_text($row['slide_title']) : '제목 없음'; ?></h4>
                        <span class="statusBadge <?php echo $row['slide_use'] ? 'active' : 'inactive'; ?>">
                            <?php echo $row['slide_use'] ? '사용중' : '미사용'; ?>
                        </span>
                    </div>
                    <p class="contentDesc"><?php echo $row['slide_subtitle'] ? get_text(cut_str(strip_tags($row['slide_subtitle']), 100)) : '설명 텍스트가 없습니다.'; ?></p>
                    <div class="contentMeta">
                        <?php if($row['slide_link']) { ?>
                        <div class="metaItem">
                            <i class='bx bx-link'></i>
                            <span><?php echo htmlspecialchars(cut_str($row['slide_link'], 40)); ?></span>
                        </div>
                        <?php } ?>
                        <?php if($row['slide_button']) { ?>
                        <div class="metaItem">
                            <i class='bx bx-mouse'></i>
                            <span>버튼: <?php echo get_text($row['slide_button']); ?></span>
                        </div>
                        <?php } ?>
                        <div class="metaItem">
                            <i class='bx bx-palette'></i>
                            <span>텍스트: <?php echo htmlspecialchars($row['text_color']) == 'white' ? '흰색' : '검정색'; ?></span>
                        </div>
                        <div class="metaItem">
                            <i class='bx bx-square-rounded'></i>
                            <span>버튼: <?php echo htmlspecialchars($row['button_color']) == 'white' ? '흰색' : (htmlspecialchars($row['button_color']) == 'black' ? '검정색' : '주색상'); ?></span>
                        </div>
                        <?php if(isset($row['click_count']) && $row['click_count'] > 0) { ?>
                        <div class="metaItem">
                            <i class='bx bx-mouse-alt'></i>
                            <span style="color:var(--ui-color-info);">클릭: <?php echo number_format($row['click_count']); ?>회</span>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="itemActions">
                    <div class="orderControl">
                        <?php if($i > 1) { ?>
                        <button type="button" onclick="moveSlider(<?php echo (int)$row['slide_id']; ?>, 'up')" class="orderBtn" title="위로">
                            <i class='bx bx-chevron-up'></i>
                        </button>
                        <?php } ?>
                        <span class="orderNumber"><?php echo (int)$row['slide_order']; ?></span>
                        <?php if($i < $total_count) { ?>
                        <button type="button" onclick="moveSlider(<?php echo (int)$row['slide_id']; ?>, 'down')" class="orderBtn" title="아래로">
                            <i class='bx bx-chevron-down'></i>
                        </button>
                        <?php } ?>
                    </div>
                    <div class="actionButtons">
                        <button type="button" onclick="editSlider(<?php echo (int)$row['slide_id']; ?>)" class="actionBtn edit">
                            <i class='bx bx-edit'></i> 수정
                        </button>
                        <button type="button" onclick="deleteSlider(<?php echo (int)$row['slide_id']; ?>)" class="actionBtn delete">
                            <i class='bx bx-trash'></i> 삭제
                        </button>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <div class="emptyState">
            <div class="emptyIcon">
                <i class='bx bx-slideshow'></i>
            </div>
            <h3 class="emptyTitle">아직 등록된 슬라이더가 없습니다</h3>
            <p class="emptyDesc">메인 페이지에 표시할 슬라이더를 추가해보세요.</p>
        </div>
        <?php } ?>
    </div>
    <?php } ?>
</div>

<!-- 슬라이더 편집 모달 -->
<div id="sliderModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">슬라이더 추가</h3>
            <button type="button" class="modal-close" onclick="closeSliderModal()">&times;</button>
        </div>
        <div class="modal-body-wrapper">
            <div class="modal-body">
                <form id="sliderForm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="mode" value="add">
                <input type="hidden" name="slide_id" value="">
                
                <div class="formGroup">
                    <label class="formLabel required">슬라이더 이미지</label>
                    <div class="imageUploadBox">
                        <div class="imagePreview" id="imagePreview">
                            <i class='bx bx-image'></i>
                            <span>이미지를 선택하세요</span>
                        </div>
                        <input type="file" name="slide_image" id="slideImage" accept="image/*" onchange="previewImage(this)">
                        <label for="slideImage" class="button mBtn shadowline-de round-m bxicon">
                            <i class='bx bx-upload'></i>
                            <span class="text">이미지 선택</span>
                        </label>
                        <p class="helpText">권장 크기: 1040x200px, 최대 2MB</p>
                    </div>
                </div>
                
                <div class="formGroup">
                    <label for="slide_title" class="formLabel">메인 텍스트</label>
                    <input type="text" name="slide_title" id="slide_title" class="formInput" placeholder="메인 타이틀을 입력하세요">
                </div>
                
                <div class="formGroup">
                    <label for="slide_subtitle" class="formLabel">서브 텍스트</label>
                    <textarea name="slide_subtitle" id="slide_subtitle" class="formTextarea" rows="3" placeholder="서브 텍스트를 입력하세요"></textarea>
                </div>
                
                <div class="formRow">
                    <div class="formGroup">
                        <label for="slide_button" class="formLabel">버튼 텍스트</label>
                        <input type="text" name="slide_button" id="slide_button" class="formInput" placeholder="자세히 보기">
                    </div>
                    
                    <div class="formGroup">
                        <label for="slide_link" class="formLabel">버튼 링크 URL</label>
                        <input type="text" name="slide_link" id="slide_link" class="formInput" placeholder="https://">
                    </div>
                </div>
                
                <div class="formRow">
                    <div class="formGroup">
                        <label class="formLabel">링크 타겟</label>
                        <div class="optBox fb_ey">
                            <div class="opt">
                                <input type="radio" id="target_self" name="slide_link_target" value="_self" checked>
                                <label for="target_self"><span class="text">현재창</span></label>
                            </div>
                            <div class="opt">
                                <input type="radio" id="target_blank" name="slide_link_target" value="_blank">
                                <label for="target_blank"><span class="text">새창</span></label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="formGroup">
                        <!-- 빈 공간 또는 다른 옵션 -->
                    </div>
                </div>
                
                <div class="formRow">
                    <div class="formGroup">
                        <label class="formLabel">텍스트 색상</label>
                        <div class="optBox fb_ey">
                            <div class="opt">
                                <input type="radio" id="text_white" name="text_color" value="white" checked>
                                <label for="text_white"><span class="text">흰색</span></label>
                            </div>
                            <div class="opt">
                                <input type="radio" id="text_black" name="text_color" value="black">
                                <label for="text_black"><span class="text">검정색</span></label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="formGroup">
                        <label class="formLabel">버튼 색상</label>
                        <div class="colorButtonGroup">
                            <div class="colorButtonItem">
                                <input type="radio" id="button_white" name="button_color" value="white">
                                <label for="button_white" class="colorButton white" title="흰색">
                                    <span class="colorPreview"></span>
                                </label>
                            </div>
                            <div class="colorButtonItem">
                                <input type="radio" id="button_black" name="button_color" value="black">
                                <label for="button_black" class="colorButton black" title="검정색">
                                    <span class="colorPreview"></span>
                                </label>
                            </div>
                            <div class="colorButtonItem">
                                <input type="radio" id="button_primary" name="button_color" value="primary" checked>
                                <label for="button_primary" class="colorButton primary" title="주 색상">
                                    <span class="colorPreview"></span>
                                </label>
                            </div>
                            <div class="colorButtonItem">
                                <input type="radio" id="button_pink" name="button_color" value="pink">
                                <label for="button_pink" class="colorButton pink" title="핑크">
                                    <span class="colorPreview"></span>
                                </label>
                            </div>
                            <div class="colorButtonItem">
                                <input type="radio" id="button_purple" name="button_color" value="purple">
                                <label for="button_purple" class="colorButton purple" title="보라">
                                    <span class="colorPreview"></span>
                                </label>
                            </div>
                            <div class="colorButtonItem">
                                <input type="radio" id="button_yellow" name="button_color" value="yellow">
                                <label for="button_yellow" class="colorButton yellow" title="노랑">
                                    <span class="colorPreview"></span>
                                </label>
                            </div>
                            <div class="colorButtonItem">
                                <input type="radio" id="button_green" name="button_color" value="green">
                                <label for="button_green" class="colorButton green" title="초록">
                                    <span class="colorPreview"></span>
                                </label>
                            </div>
                            <div class="colorButtonItem">
                                <input type="radio" id="button_mint" name="button_color" value="mint">
                                <label for="button_mint" class="colorButton mint" title="민트">
                                    <span class="colorPreview"></span>
                                </label>
                            </div>
                            <div class="colorButtonItem">
                                <input type="radio" id="button_lightblue" name="button_color" value="lightblue">
                                <label for="button_lightblue" class="colorButton lightblue" title="연파랑">
                                    <span class="colorPreview"></span>
                                </label>
                            </div>
                            <div class="colorButtonItem">
                                <input type="radio" id="button_blue" name="button_color" value="blue">
                                <label for="button_blue" class="colorButton blue" title="파랑">
                                    <span class="colorPreview"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="formRow">
                    <div class="formGroup">
                        <label for="slide_order" class="formLabel">표시 순서</label>
                        <input type="number" name="slide_order" id="slide_order" class="formInput" value="0" min="0">
                        <p class="helpText">숫자가 작을수록 먼저 표시됩니다</p>
                    </div>
                    
                    <div class="formGroup">
                        <label class="formLabel">사용 여부</label>
                        <div class="switch">
                            <input type="checkbox" name="slide_use" id="slide_use" value="1" checked class="switch_input">
                            <label for="slide_use" class="switch_label" data-on="ON" data-off="OFF"></label>
                            <span class="switch_handle"></span>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
        <div class="modalFooter">
            <button type="button" id="sliderSaveBtn" onclick="saveSlider()" class="button mBtn bg-pr color-wh-only round-m bxicon">
                <i class='bx bx-save'></i>
                <span class="text">저장</span>
            </button>
            <button type="button" onclick="closeSliderModal()" class="button mBtn shadowline-de round-m">
                취소
            </button>
        </div>
    </div>
</div>

<!-- 테이블 삭제 확인 팝업 -->
<div id="deleteTableModal" class="multiModal">
    <div class="modalBox w400">
        <div class="mbHeader">
            <div class="title">
                <h2><i class='bx bx-error-circle'></i> 경고</h2>
            </div>
        </div>
        <div class="mbBody" style="padding:30px; text-align:center;">
            <div style="margin-bottom:20px;">
                <i class='bx bx-error' style="font-size:60px; color:var(--ui-color-danger);"></i>
            </div>
            <h3 style="margin-bottom:10px; color:var(--ui-color-gray-900); font-size:18px;">
                정말 슬라이더 테이블을 삭제하시겠습니까?
            </h3>
            <p style="color:var(--ui-color-gray-600); font-size:14px; line-height:1.5; margin-bottom:30px;">
                모든 슬라이더 데이터가 영구적으로 삭제됩니다.<br>
                이 작업은 되돌릴 수 없습니다.
            </p>
            <div class="buttonWrap" style="display:flex; gap:10px; justify-content:center;">
                <button type="button" onclick="closeDeleteTableModal()" class="button mBtn shadowline-de round-m">
                    아니오
                </button>
                <button type="button" onclick="deleteSliderTable()" class="button mBtn bg-danger color-wh-only round-m">
                    <i class='bx bx-trash'></i> 삭제하기
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// 테이블 생성
function createSliderTable() {
    if(!confirm('슬라이더 테이블을 생성하시겠습니까?')) return;
    
    fetch('<?php echo htmlspecialchars(G5_BBS_URL); ?>/uxc_theme_slider.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=create_slider_table'
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('테이블이 생성되었습니다.');
            location.reload();
        } else {
            alert(data.message || '테이블 생성에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('오류가 발생했습니다.');
    });
}

// 슬라이더 모달 열기
function openSliderModal(slideId = null) {
    const modal = document.getElementById('sliderModal');
    const form = document.getElementById('sliderForm');
    const modalTitle = document.getElementById('modalTitle');
    
    // 폼 초기화
    form.reset();
    document.getElementById('imagePreview').innerHTML = '<i class="bx bx-image"></i><span>이미지를 선택하세요</span>';
    
    if(slideId) {
        // 수정 모드
        modalTitle.textContent = '슬라이더 수정';
        form.mode.value = 'edit';
        form.slide_id.value = slideId;
        
        // 데이터 로드
        fetch('<?php echo htmlspecialchars(G5_BBS_URL); ?>/uxc_theme_slider.php?action=get_slider&slide_id=' + slideId)
            .then(response => response.json())
            .then(data => {
                if(data.success && data.slider) {
                    const slider = data.slider;
                    form.slide_title.value = slider.slide_title || '';
                    form.slide_subtitle.value = slider.slide_subtitle || '';
                    form.slide_button.value = slider.slide_button || '';
                    form.slide_link.value = slider.slide_link || '';
                    form.slide_order.value = slider.slide_order || 0;
                    
                    // 라디오 버튼 설정
                    if(slider.text_color) {
                        document.querySelector(`input[name="text_color"][value="${slider.text_color}"]`).checked = true;
                    }
                    if(slider.button_color) {
                        document.querySelector(`input[name="button_color"][value="${slider.button_color}"]`).checked = true;
                    }
                    
                    // 사용 여부
                    document.getElementById('slide_use').checked = slider.slide_use == 1;
                    
                    // 링크 타겟
                    if(slider.slide_link_target) {
                        document.querySelector(`input[name="slide_link_target"][value="${slider.slide_link_target}"]`).checked = true;
                    }
                    
                    // 이미지 프리뷰
                    if(slider.slide_image) {
                        document.getElementById('imagePreview').innerHTML = 
                            `<img src="<?php echo htmlspecialchars(G5_DATA_URL); ?>/mainkv/${slider.slide_image}" alt="">`;
                    }
                }
            });
    } else {
        // 추가 모드
        modalTitle.textContent = '슬라이더 추가';
        form.mode.value = 'add';
        form.slide_id.value = '';
    }
    
    modal.style.display = 'block';
}

// 슬라이더 모달 닫기
function closeSliderModal() {
    document.getElementById('sliderModal').style.display = 'none';
}

// 이미지 미리보기
function previewImage(input) {
    if(input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').innerHTML = 
                `<img src="${e.target.result}" alt="">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// 슬라이더 삭제
function deleteSlider(slideId) {
    if(!confirm('이 슬라이더를 삭제하시겠습니까?')) return;
    
    fetch('<?php echo htmlspecialchars(G5_BBS_URL); ?>/uxc_theme_slider.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=delete_slider&slide_id=${slideId}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('삭제되었습니다.');
            document.getElementById('slider_' + slideId).remove();
            
            // 목록이 비었는지 확인
            if(document.querySelectorAll('.sliderItem').length === 0) {
                location.reload();
            }
        } else {
            alert(data.message || '삭제에 실패했습니다.');
        }
    });
}

// 슬라이더 순서 변경
function moveSlider(slideId, direction) {
    console.log('Moving slider:', slideId, direction);
    
    fetch('<?php echo htmlspecialchars(G5_BBS_URL); ?>/uxc_theme_slider.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=move_slider&slide_id=${slideId}&direction=${direction}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data);
        if(data.success) {
            location.reload();
        } else {
            alert(data.message || '순서 변경에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('순서 변경 중 오류가 발생했습니다.');
    });
}

// 슬라이더 수정
function editSlider(slideId) {
    openSliderModal(slideId);
}

// 슬라이더 저장 함수
function saveSlider() {
    const form = document.getElementById('sliderForm');
    const formData = new FormData(form);
    formData.append('action', 'save_slider');
    
    // 로딩 표시
    const submitBtn = document.getElementById('sliderSaveBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> 처리 중...';
    
    fetch('<?php echo htmlspecialchars(G5_BBS_URL); ?>/uxc_theme_slider.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('저장되었습니다.');
            location.reload();
        } else {
            alert(data.message || '저장에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('오류가 발생했습니다.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// 폼 제출 이벤트 (엔터키 등으로 제출될 경우를 위해 유지)
document.getElementById('sliderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    saveSlider();
});

// 모달 외부 클릭시 닫기 - 주석 처리하여 비활성화
// window.addEventListener('click', function(e) {
//     const modal = document.getElementById('sliderModal');
//     if(e.target === modal) {
//         closeSliderModal();
//     }
// });

// 테이블 삭제 모달 열기
function openDeleteTableModal() {
    document.getElementById('deleteTableModal').classList.add('active');
}

// 테이블 삭제 모달 닫기
function closeDeleteTableModal() {
    const modal = document.getElementById('deleteTableModal');
    modal.classList.add('closing');
    setTimeout(function() {
        modal.classList.remove('active', 'closing');
    }, 300);
}

// 테이블 삭제 실행
function deleteSliderTable() {
    fetch('<?php echo htmlspecialchars(G5_BBS_URL); ?>/uxc_theme_slider.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=delete_slider_table'
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('테이블이 삭제되었습니다.');
            location.reload();
        } else {
            alert(data.message || '테이블 삭제에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('오류가 발생했습니다.');
    });
}
</script>