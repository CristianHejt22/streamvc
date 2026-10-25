// CStreame Progressive Web App (PWA) Service Worker
const CACHE_NAME = 'cstreame-pwa-v2';
const PRECACHE_ASSETS = [
    '/',
    '/manifest.json',
    '/boveda/login',
    '/images/cstreame-icon-192.png',
    '/images/cstreame-icon-512.png',
    '/images/cstreame-icon.svg',
    '/images/spro-play-icon-192.png',
    '/images/spro-play-icon-512.png',
    '/images/spro-play-icon.svg',
    '/images/spro-play-logo.svg',
    '/favicon.ico'
];

// Install Event: Precaching static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch((err) => {
                console.warn('PWA Precache warning:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// Activate Event: Cleaning old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event: Network-first with cache-fallback
self.addEventListener('fetch', (event) => {
    // Only handle GET requests and http/https schemes
    if (event.request.method !== 'GET' || !event.request.url.startsWith('http')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((networkResponse) => {
                // If successful response, optionally cache it
                if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return networkResponse;
            })
            .catch(() => {
                // Fallback to cache if network fails (offline)
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // If asking for HTML, return cached root or login
                    if (event.request.headers.get('accept') && event.request.headers.get('accept').includes('text/html')) {
                        return caches.match('/boveda/login');
                    }
                });
            })
    );
});
