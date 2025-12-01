// service-worker.js
const CACHE_NAME = 'pos-cache-v1';
const OFFLINE_ORDER_QUEUE = 'offline-orders';

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll([
                // Add files to cache if necessary
                // '/index.html',
                // '/styles.css',
                // '/script.js',
            ]);
        })
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request);
        })
    );
});

self.addEventListener('sync', event => {
    if (event.tag === 'sync-offline-orders') {
        event.waitUntil(syncOfflineOrders());
    }
});

async function syncOfflineOrders() {
    const offlineOrders = await getOfflineOrders();
    if (offlineOrders.length === 0) return;

    const requests = offlineOrders.map(order => {
        return fetch('/path/to/checkout/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': 'YOUR_CSRF_TOKEN_HERE', // Pass CSRF token if necessary
            },
            body: JSON.stringify(order),
        });
    });

    await Promise.all(requests);
    await clearOfflineOrders();
}

async function getOfflineOrders() {
    const cache = await caches.open(OFFLINE_ORDER_QUEUE);
    const response = await cache.match('orders');
    if (!response) return [];

    return response.json();
}

async function clearOfflineOrders() {
    const cache = await caches.open(OFFLINE_ORDER_QUEUE);
    await cache.delete('orders');
}