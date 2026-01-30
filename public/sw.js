/* Service Worker - Olika PWA */
const CACHE_NAME = 'olika-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))
            )
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    /* network-first; cache fallback opcional */
    event.respondWith(
        fetch(event.request).catch(() =>
            caches.match(event.request)
        )
    );
});

// Suporte para notificações push
self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'Nova Notificação';
    const options = {
        body: data.body || 'Você tem uma nova atualização',
        icon: data.icon || '/favicon/android-chrome-192x192.png',
        badge: data.badge || '/favicon/android-chrome-192x192.png',
        data: data.data || {},
        tag: data.tag || 'default',
        requireInteraction: data.requireInteraction || false
    };
    
    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Lidar com cliques em notificações
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    
    event.waitUntil(
        clients.openWindow(event.notification.data.url || '/')
    );
});
