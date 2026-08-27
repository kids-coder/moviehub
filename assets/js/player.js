/**
 * MovieHub Player (v2)
 * - Source fallback via ONE error handler per freshly created <source> (no duplicates).
 * - Subtitle <track> elements preserved and re-enabled across source switches.
 * - Seeks issued before metadata are queued and applied on loadedmetadata.
 * ES5-safe for older smart-TV browsers.
 */
(function () {
    'use strict';

    var READYSTATE_HAVE_METADATA = 1;

    function MoviePlayer(root) {
        this.root = root;
        this.video = root.querySelector('video');
        if (!this.video) {
            throw new Error('MoviePlayer: <video> element not found');
        }

        this.sources = this.readSources();
        this.sourceIndex = -1;
        this.pendingSeek = null;
        this.exhausted = false;

        this.trackTemplates = Array.prototype.map.call(
            this.video.querySelectorAll('track'),
            function (t) {
                return {
                    kind: t.getAttribute('kind') || 'subtitles',
                    src: t.src,
                    srclang: t.getAttribute('srclang') || '',
                    label: t.getAttribute('label') || '',
                    isDefault: t.hasAttribute('default')
                };
            }
        );

        this.errorBox = this.ensureErrorBox();
        this.bindCoreEvents();
        this.nextSource();
    }

    MoviePlayer.prototype.readSources = function () {
        var raw = this.root.getAttribute('data-sources');
        if (raw) {
            try {
                var parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    return parsed.filter(function (u) {
                        return typeof u === 'string' && u.length > 0;
                    });
                }
            } catch (e) {
                return [];
            }
        }
        return Array.prototype.map.call(
            this.video.querySelectorAll('source'),
            function (s) { return s.getAttribute('src'); }
        ).filter(Boolean);
    };

    MoviePlayer.prototype.ensureErrorBox = function () {
        var box = this.root.querySelector('.player-error');
        if (!box) {
            box = document.createElement('div');
            box.className = 'player-error';
            box.setAttribute('role', 'alert');
            box.hidden = true;
            this.root.appendChild(box);
        }
        return box;
    };

    MoviePlayer.prototype.bindCoreEvents = function () {
        var self = this;
        // Bound exactly once for the lifetime of the player.
        this.video.addEventListener('loadedmetadata', function () { self.flushPendingSeek(); });
        this.video.addEventListener('error', function () { self.handleSourceError(); });
    };

    MoviePlayer.prototype.nextSource = function () {
        var self = this;
        this.sourceIndex += 1;
        if (this.sourceIndex >= this.sources.length) {
            this.showFatalError();
            return;
        }

        this.hideError();

        // Fresh <source> nodes carry no stale listeners:
        // exactly one error handler per fallback attempt.
        while (this.video.firstChild) {
            this.video.removeChild(this.video.firstChild);
        }

        var sourceEl = document.createElement('source');
        sourceEl.src = this.sources[this.sourceIndex];
        sourceEl.addEventListener('error', function () { self.handleSourceError(); });
        this.video.appendChild(sourceEl);

        this.restoreTracks();
        this.video.load();
    };

    MoviePlayer.prototype.restoreTracks = function () {
        var self = this;
        var activeIndex = this.getActiveTrackIndex();

        this.trackTemplates.forEach(function (tpl) {
            var track = document.createElement('track');
            track.kind = tpl.kind;
            track.src = tpl.src;
            track.srclang = tpl.srclang;
            track.label = tpl.label;
            if (tpl.isDefault) {
                track.default = true;
            }
            self.video.appendChild(track);
        });

        if (activeIndex !== null && this.trackTemplates[activeIndex]) {
            requestAnimationFrame(function () {
                var t = self.video.textTracks[activeIndex];
                if (t) {
                    t.mode = 'showing';
                }
            });
        }
    };

    MoviePlayer.prototype.getActiveTrackIndex = function () {
        var i;
        for (i = 0; i < this.video.textTracks.length; i += 1) {
            if (this.video.textTracks[i].mode === 'showing') {
                return i;
            }
        }
        for (i = 0; i < this.trackTemplates.length; i += 1) {
            if (this.trackTemplates[i].isDefault) {
                return i;
            }
        }
        return null;
    };

    MoviePlayer.prototype.seek = function (seconds) {
        var target = Number(seconds);
        if (!isFinite(target) || target < 0) {
            return;
        }
        if (this.video.readyState >= READYSTATE_HAVE_METADATA) {
            this.safeSeek(target);
        } else {
            this.pendingSeek = target; // applied automatically on loadedmetadata
        }
    };

    MoviePlayer.prototype.flushPendingSeek = function () {
        if (this.pendingSeek !== null) {
            this.safeSeek(this.pendingSeek);
            this.pendingSeek = null;
        }
    };

    MoviePlayer.prototype.safeSeek = function (seconds) {
        try {
            if (seconds <= this.video.duration) {
                this.video.currentTime = seconds;
            }
        } catch (e) {
            this.pendingSeek = seconds; // retry once duration is known
        }
    };

    MoviePlayer.prototype.handleSourceError = function () {
        if (this.exhausted) {
            return;
        }
        this.nextSource();
    };

    MoviePlayer.prototype.showError = function (message) {
        this.errorBox.textContent = message;
        this.errorBox.hidden = false;
    };

    MoviePlayer.prototype.hideError = function () {
        this.errorBox.hidden = true;
    };

    MoviePlayer.prototype.showFatalError = function () {
        this.exhausted = true;
        this.showError('All sources failed to load. Please try another server.');
    };

    document.querySelectorAll('[data-movie-player]').forEach(function (root) {
        new MoviePlayer(root);
    });
    window.MovieHubPlayer = { init: function (root) { return new MoviePlayer(root); } };
})();
