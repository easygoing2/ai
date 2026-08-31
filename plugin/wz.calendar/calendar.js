(function () {
    'use strict';

    window.addEventListener('pageshow', function (event) {
        if (event.persisted) window.location.reload();
    });

    function ready(callback) {
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', callback);
        else callback();
    }

    ready(function () {
        var root = document.getElementById('wzcCalendar');
        var configNode = document.getElementById('wzcRuntimeConfig');
        if (!root || !configNode) return;

        var config;
        try { config = JSON.parse(configNode.textContent); } catch (e) { return; }
        var events = config.events || {};
        var sortables = [];
        var currentFilter = 0;
        var activeDisplayDate = config.selectedDay;
        var eventModal = document.getElementById('wzcEventModal');
        var settingsModal = document.getElementById('wzcSettingsModal');
        var eventForm = document.getElementById('wzcEventForm');
        var deleteButton = document.getElementById('wzcDeleteEvent');
        var orderActions = document.getElementById('wzcOrderActions');
        var toast = document.getElementById('wzcToast');
        var toastTimer = null;

        function api(path, payload) {
            payload = payload || {};
            payload.csrf_token = config.csrfToken;
            return fetch(config.baseUrl + '/' + path, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                body: JSON.stringify(payload)
            }).then(function (response) {
                return response.json().catch(function () {
                    return {success: false, message: '서버 응답을 확인할 수 없습니다.'};
                }).then(function (body) {
                    if (!response.ok || !body.success) {
                        var error = new Error(body.message || '요청을 처리하지 못했습니다.');
                        error.code = body.code || '';
                        error.status = response.status;
                        throw error;
                    }
                    return body;
                });
            });
        }

        function showToast(message, actionLabel, actionCallback, duration) {
            if (!toast) return;
            window.clearTimeout(toastTimer);
            toast.hidden = false;
            toast.querySelector('.wzc-toast-message').textContent = message;
            var action = toast.querySelector('.wzc-toast-action');
            if (actionLabel && actionCallback) {
                action.hidden = false;
                action.textContent = actionLabel;
                action.onclick = actionCallback;
            } else {
                action.hidden = true;
                action.onclick = null;
            }
            toastTimer = window.setTimeout(hideToast, duration || 5000);
        }

        function hideToast() {
            if (toast) toast.hidden = true;
            window.clearTimeout(toastTimer);
        }

        if (toast) toast.querySelector('.wzc-toast-close').addEventListener('click', hideToast);

        function openModal(modal) {
            if (!modal) return;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('wzc-modal-open');
            window.setTimeout(function () {
                var target = modal.querySelector('input:not([type=hidden]), button, select, textarea');
                if (target) target.focus();
            }, 30);
        }

        function closeModal(modal) {
            if (!modal) return;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            if (!document.querySelector('.wzc-modal.is-open')) document.body.classList.remove('wzc-modal-open');
        }

        document.querySelectorAll('[data-close-modal]').forEach(function (button) {
            button.addEventListener('click', function () { closeModal(button.closest('.wzc-modal')); });
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') document.querySelectorAll('.wzc-modal.is-open').forEach(closeModal);
        });

        function setTimeFields() {
            var allDay = eventForm.elements.all_day.checked;
            eventForm.querySelectorAll('.wzc-time-field input').forEach(function (input) { input.disabled = allDay; });
            eventForm.querySelectorAll('.wzc-time-field').forEach(function (field) { field.style.opacity = allDay ? '.45' : '1'; });
        }

        function resetEventForm(date) {
            eventForm.reset();
            eventForm.elements.event_id.value = '0';
            eventForm.elements.version.value = '0';
            eventForm.elements.start_date.value = date || config.selectedDay;
            eventForm.elements.end_date.value = date || config.selectedDay;
            eventForm.elements.all_day.checked = true;
            eventForm.querySelector('.wzc-form-message').hidden = true;
            deleteButton.hidden = true;
            orderActions.hidden = true;
            eventModal.querySelector('#wzcEventModalTitle').textContent = '일정 추가';
            activeDisplayDate = date || config.selectedDay;
            setTimeFields();
        }

        function fillEventForm(eventData, displayDate) {
            resetEventForm(eventData.start_date);
            eventForm.elements.event_id.value = eventData.id;
            eventForm.elements.version.value = eventData.version;
            eventForm.elements.title.value = eventData.title || '';
            eventForm.elements.category_id.value = eventData.category_id || 0;
            eventForm.elements.start_date.value = eventData.start_date;
            eventForm.elements.end_date.value = eventData.end_date;
            eventForm.elements.all_day.checked = !!Number(eventData.all_day);
            eventForm.elements.start_time.value = eventData.start_time || '';
            eventForm.elements.end_time.value = eventData.end_time || '';
            eventForm.elements.location.value = eventData.location || '';
            eventForm.elements.content.value = eventData.content || '';
            eventForm.elements.link_url.value = eventData.link_url || '';
            deleteButton.hidden = false;
            orderActions.hidden = false;
            eventModal.querySelector('#wzcEventModalTitle').textContent = '일정 상세 및 수정';
            activeDisplayDate = displayDate || eventData.start_date;
            setTimeFields();
        }

        root.querySelectorAll('[data-add-event]').forEach(function (button) {
            button.addEventListener('click', function () {
                resetEventForm(button.getAttribute('data-add-event'));
                openModal(eventModal);
            });
        });

        document.querySelectorAll('[data-open-modal="settings"]').forEach(function (button) {
            button.addEventListener('click', function () { openModal(settingsModal); });
        });

        document.addEventListener('click', function (event) {
            var opener = event.target.closest('.wzc-event-open');
            if (!opener) return;
            var article = opener.closest('.wzc-event');
            var id = Number(article ? article.getAttribute('data-event-id') : opener.getAttribute('data-event-id'));
            var data = events[id];
            if (!data) return;
            var list = article ? article.closest('.wzc-event-list') : null;
            fillEventForm(data, list ? list.getAttribute('data-date') : config.selectedDay);
            openModal(eventModal);
        });

        eventForm.elements.all_day.addEventListener('change', setTimeFields);
        eventForm.elements.start_date.addEventListener('change', function () {
            if (!eventForm.elements.end_date.value || eventForm.elements.end_date.value < this.value) eventForm.elements.end_date.value = this.value;
        });

        eventForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var message = eventForm.querySelector('.wzc-form-message');
            message.hidden = true;
            var submit = eventForm.querySelector('[type=submit]');
            submit.disabled = true;
            var payload = {
                event_id: Number(eventForm.elements.event_id.value || 0),
                version: Number(eventForm.elements.version.value || 0),
                title: eventForm.elements.title.value,
                category_id: Number(eventForm.elements.category_id.value || 0),
                start_date: eventForm.elements.start_date.value,
                end_date: eventForm.elements.end_date.value,
                all_day: eventForm.elements.all_day.checked ? 1 : 0,
                start_time: eventForm.elements.start_time.value,
                end_time: eventForm.elements.end_time.value,
                location: eventForm.elements.location.value,
                content: eventForm.elements.content.value,
                link_url: eventForm.elements.link_url.value
            };
            api('ajax.event.save.php', payload).then(function (body) {
                closeModal(eventModal);
                showToast(body.message || '일정을 저장했습니다.');
                window.setTimeout(function () { window.location.reload(); }, 300);
            }).catch(function (error) {
                message.textContent = error.message;
                message.hidden = false;
            }).finally(function () { submit.disabled = false; });
        });

        deleteButton.addEventListener('click', function () {
            if (!window.confirm('이 일정을 휴지통으로 이동하시겠습니까?')) return;
            deleteButton.disabled = true;
            api('ajax.event.delete.php', {
                event_id: Number(eventForm.elements.event_id.value),
                version: Number(eventForm.elements.version.value)
            }).then(function (body) {
                closeModal(eventModal);
                showToast(body.message || '일정을 삭제했습니다.');
                window.setTimeout(function () { window.location.reload(); }, 300);
            }).catch(function (error) {
                var message = eventForm.querySelector('.wzc-form-message');
                message.textContent = error.message;
                message.hidden = false;
            }).finally(function () { deleteButton.disabled = false; });
        });

        root.querySelectorAll('.wzc-more').forEach(function (button) {
            button.addEventListener('click', function () {
                var list = button.previousElementSibling;
                var expanded = list.classList.toggle('is-expanded');
                button.textContent = expanded ? '접기' : '+' + button.getAttribute('data-more-count') + '개 더보기';
            });
        });

        function eventIds(list) {
            return Array.prototype.map.call(list.querySelectorAll(':scope > .wzc-event'), function (item) {
                return Number(item.getAttribute('data-event-id'));
            });
        }

        function setDragEnabled(enabled) {
            sortables.forEach(function (sortable) { sortable.option('disabled', !enabled); });
        }

        root.querySelectorAll('.wzc-filter-chip').forEach(function (button) {
            button.addEventListener('click', function () {
                currentFilter = Number(button.getAttribute('data-category-filter'));
                root.querySelectorAll('.wzc-filter-chip').forEach(function (chip) { chip.classList.toggle('is-active', chip === button); });
                root.querySelectorAll('.wzc-event').forEach(function (item) {
                    var match = !currentFilter || Number(item.getAttribute('data-category-id')) === currentFilter;
                    item.hidden = !match;
                });
                var note = document.getElementById('wzcFilterNote');
                note.hidden = !currentFilter;
                setDragEnabled(!currentFilter && (config.touchDragUse || !window.matchMedia('(pointer: coarse)').matches));
            });
        });

        function saveOrder(date, list) {
            return api('ajax.event.order.php', {date: date, event_ids: eventIds(list)});
        }

        orderActions.querySelectorAll('[data-order-direction]').forEach(function (button) {
            button.addEventListener('click', function () {
                var id = Number(eventForm.elements.event_id.value);
                var list = root.querySelector('.wzc-event-list[data-date="' + activeDisplayDate + '"]');
                if (!list) return showToast('현재 화면에서 일정 순서를 변경할 수 없습니다.');
                var item = list.querySelector('.wzc-event[data-event-id="' + id + '"]');
                if (!item) return showToast('현재 날짜에서 일정을 찾을 수 없습니다.');
                var direction = button.getAttribute('data-order-direction');
                var sibling = direction === 'up' ? item.previousElementSibling : (direction === 'down' ? item.nextElementSibling : list.firstElementChild);
                if (!sibling || (direction === 'top' && sibling === item)) return showToast(direction === 'down' ? '이미 마지막 일정입니다.' : '이미 첫 번째 일정입니다.');
                if (direction === 'up' || direction === 'top') list.insertBefore(item, sibling);
                else list.insertBefore(sibling, item);
                button.disabled = true;
                saveOrder(activeDisplayDate, list).then(function () {
                    closeModal(eventModal);
                    showToast('일정 순서를 변경했습니다.');
                    window.setTimeout(function () { window.location.reload(); }, 250);
                }).catch(function (error) {
                    showToast(error.message);
                    window.setTimeout(function () { window.location.reload(); }, 600);
                }).finally(function () { button.disabled = false; });
            });
        });

        function storeUndo(eventId, undo) {
            try {
                sessionStorage.setItem('wzc_move_undo', JSON.stringify({
                    event_id: eventId,
                    source_date: undo.source_date,
                    target_date: undo.target_date,
                    version: undo.version,
                    expires: Date.now() + 12000
                }));
            } catch (e) {}
        }

        function initSortable() {
            if (typeof window.Sortable === 'undefined') {
                showToast('드래그 라이브러리를 불러오지 못했습니다. 날짜는 일정 수정 화면에서 변경할 수 있습니다.');
                return;
            }
            var coarse = window.matchMedia('(pointer: coarse)').matches;
            root.querySelectorAll('.wzc-event-list').forEach(function (list) {
                var sortable = new window.Sortable(list, {
                    group: 'personal-calendar-events',
                    draggable: '.wzc-event',
                    handle: '.wzc-drag-handle',
                    animation: 150,
                    delay: 300,
                    delayOnTouchOnly: true,
                    touchStartThreshold: 5,
                    fallbackTolerance: 5,
                    emptyInsertThreshold: 18,
                    forceFallback: coarse,
                    fallbackOnBody: true,
                    scroll: true,
                    scrollSensitivity: 55,
                    scrollSpeed: 12,
                    ghostClass: 'wzc-event-ghost',
                    chosenClass: 'wzc-event-chosen',
                    dragClass: 'wzc-event-dragging',
                    disabled: coarse && !config.touchDragUse,
                    onMove: function (evt) {
                        root.querySelectorAll('.wzc-day.is-drag-over').forEach(function (day) { day.classList.remove('is-drag-over'); });
                        var day = evt.to.closest('.wzc-day');
                        if (day) day.classList.add('is-drag-over');
                    },
                    onEnd: function (evt) {
                        root.querySelectorAll('.wzc-day.is-drag-over').forEach(function (day) { day.classList.remove('is-drag-over'); });
                        if (evt.from === evt.to && evt.oldIndex === evt.newIndex) return;
                        var sourceDate = evt.from.getAttribute('data-date');
                        var targetDate = evt.to.getAttribute('data-date');
                        var eventId = Number(evt.item.getAttribute('data-event-id'));
                        var eventData = events[eventId];
                        evt.item.classList.add('is-saving');
                        if (sourceDate === targetDate) {
                            saveOrder(targetDate, evt.to).then(function (body) {
                                showToast(body.message || '일정 순서를 저장했습니다.');
                            }).catch(function (error) {
                                showToast(error.message + ' 화면을 다시 불러옵니다.');
                                window.setTimeout(function () { window.location.reload(); }, 700);
                            }).finally(function () { evt.item.classList.remove('is-saving'); });
                            return;
                        }
                        api('ajax.event.move.php', {
                            event_id: eventId,
                            version: eventData ? Number(eventData.version) : 0,
                            source_date: sourceDate,
                            target_date: targetDate,
                            source_ids: eventIds(evt.from),
                            target_ids: eventIds(evt.to)
                        }).then(function (body) {
                            events[eventId] = body.event;
                            storeUndo(eventId, body.undo);
                            window.location.reload();
                        }).catch(function (error) {
                            showToast(error.message + ' 원래 위치로 되돌립니다.');
                            window.setTimeout(function () { window.location.reload(); }, 700);
                        });
                    }
                });
                sortables.push(sortable);
            });
        }

        document.querySelectorAll('.wzc-category-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                var submit = form.querySelector('[type=submit]');
                submit.disabled = true;
                api('ajax.category.save.php', {
                    action: 'save',
                    category_id: Number(form.getAttribute('data-category-id')),
                    name: form.elements.name.value,
                    color: form.elements.color.value
                }).then(function (body) {
                    showToast(body.message);
                    window.setTimeout(function () { window.location.reload(); }, 300);
                }).catch(function (error) { showToast(error.message); }).finally(function () { submit.disabled = false; });
            });
            var deleteCategory = form.querySelector('[data-delete-category]');
            if (deleteCategory) deleteCategory.addEventListener('click', function () {
                if (!window.confirm('이 분류를 삭제하시겠습니까? 기존 일정은 분류 없음으로 변경됩니다.')) return;
                api('ajax.category.save.php', {action: 'delete', category_id: Number(form.getAttribute('data-category-id'))})
                    .then(function (body) { showToast(body.message); window.setTimeout(function () { window.location.reload(); }, 300); })
                    .catch(function (error) { showToast(error.message); });
            });
        });

        var preferenceForm = document.getElementById('wzcPreferenceForm');
        preferenceForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var submit = preferenceForm.querySelector('[type=submit]');
            submit.disabled = true;
            api('ajax.preference.save.php', {
                default_category: Number(preferenceForm.elements.default_category.value || 0),
                events_per_day: Number(preferenceForm.elements.events_per_day.value),
                touch_drag_use: preferenceForm.elements.touch_drag_use.checked ? 1 : 0
            }).then(function (body) {
                showToast(body.message);
                window.setTimeout(function () { window.location.reload(); }, 300);
            }).catch(function (error) { showToast(error.message); }).finally(function () { submit.disabled = false; });
        });

        function offerStoredUndo() {
            var undo;
            try { undo = JSON.parse(sessionStorage.getItem('wzc_move_undo') || 'null'); } catch (e) { undo = null; }
            if (!undo || !undo.event_id || undo.expires < Date.now()) {
                try { sessionStorage.removeItem('wzc_move_undo'); } catch (e) {}
                return;
            }
            showToast('일정을 이동했습니다.', '실행 취소', function () {
                api('ajax.event.move.php', {
                    event_id: undo.event_id,
                    version: undo.version,
                    source_date: undo.source_date,
                    target_date: undo.target_date,
                    source_ids: [],
                    target_ids: []
                }).then(function () {
                    try { sessionStorage.removeItem('wzc_move_undo'); } catch (e) {}
                    window.location.reload();
                }).catch(function (error) { showToast(error.message); });
            }, Math.max(1500, undo.expires - Date.now()));
        }

        initSortable();
        offerStoredUndo();
        setTimeFields();
    });
})();
