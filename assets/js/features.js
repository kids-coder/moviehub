/* ============================================================
   BDMovieHub - features.js
   Theme toggle, favorites, search helpers, live search,
   share buttons, recently watched, report broken video
   ============================================================ */

(function () {
    'use strict';

    var THEME_KEY = 'moviehub-theme';
    var FAV_KEY = 'moviehub-favs';
    var HISTORY_KEY = 'moviehub-history';

    /* ---------- Theme Toggle ---------- */
    function getTheme() {
        try { return localStorage.getItem(THEME_KEY) || 'dark'; }
        catch (e) { return 'dark'; }
    }
    function setTheme(theme) {
        try { localStorage.setItem(THEME_KEY, theme); } catch (e) {}
        if (theme === 'light') {
            document.body.classList.add('light-theme');
        } else {
            document.body.classList.remove('light-theme');
        }
        var icon = document.querySelector('.theme-toggle i');
        if (icon) {
            icon.className = (theme === 'light') ? 'fas fa-moon' : 'fas fa-sun';
        }
    }
    function toggleTheme() {
        var current = getTheme();
        setTheme(current === 'light' ? 'dark' : 'light');
        if (window.showToast) {
            showToast('Theme changed to ' + (getTheme() === 'light' ? 'light' : 'dark') + ' mode');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        setTheme(getTheme());
        // The actual theme toggle is the <button class="theme-toggle">.
        // The Favorites link now uses a separate class (.nav-action-btn),
        // so it no longer collides with the theme toggle.
        var toggle = document.querySelector('button.theme-toggle');
        if (toggle) {
            toggle.addEventListener('click', toggleTheme);
        }
    });

    /* ---------- Favorites ---------- */
    function getFavs() {
        try {
            var raw = localStorage.getItem(FAV_KEY);
            return raw ? JSON.parse(raw) : { movies: [], anime: [] };
        } catch (e) { return { movies: [], anime: [] }; }
    }
    function saveFavs(favs) {
        try { localStorage.setItem(FAV_KEY, JSON.stringify(favs)); } catch (e) {}
    }
    window.isFav = function (type, id) {
        var favs = getFavs();
        var key = type === 'anime' ? 'anime' : 'movies';
        return favs[key].indexOf(id) !== -1;
    };
    window.toggleFav = function (type, id, title) {
        var favs = getFavs();
        var key = type === 'anime' ? 'anime' : 'movies';
        var idx = favs[key].indexOf(id);
        if (idx === -1) {
            favs[key].push(id);
            if (window.showToast) { showToast((title || 'Item') + ' added to favorites', 'success'); }
        } else {
            favs[key].splice(idx, 1);
            if (window.showToast) { showToast((title || 'Item') + ' removed from favorites'); }
        }
        saveFavs(favs);
        updateFavButtons();
    };
    function updateFavButtons() {
        var btns = document.querySelectorAll('.fav-btn');
        for (var i = 0; i < btns.length; i++) {
            var b = btns[i];
            var type = b.getAttribute('data-type');
            var id = b.getAttribute('data-id');
            if (!type || !id) { continue; }
            var icon = b.querySelector('i');
            if (isFav(type, id)) {
                b.classList.add('active');
                if (icon) { icon.className = 'fas fa-heart'; }
            } else {
                b.classList.remove('active');
                if (icon) { icon.className = 'far fa-heart'; }
            }
        }
    }
    document.addEventListener('DOMContentLoaded', function () {
        updateFavButtons();
        var btns = document.querySelectorAll('.fav-btn');
        for (var i = 0; i < btns.length; i++) {
            (function (b) {
                b.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var type = b.getAttribute('data-type');
                    var id = b.getAttribute('data-id');
                    var title = b.getAttribute('data-title') || 'Item';
                    toggleFav(type, id, title);
                });
            })(btns[i]);
        }
    });

    /* ---------- Watch History (Recently Watched) ---------- */
    function getHistory() {
        try {
            var raw = localStorage.getItem(HISTORY_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) { return []; }
    }
    function saveHistory(history) {
        try { localStorage.setItem(HISTORY_KEY, JSON.stringify(history.slice(0, 12))); } catch (e) {}
    }
    window.addToHistory = function (item) {
        if (!item || !item.url || !item.title) { return; }
        var history = getHistory();
        // Remove duplicates by url, keep existing progress if not provided
        var prev = null;
        for (var i = 0; i < history.length; i++) {
            if (history[i].url === item.url) { prev = history[i]; break; }
        }
        history = history.filter(function (h) { return h.url !== item.url; });
        if (prev && typeof item.progress !== 'number') {
            item.progress = prev.progress || 0;
            item.episode = prev.episode || '';
        }
        history.unshift(item);
        saveHistory(history);
    };
    window.removeHistoryItem = function (url) {
        var history = getHistory().filter(function (h) { return h.url !== url; });
        saveHistory(history);
        window.renderHistory('recently-watched');
        if (window.showToast) { showToast('Removed from Continue Watching'); }
    };
    window.renderHistory = function (containerId) {
        var container = document.getElementById(containerId);
        if (!container) { return; }
        var history = getHistory();
        // Show/hide the parent section based on history presence
        var section = document.getElementById('continue-watching-section');
        if (history.length === 0) {
            container.innerHTML = '';
            if (section) { section.style.display = 'none'; }
            return;
        }
        if (section) { section.style.display = 'block'; }
        var html = '';
        for (var i = 0; i < history.length; i++) {
            var h = history[i];
            var pct = Math.max(0, Math.min(100, parseInt(h.progress || 0, 10)));
            html += '<div class="movie-card" style="position:relative;">' +
                '<a href="' + escapeAttr(h.url) + '">' +
                '<div class="card-poster">' +
                    '<img src="' + escapeAttr(h.poster || '') + '" alt="' + escapeAttr(h.title || '') + '" loading="lazy">' +
                    '<div class="card-overlay"><button class="card-play-btn" aria-label="Resume"><i class="fas fa-play"></i></button></div>' +
                '</div>' +
                '<div class="card-info">' +
                    '<div class="card-title">' + escapeHtml(h.title || '') + '</div>' +
                    (h.episode ? '<div class="card-meta"><span>EP ' + escapeHtml(String(h.episode)) + '</span></div>' : '') +
                    (pct > 0 ? '<div style="height:4px;background:var(--border);border-radius:2px;margin-top:6px;"><div style="width:' + pct + '%;height:4px;background:var(--primary);border-radius:2px;"></div></div>' : '') +
                '</div>' +
                '</a>' +
                '<button type="button" class="history-remove" data-url="' + escapeAttr(h.url) + '" title="Remove" aria-label="Remove from Continue Watching" style="position:absolute;top:8px;right:8px;z-index:5;background:rgba(10,10,15,0.8);border:none;color:#fff;width:26px;height:26px;border-radius:50%;cursor:pointer;"><i class="fas fa-times"></i></button>' +
            '</div>';
        }
        container.innerHTML = html;
        var removes = container.querySelectorAll('.history-remove');
        for (var j = 0; j < removes.length; j++) {
            removes[j].addEventListener('click', function () {
                window.removeHistoryItem(this.getAttribute('data-url'));
            });
        }
    };

    /* ---------- Clear history button (on homepage) ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        var clearBtn = document.getElementById('clear-history-btn');
        if (!clearBtn) { return; }
        clearBtn.addEventListener('click', function () {
            if (!confirm('Clear your watch history? This cannot be undone.')) { return; }
            try { localStorage.removeItem(HISTORY_KEY); } catch (e) {}
            window.renderHistory('recently-watched');
            if (window.showToast) { showToast('Watch history cleared'); }
        });
        // Initial render on homepage
        if (document.getElementById('recently-watched')) {
            window.renderHistory('recently-watched');
        }
    });

    function escapeHtml(s) {
        if (s === undefined || s === null) { return ''; }
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c];
        });
    }
    function escapeAttr(s) {
        return escapeHtml(s).replace(/`/g, '&#96;');
    }

    /* ---------- Live Search Dropdown ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('nav-search-input');
        var dropdown = document.getElementById('live-search-dropdown');
        if (!input || !dropdown) { return; }

        var baseUrl = window.BDMH_BASE_URL || '';
        var timer = null;
        var currentQuery = '';

        function showDropdown(html) {
            dropdown.innerHTML = html;
            dropdown.style.display = 'block';
        }
        function hideDropdown() {
            dropdown.style.display = 'none';
        }

        function performSearch(q) {
            if (q.length < 2) { hideDropdown(); return; }
            var xhr = new XMLHttpRequest();
            xhr.open('GET', baseUrl + '/api-search.php?q=' + encodeURIComponent(q), true);
            xhr.onload = function () {
                if (xhr.status !== 200) { return; }
                if (currentQuery !== q) { return; } // outdated response
                var data;
                try { data = JSON.parse(xhr.responseText); } catch (e) { return; }
                var movies = data.movies || [];
                var anime = data.anime || [];
                if (movies.length === 0 && anime.length === 0) {
                    showDropdown('<div class="ls-empty">No results for "' + escapeHtml(q) + '"</div>');
                    return;
                }
                var html = '';
                if (movies.length > 0) {
                    html += '<div class="ls-section">Movies (' + movies.length + ')</div>';
                    for (var i = 0; i < movies.length; i++) {
                        var m = movies[i];
                        html += '<a href="' + escapeAttr(m.url) + '" class="ls-item">' +
                            '<img src="' + escapeAttr(m.poster || '') + '" alt="">' +
                            '<div><div class="ls-title">' + escapeHtml(m.title) + '</div>' +
                            '<div class="ls-meta">' + escapeHtml(m.year || '') + (m.quality ? ' • ' + escapeHtml(m.quality) : '') + (m.rating ? ' • ★ ' + escapeHtml(m.rating) : '') + '</div></div>' +
                        '</a>';
                    }
                }
                if (anime.length > 0) {
                    html += '<div class="ls-section">Anime (' + anime.length + ')</div>';
                    for (var j = 0; j < anime.length; j++) {
                        var a = anime[j];
                        html += '<a href="' + escapeAttr(a.url) + '" class="ls-item">' +
                            '<img src="' + escapeAttr(a.poster || '') + '" alt="">' +
                            '<div><div class="ls-title">' + escapeHtml(a.title) + '</div>' +
                            '<div class="ls-meta">' + escapeHtml(a.status ? (a.status.charAt(0).toUpperCase() + a.status.slice(1)) : 'Anime') + (a.rating ? ' • ★ ' + escapeHtml(a.rating) : '') + '</div></div>' +
                        '</a>';
                    }
                }
                html += '<div class="ls-footer"><a href="' + escapeAttr(baseUrl + '/search.php?q=' + encodeURIComponent(q)) + '">See all results for "' + escapeHtml(q) + '" →</a></div>';
                showDropdown(html);
            };
            xhr.send();
        }

        input.addEventListener('input', function () {
            var val = input.value.trim();
            currentQuery = val;
            if (timer) { clearTimeout(timer); }
            if (val.length < 2) { hideDropdown(); return; }
            timer = setTimeout(function () { performSearch(val); }, 250);
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var val = input.value.trim();
                if (val) { window.location.href = baseUrl + '/search.php?q=' + encodeURIComponent(val); }
            }
            if (e.key === 'Escape') {
                hideDropdown();
                input.blur();
            }
        });

        // Show dropdown when input is focused
        input.addEventListener('focus', function () {
            var val = input.value.trim();
            if (val.length >= 2 && dropdown.innerHTML !== '') {
                dropdown.style.display = 'block';
            }
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                hideDropdown();
            }
        });
    });

    /* ---------- Share Buttons ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle share options
        var shareBtns = document.querySelectorAll('.share-btn');
        for (var i = 0; i < shareBtns.length; i++) {
            shareBtns[i].addEventListener('click', function (e) {
                e.preventDefault();
                var opts = document.getElementById('share-options');
                if (opts) {
                    opts.style.display = (opts.style.display === 'none' || opts.style.display === '') ? 'flex' : 'none';
                }
            });
        }

        // Share link clicks
        var shareLinks = document.querySelectorAll('.share-link');
        for (var j = 0; j < shareLinks.length; j++) {
            shareLinks[j].addEventListener('click', function (e) {
                e.preventDefault();
                var type = this.getAttribute('data-share');
                var shareBtn = document.querySelector('.share-btn');
                if (!shareBtn) { return; }
                var url = shareBtn.getAttribute('data-share-url') || window.location.href;
                var title = shareBtn.getAttribute('data-share-title') || document.title;
                var shareUrl = '';

                if (type === 'facebook') {
                    shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
                } else if (type === 'twitter') {
                    shareUrl = 'https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title);
                } else if (type === 'whatsapp') {
                    shareUrl = 'https://wa.me/?text=' + encodeURIComponent(title + ' ' + url);
                } else if (type === 'telegram') {
                    shareUrl = 'https://t.me/share/url?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title);
                } else if (type === 'copy') {
                    var tempInput = document.createElement('input');
                    document.body.appendChild(tempInput);
                    tempInput.value = url;
                    tempInput.select();
                    try {
                        document.execCommand('copy');
                        if (window.showToast) { showToast('Link copied to clipboard!', 'success'); }
                    } catch (err) {
                        if (window.showToast) { showToast('Failed to copy link', 'error'); }
                    }
                    document.body.removeChild(tempInput);
                    return;
                }
                if (shareUrl) {
                    window.open(shareUrl, '_blank', 'width=600,height=400,scrollbars=yes');
                }
            });
        }
    });

    /* ---------- Report Broken Video ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        var reportBtn = document.getElementById('report-broken-btn');
        var reportForm = document.getElementById('report-form');
        if (!reportBtn || !reportForm) { return; }
        reportBtn.addEventListener('click', function () {
            reportForm.style.display = (reportForm.style.display === 'none' || reportForm.style.display === '') ? 'block' : 'none';
        });

        var form = document.getElementById('report-form-el');
        var result = document.getElementById('report-result');
        if (!form) { return; }
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var data = new FormData(form);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', (window.BDMH_BASE_URL || '') + '/api-report.php', true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    var resp;
                    try { resp = JSON.parse(xhr.responseText); } catch (err) { resp = { success: false }; }
                    if (resp.success) {
                        result.innerHTML = '<span style="color:#2ecc71;"><i class="fas fa-check-circle"></i> ' + escapeHtml(resp.message || 'Report submitted') + '</span>';
                        form.reset();
                        setTimeout(function () { reportForm.style.display = 'none'; }, 2500);
                    } else {
                        result.innerHTML = '<span style="color:#e74c3c;"><i class="fas fa-times-circle"></i> ' + escapeHtml(resp.error || 'Failed to submit') + '</span>';
                    }
                } else {
                    result.innerHTML = '<span style="color:#e74c3c;"><i class="fas fa-times-circle"></i> Network error</span>';
                }
            };
            xhr.send(data);
        });
    });

    /* ---------- Track watch history on movie/anime-watch pages ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        // Detect if we're on a movie or anime page by looking for specific elements
        var favBtn = document.querySelector('.fav-btn');
        if (favBtn && window.addToHistory) {
            var type = favBtn.getAttribute('data-type');
            var id = favBtn.getAttribute('data-id');
            var title = favBtn.getAttribute('data-title');
            var poster = '';
            var posterImg = document.querySelector('.movie-poster img');
            if (posterImg) { poster = posterImg.getAttribute('src'); }
            if (type && id && title) {
                window.addToHistory({
                    type: type,
                    id: id,
                    title: title,
                    poster: poster,
                    url: window.location.pathname + window.location.search,
                });
            }
        }

        // Resume playback + live progress tracking for the premium player
        var video = document.getElementById('video-player');
        if (video) {
            var key = 'bdmh_progress_' + (window.location.pathname + window.location.search);
            var savedPct = 0;
            try { savedPct = parseInt(localStorage.getItem(key) || '0', 10) || 0; } catch (e) { savedPct = 0; }
            var resumed = false;
            video.addEventListener('loadedmetadata', function () {
                if (!resumed && savedPct > 2 && savedPct < 95 && isFinite(video.duration) && video.duration > 0) {
                    video.currentTime = (savedPct / 100) * video.duration;
                }
                resumed = true;
            });
            var lastSaved = 0;
            video.addEventListener('timeupdate', function () {
                if (!isFinite(video.duration) || video.duration <= 0) { return; }
                var now = Date.now();
                if (now - lastSaved > 3000) {
                    lastSaved = now;
                    var pct = Math.round((video.currentTime / video.duration) * 100);
                    try { localStorage.setItem(key, String(pct)); } catch (e) {}
                    if (favBtn && window.addToHistory) {
                        window.addToHistory({
                            type: favBtn.getAttribute('data-type'),
                            id: favBtn.getAttribute('data-id'),
                            title: favBtn.getAttribute('data-title'),
                            poster: posterImg ? posterImg.getAttribute('src') : '',
                            episode: new URLSearchParams(window.location.search).get('ep') || '',
                            progress: pct,
                            url: window.location.pathname + window.location.search,
                        });
                    }
                }
            });
        }
    });

    /* ---------- Hero slider auto-rotate ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        var slides = document.querySelectorAll('.hero-slide');
        var dots = document.querySelectorAll('.hero-dots span');
        if (slides.length <= 1) { return; }
        var current = 0;
        function showSlide(idx) {
            for (var i = 0; i < slides.length; i++) {
                slides[i].classList.remove('active');
                if (dots[i]) { dots[i].classList.remove('active'); }
            }
            slides[idx].classList.add('active');
            if (dots[idx]) { dots[idx].classList.add('active'); }
            current = idx;
        }
        for (var i = 0; i < dots.length; i++) {
            (function (idx) {
                dots[idx].addEventListener('click', function () { showSlide(idx); });
            })(i);
        }
        setInterval(function () {
            var next = (current + 1) % slides.length;
            showSlide(next);
        }, 5000);
    });
})();
