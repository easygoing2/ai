/**
 * Board Markdown Renderer
 * Toast UI Editor를 사용한 마크다운 렌더링 스크립트
 */

document.addEventListener('DOMContentLoaded', function() {
    // Toast UI Editor가 로드될 때까지 대기
    function waitForToastUI(callback) {
        if (window.toastui && window.toastui.Editor) {
            callback();
        } else {
            setTimeout(() => waitForToastUI(callback), 50);
        }
    }

    waitForToastUI(function() {
        // 마크다운 콘텐츠 찾기
        const contentContainer = document.querySelector('[data-markdown="true"]');

        if (contentContainer) {
            const sourceDiv = document.getElementById('markdown-source');
            const viewerDiv = document.getElementById('markdown-viewer');

            if (sourceDiv && viewerDiv) {
                // 마크다운 텍스트 가져오기
                const markdown = sourceDiv.textContent;

                try {
                    // 임시 컨테이너 생성
                    const tempDiv = document.createElement('div');
                    tempDiv.style.cssText = 'position:absolute;left:-9999px;top:-9999px;';
                    document.body.appendChild(tempDiv);

                    // Viewer 인스턴스 생성하여 마크다운 렌더링
                    const tempViewer = new toastui.Editor({
                        el: tempDiv,
                        viewer: true,
                        initialValue: markdown || ' ',
                        usageStatistics: false,
                        autofocus: false
                    });

                    // 렌더링된 HTML을 DOM에서 추출 (뷰어 전용 번들 호환)
                    const previewEl = tempDiv.querySelector('.toastui-editor-contents');
                    const htmlContent = previewEl ? previewEl.innerHTML : (tempDiv.innerHTML || '');

                    // 뷰어 div에 HTML 삽입
                    viewerDiv.innerHTML = htmlContent;
                    // 모든 링크를 새 창에서 열리도록 설정
                    var links = viewerDiv.querySelectorAll('a');
                    links.forEach(function(link) {
                        link.setAttribute('target', '_blank');
                        link.setAttribute('rel', 'noopener noreferrer');
                    });
                    viewerDiv.classList.add('toastui-editor-contents');

                    // 임시 컨테이너 제거
                    document.body.removeChild(tempDiv);

                    // 다크모드 체크 및 적용
                    // CSS: .toastui-editor-dark .toastui-editor-contents p {} 구조이므로
                    // 부모(contentContainer)에 dark 클래스, 자식(viewerDiv)에 contents 클래스
                    var isDarkMode = localStorage.getItem('theme') === 'darkMode' ||
                                    document.documentElement.classList.contains('darkMode');
                    if (isDarkMode) {
                        contentContainer.classList.add('toastui-editor-dark');
                    }

                    // 렌더링 완료 후 페이드인 효과로 표시
                    setTimeout(() => {
                        contentContainer.style.opacity = '1';
                    }, 50);

                    // 코드 하이라이팅 적용
                    setTimeout(() => {
                        if (window.Prism) {
                            Prism.highlightAllUnder(viewerDiv);
                        }
                    }, 100);

                    // 소스 숨기기
                    sourceDiv.style.display = 'none';

                } catch (error) {
                    console.error('Error rendering markdown:', error);
                    // 에러 발생 시 원본 텍스트 표시
                    viewerDiv.innerHTML = '<pre>' + markdown + '</pre>';
                }
            }

            // 다크모드 실시간 전환 감지 (MutationObserver)
            var darkObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        var isDark = document.documentElement.classList.contains('darkMode');
                        if (isDark) {
                            contentContainer.classList.add('toastui-editor-dark');
                        } else {
                            contentContainer.classList.remove('toastui-editor-dark');
                        }
                    }
                });
            });
            darkObserver.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });
        }
    });
});
