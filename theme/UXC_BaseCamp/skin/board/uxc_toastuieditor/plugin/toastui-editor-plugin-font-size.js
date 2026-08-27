/**
 * TOAST UI Editor Font Size Plugin
 * @version 1.0.1
 * @license MIT
 * Based on TOAST UI Editor v3.x plugin structure
 */

(function(global) {
    'use strict';

    // 폰트 사이즈 목록
    const FONT_SIZES = [8, 9, 10, 11, 12, 14, 16, 18, 20, 22, 24, 26, 28, 36, 48, 72, 96];
    
    /**
     * Font Size 플러그인 팩토리 함수
     * TOAST UI Editor v3 플러그인 구조에 맞춤
     */
    function fontSizePlugin(context, options) {
        const { eventEmitter, i18n, pmState, usageStatistics = true } = context;
        const container = context.getEditorElements && context.getEditorElements() || {};
        
        let currentEditor = null;
        let currentContainerClass = '';
        let dropdownEl = null;
        let targetEditor = null;
        
        // 한국어 텍스트 설정
        i18n.setLanguage(['ko', 'ko-KR'], {
            'Font size': '글자 크기'
        });
        i18n.setLanguage(['en', 'en-US'], {
            'Font size': 'Font size'
        });
        
        /**
         * 드롭다운 생성
         */
        function createDropdown() {
            const dropdown = document.createElement('div');
            dropdown.className = 'toastui-editor-popup-body';
            dropdown.style.padding = '0';
            
            // 프리셋 사이즈 목록
            const list = document.createElement('ul');
            list.className = 'toastui-editor-font-size-list';
            list.style.listStyle = 'none';
            list.style.margin = '0';
            list.style.padding = '8px 0';
            list.style.maxHeight = '300px';
            list.style.overflowY = 'auto';
            list.style.minWidth = '150px';
            
            FONT_SIZES.forEach(size => {
                const item = document.createElement('li');
                item.className = 'toastui-editor-font-size-item';
                item.textContent = size + 'px';
                item.style.padding = '6px 16px';
                item.style.cursor = 'pointer';
                item.style.fontSize = Math.min(size, 24) + 'px';
                
                item.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f0f0f0';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
                
                item.addEventListener('click', function() {
                    const selectedSize = size + 'px';
                    applyFontSize(selectedSize);
                    eventEmitter.emit('closePopup');
                    if (targetEditor) {
                        targetEditor.focus();
                    }
                });
                
                list.appendChild(item);
            });
            
            dropdown.appendChild(list);
            
            // 커스텀 입력 필드
            const inputWrapper = document.createElement('div');
            inputWrapper.style.padding = '8px 16px';
            inputWrapper.style.borderTop = '1px solid #e1e1e1';
            
            const input = document.createElement('input');
            input.type = 'number';
            input.className = 'toastui-editor-font-size-input';
            input.placeholder = '직접입력 (px)';
            input.min = '1';
            input.max = '200';
            input.style.width = '100%';
            input.style.padding = '6px 8px';
            input.style.border = '1px solid #ddd';
            input.style.borderRadius = '3px';
            input.style.boxSizing = 'border-box';
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const size = parseInt(this.value);
                    if (size && size > 0 && size <= 200) {
                        applyFontSize(size + 'px');
                        eventEmitter.emit('closePopup');
                        if (targetEditor) {
                            targetEditor.focus();
                        }
                    }
                }
            });
            
            inputWrapper.appendChild(input);
            dropdown.appendChild(inputWrapper);
            
            return dropdown;
        }
        
        /**
         * 현재 에디터 엘리먼트 찾기
         */
        function getCurrentEditorEl(containerEl, containerClass) {
            const defaultUI = containerEl.closest('.toastui-editor-defaultUI');
            if (!defaultUI) return null;
            return defaultUI.querySelector('.' + containerClass + ' .ProseMirror');
        }
        
        /**
         * 폰트 사이즈 적용
         */
        function applyFontSize(fontSize) {
            eventEmitter.emit('command', 'fontSize', { selectedSize: fontSize });
        }
        
        // 포커스 이벤트 리스너
        eventEmitter.listen('focus', function(editType) {
            currentContainerClass = 'toastui-editor-' + (editType === 'markdown' ? 'md' : 'ww') + '-container';
        });
        
        // 드롭다운 DOM 생성
        dropdownEl = createDropdown();
        
        // 드롭다운 클릭 이벤트
        dropdownEl.addEventListener('click', function(e) {
            const button = e.target.closest('button');
            if (button || e.target.tagName === 'LI') {
                targetEditor = getCurrentEditorEl(dropdownEl, currentContainerClass);
            }
        });
        
        // 툴바 아이템 정의
        const toolbarItem = {
            name: 'fontSize',
            tooltip: i18n.get('Font size'),
            className: 'toastui-editor-toolbar-icons font-size',
            style: { backgroundImage: 'none' },
            popup: {
                className: 'toastui-editor-popup-font-size',
                body: dropdownEl,
                style: { width: 'auto' }
            },
            text: '@'  // 임시 텍스트, 나중에 교체됨
        };
        
        // Markdown 커맨드
        const markdownCommands = {
            fontSize: function(payload, state, dispatch) {
                const { selectedSize } = payload;
                const { tr, selection, schema } = state;
                
                if (!selectedSize) return false;
                
                const { from, to } = selection;
                const content = selection.content();
                
                if (content.size === 0) {
                    // 선택된 텍스트가 없으면 샘플 텍스트 삽입
                    const text = '<span style="font-size: ' + selectedSize + '">텍스트</span>';
                    tr.replaceSelectionWith(schema.text(text));
                } else {
                    // 선택된 텍스트를 span으로 감싸기
                    const text = content.content.textBetween(0, content.content.size, '\n');
                    
                    // 기존 span 태그와 스타일 확인
                    const spanMatch = text.match(/<span\s+style="([^"]+)">([^<]+)<\/span>/);
                    if (spanMatch) {
                        const existingStyle = spanMatch[1];
                        const innerText = spanMatch[2];
                        
                        // color 스타일 추출
                        const colorMatch = existingStyle.match(/color:\s*([^;]+)/);
                        let newStyle = 'font-size: ' + selectedSize;
                        if (colorMatch) {
                            newStyle = 'color: ' + colorMatch[1] + '; font-size: ' + selectedSize;
                        }
                        
                        const styledText = '<span style="' + newStyle + '">' + innerText + '</span>';
                        tr.replaceSelectionWith(schema.text(styledText));
                    } else {
                        const styledText = '<span style="font-size: ' + selectedSize + '">' + text + '</span>';
                        tr.replaceSelectionWith(schema.text(styledText));
                    }
                }
                
                dispatch(tr);
                return true;
            }
        };
        
        // WYSIWYG 커맨드
        const wysiwygCommands = {
            fontSize: function(payload, state, dispatch) {
                const { selectedSize } = payload;
                const { tr, selection, schema } = state;
                
                if (!selectedSize) return false;
                
                const { from, to } = selection;
                
                // 기존 span 마크 확인 및 스타일 병합
                let existingStyle = '';
                state.doc.nodesBetween(from, to, (node, pos) => {
                    if (node.marks) {
                        node.marks.forEach(mark => {
                            if (mark.type.name === 'span' && mark.attrs.htmlAttrs && mark.attrs.htmlAttrs.style) {
                                const style = mark.attrs.htmlAttrs.style;
                                // color 스타일 추출
                                const colorMatch = style.match(/color:\s*([^;]+)/);
                                if (colorMatch) {
                                    existingStyle = 'color: ' + colorMatch[1] + '; ';
                                }
                            }
                        });
                    }
                });
                
                const attrs = {
                    htmlAttrs: {
                        style: existingStyle + 'font-size: ' + selectedSize
                    }
                };
                
                const mark = schema.marks.span ? schema.marks.span.create(attrs) : null;
                
                if (mark) {
                    // 기존 span 마크 제거
                    if (schema.marks.span) {
                        tr.removeMark(from, to, schema.marks.span);
                    }
                    
                    if (from === to) {
                        // 커서 위치에 텍스트 삽입
                        const node = schema.text('텍스트', [mark]);
                        tr.replaceSelectionWith(node);
                    } else {
                        // 선택된 텍스트에 새 마크 적용
                        tr.addMark(from, to, mark);
                    }
                    dispatch(tr);
                    return true;
                }
                
                return false;
            }
        };
        
        // HTML 렌더러
        const toHTMLRenderers = {
            htmlInline: {
                span: function(node, { entering }) {
                    const attrs = node.attrs;
                    if (entering) {
                        return {
                            type: 'openTag',
                            tagName: 'span',
                            attributes: attrs
                        };
                    } else {
                        return {
                            type: 'closeTag',
                            tagName: 'span'
                        };
                    }
                }
            }
        };
        
        // 플러그인이 로드된 후 툴바 버튼 스타일 수정
        setTimeout(function() {
            const button = document.querySelector('.toastui-editor-toolbar-icons.font-size');
            if (button) {
                // 텍스트 아이콘으로 변경
                button.style.backgroundImage = 'none';
                button.innerHTML = '<span style="font-size: 11px; font-weight: bold;">가</span><span style="font-size: 16px; font-weight: bold;">가</span>';
                button.style.display = 'inline-flex';
                button.style.alignItems = 'center';
                button.style.justifyContent = 'center';
                button.style.gap = '1px';
            }
        }, 100);
        
        // 플러그인 객체 반환
        return {
            markdownCommands: markdownCommands,
            wysiwygCommands: wysiwygCommands,
            toolbarItems: [
                {
                    groupIndex: 0,
                    itemIndex: 4,
                    item: toolbarItem
                }
            ],
            toHTMLRenderers: toHTMLRenderers
        };
    }
    
    // 글로벌 스코프에 플러그인 등록
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = fontSizePlugin;
    } else if (typeof define === 'function' && define.amd) {
        define([], function() {
            return fontSizePlugin;
        });
    } else {
        // 브라우저 환경
        global.fontSizePlugin = fontSizePlugin;
        
        // TOAST UI Editor 네임스페이스에도 등록
        if (!global.toastui) global.toastui = {};
        if (!global.toastui.Editor) global.toastui.Editor = {};
        if (!global.toastui.Editor.plugin) global.toastui.Editor.plugin = {};
        global.toastui.Editor.plugin.fontSize = fontSizePlugin;
    }
    
})(typeof window !== 'undefined' ? window : this);