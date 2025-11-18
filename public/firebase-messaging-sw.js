importScripts(
    'https://storage.googleapis.com/workbox-cdn/releases/6.4.1/workbox-sw.js'
  );

// Configure Workbox
workbox.setConfig({
    debug: false
});

// Destructure Workbox modules
const { registerRoute } = workbox.routing;
const { CacheFirst, NetworkFirst } = workbox.strategies;
const { CacheableResponsePlugin } = workbox.cacheableResponse;

// Cache names for better organization
const STATIC_CACHE = 'static-assets-v1';

// Cache static assets (CSS, vendors, media) with CacheFirst strategy
registerRoute(
    ({ url }) =>
        url.pathname.startsWith('/assets/css/') ||
        url.pathname.startsWith('/assets/vendor/') ||
        url.pathname.startsWith('/assets/media/') ||
        url.pathname.startsWith('/assets/js/'),
    new CacheFirst({
        cacheName: STATIC_CACHE,
        plugins: [
            new CacheableResponsePlugin({
                statuses: [0, 200]
            })
        ],
    })
);

// Handle push notifications
self.addEventListener('push', (event) => {
    if (!event.data) {
        return;
    }

    try {
        const payload = event.data.json();
        const title = payload?.notification?.title || 'Notification';
        const options = {
            body: payload?.notification?.body || '',
            icon: '/favicon.ico',
            badge: payload?.notification?.badge || '/favicon.ico',
            vibrate: [200, 100, 200],
            data: {
                url: payload?.data?.action_url || '/',
                timestamp: payload?.data?.timestamp || Date.now()
            },
            requireInteraction: false,
            tag: payload?.data?.type || 'default'
        };

        event.waitUntil(
            self.registration.showNotification(title, options)
        );
    } catch (error) {
        console.error('Error handling push notification:', error);
    }
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const urlToOpen = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then((clientList) => {
            // Check if there's already a window open with this URL
            for (const client of clientList) {
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }

            // If no window is open, open a new one
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});
