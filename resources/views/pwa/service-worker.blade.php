const CACHE_NAME = 'koordli-static-{{ $version }}';
const OFFLINE_URL = '/offline';

const STATIC_CACHE_PATTERNS = [
    /\/build\/assets\/.*\.(js|css)$/,
    /\.(png|jpg|jpeg|svg|webp|ico|woff2?)$/,
];

const NEVER_CACHE_PATTERNS = [
    /\/livewire\//,
    /\/broadcasting\/auth/,
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.add(OFFLINE_URL))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') return;

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() =>
                caches.open(CACHE_NAME).then((cache) => cache.match(OFFLINE_URL))
            )
        );
        return;
    }

    const url = new URL(request.url);

    if (NEVER_CACHE_PATTERNS.some((p) => p.test(url.pathname))) return;
    if (!STATIC_CACHE_PATTERNS.some((p) => p.test(url.pathname))) return;

    event.respondWith(
        caches.open(CACHE_NAME).then((cache) =>
            cache.match(request).then((cached) => {
                const networkFetch = fetch(request)
                    .then((response) => {
                        if (response.ok) cache.put(request, response.clone());
                        return response;
                    })
                    .catch(() => cached);

                return cached || networkFetch;
            })
        )
    );
});

self.addEventListener('push', (event) => {
    if (!event.data) return;

    let payload;
    try {
        payload = event.data.json();
    } catch (e) {
        payload = { title: 'Koordli', body: event.data.text() };
    }

    event.waitUntil(
        self.registration.showNotification(payload.title || 'Koordli', {
            body: payload.body || '',
            icon: '/pwa/icon/192.png',
            badge: '/pwa/icon/192.png',
            data: { url: payload.url || '/' },
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (const client of windowClients) {
                if (client.url === url && 'focus' in client) return client.focus();
            }
            if (clients.openWindow) return clients.openWindow(url);
        })
    );
});