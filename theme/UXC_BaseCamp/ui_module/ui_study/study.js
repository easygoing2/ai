(function () {
    'use strict';
    let pending;
    document.addEventListener('error', function (event) {
        if (event.target.matches && event.target.matches('.study-lesson__thumbnail img')) event.target.hidden = true;
    }, true);
    // Real links remain usable with JavaScript disabled. Refresh only the sidebar when possible.
    document.addEventListener('click', async function (event) {
        const link = event.target.closest('#studySidebar a[data-study-date]');
        if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        if (!window.fetch || !window.AbortController) return;
        event.preventDefault();
        if (pending) pending.abort();
        const request = new AbortController();
        pending = request;
        const sidebar = document.getElementById('studySidebar');
        const focusLabel = link.getAttribute('aria-label');
        sidebar.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(link.href, { signal: request.signal, credentials: 'same-origin', cache: 'no-store' });
            if (!response.ok) throw new Error('Unable to load study calendar');
            const html = await response.text();
            if (pending !== request) return;
            const page = new DOMParser().parseFromString(html, 'text/html');
            const updated = page.getElementById('studySidebar');
            if (!updated) throw new Error('Study calendar unavailable');
            sidebar.replaceWith(updated);
            const links = Array.from(updated.querySelectorAll('[data-study-date]'));
            const focus = links.find(function (item) { return item.getAttribute('aria-label') === focusLabel; }) || updated.querySelector('.is-selected');
            if (focus) focus.focus({ preventScroll: true });
            updated.querySelector('[role="status"]').textContent = updated.querySelector('.study-calendar__month strong').textContent + ' 학습 기록을 불러왔습니다.';
        } catch (error) {
            if (error.name === 'AbortError') return;
            sidebar.removeAttribute('aria-busy');
            const status = sidebar.querySelector('[role="status"]');
            status.classList.remove('sound_only');
            status.textContent = '학습 기록을 불러오지 못했습니다. 날짜를 다시 선택해 주세요.';
        } finally {
            if (pending === request) pending = null;
        }
    });
}());
