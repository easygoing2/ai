(function () {
    'use strict';

    var configNode = document.getElementById('wzyRuntimeConfig');
    if (!configNode) return;

    var config;
    try { config = JSON.parse(configNode.textContent); } catch (error) { return; }

    var records = Object.create(null);
    var statusLoaded = Object.create(null);
    var displays = Object.create(null);
    var trackers = [];
    var registeredPlayers = [];
    var sourceSequence = 0;
    var youtubePromise = null;
    var shortSeekMaxSeconds = 15;

    function ready(callback) {
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', callback);
        else callback();
    }

    function keyOf(meta) {
        return meta.boTable + ':' + meta.wrId + ':' + meta.videoId;
    }

    function pendingStorageKey(meta) {
        return 'wzy-watch-pending:' + keyOf(meta);
    }

    function number(value, fallback) {
        value = Number(value);
        return Number.isFinite(value) ? value : (fallback || 0);
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function api(path, payload, keepalive) {
        payload = payload || {};
        payload.csrf_token = config.csrfToken;
        return fetch(config.apiBase + '/' + path, {
            method: 'POST',
            credentials: 'same-origin',
            keepalive: !!keepalive,
            headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            body: JSON.stringify(payload)
        }).then(function (response) {
            return response.json().catch(function () {
                return {success: false, message: '서버 응답을 확인할 수 없습니다.'};
            }).then(function (body) {
                if (!response.ok || !body.success) {
                    var error = new Error(body.message || '요청을 처리하지 못했습니다.');
                    error.code = body.code || '';
                    throw error;
                }
                return body;
            });
        });
    }

    function mergeRanges(ranges, duration) {
        var clean = [];
        (ranges || []).forEach(function (range) {
            if (!Array.isArray(range) || range.length < 2) return;
            var start = clamp(number(range[0]), 0, duration || Number.MAX_SAFE_INTEGER);
            var end = clamp(number(range[1]), 0, duration || Number.MAX_SAFE_INTEGER);
            if (end - start >= 0.2) clean.push([start, end]);
        });
        clean.sort(function (a, b) { return a[0] - b[0] || a[1] - b[1]; });
        return clean.reduce(function (result, range) {
            var last = result[result.length - 1];
            if (last && range[0] <= last[1] + 0.75) last[1] = Math.max(last[1], range[1]);
            else result.push(range.slice());
            return result;
        }, []);
    }

    function watchedSeconds(ranges) {
        return (ranges || []).reduce(function (sum, range) {
            return sum + Math.max(0, range[1] - range[0]);
        }, 0);
    }

    function formatTime(value, empty) {
        var seconds = Math.max(0, Math.floor(number(value)));
        if (!seconds && empty) return '--:--';
        var hours = Math.floor(seconds / 3600);
        var minutes = Math.floor((seconds % 3600) / 60);
        var remainder = seconds % 60;
        return (hours ? hours + ':' + String(minutes).padStart(2, '0') : minutes) + ':' + String(remainder).padStart(2, '0');
    }

    function videoIdFromUrl(value) {
        try {
            var url = new URL(value, window.location.href);
            var host = url.hostname.replace(/^(www\.|m\.)/, '').toLowerCase();
            var path = url.pathname.replace(/^\/+|\/+$/g, '').split('/');
            var id = '';
            if (host === 'youtu.be') id = path[0] || '';
            else if (host === 'youtube.com' || host === 'youtube-nocookie.com') {
                if (path[0] === 'watch') id = url.searchParams.get('v') || '';
                else if (['embed', 'shorts', 'live'].indexOf(path[0]) !== -1) id = path[1] || '';
            }
            return /^[A-Za-z0-9_-]{11}$/.test(id) ? id : '';
        } catch (error) {
            return '';
        }
    }

    function metaFromElement(element) {
        return {
            boTable: element.getAttribute('data-wzy-bo-table') || '',
            wrId: parseInt(element.getAttribute('data-wzy-wr-id'), 10) || 0,
            videoId: element.getAttribute('data-wzy-video-id') || '',
            mode: element.getAttribute('data-wzy-mode') || 'player',
            element: element
        };
    }

    function validMeta(meta) {
        return /^[a-zA-Z0-9_]{1,20}$/.test(meta.boTable) && meta.wrId > 0 && /^[A-Za-z0-9_-]{11}$/.test(meta.videoId);
    }

    function ensureRecord(meta) {
        var key = keyOf(meta);
        if (!records[key]) {
            records[key] = {
                percent: 0,
                watched_seconds: 0,
                duration: 0,
                last_position: 0,
                status: 'watching',
                calendar_status: 'none',
                ranges: []
            };
        }
        return records[key];
    }

    function formatMessage(record, saving, failed, percent) {
        if (failed) return '저장 대기 중';
        if (saving) return '저장 중';
        if (record.calendar_status === 'created' && record.status !== 'completed') return '시청 중 · 캘린더 등록됨';
        if (record.calendar_status === 'pending' && record.status !== 'completed') return '시청 중 · 캘린더 등록 대기';
        if (record.status === 'completed') {
            if (record.calendar_status === 'created') return '시청 완료 · 캘린더 등록됨';
            if (record.calendar_status === 'pending') return '시청 완료 · 캘린더 등록 대기';
            if (config.calendarUse && percent < number(config.calendarPercent, 90)) return '시청 완료 · 캘린더 기준 대기';
            return '시청 완료';
        }
        return '시청 중';
    }

    function updateDisplays(meta, state) {
        var key = keyOf(meta);
        var record = ensureRecord(meta);
        var percent = clamp(Math.floor(number(state && state.percent, record.percent)), 0, 100);
        var watched = state && state.watchedSeconds !== undefined ? number(state.watchedSeconds) : number(record.watched_seconds);
        var duration = state && state.duration !== undefined ? number(state.duration) : number(record.duration);
        (displays[key] || []).forEach(function (display) {
            if (display.badge) {
                display.root.textContent = record.status === 'completed' ? '시청 완료' : (percent > 0 ? '시청 중 ' + percent + '%' : '미시청');
                display.root.classList.toggle('is-completed', record.status === 'completed');
                return;
            }
            display.percent.textContent = '시청률 ' + percent + '%';
            display.time.textContent = formatTime(watched) + ' / ' + formatTime(duration, true);
            display.progress.value = percent;
            display.progress.textContent = percent + '%';
            display.message.textContent = formatMessage(record, !!(state && state.saving), !!(state && state.failed), percent);
            display.root.classList.toggle('is-completed', record.status === 'completed');
        });
    }

    function addDisplay(meta, anchor, badge) {
        var key = keyOf(meta);
        if (!displays[key]) displays[key] = [];
        var root = document.createElement(badge ? 'span' : 'div');
        if (badge) {
            root.className = 'wzy-watch-badge';
            root.setAttribute('aria-label', '유튜브 시청 상태');
            anchor.appendChild(root);
            displays[key].push({root: root, badge: true});
        } else {
            root.className = 'wzy-watch-status';
            root.innerHTML = '<span class="wzy-watch-percent">시청률 0%</span><span class="wzy-watch-time">0:00 / --:--</span><progress max="100" value="0" aria-label="유튜브 시청률">0%</progress><span class="wzy-watch-message" aria-live="polite">시청 중</span>';
            anchor.insertAdjacentElement('afterend', root);
            displays[key].push({
                root: root,
                badge: false,
                percent: root.querySelector('.wzy-watch-percent'),
                time: root.querySelector('.wzy-watch-time'),
                progress: root.querySelector('progress'),
                message: root.querySelector('.wzy-watch-message')
            });
        }
        updateDisplays(meta);
    }

    function setRecord(meta, data) {
        var key = keyOf(meta);
        var current = ensureRecord(meta);
        Object.keys(data || {}).forEach(function (name) { current[name] = data[name]; });
        current.ranges = mergeRanges(current.ranges || [], number(current.duration));
        statusLoaded[key] = true;
        updateDisplays(meta);
        trackers.forEach(function (tracker) {
            if (tracker.key === key) tracker.onServerState(current);
        });
    }

    function markStatusLoaded(meta) {
        var key = keyOf(meta);
        statusLoaded[key] = true;
        trackers.forEach(function (tracker) {
            if (tracker.key === key) tracker.onServerState(ensureRecord(meta));
        });
    }

    function Tracker(meta, adapter) {
        this.meta = meta;
        this.key = keyOf(meta);
        this.adapter = adapter;
        this.pending = [];
        this.inFlight = [];
        this.restoredDuration = 0;
        this.restoredPosition = 0;
        this.segmentStart = null;
        this.lastPosition = null;
        this.lastObservedPosition = null;
        this.lastWallTime = null;
        this.playing = false;
        this.seeking = false;
        this.seekFrom = null;
        this.saving = false;
        this.failed = false;
        this.playerReady = false;
        this.statusReady = !!statusLoaded[this.key];
        this.resumeResolved = false;
        this.userStarted = false;
        this.restoring = false;
        this.restoreTimer = null;
        this.lastSaveAt = Date.now();
        this.restorePending();
        this.timer = window.setInterval(this.tick.bind(this), 1000);
        var self = this;
        if (typeof this.adapter.ready === 'function') this.adapter.ready(function () { self.onPlayerReady(); });
        else this.onPlayerReady();
    }

    Tracker.prototype.restorePending = function () {
        try {
            var saved = JSON.parse(sessionStorage.getItem(pendingStorageKey(this.meta)) || 'null');
            if (!saved || !Array.isArray(saved.ranges)) return;
            this.restoredDuration = clamp(number(saved.duration), 0, 604800);
            this.restoredPosition = clamp(number(saved.last_position), 0, this.restoredDuration || 604800);
            this.pending = mergeRanges(saved.ranges, this.restoredDuration || Number.MAX_SAFE_INTEGER);
            if (this.pending.length) this.failed = true;
        } catch (error) {}
    };

    Tracker.prototype.persistPending = function () {
        try {
            var ranges = mergeRanges(this.inFlight.concat(this.pending), this.duration() || Number.MAX_SAFE_INTEGER);
            var storageKey = pendingStorageKey(this.meta);
            if (!ranges.length) {
                sessionStorage.removeItem(storageKey);
                return;
            }
            sessionStorage.setItem(storageKey, JSON.stringify({
                duration: this.duration() || this.restoredDuration,
                ranges: ranges,
                last_position: this.positionForSave(),
                saved_at: Date.now()
            }));
        } catch (error) {}
    };

    Tracker.prototype.onServerState = function () {
        this.statusReady = true;
        this.failed = false;
        this.render();
        this.maybeResume();
        if (this.pending.length && !this.saving) this.flush(false);
    };

    Tracker.prototype.onPlayerReady = function () {
        this.playerReady = true;
        this.maybeResume();
    };

    Tracker.prototype.finishResume = function (position) {
        if (this.restoreTimer !== null) {
            window.clearTimeout(this.restoreTimer);
            this.restoreTimer = null;
        }
        this.restoring = false;
        this.lastObservedPosition = number(position, this.currentTime());
        this.resetSample();
    };

    Tracker.prototype.maybeResume = function () {
        if (this.resumeResolved || !this.playerReady || !this.statusReady) return;

        var duration = this.duration();
        if (duration <= 0) return;

        this.resumeResolved = true;
        var current = clamp(this.currentTime(), 0, duration);
        var saved = this.restoredPosition > 0
            ? this.restoredPosition
            : number(ensureRecord(this.meta).last_position);
        var position = clamp(saved, 0, duration);

        // Do not interrupt playback or a seek that happened before the status
        // request completed. Positions at the very beginning or end are also
        // more useful when restarted from zero.
        if (this.userStarted || current > 1 || position < 2 || duration - position <= 5) return;

        try {
            this.restoring = true;
            this.adapter.seek(position);
            this.lastObservedPosition = position;
            var self = this;
            this.restoreTimer = window.setTimeout(function () {
                self.finishResume(position);
            }, 1500);
        } catch (error) {
            this.finishResume(current);
        }
    };

    Tracker.prototype.duration = function () {
        var adapterDuration = number(this.adapter.duration());
        if (adapterDuration > 0) return adapterDuration;
        var savedDuration = number(ensureRecord(this.meta).duration);
        return savedDuration > 0 ? savedDuration : this.restoredDuration;
    };

    Tracker.prototype.currentTime = function () {
        return number(this.adapter.currentTime(), 0);
    };

    Tracker.prototype.positionForSave = function () {
        if (this.restoring && this.lastObservedPosition !== null) return this.lastObservedPosition;
        return this.currentTime();
    };

    Tracker.prototype.allRanges = function () {
        var ranges = (ensureRecord(this.meta).ranges || []).concat(this.pending);
        if (this.segmentStart !== null && this.lastPosition !== null && this.lastPosition > this.segmentStart) {
            ranges.push([this.segmentStart, this.lastPosition]);
        }
        return mergeRanges(ranges, this.duration());
    };

    Tracker.prototype.render = function () {
        var duration = this.duration();
        var watched = watchedSeconds(this.allRanges());
        var percent = duration > 0 ? Math.floor(watched / duration * 100) : ensureRecord(this.meta).percent;
        updateDisplays(this.meta, {
            percent: clamp(percent, 0, 100),
            watchedSeconds: watched,
            duration: duration,
            saving: this.saving,
            failed: this.failed
        });
    };

    Tracker.prototype.resetSample = function () {
        this.segmentStart = null;
        this.lastPosition = null;
        this.lastWallTime = null;
    };

    Tracker.prototype.closeSegment = function (position) {
        if (position !== undefined && this.lastPosition !== null) this.lastPosition = number(position, this.lastPosition);
        if (this.segmentStart !== null && this.lastPosition !== null && this.lastPosition - this.segmentStart >= 0.2) {
            this.pending.push([this.segmentStart, this.lastPosition]);
            this.pending = mergeRanges(this.pending, this.duration());
        }
        if (this.lastPosition !== null) this.lastObservedPosition = this.lastPosition;
        this.resetSample();
    };

    Tracker.prototype.finishSeek = function () {
        var position = clamp(this.currentTime(), 0, this.duration() || Number.MAX_SAFE_INTEGER);
        if (this.seekFrom !== null) {
            var delta = position - this.seekFrom;
            if (config.countShortSeek && delta >= 0.2 && delta <= shortSeekMaxSeconds + 0.75) {
                this.pending.push([this.seekFrom, position]);
                this.pending = mergeRanges(this.pending, this.duration());
            }
        }
        this.lastObservedPosition = position;
        this.seekFrom = null;
        this.seeking = false;
        this.resetSample();
        this.render();
        if (this.pending.length) this.flush(false);
    };

    Tracker.prototype.tick = function () {
        if (!this.playing || this.seeking || document.hidden) return;
        var duration = this.duration();
        var position = clamp(this.currentTime(), 0, duration || Number.MAX_SAFE_INTEGER);
        if (duration <= 0) return;
        var now = performance.now();
        if (this.lastPosition === null) {
            this.segmentStart = position;
        } else {
            var elapsed = Math.max(0, (now - this.lastWallTime) / 1000);
            var rate = clamp(number(this.adapter.playbackRate(), 1), 0.25, 4);
            var delta = position - this.lastPosition;
            if (delta < -0.25 || delta > elapsed * rate + 1.5) {
                this.closeSegment();
                this.segmentStart = position;
            }
        }
        this.lastPosition = position;
        this.lastObservedPosition = position;
        this.lastWallTime = now;
        this.render();
        if (Date.now() - this.lastSaveAt >= number(config.saveInterval, 10) * 1000) {
            this.closeSegment(position);
            this.flush(false);
            this.segmentStart = position;
            this.lastPosition = position;
            this.lastWallTime = now;
        }
    };

    Tracker.prototype.onPlay = function () {
        if (this.restoring) this.finishResume(this.currentTime());
        this.userStarted = true;
        if (this.seeking) this.finishSeek();
        this.playing = true;
        this.seeking = false;
        this.resetSample();
    };

    Tracker.prototype.onPause = function () {
        if (this.restoring) {
            this.finishResume(this.currentTime());
            return;
        }
        if (this.seeking) this.finishSeek();
        else this.closeSegment(this.currentTime());
        this.playing = false;
        this.flush(false);
    };

    Tracker.prototype.onSeeking = function () {
        if (this.restoring) return;
        this.userStarted = true;
        if (this.seekFrom === null) {
            this.seekFrom = this.lastObservedPosition !== null ? this.lastObservedPosition : this.currentTime();
        }
        this.closeSegment();
        this.seeking = true;
        this.flush(false);
    };

    Tracker.prototype.onSeeked = function () {
        if (this.restoring) {
            this.finishResume(this.currentTime());
            return;
        }
        this.finishSeek();
    };

    Tracker.prototype.onEnded = function () {
        if (this.restoring) this.finishResume(this.currentTime());
        this.userStarted = true;
        if (this.seeking) this.finishSeek();
        else this.closeSegment(this.currentTime());
        this.playing = false;
        this.flush(false);
    };

    Tracker.prototype.onVisibilityHidden = function () {
        if (!this.playing) return;
        this.closeSegment(this.currentTime());
        this.flush(true);
    };

    Tracker.prototype.flush = function (keepalive) {
        if (this.saving || !this.pending.length) return;
        var duration = this.duration();
        if (duration < 1) return;
        var sent = this.pending.splice(0, 50);
        this.inFlight = sent.slice();
        this.saving = true;
        this.failed = false;
        this.lastSaveAt = Date.now();
        this.persistPending();
        this.render();
        var self = this;
        api('ajax.watch.progress.php', {
            bo_table: this.meta.boTable,
            wr_id: this.meta.wrId,
            video_id: this.meta.videoId,
            duration: duration,
            ranges: sent,
            last_position: this.positionForSave(),
            player: this.adapter.name
        }, keepalive).then(function (body) {
            self.saving = false;
            self.inFlight = [];
            self.persistPending();
            setRecord(self.meta, body);
            self.render();
            if (self.pending.length && !self.playing) self.flush(false);
        }).catch(function () {
            self.saving = false;
            self.failed = true;
            self.pending = mergeRanges(self.inFlight.concat(self.pending), duration);
            self.inFlight = [];
            self.persistPending();
            self.render();
        });
    };

    function registerAdapter(meta, adapter) {
        if (registeredPlayers.indexOf(adapter.identity) !== -1) return;
        registeredPlayers.push(adapter.identity);
        var tracker = new Tracker(meta, adapter);
        trackers.push(tracker);
        adapter.bind({
            play: tracker.onPlay.bind(tracker),
            pause: tracker.onPause.bind(tracker),
            seeking: tracker.onSeeking.bind(tracker),
            seeked: tracker.onSeeked.bind(tracker),
            ended: tracker.onEnded.bind(tracker)
        });
        if (tracker.pending.length) window.setTimeout(function () { tracker.flush(false); }, 1000);
    }

    function loadYouTubeApi() {
        if (window.YT && window.YT.Player) return Promise.resolve(window.YT);
        if (youtubePromise) return youtubePromise;
        youtubePromise = new Promise(function (resolve) {
            var previous = window.onYouTubeIframeAPIReady;
            window.onYouTubeIframeAPIReady = function () {
                if (typeof previous === 'function') previous();
                resolve(window.YT);
            };
            if (!document.querySelector('script[src="https://www.youtube.com/iframe_api"]')) {
                var script = document.createElement('script');
                script.src = 'https://www.youtube.com/iframe_api';
                script.async = true;
                document.head.appendChild(script);
            }
        });
        return youtubePromise;
    }

    function enableIframeApi(iframe) {
        try {
            var url = new URL(iframe.src, window.location.href);
            url.searchParams.set('enablejsapi', '1');
            url.searchParams.set('origin', window.location.origin);
            if (iframe.src !== url.href) iframe.src = url.href;
        } catch (error) {}
        if (!iframe.id) iframe.id = 'wzy-youtube-player-' + (++sourceSequence);
    }

    function registerIframe(meta, iframe) {
        if (!iframe || iframe.getAttribute('data-wzy-registered') === '1') return;
        iframe.setAttribute('data-wzy-registered', '1');
        enableIframeApi(iframe);
        loadYouTubeApi().then(function (YT) {
            var player;
            var playerReady = false;
            var readyCallbacks = [];
            player = new YT.Player(iframe, {
                events: {
                    onReady: function () {
                        playerReady = true;
                        readyCallbacks.splice(0).forEach(function (callback) { callback(); });
                    },
                    onStateChange: function (event) {
                        if (event.data === YT.PlayerState.PLAYING) handlers.play();
                        else if (event.data === YT.PlayerState.PAUSED) handlers.pause();
                        else if (event.data === YT.PlayerState.ENDED) handlers.ended();
                        else if (event.data === YT.PlayerState.BUFFERING) handlers.seeking();
                    }
                }
            });
            var handlers = {play: function(){}, pause: function(){}, seeking: function(){}, seeked: function(){}, ended: function(){}};
            var adapter = {
                name: 'youtube_iframe',
                identity: iframe,
                currentTime: function () { try { return player.getCurrentTime(); } catch (error) { return 0; } },
                duration: function () { try { return player.getDuration(); } catch (error) { return 0; } },
                playbackRate: function () { try { return player.getPlaybackRate(); } catch (error) { return 1; } },
                seek: function (position) { player.seekTo(position, true); },
                ready: function (callback) {
                    if (playerReady) callback();
                    else readyCallbacks.push(callback);
                },
                bind: function (callbacks) { handlers = callbacks; }
            };
            registerAdapter(meta, adapter);
        }).catch(function () {
            iframe.removeAttribute('data-wzy-registered');
        });
    }

    function registerPlyrInstance(instance) {
        if (!instance || registeredPlayers.indexOf(instance) !== -1) return;
        var container = instance.elements && instance.elements.container ? instance.elements.container : instance.media;
        var marker = container && container.closest ? container.closest('[data-wzy-bo-table][data-wzy-wr-id][data-wzy-video-id]') : null;
        if (!marker) return;
        var meta = metaFromElement(marker);
        if (!validMeta(meta) || meta.videoId !== String(instance.source || meta.videoId)) {
            if (!validMeta(meta)) return;
        }
        var adapter = {
            name: 'plyr_youtube',
            identity: container || instance,
            currentTime: function () { return instance.currentTime; },
            duration: function () { return instance.duration; },
            playbackRate: function () { return instance.speed || 1; },
            seek: function (position) { instance.currentTime = position; },
            ready: function (callback) {
                if (instance.ready) {
                    callback();
                    return;
                }
                var called = false;
                var once = function () {
                    if (called) return;
                    called = true;
                    callback();
                };
                instance.once('ready', once);
                window.setTimeout(once, 1000);
            },
            bind: function (handlers) {
                instance.on('playing', handlers.play);
                instance.on('play', handlers.play);
                instance.on('pause', handlers.pause);
                instance.on('seeking', handlers.seeking);
                instance.on('seeked', handlers.seeked);
                instance.on('ended', handlers.ended);
            }
        };
        registerAdapter(meta, adapter);
    }

    function discoverSources() {
        var metas = [];
        document.querySelectorAll('[data-wzy-bo-table][data-wzy-wr-id][data-wzy-video-id]').forEach(function (element) {
            var meta = metaFromElement(element);
            if (validMeta(meta)) metas.push(meta);
        });

        var current = config.currentPost;
        if (current && validMeta({boTable: current.boTable, wrId: current.wrId, videoId: current.videoId})) {
            document.querySelectorAll('iframe[src*="youtube.com/embed/"], iframe[src*="youtube-nocookie.com/embed/"]').forEach(function (iframe) {
                if (videoIdFromUrl(iframe.src) !== current.videoId) return;
                if (!iframe.hasAttribute('data-wzy-bo-table')) {
                    iframe.setAttribute('data-wzy-bo-table', current.boTable);
                    iframe.setAttribute('data-wzy-wr-id', current.wrId);
                    iframe.setAttribute('data-wzy-video-id', current.videoId);
                }
                metas.push(metaFromElement(iframe));
            });
        }

        var unique = [];
        metas.forEach(function (meta) {
            if (meta.element.getAttribute('data-wzy-discovered') === '1') return;
            meta.element.setAttribute('data-wzy-discovered', '1');
            unique.push(meta);
            ensureRecord(meta);
            if (meta.mode === 'badge') {
                if (config.showListBadge) addDisplay(meta, meta.element, true);
                return;
            }
            var iframe = meta.element.matches && meta.element.matches('iframe') ? meta.element : meta.element.querySelector('iframe[src*="youtube"]');
            var anchor = meta.element.matches && meta.element.matches('iframe')
                ? (meta.element.closest('.video-container, .blog-youtube') || meta.element)
                : (meta.element.closest('.img-wrap') || meta.element);
            addDisplay(meta, anchor, false);
            if (iframe && !iframe.closest('.plyr')) registerIframe(meta, iframe);
        });
        return unique;
    }

    function loadStatuses(metas) {
        var seen = Object.create(null);
        var items = [];
        metas.forEach(function (meta) {
            var key = keyOf(meta);
            if (seen[key]) return;
            seen[key] = true;
            items.push({bo_table: meta.boTable, wr_id: meta.wrId, video_id: meta.videoId});
        });
        if (!items.length) return;
        api('ajax.watch.status.php', {items: items}).then(function (body) {
            Object.keys(body.items || {}).forEach(function (key) {
                var meta = metas.find(function (item) { return keyOf(item) === key; });
                if (meta) setRecord(meta, body.items[key]);
            });
            metas.forEach(function (meta) {
                if (!statusLoaded[keyOf(meta)]) markStatusLoaded(meta);
            });
        }).catch(function () {
            // A pending session record can still provide a resume position when
            // the initial server status request is temporarily unavailable.
            metas.forEach(markStatusLoaded);
        });
    }

    function scanPlyrPlayers() {
        (window.wzyPlyrPlayers || []).forEach(registerPlyrInstance);
    }

    ready(function () {
        var metas = discoverSources();
        loadStatuses(metas);
        scanPlyrPlayers();
        document.addEventListener('wzy:plyr-ready', scanPlyrPlayers);
        window.setTimeout(scanPlyrPlayers, 500);
        window.setTimeout(scanPlyrPlayers, 2000);

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) trackers.forEach(function (tracker) { tracker.onVisibilityHidden(); });
            else trackers.forEach(function (tracker) { tracker.resetSample(); });
        });
        window.addEventListener('online', function () {
            trackers.forEach(function (tracker) { tracker.flush(false); });
        });
        window.addEventListener('pagehide', function () {
            trackers.forEach(function (tracker) {
                tracker.closeSegment(tracker.currentTime());
                tracker.persistPending();
                tracker.flush(true);
            });
        });
    });
}());
