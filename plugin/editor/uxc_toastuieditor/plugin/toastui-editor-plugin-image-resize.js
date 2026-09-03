/**
 * Toast UI Editor Image Resize Plugin (Overlay 방식)
 *
 * ProseMirror가 contenteditable 내부 DOM을 관리하므로,
 * 리사이즈 UI를 에디터 컨테이너 위에 오버레이로 표시합니다.
 *
 * 리사이즈된 이미지는 저장 시 마크다운 내 HTML <img> 태그로 변환되어
 * width 속성이 보존됩니다.
 *
 * @version 2.1.0
 * @license MIT
 */

(function(global) {
    'use strict';

    var MIN_WIDTH = 50;
    var SIZE_PRESETS = ['25%', '50%', '75%', '100%'];

    // 리사이즈된 이미지 width 추적 (src → width)
    // ProseMirror DOM과 독립적으로 유지
    var imageWidths = {};

    function imageResizePlugin(context) {
        var eventEmitter = context.eventEmitter;
        var wwEditorEl = null;
        var activeImg = null;
        var overlay = null;
        var editorContainer = null;
        var scrollContainer = null;
        var startX = 0;
        var startWidth = 0;
        var aspectRatio = 1;
        var resizing = false;

        // WYSIWYG 에디터 요소 찾기
        function getWWEditor() {
            if (wwEditorEl) return wwEditorEl;
            wwEditorEl = document.querySelector('.toastui-editor-ww-container .ProseMirror') || null;
            return wwEditorEl;
        }

        // 에디터 UI 컨테이너 찾기 (오버레이 부모)
        function getEditorContainer() {
            if (editorContainer) return editorContainer;
            editorContainer = document.querySelector('.toastui-editor-defaultUI');
            return editorContainer;
        }

        // 스크롤 컨테이너 찾기
        function getScrollContainer() {
            if (scrollContainer) return scrollContainer;
            var editor = getWWEditor();
            if (editor) {
                scrollContainer = editor.closest('.toastui-editor-ww-container');
            }
            return scrollContainer;
        }

        // 오버레이 생성
        function createOverlay(img) {
            // 같은 이미지면 위치만 갱신
            if (activeImg === img && overlay) {
                updateOverlayPosition();
                return;
            }

            removeOverlay();

            var container = getEditorContainer();
            if (!container) return;

            activeImg = img;
            aspectRatio = img.naturalWidth / (img.naturalHeight || 1) || 1;

            // 오버레이 컨테이너
            overlay = document.createElement('div');
            overlay.className = 'toastui-image-resize-overlay';

            // 선택 테두리
            var border = document.createElement('div');
            border.className = 'toastui-image-resize-border';
            overlay.appendChild(border);

            // 핸들 (4 모서리)
            ['nw', 'ne', 'sw', 'se'].forEach(function(pos) {
                var handle = document.createElement('div');
                handle.className = 'toastui-image-resize-handle toastui-image-resize-handle--' + pos;
                handle.dataset.handle = pos;
                overlay.appendChild(handle);
            });

            // 크기 정보
            var info = document.createElement('div');
            info.className = 'toastui-image-resize-info';
            overlay.appendChild(info);

            // 크기 프리셋 툴바
            var toolbar = document.createElement('div');
            toolbar.className = 'toastui-image-resize-toolbar';
            SIZE_PRESETS.forEach(function(pct) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = pct;
                btn.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var editor = getWWEditor();
                    var parentWidth = editor ? editor.clientWidth - 40 : 600;
                    var ratio = parseInt(pct, 10) / 100;
                    var newWidth = Math.round(parentWidth * ratio);
                    applySize(newWidth);
                });
                toolbar.appendChild(btn);
            });
            overlay.appendChild(toolbar);

            // 핸들 드래그 이벤트
            overlay.addEventListener('mousedown', function(e) {
                var handle = e.target.closest('.toastui-image-resize-handle');
                if (handle) {
                    onHandleMouseDown(e, handle);
                }
            });

            // 에디터 컨테이너에 추가
            container.style.position = 'relative';
            container.appendChild(overlay);

            // 위치 업데이트
            updateOverlayPosition();
        }

        // 오버레이 제거
        function removeOverlay() {
            if (overlay && overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
            overlay = null;
            activeImg = null;
        }

        // 오버레이 위치 업데이트
        function updateOverlayPosition() {
            if (!overlay || !activeImg) return;

            var container = getEditorContainer();
            if (!container) return;

            var imgRect = activeImg.getBoundingClientRect();
            var containerRect = container.getBoundingClientRect();

            if (imgRect.width === 0 || imgRect.height === 0) {
                overlay.style.display = 'none';
                return;
            }

            overlay.style.display = '';
            overlay.style.top = (imgRect.top - containerRect.top) + 'px';
            overlay.style.left = (imgRect.left - containerRect.left) + 'px';
            overlay.style.width = imgRect.width + 'px';
            overlay.style.height = imgRect.height + 'px';

            var info = overlay.querySelector('.toastui-image-resize-info');
            if (info) {
                info.textContent = Math.round(imgRect.width) + ' \u00D7 ' + Math.round(imgRect.height);
            }
        }

        // 크기 적용
        function applySize(newWidth) {
            if (!activeImg) return;
            newWidth = Math.max(MIN_WIDTH, Math.round(newWidth));
            activeImg.style.width = newWidth + 'px';
            activeImg.style.height = 'auto';
            activeImg.setAttribute('width', newWidth);
            activeImg.removeAttribute('height');

            // width 추적 맵에 기록
            var src = activeImg.getAttribute('src');
            if (src) {
                imageWidths[src] = newWidth;
            }

            requestAnimationFrame(function() {
                updateOverlayPosition();
            });
        }

        // ProseMirror 재렌더링 후 width 재적용
        function reapplyWidths() {
            var wwEditor = getWWEditor();
            if (!wwEditor) return;
            var srcs = Object.keys(imageWidths);
            if (!srcs.length) return;

            var imgs = wwEditor.querySelectorAll('img');
            imgs.forEach(function(img) {
                var src = img.getAttribute('src');
                if (src && imageWidths[src]) {
                    img.style.width = imageWidths[src] + 'px';
                    img.style.height = 'auto';
                    img.setAttribute('width', imageWidths[src]);
                }
            });
        }

        // 기존 콘텐츠에서 width 정보 로드 (수정 모드)
        function loadInitialWidths() {
            var wwEditor = getWWEditor();
            if (!wwEditor) return;

            // 1) ProseMirror DOM에서 width 속성이 있는 이미지 찾기
            var imgs = wwEditor.querySelectorAll('img[width]');
            imgs.forEach(function(img) {
                var src = img.getAttribute('src');
                var width = parseInt(img.getAttribute('width'), 10);
                if (src && width && width > 0) {
                    imageWidths[src] = width;
                }
            });

            // 2) textarea 원본 콘텐츠에서 <img> 태그의 width 파싱
            //    ProseMirror가 width 속성을 제거하므로 원본에서 직접 추출
            var textareas = document.querySelectorAll('textarea[id]');
            textareas.forEach(function(textarea) {
                var val = textarea.value || '';
                var imgTagPattern = /<img\s+[^>]*src=["']([^"']+)["'][^>]*width=["'](\d+)["'][^>]*\/?>/gi;
                var imgTagPattern2 = /<img\s+[^>]*width=["'](\d+)["'][^>]*src=["']([^"']+)["'][^>]*\/?>/gi;
                var m;
                while ((m = imgTagPattern.exec(val)) !== null) {
                    var src = m[1];
                    var width = parseInt(m[2], 10);
                    if (src && width > 0) {
                        imageWidths[src] = width;
                    }
                }
                while ((m = imgTagPattern2.exec(val)) !== null) {
                    var width2 = parseInt(m[1], 10);
                    var src2 = m[2];
                    if (src2 && width2 > 0) {
                        imageWidths[src2] = width2;
                    }
                }
            });

            // 3) 추적 맵에 width가 있으면 DOM에 적용
            if (Object.keys(imageWidths).length > 0) {
                reapplyWidths();
            }
        }

        // toastuiSyncContent 래핑: 마크다운 내 리사이즈 이미지를 HTML img 태그로 변환
        function wrapSyncContent() {
            var originalSync = global.toastuiSyncContent;
            if (!originalSync || originalSync._imageResizePatched) return;

            global.toastuiSyncContent = function(editorInstance, textareaId) {
                var result = originalSync(editorInstance, textareaId);

                // 리사이즈된 이미지가 없으면 원본 그대로
                if (Object.keys(imageWidths).length === 0) return result;

                var textarea = document.getElementById(textareaId);
                if (!textarea || !textarea.value) return result;

                var content = textarea.value;

                // 마크다운 이미지를 순서대로 매칭
                // DOM 이미지 순서와 마크다운 이미지 순서가 동일하므로 인덱스 기반 매칭
                var wwEditor = document.querySelector('.toastui-editor-ww-container .ProseMirror');
                if (!wwEditor) return result;

                var domImgs = Array.from(wwEditor.querySelectorAll('img'));

                // 마크다운 마커 분리
                var marker = '';
                var body = content;
                if (content.indexOf('<!--TOASTUI_EDITOR_MARKDOWN-->\n') === 0) {
                    marker = '<!--TOASTUI_EDITOR_MARKDOWN-->\n';
                    body = content.substring(marker.length);
                }

                // 1) 마크다운 이미지 패턴 찾기 및 치환 ![alt](src)
                var imgPattern = /!\[([^\]]*)\]\(([^)]+)\)/g;
                var mdMatches = [];
                var m;
                while ((m = imgPattern.exec(body)) !== null) {
                    mdMatches.push({ index: m.index, length: m[0].length, alt: m[1], src: m[2] });
                }

                // 뒤에서부터 치환 (인덱스 유지)
                for (var i = mdMatches.length - 1; i >= 0; i--) {
                    var match = mdMatches[i];
                    var domImg = domImgs[i];
                    var src = domImg ? domImg.getAttribute('src') : match.src;
                    var width = imageWidths[src];

                    if (width) {
                        var htmlImg = '<img src="' + escapeHtml(match.src) + '" alt="' + escapeHtml(match.alt) + '" width="' + width + '" />';
                        body = body.substring(0, match.index) + htmlImg + body.substring(match.index + match.length);
                    }
                }

                // 2) HTML <img> 태그에 width 추가/갱신
                //    getMarkdown()이 이미지를 <img> 태그로 출력하는 경우 처리
                var htmlImgPattern = /<img\s+[^>]*src=["']([^"']+)["'][^>]*\/?>/gi;
                var htmlMatches = [];
                var m2;
                while ((m2 = htmlImgPattern.exec(body)) !== null) {
                    htmlMatches.push({ index: m2.index, length: m2[0].length, fullTag: m2[0], src: m2[1] });
                }

                for (var j = htmlMatches.length - 1; j >= 0; j--) {
                    var tag = htmlMatches[j];
                    var tagWidth = imageWidths[tag.src];
                    if (!tagWidth) continue;

                    var newTag;
                    if (/width=["']\d+["']/.test(tag.fullTag)) {
                        // 기존 width 갱신
                        newTag = tag.fullTag.replace(/width=["']\d+["']/, 'width="' + tagWidth + '"');
                    } else {
                        // width 속성 추가 (닫는 부분 앞에 삽입)
                        newTag = tag.fullTag.replace(/(\s*\/?>)$/, ' width="' + tagWidth + '"$1');
                    }
                    body = body.substring(0, tag.index) + newTag + body.substring(tag.index + tag.length);
                }

                textarea.value = marker + body;
                return result;
            };

            global.toastuiSyncContent._imageResizePatched = true;
        }

        // HTML 이스케이프
        function escapeHtml(str) {
            return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        // 드래그 핸들러
        function onHandleMouseDown(e, handle) {
            if (!activeImg) return;
            e.preventDefault();
            e.stopPropagation();

            resizing = true;
            startX = e.clientX;
            startWidth = activeImg.getBoundingClientRect().width;
            var handlePos = handle.dataset.handle;

            function onMouseMove(ev) {
                if (!resizing) return;
                var dx = ev.clientX - startX;
                if (handlePos === 'nw' || handlePos === 'sw') dx = -dx;
                applySize(startWidth + dx);
            }

            function onMouseUp() {
                resizing = false;
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
            }

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        }

        // 에디터 내 이미지 클릭 감지
        function onEditorClick(e) {
            if (resizing) return;

            var target = e.target;

            if (target.tagName === 'IMG') {
                createOverlay(target);
                return;
            }

            removeOverlay();
        }

        // 문서 클릭 시 오버레이 제거 (에디터 외부)
        function onDocumentMouseDown(e) {
            if (!overlay) return;
            if (overlay.contains(e.target)) return;
            var editor = getWWEditor();
            if (editor && editor.contains(e.target)) return;
            removeOverlay();
        }

        // 초기화
        function init() {
            var editor = getWWEditor();
            if (!editor) {
                setTimeout(init, 200);
                return;
            }

            editor.addEventListener('click', onEditorClick);
            document.addEventListener('mousedown', onDocumentMouseDown);

            // 스크롤 시 위치 갱신
            var sc = getScrollContainer();
            if (sc) {
                sc.addEventListener('scroll', function() {
                    if (overlay) requestAnimationFrame(updateOverlayPosition);
                });
            }

            // 윈도우 리사이즈 시 위치 갱신
            window.addEventListener('resize', function() {
                if (overlay) requestAnimationFrame(updateOverlayPosition);
            });

            // 기존 콘텐츠의 width 로드 (수정 모드)
            setTimeout(loadInitialWidths, 500);

            // 에디터 변경 시 width 재적용 (ProseMirror 재렌더링 대응)
            eventEmitter.listen('change', function() {
                if (Object.keys(imageWidths).length > 0) {
                    setTimeout(reapplyWidths, 50);
                }
            });

            // toastuiSyncContent 래핑
            wrapSyncContent();
        }

        // 에디터 모드 전환 시 정리
        eventEmitter.listen('changeMode', function() {
            removeOverlay();
            wwEditorEl = null;
            editorContainer = null;
            scrollContainer = null;
            setTimeout(function() {
                init();
                // 모드 전환 후 width 재적용
                setTimeout(reapplyWidths, 300);
            }, 200);
        });

        // 초기 바인딩
        setTimeout(init, 300);

        return {};
    }

    // 전역 노출
    if (typeof global !== 'undefined') {
        global.imageResizePlugin = imageResizePlugin;
    }

})(typeof window !== 'undefined' ? window : this);
