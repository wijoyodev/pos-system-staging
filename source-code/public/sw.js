const CACHE_NAME = 'pos-app-v1';
const STATIC_CACHE = 'pos-static-v1';
const DYNAMIC_CACHE = 'pos-dynamic-v1';

// Assets to cache immediately on install
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    'https://cdn.tailwindcss.com?plugins=forms,container-queries',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
    'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;700;800&display=swap',
    'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap',
];

// Pages that should be available offline (for kasir role)
const OFFLINE_PAGES = [
    '/',
    '/pending-orders',
    '/history',
    '/presensi-page',
];

// API endpoints to cache
const OFFLINE_APIS = [
    '/api/offline/products',
    '/api/offline/stocks',
    '/api/offline/categories',
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker...');
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating service worker...');
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== STATIC_CACHE && key !== DYNAMIC_CACHE)
                    .map((key) => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - network first, fallback to cache
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip chrome-extension and other non-http(s) requests
    if (!url.protocol.startsWith('http')) {
        return;
    }

    // For API requests - network first, fallback to cache
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const responseClone = response.clone();
                    caches.open(DYNAMIC_CACHE).then((cache) => {
                        cache.put(request, responseClone);
                    });
                    return response;
                })
                .catch(() => {
                    return caches.match(request);
                })
        );
        return;
    }

    // For HTML pages - network first, fallback to offline page
    if (request.headers.get('accept').includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const responseClone = response.clone();
                    caches.open(DYNAMIC_CACHE).then((cache) => {
                        cache.put(request, responseClone);
                    });
                    return response;
                })
                .catch(() => {
                    return caches.match('/offline') || caches.match('/');
                })
        );
        return;
    }

    // For static assets (JS, CSS, fonts, images) - cache first
    if (
        url.pathname.endsWith('.js') ||
        url.pathname.endsWith('.css') ||
        url.pathname.includes('fonts.googleapis') ||
        url.pathname.includes('gstatic.com') ||
        url.pathname.includes('cdn.jsdelivr') ||
        url.pathname.includes('cdn.tailwindcss')
    ) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) {
                    return cached;
                }
                return fetch(request).then((response) => {
                    const responseClone = response.clone();
                    caches.open(STATIC_CACHE).then((cache) => {
                        cache.put(request, responseClone);
                    });
                    return response;
                });
            })
        );
        return;
    }

    // Default: network first
    event.respondWith(
        fetch(request)
            .catch(() => caches.match(request))
    );
});

// Background sync for offline transactions
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-transactions') {
        event.waitUntil(syncTransactions());
    }
});

async function syncTransactions() {
    const pendingTransactions = await getPendingTransactions();
    for (const transaction of pendingTransactions) {
        try {
            await fetch('/api/offline/transactions', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(transaction),
            });
            await markTransactionSynced(transaction.offlineId);
        } catch (error) {
            console.error('[SW] Failed to sync transaction:', error);
        }
    }
}

function getPendingTransactions() {
    return new Promise((resolve) => {
        const dbRequest = indexedDB.open('pos-offline', 1);
        dbRequest.onsuccess = (event) => {
            const db = event.target.result;
            const transaction = db.transaction(['transactions'], 'readonly');
            const store = transaction.objectStore('transactions');
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result || []);
        };
        dbRequest.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('transactions')) {
                db.createObjectStore('transactions', { keyPath: 'offlineId' });
            }
        };
    });
}

function markTransactionSynced(offlineId) {
    return new Promise((resolve) => {
        const dbRequest = indexedDB.open('pos-offline', 1);
        dbRequest.onsuccess = (event) => {
            const db = event.target.result;
            const transaction = db.transaction(['transactions'], 'readwrite');
            const store = transaction.objectStore('transactions');
            store.delete(offlineId);
            transaction.oncomplete = () => resolve();
        };
    });
}

// Push notification handler (for future use)
self.addEventListener('push', (event) => {
    if (event.data) {
        const data = event.data.json();
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/pwa/icon-192.png',
            badge: '/pwa/icon-192.png',
        });
    }
});

console.log('[SW] Service worker loaded');
