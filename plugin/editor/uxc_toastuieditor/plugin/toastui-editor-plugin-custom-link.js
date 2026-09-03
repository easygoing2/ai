/**
 * TOAST UI Editor : Custom Link Plugin
 * 타겟 옵션을 포함한 링크 삽입 플러그인
 */

(function (global, factory) {
    if (typeof exports === 'object' && typeof module !== 'undefined') {
        module.exports = factory();
    } else if (typeof define === 'function' && define.amd) {
        define(factory);
    } else {
        global = global || self;
        global.toastui = global.toastui || {};
        global.toastui.Editor = global.toastui.Editor || {};
        global.toastui.Editor.plugin = global.toastui.Editor.plugin || {};
        global.toastui.Editor.plugin.customLink = factory();
    }
}(this, function () {
    'use strict';

    function customLinkPlugin(context, options) {
        const { eventEmitter, i18n, pmState } = context;
        const { preset } = options || {};
        
        // 팝업 HTML 생성
        const container = document.createElement('div');
        container.innerHTML = `
            <div class="toastui-editor-custom-link-popup">
                <div class="popup-body">
                    <label>URL</label>
                    <input type="text" class="url-input" style="width: 100%; margin-bottom: 8px;" />
                    
                    <label>링크 텍스트</label>
                    <input type="text" class="text-input" style="width: 100%; margin-bottom: 8px;" />
                    
                    <label>타겟</label>
                    <select class="target-select" style="width: 100%; margin-bottom: 12px;">
                        <option value="">기본 (_self)</option>
                        <option value="_blank">새 창 (_blank)</option>
                    </select>
                    
                    <button type="button" class="ok-button">확인</button>
                </div>
            </div>
        `;
        
        const urlInput = container.querySelector('.url-input');
        const textInput = container.querySelector('.text-input');
        const targetSelect = container.querySelector('.target-select');
        const okButton = container.querySelector('.ok-button');
        
        let currentMode = 'markdown';
        let currentEditor = null;
        
        // 모드 변경 감지
        eventEmitter.listen('changeMode', (mode) => {
            currentMode = mode;
        });
        
        // 에디터 참조 저장
        eventEmitter.listen('focus', (editorType) => {
            const containerClass = `toastui-editor-${editorType === 'markdown' ? 'md' : 'ww'}-container`;
            const editorContainer = container.closest('.toastui-editor-defaultUI');
            if (editorContainer) {
                currentEditor = editorContainer.querySelector(`.${containerClass} .ProseMirror`);
            }
        });
        
        // 확인 버튼 클릭
        okButton.addEventListener('click', () => {
            const url = urlInput.value.trim();
            const text = textInput.value.trim() || url;
            const target = targetSelect.value;
            
            if (url) {
                eventEmitter.emit('command', 'customLink', {
                    url: url,
                    text: text,
                    target: target
                });
                eventEmitter.emit('closePopup');
                
                // 입력 필드 초기화
                urlInput.value = '';
                textInput.value = '';
                targetSelect.value = '';
                
                // 에디터에 포커스 복원
                if (currentEditor) {
                    currentEditor.focus();
                }
            }
        });
        
        // 툴바 아이템 정의
        const toolbarItem = {
            name: 'customLink',
            tooltip: i18n.get('Insert link'),
            className: 'toastui-editor-toolbar-icons link',
            popup: {
                className: 'toastui-editor-popup-add-custom-link',
                body: container,
                style: { width: '320px' }
            }
        };
        
        return {
            markdownCommands: {
                customLink: (payload, state, dispatch) => {
                    const { url, text, target } = payload;
                    const { tr, selection, schema } = state;
                    
                    let linkText;
                    if (target === '_blank') {
                        // HTML 형태로 삽입
                        linkText = `<a href="${url}" target="_blank">${text}</a>`;
                    } else {
                        // 일반 마크다운 링크
                        linkText = `[${text}](${url})`;
                    }
                    
                    tr.replaceSelectionWith(schema.text(linkText));
                    dispatch(tr);
                    return true;
                }
            },
            wysiwygCommands: {
                customLink: (payload, state, dispatch) => {
                    const { url, text, target } = payload;
                    const { tr, selection, schema } = state;
                    const { from, to } = selection;
                    
                    // 기존 텍스트 삭제
                    tr.deleteRange(from, to);
                    
                    // 링크 속성 설정
                    const attrs = { 
                        href: url,
                        target: target || null
                    };
                    
                    // 링크 마크 생성
                    const linkMark = schema.marks.link.create(attrs);
                    
                    // 텍스트 노드 생성 및 링크 마크 적용
                    const textNode = schema.text(text, [linkMark]);
                    tr.insert(from, textNode);
                    
                    dispatch(tr);
                    return true;
                }
            },
            toolbarItems: [
                {
                    groupIndex: 3,
                    itemIndex: 2,
                    item: toolbarItem
                }
            ],
            toHTMLRenderers: {
                htmlInline: {
                    link(node, { entering }) {
                        const { destination, target } = node;
                        
                        if (entering) {
                            const attrs = { href: destination };
                            if (target) {
                                attrs.target = target;
                            }
                            return {
                                type: 'openTag',
                                tagName: 'a',
                                attributes: attrs
                            };
                        } else {
                            return {
                                type: 'closeTag',
                                tagName: 'a'
                            };
                        }
                    }
                }
            }
        };
    }

    return customLinkPlugin;
}));