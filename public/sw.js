const CACHE_NAME = 'pos-cache-v1';

// Assets to cache immediately
const PRECACHE_URLS = [
    '/',
    '/favicon.ico',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdn.tailwindcss.com',
    'https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js'
];

// 1. INSTALL: Cache static assets
self.addEventListener('install', event => {
    self.skipWaiting(); // Force activation
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(PRECACHE_URLS);
        })
    );
});

// 2. ACTIVATE: Clean up old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cache => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. FETCH: Intercept network requests
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // IGNORE: Do not cache POST requests (Checkout data)
    // IGNORE: Do not cache Analytics or non-essential external calls
    if (event.request.method !== 'GET') {
        return;
    }

    // STRATEGY: Network First, Fallback to Cache
    // This ensures we get the latest stock if online, but app still works if offline.
    event.respondWith(
        fetch(event.request)
            .then(networkResponse => {
                // If network works, cache a copy for next time
                if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, responseClone);
                    });
                }
                return networkResponse;
            })
            .catch(() => {
                // Network failed, try cache
                return caches.match(event.request);
            })
    );
});