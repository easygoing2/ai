/**
 * Toast UI Editor v3 초기화 모듈
 * editor.lib.php의 인라인 JS를 외부 파일로 분리
 *
 * 사용법: initToastUIEditor(config) 호출
 */
(function() {
    'use strict';

    var GLOBAL_CFG = window.TOASTUI_EDITOR_CONFIG || {};

    // Prism autoloader 설정
    if (window.Prism && window.Prism.plugins && window.Prism.plugins.autoloader) {
        Prism.plugins.autoloader.languages_path =
            GLOBAL_CFG.prismLanguagesPath || 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/';
    }

    // 공통 동기화 함수 (에디터 마크다운 → textarea)
    window.toastuiSyncContent = function(editorInstance, textareaId) {
        try {
            var content = editorInstance.getMarkdown();
            var textarea = document.getElementById(textareaId);
            if (!textarea) return false;
            if (textarea.hasAttribute('maxlength')) textarea.removeAttribute('maxlength');
            textarea.value = (!content || content.trim() === '')
                ? ''
                : '<!--TOASTUI_EDITOR_MARKDOWN-->\n' + content;
            return true;
        } catch (e) {
            console.error('Sync error:', e);
            return false;
        }
    };

    /**
     * Toast UI Editor 인스턴스 초기화
     * @param {Object} config
     * @param {string} config.id - textarea ID
     * @param {string} config.height - 에디터 높이 (기본: GLOBAL_CFG.editorHeight || '600px')
     * @param {string} config.boTable - 게시판 테이블명
     * @param {string} config.uploadToken - CSRF 업로드 토큰
     * @param {boolean} config.isEdit - 수정 모드 여부
     */
    window.initToastUIEditor = function(config) {
        var id = config.id;
        var height = config.height || GLOBAL_CFG.editorHeight || '600px';
        var boTable = config.boTable || '';
        var uploadToken = config.uploadToken || '';
        var isEdit = config.isEdit || false;
        var pluginUrl = GLOBAL_CFG.pluginUrl || '';
        var uploadUrl = GLOBAL_CFG.uploadUrl || (pluginUrl + '/upload.php');
        var toolbarItems = config.toolbarItems || GLOBAL_CFG.toolbarItems || [
            ['heading', 'bold', 'italic', 'strike'],
            ['hr', 'quote'],
            ['ul', 'ol', 'task', 'indent', 'outdent'],
            ['table', 'image', 'link'],
            ['code', 'codeblock'],
            ['scrollSync']
        ];
        var maxContentWarningSize = GLOBAL_CFG.maxContentWarningSize || 1000000;
        var autoSaveInterval = GLOBAL_CFG.autoSaveInterval || 30000;

        // 에디터 로드 대기 (최대 5초)
        function waitForEditor(callback, retries) {
            if (typeof retries === 'undefined') retries = 50;
            if (window.toastui && window.toastui.Editor && window.tui && window.tui.colorPicker && window.fontSizePlugin) {
                callback();
            } else if (retries <= 0) {
                var el = document.querySelector('#' + id + '_editor');
                if (el) el.innerHTML = '<p style="color:red;padding:16px;">에디터를 불러올 수 없습니다. 페이지를 새로고침해주세요.</p>';
            } else {
                setTimeout(function() { waitForEditor(callback, retries - 1); }, 100);
            }
        }

        waitForEditor(function() {
            var Editor = toastui.Editor;
            var colorPicker = tui.colorPicker;
            var plugin = Editor.plugin || {};

            var codeSyntaxHighlight = plugin.codeSyntaxHighlight;
            var colorSyntax = plugin.colorSyntax;

            // textarea 초기값
            var initialTextarea = document.getElementById(id);
            var initialContent = initialTextarea ? initialTextarea.value : '';

            // 다크모드 확인
            var currentTheme = localStorage.getItem('theme');
            var isDarkMode = currentTheme === 'darkMode';

            // 빈 콘텐츠 처리
            var cleanContent = initialContent || '';
            if (cleanContent) {
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = cleanContent;
                var textContent = (tempDiv.textContent || tempDiv.innerText || '').trim();
                if (!textContent && (
                    cleanContent === '<p><br /></p>' ||
                    cleanContent === '<p><br></p>' ||
                    cleanContent === '<p><br/></p>' ||
                    cleanContent === '<p>&nbsp;</p>' ||
                    cleanContent === '<br>' ||
                    cleanContent === '<br/>' ||
                    cleanContent === '<br />'
                )) {
                    cleanContent = '';
                }
            }

            // 플러그인 구성
            var plugins = [];
            if (codeSyntaxHighlight && window.Prism) {
                plugins.push([codeSyntaxHighlight, { highlighter: Prism }]);
            }
            if (colorSyntax) {
                plugins.push(colorSyntax);
            }
            if (window.fontSizePlugin) {
                plugins.push(fontSizePlugin);
            }
            if (window.imageResizePlugin) {
                plugins.push(imageResizePlugin);
            }
            // 에디터 인스턴스 생성
            var editorInstance = new Editor({
                el: document.querySelector('#' + id + '_editor'),
                height: height,
                initialEditType: 'wysiwyg',
                initialValue: cleanContent,
                previewStyle: 'vertical',
                language: GLOBAL_CFG.language || 'ko-KR',
                usageStatistics: false,
                autofocus: false,
                toolbarItems: toolbarItems,
                hideModeSwitch: false,
                plugins: plugins,
                customHTMLRenderer: {
                    link: function(node, context) {
                        var entering = context.entering;
                        if (entering) {
                            return {
                                type: 'openTag',
                                tagName: 'a',
                                attributes: {
                                    href: node.destination,
                                    title: node.title || '',
                                    target: '_blank',
                                    rel: 'noopener noreferrer'
                                }
                            };
                        }
                        return { type: 'closeTag', tagName: 'a' };
                    }
                },
                hooks: {
                    addImageBlobHook: function(blob, callback) {
                        var formData = new FormData();
                        formData.append('image', blob);
                        formData.append('bo_table', boTable);
                        formData.append('upload_token', uploadToken);

                        // 업로드 진행 표시
                        var editorEl = document.querySelector('#' + id + '_editor');
                        editorEl.style.position = 'relative';
                        var overlay = document.createElement('div');
                        overlay.className = 'toastui-upload-overlay';
                        overlay.textContent = '이미지 업로드 중...';
                        editorEl.appendChild(overlay);

                        fetch(uploadUrl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(function(response) { return response.json(); })
                        .then(function(result) {
                            if (result.success) {
                                callback(result.url, result.alt || 'image');
                            } else {
                                alert(result.message || '이미지 업로드에 실패했습니다.');
                            }
                        })
                        .catch(function(error) {
                            console.error('Upload error:', error);
                            alert('이미지 업로드 중 오류가 발생했습니다.');
                        })
                        .finally(function() {
                            overlay.remove();
                        });
                    }
                },
                events: {
                    change: (function() {
                        var syncTimer = null;
                        return function() {
                            clearTimeout(syncTimer);
                            syncTimer = setTimeout(function() {
                                toastuiSyncContent(editorInstance, id);
                            }, 300);
                        };
                    })()
                }
            });

            // 전역 변수 등록
            window['editor_' + id] = editorInstance;

            // 모바일 높이 조정
            if (window.innerWidth <= 768) {
                editorInstance.setHeight('300px');
            }

            // 전역 동기화 함수
            window['sync_' + id + '_editor'] = function() {
                return toastuiSyncContent(editorInstance, id);
            };

            // 다크모드 초기 설정
            setTimeout(function() {
                if (isDarkMode) {
                    var editorEls = editorInstance.getEditorElements();
                    if (editorEls.wwEditor) {
                        editorEls.wwEditor.classList.add('toastui-editor-dark');
                    }
                    var defaultUI = document.querySelector('#' + id + '_editor .toastui-editor-defaultUI');
                    if (defaultUI) {
                        defaultUI.classList.add('toastui-editor-dark');
                    }
                }
            }, 100);

            // 다크모드 변경 감지
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        var isDark = document.documentElement.classList.contains('darkMode');
                        var defaultUI = document.querySelector('#' + id + '_editor .toastui-editor-defaultUI');
                        var editorEls = editorInstance.getEditorElements();
                        if (defaultUI) {
                            if (isDark) {
                                defaultUI.classList.add('toastui-editor-dark');
                                if (editorEls.wwEditor) editorEls.wwEditor.classList.add('toastui-editor-dark');
                            } else {
                                defaultUI.classList.remove('toastui-editor-dark');
                                if (editorEls.wwEditor) editorEls.wwEditor.classList.remove('toastui-editor-dark');
                            }
                        }
                    }
                });
            });
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

            // textarea maxlength 제거
            var textarea = document.getElementById(id);
            if (textarea && textarea.hasAttribute('maxlength')) {
                textarea.removeAttribute('maxlength');
            }

            // 폼 제출 핸들러
            var form = textarea ? textarea.form : null;
            if (form) {
                form.addEventListener('submit', function(e) {
                    try {
                        toastuiSyncContent(editorInstance, id);
                        var finalContent = textarea.value;

                        if (finalContent.length > maxContentWarningSize) {
                            if (!confirm('콘텐츠가 매우 큽니다 (' + Math.round(finalContent.length / 1024) + 'KB). 계속하시겠습니까?')) {
                                e.preventDefault();
                                return false;
                            }
                        }

                        var isAdminPage = window.location.pathname.indexOf('/adm/') !== -1;
                        if (!isAdminPage && (!finalContent || finalContent.trim() === '')) {
                            alert('내용을 입력해 주세요.');
                            e.preventDefault();
                            return false;
                        }
                    } catch (error) {
                        console.error('Error on form submit:', error);
                        alert('콘텐츠 저장 중 오류가 발생했습니다: ' + error.message);
                        e.preventDefault();
                        return false;
                    }
                }, true);
            }

            // 자동 임시저장 (새 글 작성 시)
            (function() {
                if (isEdit) return;
                var autoSaveKey = 'toastui_draft_' + (boTable || 'default');

                // 저장된 임시글 복원
                try {
                    var saved = localStorage.getItem(autoSaveKey);
                    if (saved) {
                        var draft = JSON.parse(saved);
                        if (draft.c && draft.t && (Date.now() - draft.t) < 86400000) {
                            var bar = document.createElement('div');
                            bar.style.cssText = 'padding:8px 12px;background:#fff3cd;border:1px solid #ffc107;border-radius:4px;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;font-size:13px';
                            bar.innerHTML = '임시저장된 글이 있습니다. <span style="display:flex;gap:8px"><button type="button" style="padding:4px 12px;background:#ffc107;border:none;border-radius:3px;cursor:pointer">복원</button><button type="button" style="padding:4px 12px;background:#e9ecef;border:none;border-radius:3px;cursor:pointer">무시</button></span>';
                            var editorWrapper = document.querySelector('#' + id + '_editor');
                            editorWrapper.parentNode.insertBefore(bar, editorWrapper);
                            var btns = bar.querySelectorAll('button');
                            btns[0].addEventListener('click', function() { editorInstance.setMarkdown(draft.c); bar.remove(); });
                            btns[1].addEventListener('click', function() { localStorage.removeItem(autoSaveKey); bar.remove(); });
                        } else {
                            localStorage.removeItem(autoSaveKey);
                        }
                    }
                } catch (e) { /* ignore */ }

                // 주기적 자동 저장
                setInterval(function() {
                    try {
                        var content = editorInstance.getMarkdown();
                        if (content && content.trim()) {
                            localStorage.setItem(autoSaveKey, JSON.stringify({ c: content, t: Date.now() }));
                        }
                    } catch (e) { /* ignore */ }
                }, autoSaveInterval);

                // 제출 시 임시저장 삭제
                if (form) {
                    form.addEventListener('submit', function() { localStorage.removeItem(autoSaveKey); });
                }
            })();

        }); // waitForEditor 끝
    }; // initToastUIEditor 끝

})();
