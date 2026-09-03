<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


// 게시판 읽기 페이지에서 Toast UI Editor 스타일 추가
add_event('tail_sub', 'toastuieditor_add_viewer_assets');
function toastuieditor_add_viewer_assets() {
    global $g5, $bo_table, $wr_id, $view;
    
    // 게시판 view 페이지인지 확인
    if (!isset($view) || !isset($view['wr_id'])) {
        return;
    }
    
    // ToastUI Editor로 작성된 콘텐츠인지 확인
    // 원본 데이터를 가져오기 위해 DB에서 직접 조회
    $write_table = $g5['write_prefix'] . $bo_table;
    $wr_id = (int)$view['wr_id'];
    $sql = "SELECT wr_content FROM {$write_table} WHERE wr_id = '{$wr_id}'";
    $result = sql_fetch($sql);
    if (!$result) {
        return;
    }
    
    $content = $result['wr_content'];
    
    // ToastUI Editor로 작성된 콘텐츠인지 확인
    if (strpos($content, '<!--TOASTUI_EDITOR_MARKDOWN-->') !== 0 && 
        strpos($content, '<!--TOASTUI_EDITOR_HTML-->') !== 0) {
        return;
    }
    
    $plugin_url = G5_PLUGIN_URL . '/editor/uxc_toastuieditor';
    
    // 마커 제거한 콘텐츠
    $clean_content = str_replace(['<!--TOASTUI_EDITOR_MARKDOWN-->', '<!--TOASTUI_EDITOR_HTML-->'], '', $content);
    $clean_content = ltrim($clean_content, "\n");
    
    // 마크다운인지 HTML인지 확인
    $is_markdown = strpos($content, '<!--TOASTUI_EDITOR_MARKDOWN-->') === 0;
    ?>
    
    <!-- 초기 콘텐츠 숨기기 위한 인라인 스타일 -->
    <style id="toastui-init-style">
    #bo_v_con {
        visibility: hidden;
        position: relative;
        min-height: 100px;
    }
    </style>
    <noscript><style>#bo_v_con { visibility: visible !important; min-height: auto !important; }</style></noscript>
    
    <!-- Toast UI Editor Viewer CSS -->
    <link rel="stylesheet" href="<?php echo $plugin_url; ?>/css/toastui-editor.min.css" />
    <link rel="stylesheet" href="<?php echo $plugin_url; ?>/css/toastui-editor-viewer.min.css" />
    <link rel="stylesheet" href="<?php echo $plugin_url; ?>/css/toastui-editor-dark.min.css" />
    <link rel="stylesheet" href="<?php echo $plugin_url; ?>/css/prism.min.css" />
    
    <!-- Custom styles for viewer -->
    <style>
    /* 로딩 인디케이터 */
    #bo_v_con::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 40px;
        height: 40px;
        margin: -20px 0 0 -20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        animation: toastui-spin 1s linear infinite;
        z-index: 1;
    }
    
    #bo_v_con::after {
        content: "콘텐츠를 불러오는 중...";
        position: absolute;
        top: 50%;
        left: 50%;
        margin-top: 30px;
        transform: translateX(-50%);
        color: #666;
        font-size: 14px;
        z-index: 1;
    }
    
    #bo_v_con.toastui-loaded::before,
    #bo_v_con.toastui-loaded::after {
        display: none;
    }
    
    @keyframes toastui-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* .toastui-editor-contents h1 { font-size: 2em; margin: 1em 0 0.5em; }
    .toastui-editor-contents h2 { font-size: 1.5em; margin: 1em 0 0.5em; }
    .toastui-editor-contents h3 { font-size: 1.17em; margin: 1em 0 0.5em; }
    .toastui-editor-contents p { margin: 0.5em 0; }
    .toastui-editor-contents pre { background-color: #f4f4f4; padding: 1em; border-radius: 4px; overflow-x: auto; }
    .toastui-editor-contents code { background-color: #f4f4f4; padding: 0.2em 0.4em; border-radius: 3px; }
    .toastui-editor-contents pre code { background: none; padding: 0; }
    .toastui-editor-contents blockquote { border-left: 4px solid #ddd; padding-left: 1em; margin: 1em 0; color: #666; }
    .toastui-editor-contents table { border-collapse: collapse; width: 100%; margin: 1em 0; }
    .toastui-editor-contents th, .toastui-editor-contents td { border: 1px solid #ddd; padding: 0.5em; }
    .toastui-editor-contents th { background-color: #f4f4f4; } */

    /* 뷰어 모바일 대응 */
    @media (max-width: 768px) {
        #bo_v_con .toastui-editor-contents img {
            max-width: 100%;
            height: auto;
        }
        #bo_v_con .toastui-editor-contents table {
            display: block;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        #bo_v_con .toastui-editor-contents pre {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            max-width: 100%;
        }
    }
    </style>
    
    <!-- Toast UI Editor Viewer JS (뷰어 전용 번들 ~141KB, 전체 에디터 ~534KB 대비 74% 경량) -->
    <script src="<?php echo $plugin_url; ?>/js/toastui-editor-viewer.min.js"></script>
    
    <!-- Prism.js for code highlighting -->
    <script src="<?php echo $plugin_url; ?>/js/prism.min.js"></script>
    <!-- DOMPurify for XSS prevention -->
    <script src="<?php echo $plugin_url; ?>/js/purify.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const contentDiv = document.querySelector('#bo_v_con');
        if (!contentDiv) {
            console.error('ToastUI Viewer: Content div not found');
            return;
        }
        
        const content = <?php echo json_encode($clean_content); ?>;
        const isMarkdown = <?php echo json_encode($is_markdown); ?>;
        
        // 텍스트 노드 내 bare URL을 <a> 링크로 변환
        function autoLinkURLs(container) {
            var walker = document.createTreeWalker(container, NodeFilter.SHOW_TEXT, null, false);
            var textNodes = [];
            while (walker.nextNode()) textNodes.push(walker.currentNode);
            var urlRegex = /(https?:\/\/[^\s<>"')\]]+)/g;
            textNodes.forEach(function(node) {
                if (node.parentNode.tagName === 'A' || node.parentNode.tagName === 'CODE' || node.parentNode.tagName === 'PRE') return;
                if (!urlRegex.test(node.textContent)) return;
                urlRegex.lastIndex = 0;
                var frag = document.createDocumentFragment();
                var lastIndex = 0;
                var match;
                while ((match = urlRegex.exec(node.textContent)) !== null) {
                    if (match.index > lastIndex) {
                        frag.appendChild(document.createTextNode(node.textContent.slice(lastIndex, match.index)));
                    }
                    var a = document.createElement('a');
                    a.href = match[1];
                    a.textContent = match[1];
                    a.setAttribute('target', '_blank');
                    a.setAttribute('rel', 'noopener noreferrer');
                    frag.appendChild(a);
                    lastIndex = urlRegex.lastIndex;
                }
                if (lastIndex < node.textContent.length) {
                    frag.appendChild(document.createTextNode(node.textContent.slice(lastIndex)));
                }
                node.parentNode.replaceChild(frag, node);
            });
        }

        function initViewer() {
            // ToastUI가 로드되었는지 확인
            if (!window.toastui || !window.toastui.Editor) {
                setTimeout(initViewer, 50);
                return;
            }
            
            try {
                if (isMarkdown) {
                    // 기존 콘텐츠 비우기
                    contentDiv.innerHTML = '';
                    
                    // ToastUI Viewer 인스턴스 생성
                    const viewer = new toastui.Editor({
                        el: contentDiv,
                        viewer: true,
                        initialValue: content,
                        usageStatistics: false,
                        autofocus: false,
                        height: 'auto',  // height를 auto로 설정
                        events: {
                            load: function() {
                                console.log('ToastUI Viewer loaded');
                            }
                        }
                    });
                    
                    // 뷰어 렌더링 후 링크 후처리
                    setTimeout(() => {
                        // 뷰어 전용 번들은 .toastui-editor-contents 직접 생성
                        const rendered = contentDiv.querySelector('.toastui-editor-contents') || contentDiv.querySelector('.toastui-editor-md-preview');
                        if (rendered) {
                            var html = rendered.innerHTML;
                            if (window.DOMPurify) html = DOMPurify.sanitize(html, { ADD_ATTR: ['target'] });

                            // 내부 래퍼 생성 (다크모드 CSS 셀렉터 구조 대응)
                            var wrapper = document.createElement('div');
                            wrapper.className = 'toastui-editor-contents';
                            wrapper.innerHTML = html;

                            contentDiv.innerHTML = '';
                            contentDiv.appendChild(wrapper);
                            contentDiv.removeAttribute('style');

                            // bare URL을 링크로 변환
                            autoLinkURLs(wrapper);

                            // 모든 링크를 새 창에서 열리도록 설정
                            const links = wrapper.querySelectorAll('a');
                            links.forEach(link => {
                                link.setAttribute('target', '_blank');
                                link.setAttribute('rel', 'noopener noreferrer');
                            });

                        }

                        // 다크모드 체크 및 적용 (preview 유무와 무관하게 항상 실행)
                        const isDarkMode = localStorage.getItem('theme') === 'darkMode' ||
                                          document.documentElement.classList.contains('darkMode');
                        if (isDarkMode) {
                            contentDiv.classList.add('toastui-editor-dark');
                        }

                        // 렌더링 완료 후 콘텐츠 표시
                        contentDiv.classList.add('toastui-loaded');
                        contentDiv.style.visibility = 'visible';

                        // 초기 스타일 제거
                        const initStyle = document.getElementById('toastui-init-style');
                        if (initStyle) {
                            initStyle.remove();
                        }
                    }, 100);
                } else {
                    // HTML 콘텐츠 표시 (XSS 방지)
                    var sanitized = window.DOMPurify ? DOMPurify.sanitize(content, { ADD_ATTR: ['target'] }) : content;

                    // 내부 래퍼 생성 (다크모드 CSS 셀렉터 구조 대응)
                    var wrapper = document.createElement('div');
                    wrapper.className = 'toastui-editor-contents';
                    wrapper.innerHTML = sanitized;

                    contentDiv.innerHTML = '';
                    contentDiv.appendChild(wrapper);

                    // bare URL을 링크로 변환
                    autoLinkURLs(wrapper);

                    // 모든 링크를 새 창에서 열리도록 설정
                    const links = wrapper.querySelectorAll('a');
                    links.forEach(link => {
                        link.setAttribute('target', '_blank');
                        link.setAttribute('rel', 'noopener noreferrer');
                    });

                    // 다크모드 체크 및 적용
                    const isDarkMode = localStorage.getItem('theme') === 'darkMode' ||
                                      document.documentElement.classList.contains('darkMode');
                    if (isDarkMode) {
                        contentDiv.classList.add('toastui-editor-dark');
                    }

                    // 렌더링 완료 후 콘텐츠 표시
                    contentDiv.classList.add('toastui-loaded');
                    contentDiv.style.visibility = 'visible';

                    // 초기 스타일 제거
                    const initStyle = document.getElementById('toastui-init-style');
                    if (initStyle) {
                        initStyle.remove();
                    }
                }
                
                // Prism.js 코드 하이라이팅 적용
                if (typeof Prism !== 'undefined') {
                    setTimeout(() => {
                        Prism.highlightAllUnder(contentDiv);
                    }, 100);
                }
            } catch (error) {
                console.error('ToastUI Viewer: Error creating viewer:', error);
            }
        }
        
        // 초기화 시작
        initViewer();
        
        // 다크모드 전환 감지
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === "attributes" && mutation.attributeName === "class") {
                    const isDark = document.documentElement.classList.contains("darkMode");
                    const contentDiv = document.querySelector('#bo_v_con');
                    if (contentDiv) {
                        if (isDark) {
                            contentDiv.classList.add('toastui-editor-dark');
                        } else {
                            contentDiv.classList.remove('toastui-editor-dark');
                        }
                    }
                }
            });
        });
        
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });

        // JS 로딩 실패 시 fallback (5초 후 원본 콘텐츠 표시)
        setTimeout(function() {
            if (!contentDiv.classList.contains('toastui-loaded')) {
                contentDiv.style.visibility = 'visible';
                contentDiv.classList.add('toastui-loaded');
                var initStyle = document.getElementById('toastui-init-style');
                if (initStyle) initStyle.remove();
            }
        }, 5000);
    });
    </script>
    <?php
}

// 게시글 목록에서 ToastUI 마커 제거
if (!function_exists('toastuieditor_clean_list_content')) {
    function toastuieditor_clean_list_content($content) {
        // Toast UI Editor 구분자 제거
        $content = str_replace('<!--TOASTUI_EDITOR_MARKDOWN-->', '', $content);
        $content = str_replace('<!--TOASTUI_EDITOR_HTML-->', '', $content);
        
        // 마크다운 이미지 문법 제거 ![alt](url)
        $content = preg_replace('/!\[[^\]]*\]\([^)]+\)/', '', $content);
        
        // 마크다운 링크 문법을 텍스트로 변환 [text](url) -> text
        $content = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $content);
        
        // 마크다운 헤더 제거 (# ## ### 등)
        $content = preg_replace('/^#{1,6}\s+/m', '', $content);
        
        // 마크다운 굵은 글씨 제거 (**text** 또는 __text__)
        $content = preg_replace('/(\*\*|__)(.*?)\1/', '$2', $content);
        
        // 마크다운 기울임 제거 (*text* 또는 _text_)
        $content = preg_replace('/(\*|_)(.*?)\1/', '$2', $content);
        
        // 마크다운 코드 블록 제거 (```...```)
        $content = preg_replace('/```[^`]*```/', '', $content);
        
        // 마크다운 인라인 코드 제거 (`code`)
        $content = preg_replace('/`([^`]+)`/', '$1', $content);
        
        // 마크다운 인용문 제거 (> )
        $content = preg_replace('/^>\s*/m', '', $content);
        
        // 마크다운 리스트 제거 (-, *, +, 1. 등)
        $content = preg_replace('/^[\*\-\+]\s+/m', '', $content);
        $content = preg_replace('/^\d+\.\s+/m', '', $content);
        
        // HTML 태그 제거
        $content = strip_tags($content);
        
        // 연속된 공백과 줄바꿈 정리
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        return $content;
    }
}

// ToastUI Editor 콘텐츠에서 첫번째 이미지 추출
if (!function_exists('toastuieditor_get_first_image')) {
    function toastuieditor_get_first_image($content) {
        // Toast UI Editor 마커 제거
        $content = str_replace(['<!--TOASTUI_EDITOR_MARKDOWN-->', '<!--TOASTUI_EDITOR_HTML-->'], '', $content);
        
        // base64 이미지 패턴
        $pattern_base64 = '/data:image\/[^;]+;base64,[a-zA-Z0-9+\/=]+/';
        
        // 일반 이미지 URL 패턴 (Markdown 및 HTML)
        $pattern_url = '/(?:!\[[^\]]*\]\(([^)]+)\)|<img[^>]+src=["\']([^"\']+)["\'])/';
        
        // base64 이미지 먼저 찾기
        if (preg_match($pattern_base64, $content, $matches)) {
            return $matches[0];
        }
        
        // URL 이미지 찾기
        if (preg_match_all($pattern_url, $content, $matches)) {
            // Markdown 이미지
            if (!empty($matches[1][0])) {
                return $matches[1][0];
            }
            // HTML img 태그
            if (!empty($matches[2][0])) {
                return $matches[2][0];
            }
        }
        
        return '';
    }
}