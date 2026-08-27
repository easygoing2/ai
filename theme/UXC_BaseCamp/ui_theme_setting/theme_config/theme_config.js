/**
 * Theme Config Script
 * 테마 설정 페이지 JavaScript
 */

// 폼 제출 처리
function fthemeconfig_submit(f) {
    // 로고 설정과 폰트 설정을 JSON으로 변환하여 cf_1에 저장
    var logoConfig = {
        logo_icon: document.getElementById('logo_icon').value,
        logo_text: document.getElementById('logo_text').value,
        font: document.getElementById('font_select').value
    };

    // JSON 문자열로 변환하여 hidden input에 저장
    var jsonLogoConfig = JSON.stringify(logoConfig);

    // hidden input이 없으면 생성
    var hiddenLogoInput = document.getElementById('cf_1_json');
    if (!hiddenLogoInput) {
        hiddenLogoInput = document.createElement('input');
        hiddenLogoInput.type = 'hidden';
        hiddenLogoInput.id = 'cf_1_json';
        hiddenLogoInput.name = 'cf_1';
        f.appendChild(hiddenLogoInput);
    }
    hiddenLogoInput.value = jsonLogoConfig;

    // cf_9 API 설정을 JSON으로 변환하여 저장
    var apiConfig = {
        kakao_js_key: document.getElementById('kakao_js_key').value,
        gemini_api_key: document.getElementById('gemini_api_key').value
    };

    var jsonApiConfig = JSON.stringify(apiConfig);
    var hiddenCf9Input = document.getElementById('cf_9');
    if (hiddenCf9Input) {
        hiddenCf9Input.value = jsonApiConfig;
    }

    return true;
}

// 아이콘 선택 기능
function openIconSelector() {
    document.getElementById('iconSelectorModal').style.display = 'block';
}

function closeIconSelector() {
    document.getElementById('iconSelectorModal').style.display = 'none';
}

function selectIcon(iconClass) {
    document.getElementById('logo_icon').value = iconClass;
    document.getElementById('selectedIcon').className = 'bx ' + iconClass;
    closeIconSelector();
}

function filterIcons() {
    const searchTerm = document.getElementById('iconSearch').value.toLowerCase();
    const iconItems = document.querySelectorAll('.icon-item');

    iconItems.forEach(item => {
        const iconName = item.querySelector('span').textContent.toLowerCase();
        if (iconName.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// 컬러셋 선택 기능
var originalColorset = '';
var currentColorset = '';

function selectColorset(filename) {
    // 이전 선택 해제
    document.querySelectorAll('.colorset-item').forEach(item => {
        item.classList.remove('active');
        item.querySelector('.colorset-chip i')?.remove();
    });

    // 새로운 선택 활성화
    event.currentTarget.classList.add('active');
    const checkIcon = document.createElement('i');
    checkIcon.className = 'bx bx-check';
    event.currentTarget.querySelector('.colorset-chip').appendChild(checkIcon);

    // hidden input 값 변경
    document.getElementById('cf_3').value = filename;
    currentColorset = filename;

    // 실시간 미리보기 적용
    applyColorsetPreview(filename);
}

function applyColorsetPreview(filename) {
    // 기존 컬러셋 link 태그 찾기
    const colorsetLinks = document.querySelectorAll('link[href*="/color_set/"]');
    const themeUrl = document.body.getAttribute('data-theme-url') || '';

    if (colorsetLinks.length > 0) {
        // 기존 link 태그의 href 변경
        colorsetLinks.forEach(link => {
            const href = link.getAttribute('href');
            const newHref = href.replace(/color_set\/[^\/]+\.css/, 'color_set/' + filename);
            link.setAttribute('href', newHref);
        });
    } else {
        // link 태그가 없으면 새로 생성
        const newLink = document.createElement('link');
        newLink.rel = 'stylesheet';
        newLink.href = themeUrl + '/css/color_set/' + filename + '?ver=' + Date.now();
        document.head.appendChild(newLink);
    }
}

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    // 원본 컬러셋 저장
    originalColorset = document.body.getAttribute('data-original-colorset') || 'default_6f48ff.css';
    currentColorset = originalColorset;

    // 폼 제출 시 플래그 설정
    const form = document.getElementById('fthemeconfig');
    if (form) {
        form.addEventListener('submit', function(e) {
            this.submitting = true;
            return true;
        });
    }
});

// 모달 외부 클릭 시 닫기
window.onclick = function(event) {
    const modal = document.getElementById('iconSelectorModal');
    if (event.target == modal) {
        closeIconSelector();
    }
}

// 페이지 떠날 때 원본으로 복구 (저장하지 않은 경우)
window.addEventListener('beforeunload', function(e) {
    if (currentColorset !== originalColorset) {
        const form = document.getElementById('fthemeconfig');
        if (!form.submitting) {
            // 저장하지 않고 떠나는 경우 원본으로 복구
            applyColorsetPreview(originalColorset);
        }
    }
});
