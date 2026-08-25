/* ============================================================
   BDMovieHub - ui.js
   UI interactions: mobile menu, scroll-to-top, lazy loading,
   notifications, modals
   ============================================================ */

(function () {
    'use strict';

    /* ---------- Mobile hamburger menu ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        var hamburger = document.querySelector('.hamburger');
        var mobileMenu = document.querySelector('.mobile-menu');
        var overlay = document.querySelector('.menu-overlay');
        if (!hamburger || !mobileMenu) { return; }

        function openMenu() {
            mobileMenu.classList.add('open');
            if (overlay) { overlay.classList.add('open'); }
            hamburger.innerHTML = '<i class="fas fa-times"></i>';
        }
        function closeMenu() {
            mobileMenu.classList.remove('open');
            if (overlay) { overlay.classList.remove('open'); }
            hamburger.innerHTML = '<i class="fas fa-bars"></i>';
        }

        hamburger.addEventListener('click', function () {
            if (mobileMenu.classList.contains('open')) { closeMenu(); }
            else { openMenu(); }
        });
        if (overlay) { overlay.addEventListener('click', closeMenu); }
        var links = mobileMenu.querySelectorAll('a');
        for (var i = 0; i < links.length; i++) {
            links[i].addEventListener('click', closeMenu);
        }
    });

    /* ---------- Scroll to top button ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.querySelector('.scroll-top');
        if (!btn) {
            btn = document.createElement('button');
            btn.className = 'scroll-top';
            btn.innerHTML = '<i class="fas fa-arrow-up"></i>';
            btn.setAttribute('aria-label', 'Scroll to top');
            document.body.appendChild(btn);
        }
        btn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        window.addEventListener('scroll', function () {
            if (window.scrollY > 400) { btn.classList.add('visible'); }
            else { btn.classList.remove('visible'); }
        });
    });

    /* ---------- Lazy load images (using IntersectionObserver) ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        var imgs = document.querySelectorAll('img[data-src]');
        if (imgs.length === 0) { return; }
        if (!('IntersectionObserver' in window)) {
            // Fallback: load all immediately
            for (var i = 0; i < imgs.length; i++) {
                var s = imgs[i].getAttribute('data-src');
                if (s) { imgs[i].src = s; }
            }
            return;
        }
        var observer = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var img = entry.target;
                    var src = img.getAttribute('data-src');
                    if (src) { img.src = src; }
                    obs.unobserve(img);
                }
            });
        }, { rootMargin: '100px' });
        for (var j = 0; j < imgs.length; j++) { observer.observe(imgs[j]); }
    });

    /* ---------- Toast notifications ---------- */
    function ensureToastContainer() {
        var c = document.querySelector('.toast-container');
        if (!c) {
            c = document.createElement('div');
            c.className = 'toast-container';
            document.body.appendChild(c);
        }
        return c;
    }
    window.showToast = function (message, type) {
        var c = ensureToastContainer();
        var t = document.createElement('div');
        t.className = 'toast' + (type ? ' ' + type : '');
        t.textContent = message;
        c.appendChild(t);
        setTimeout(function () {
            t.style.opacity = '0';
            t.style.transform = 'translateX(120%)';
            setTimeout(function () {
                if (t.parentNode) { t.parentNode.removeChild(t); }
            }, 300);
        }, 2800);
    };

    /* ---------- Modal helper ---------- */
    window.openModal = function (id) {
        var m = document.getElementById(id);
        if (m) { m.style.display = 'flex'; }
    };
    window.closeModal = function (id) {
        var m = document.getElementById(id);
        if (m) { m.style.display = 'none'; }
    };
    document.addEventListener('DOMContentLoaded', function () {
        var closes = document.querySelectorAll('[data-modal-close]');
        for (var i = 0; i < closes.length; i++) {
            closes[i].addEventListener('click', function () {
                var modal = this.closest('.modal-overlay');
                if (modal) { modal.style.display = 'none'; }
            });
        }
        // Click outside to close
        var overlays = document.querySelectorAll('.modal-overlay');
        for (var j = 0; j < overlays.length; j++) {
            (function (ov) {
                ov.addEventListener('click', function (e) {
                    if (e.target === ov) { ov.style.display = 'none'; }
                });
            })(overlays[j]);
        }
    });

    /* ---------- Confirm dialog for delete actions ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        var confirms = document.querySelectorAll('[data-confirm]');
        for (var i = 0; i < confirms.length; i++) {
            confirms[i].addEventListener('click', function (e) {
                var msg = this.getAttribute('data-confirm') || 'Are you sure?';
                if (!window.confirm(msg)) { e.preventDefault(); return false; }
            });
        }
    });

    /* ---------- Auto-dismiss flash messages ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        var flashes = document.querySelectorAll('.flash-msg');
        for (var i = 0; i < flashes.length; i++) {
            (function (f) {
                setTimeout(function () {
                    f.style.opacity = '0';
                    f.style.transform = 'translateY(-10px)';
                    setTimeout(function () {
                        if (f.parentNode) { f.parentNode.removeChild(f); }
                    }, 300);
                }, 4000);
            })(flashes[i]);
        }
    });
})();
