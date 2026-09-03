<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

/**
 * TOAST UI Editor v3.x for GnuBoard5
 * 
 * @version 3.0.0
 * @author UX Camp
 * @license MIT
 */

// 에디터 상수 정의
if (!defined('G5_TOASTUI_EDITOR_URL')) {
    define('G5_TOASTUI_EDITOR_URL', G5_PLUGIN_URL.'/editor/uxc_toastuieditor');
}

// 에디터 업로드용 CSRF 토큰 생성
if (!function_exists('toastuieditor_get_upload_token')) {
    function toastuieditor_get_upload_token() {
        if (!get_session('ss_editor_upload_token')) {
            set_session('ss_editor_upload_token', md5(uniqid(rand(), true)));
        }
        return get_session('ss_editor_upload_token');
    }
}

// 에디터 HTML 출력
function editor_html($id, $content, $is_dhtml_editor=true)
{
    global $g5, $config, $w, $board, $write;
    global $editor_width, $editor_height;
    
    static $toastui_loaded = false;
    
    if (!$is_dhtml_editor) {
        return "<textarea id=\"$id\" name=\"$id\" style=\"width:100%;\" maxlength=\"65536\">$content</textarea>";
    }
    
    // 수정 모드에서 HTML 엔티티 디코드
    if (in_array($id, array('wr_content', 'qa_content'), true)) {
        if ($w == 'u' && strpos($content, '&lt;') !== false) {
            // HTML 엔티티가 있는 경우 디코드
            $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        // Toast UI Editor 구분자 제거
        if (strpos($content, '<!--TOASTUI_EDITOR_MARKDOWN-->') === 0) {
            $content = str_replace('<!--TOASTUI_EDITOR_MARKDOWN-->', '', $content);
            $content = ltrim($content, "\n");
            // 마크다운 콘텐츠는 에디터가 처리하도록 유지
        } else if (strpos($content, '<!--TOASTUI_EDITOR_HTML-->') === 0) {
            $content = str_replace('<!--TOASTUI_EDITOR_HTML-->', '', $content);
            $content = ltrim($content, "\n");
            // HTML 콘텐츠는 에디터가 HTML로 인식하도록 설정
        }
        
        // 빈 콘텐츠 패턴 제거 (서버사이드에서도 처리)
        $empty_patterns = array(
            '/<p><br\s*\/?><\/p>/i',
            '/<p>\s*<br\s*\/?>\s*<\/p>/i',
            '/<p>&nbsp;<\/p>/i',
            '/<p>\s*<\/p>/i'
        );
        
        $trimmed_content = trim($content);
        foreach ($empty_patterns as $pattern) {
            if (preg_match($pattern, $trimmed_content) && strip_tags($trimmed_content) == '') {
                $content = '';
                break;
            }
        }
    }
    
    // 에디터 크기 설정
    $editor_width = isset($editor_width) ? $editor_width : '100%';
    if (!isset($editor_height) || !$editor_height) {
        // 모바일에서는 300px, 데스크톱에서는 600px (JS에서 동적 조정)
        $editor_height = '600px';
    }
    
    $editor_html = '';
    
    if (!$toastui_loaded) {
        // ToastUI Editor v3 로컬 파일 사용
        $editor_html .= '
        <!-- TOAST UI Editor v3 CSS -->
        <link rel="stylesheet" href="'.G5_TOASTUI_EDITOR_URL.'/css/toastui-editor.min.css" />
        <link rel="stylesheet" href="'.G5_TOASTUI_EDITOR_URL.'/css/toastui-editor-dark.min.css" />
        
        <!-- Prism.js for code syntax highlighting -->
        <link rel="stylesheet" href="'.G5_TOASTUI_EDITOR_URL.'/css/prism.min.css" />
        
        <!-- TUI Color Picker (dependency for color syntax plugin) -->
        <link rel="stylesheet" href="'.G5_TOASTUI_EDITOR_URL.'/css/tui-color-picker.min.css" />
        <script src="'.G5_TOASTUI_EDITOR_URL.'/js/tui-color-picker.min.js"></script>
        
        <!-- TOAST UI Editor Configuration -->
        <script src="'.G5_TOASTUI_EDITOR_URL.'/config.js.php"></script>
        
        <!-- TOAST UI Editor v3 JS -->
        <script src="'.G5_TOASTUI_EDITOR_URL.'/js/toastui-editor-all.min.js"></script>
        
        <!-- TOAST UI Editor 언어 파일 -->
        <script src="'.G5_TOASTUI_EDITOR_URL.'/js/ko-kr.min.js"></script>
        
        <!-- Prism.js -->
        <script src="'.G5_TOASTUI_EDITOR_URL.'/js/prism.min.js"></script>
        <script src="'.G5_TOASTUI_EDITOR_URL.'/js/prism-autoloader.min.js"></script>
        
        <!-- TOAST UI Editor Plugins -->
        <link rel="stylesheet" href="'.G5_TOASTUI_EDITOR_URL.'/plugin/toastui-editor-plugin-color-syntax.min.css" />
        <script src="'.G5_TOASTUI_EDITOR_URL.'/plugin/toastui-editor-plugin-code-syntax-highlight.min.js"></script>
        <script src="'.G5_TOASTUI_EDITOR_URL.'/plugin/toastui-editor-plugin-color-syntax.min.js"></script>
        <script src="'.G5_TOASTUI_EDITOR_URL.'/plugin/toastui-editor-plugin-custom-link.js"></script>
        
        <!-- Font Size Plugin -->
        <link rel="stylesheet" href="'.G5_TOASTUI_EDITOR_URL.'/plugin/toastui-editor-plugin-font-size.css" />
        <script src="'.G5_TOASTUI_EDITOR_URL.'/plugin/toastui-editor-plugin-font-size.js"></script>

        <!-- Image Resize Plugin -->
        <link rel="stylesheet" href="'.G5_TOASTUI_EDITOR_URL.'/plugin/toastui-editor-plugin-image-resize.css" />
        <script src="'.G5_TOASTUI_EDITOR_URL.'/plugin/toastui-editor-plugin-image-resize.js"></script>

        <!-- Editor Init (외부 분리된 초기화 모듈) -->
        <script src="'.G5_TOASTUI_EDITOR_URL.'/js/editor-init.js"></script>
        
        <style>
        .toastui-editor-defaultUI {
            width: '.$editor_width.' !important;
        }
        .toastui-editor-contents pre {
            background-color: #f4f4f4;
            border-radius: 4px;
        }
        .toastui-editor-dark .toastui-editor-contents pre {
            background-color: #1e1e1e;
        }
        .toastui-upload-overlay {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.35); display: flex; align-items: center;
            justify-content: center; gap: 10px; z-index: 9999; color: #fff;
            font-size: 14px; border-radius: 4px;
        }
        .toastui-upload-overlay::before {
            content: ""; width: 20px; height: 20px;
            border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
            border-radius: 50%; animation: toastui-upload-spin 0.8s linear infinite;
        }
        @keyframes toastui-upload-spin { to { transform: rotate(360deg); } }

        /* 모바일 대응 */
        @media (max-width: 768px) {
            .toastui-editor-defaultUI {
                min-height: 300px;
            }
            .toastui-editor-defaultUI .toastui-editor-toolbar {
                flex-wrap: wrap;
                height: auto !important;
            }
            .toastui-editor-toolbar-group {
                flex-wrap: wrap;
            }
            .toastui-editor-defaultUI .toastui-editor-mode-switch {
                width: 100%;
                justify-content: center;
            }
            .toastui-editor-popup {
                max-width: calc(100vw - 20px) !important;
                left: 10px !important;
                right: 10px !important;
            }
        }
        @media (max-width: 480px) {
            .toastui-editor-defaultUI .toastui-editor-toolbar-group {
                gap: 1px;
            }
            .toastui-editor-defaultUI .toastui-editor-toolbar-group button {
                width: 28px;
                height: 28px;
            }
        }
        </style>
        ';
        
        $toastui_loaded = true;
    }
    
    $editor_html .= '
    <div id="'.$id.'_editor"></div>
    <textarea id="'.$id.'" name="'.$id.'" style="display:none;">'.htmlspecialchars($content, ENT_QUOTES, 'UTF-8').'</textarea>

    <script>
    initToastUIEditor({
        id: "'.$id.'",
        height: "'.$editor_height.'",
        boTable: "'.(isset($board['bo_table']) ? $board['bo_table'] : '').'",
        uploadToken: "'.toastuieditor_get_upload_token().'",
        isEdit: '.($w == 'u' ? 'true' : 'false').'
    });
    </script>
    ';
    
    return $editor_html;
}

// 에디터 내용 가져오기
function get_editor_js($id, $is_dhtml_editor=true)
{
    if (!$is_dhtml_editor) {
        return '';
    }
    
    return '
    // 에디터 내용을 textarea로 동기화하는 함수
    function sync_'.$id.'_content() {
        if (window["editor_'.$id.'"]) {
            return toastuiSyncContent(window["editor_'.$id.'"], "'.$id.'");
        }
        return false;
    }

    // 즉시 실행
    sync_'.$id.'_content();
    
    // AJAX 요청 전에 명시적으로 동기화 (그누보드 필터 검사 대응)
    if (typeof jQuery !== "undefined" && jQuery.ajax) {
        const originalAjax = jQuery.ajax;
        jQuery.ajax = function(settings) {
            // write_update 관련 AJAX 요청인 경우
            if (settings.url && settings.url.includes("ajax.filter.php")) {
                sync_'.$id.'_content();
                
                // 데이터가 FormData가 아닌 경우 content 값 업데이트
                if (settings.data && settings.data["content"] !== undefined) {
                    const textarea = document.getElementById("'.$id.'");
                    if (textarea && textarea.name === "wr_content") {
                        settings.data["content"] = textarea.value;
                    }
                }
            }
            return originalAjax.call(this, settings);
        };
    }
    ';
}

// 에디터 내용 검증
function chk_editor_js($id, $is_dhtml_editor=true)
{
    if (!$is_dhtml_editor) {
        return '';
    }
    
    return '
    if (window["editor_'.$id.'"]) {
        // 관리자 페이지에서는 빈 내용도 허용
        var isAdminPage = window.location.pathname.includes("/adm/");

        if (!isAdminPage) {
            var markdownContent = window["editor_'.$id.'"].getMarkdown();
            if (!markdownContent || markdownContent.trim() === "") {
                alert("내용을 입력해 주세요.");
                window["editor_'.$id.'"].focus();
                return false;
            }
        }

        // textarea 동기화
        toastuiSyncContent(window["editor_'.$id.'"], "'.$id.'");
    }
    ';
}
