/* BDMovieHub service worker: cache the shell, then prefer the network for pages. */
var CACHE_NAME = 'bdmoviehub-shell-v1';
var SHELL = ['./', './index.php', './assets/css/style.css', './assets/js/ui.js', './assets/js/features.js', './icon.png'];

self.addEventListener('install', function (event) {
    event.waitUntil(caches.open(CACHE_NAME).then(function (cache) { return cache.addAll(SHELL); }));
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(caches.keys().then(function (keys) {
        return Promise.all(keys.map(function (key) {
            return key !== CACHE_NAME ? caches.delete(key) : Promise.resolve();
        }));
    }));
    self.clients.claim();
});

self.addEventListener('fetch', function (event) {
    if (event.request.method !== 'GET') { return; }
    event.respondWith(fetch(event.request).catch(function () { return caches.match(event.request); }));
});
