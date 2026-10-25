const CACHE_NAME = 'hotel-management-pwa-v1';
const OFFLINE_URL = new URL('offline.html', self.registration.scope).href;
const PRECACHE_URLS = [
    OFFLINE_URL,
    new URL('manifest.webmanifest', self.registration.scope).href,
    new URL('icons/hotel.svg', self.registration.scope).href,
    new URL('favicon.svg', self.registration.scope).href,
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => key.startsWith('hotel-management-pwa-') && key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const requestUrl = new URL(request.url);

    if (requestUrl.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    const isPublicAsset = requestUrl.pathname.includes('/build/assets/')
        || requestUrl.pathname.includes('/adminkit/')
        || requestUrl.pathname.includes('/img/')
        || requestUrl.pathname.includes('/icons/')
        || requestUrl.pathname.endsWith('/favicon.svg');

    if (!isPublicAsset) {
        return;
    }

    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(request).then((response) => {
                if (response.ok && response.type === 'basic') {
                    return caches.open(CACHE_NAME)
                        .then((cache) => cache.put(request, response.clone()))
                        .then(() => response);
                }

                return response;
            });
        })
    );
});
