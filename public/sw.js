/**
 * SRM PWA Service Worker
 * Version 1.0.0
 *
 * Caching Strategies:
 * - network-first: API calls, dynamic data
 * - cache-first: Static assets (CSS, JS, images, fonts)
 * - stale-while-revalidate: HTML pages, semi-static content
 */

const CACHE_VERSION = "v1";
const CACHE_STATIC = `srm-static-${CACHE_VERSION}`;
const CACHE_DYNAMIC = `srm-dynamic-${CACHE_VERSION}`;
const CACHE_OFFLINE = `srm-offline-${CACHE_VERSION}`;
const OFFLINE_URL = "/offline.html";

// Assets to precache on install
const STATIC_ASSETS = [
    "/",
    "/offline.html",
    "/manifest.json",
    "/images/icons/icon-192x192.png",
    "/images/icons/icon-512x512.png",
    "/images/icons/apple-touch-icon.png",
];

/**
 * INSTALL EVENT
 * Cache essential assets and activate immediately
 */
self.addEventListener("install", (event) => {
    console.log("[SW] Installing...");

    event.waitUntil(
        Promise.all([
            // Cache static assets
            caches.open(CACHE_STATIC).then((cache) => {
                console.log("[SW] Caching static assets");
                return cache.addAll(STATIC_ASSETS).catch((error) => {
                    console.warn("[SW] Some assets failed to cache:", error);
                });
            }),

            // Cache offline page
            caches.open(CACHE_OFFLINE).then((cache) => {
                console.log("[SW] Caching offline page");
                return cache.add(OFFLINE_URL).catch((error) => {
                    console.warn("[SW] Failed to cache offline page:", error);
                });
            }),
        ]).then(() => {
            console.log("[SW] Installation complete");
            self.skipWaiting();
        }),
    );
});

/**
 * ACTIVATE EVENT
 * Clean up old caches and claim all clients
 */
self.addEventListener("activate", (event) => {
    console.log("[SW] Activating...");

    event.waitUntil(
        caches
            .keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        const isOldCache =
                            cacheName.startsWith("srm-") &&
                            !cacheName.includes(CACHE_VERSION);

                        if (isOldCache) {
                            console.log("[SW] Deleting old cache:", cacheName);
                            return caches.delete(cacheName);
                        }
                    }),
                );
            })
            .then(() => {
                console.log("[SW] Activation complete");
                return self.clients.claim();
            }),
    );
});

/**
 * FETCH EVENT
 * Implement caching strategies based on request type
 */
self.addEventListener("fetch", (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== "GET") {
        return;
    }

    // Skip cross-origin requests
    if (url.origin !== location.origin) {
        return;
    }

    // Offline page - cache only
    if (url.pathname === "/offline.html") {
        event.respondWith(cacheFirst(request));
        return;
    }

    // API endpoints - network first
    if (url.pathname.startsWith("/api/")) {
        event.respondWith(networkFirst(request));
        return;
    }

    // Static assets - cache first
    if (isStaticAsset(url.pathname)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // HTML pages - stale while revalidate
    if (request.headers.get("accept")?.includes("text/html")) {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }

    // Default - network first
    event.respondWith(networkFirst(request));
});

/**
 * CACHE STRATEGIES
 */

/**
 * Network First Strategy
 * Try network, fallback to cache
 * Used for: API, dynamic content
 */
async function networkFirst(request) {
    try {
        const response = await fetch(request);

        // Only cache successful responses
        if (response && response.status === 200) {
            const responseClone = response.clone();
            caches.open(CACHE_DYNAMIC).then((cache) => {
                cache.put(request, responseClone);
            });
        }

        return response;
    } catch (error) {
        console.log("[SW] Network failed, using cache:", request.url);
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }

        // Return offline page as fallback
        return caches.match(OFFLINE_URL);
    }
}

/**
 * Cache First Strategy
 * Return cached version if available, fetch otherwise
 * Used for: Static assets, rarely changing resources
 */
async function cacheFirst(request) {
    const cached = await caches.match(request);

    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);

        // Only cache successful responses
        if (response && response.status === 200) {
            const responseClone = response.clone();
            caches.open(CACHE_STATIC).then((cache) => {
                cache.put(request, responseClone);
            });
        }

        return response;
    } catch (error) {
        console.log("[SW] Cache and network failed:", request.url);
        return caches.match(OFFLINE_URL);
    }
}

/**
 * Stale While Revalidate Strategy
 * Return cached version immediately, update in background
 * Used for: HTML pages, frequently accessed content
 */
async function staleWhileRevalidate(request) {
    const cached = await caches.match(request);

    const fetchPromise = fetch(request)
        .then((response) => {
            // Only cache successful responses
            if (response && response.status === 200) {
                const responseClone = response.clone();
                caches.open(CACHE_DYNAMIC).then((cache) => {
                    cache.put(request, responseClone);
                });
            }

            return response;
        })
        .catch((error) => {
            console.log("[SW] Fetch failed, returning cached:", request.url);
            return cached || caches.match(OFFLINE_URL);
        });

    // Return cached immediately, or fetch result if no cache
    return cached || fetchPromise;
}

/**
 * HELPERS
 */

/**
 * Check if URL is a static asset
 */
function isStaticAsset(pathname) {
    const extensions = [
        ".js",
        ".css",
        ".png",
        ".jpg",
        ".jpeg",
        ".gif",
        ".svg",
        ".webp",
        ".woff",
        ".woff2",
        ".ttf",
        ".eot",
        ".ico",
        ".json",
        ".txt",
    ];

    return extensions.some((ext) => pathname.toLowerCase().endsWith(ext));
}

/**
 * MESSAGE EVENT
 * Listen for messages from clients
 */
self.addEventListener("message", (event) => {
    const { type, payload } = event.data;

    console.log("[SW] Message received:", type);

    switch (type) {
        case "CLEAR_CACHE":
            clearAllCaches();
            break;

        case "SKIP_WAITING":
            self.skipWaiting();
            break;

        case "CLAIM_CLIENTS":
            self.clients.claim();
            break;

        default:
            console.log("[SW] Unknown message type:", type);
    }
});

/**
 * Clear all SRM caches
 */
async function clearAllCaches() {
    try {
        const cacheNames = await caches.keys();
        const srmCaches = cacheNames.filter((name) => name.startsWith("srm-"));

        await Promise.all(srmCaches.map((name) => caches.delete(name)));

        console.log("[SW] All caches cleared");

        // Notify all clients
        self.clients.matchAll().then((clients) => {
            clients.forEach((client) => {
                client.postMessage({
                    type: "CACHE_CLEARED",
                    timestamp: new Date().toISOString(),
                });
            });
        });
    } catch (error) {
        console.error("[SW] Error clearing caches:", error);
    }
}

/**
 * BACKGROUND SYNC
 * Sync data when connection is restored
 */
self.addEventListener("sync", (event) => {
    console.log("[SW] Background sync triggered:", event.tag);

    if (event.tag === "sync-requetes") {
        event.waitUntil(syncRequetes());
    }

    if (event.tag === "sync-notifications") {
        event.waitUntil(syncNotifications());
    }
});

async function syncRequetes() {
    try {
        console.log("[SW] Syncing requêtes...");
        // TODO: Implement requête sync logic
        // Sync pending requêtes stored in IndexedDB
    } catch (error) {
        console.error("[SW] Requête sync failed:", error);
        throw error; // Retry sync
    }
}

async function syncNotifications() {
    try {
        console.log("[SW] Syncing notifications...");
        // TODO: Implement notification sync logic
    } catch (error) {
        console.error("[SW] Notification sync failed:", error);
        throw error;
    }
}

/**
 * PUSH NOTIFICATIONS
 * Handle incoming push events
 */
self.addEventListener("push", (event) => {
    console.log("[SW] Push notification received");

    if (!event.data) {
        console.log("[SW] No data in push event");
        return;
    }

    try {
        const data = event.data.json();

        const options = {
            body: data.body || "Nouvelle notification",
            icon: "/images/icons/icon-192x192.png",
            badge: "/images/icons/icon-96x96.png",
            tag: data.tag || "srm-notification",
            data: data.data || {},
            vibrate: [200, 100, 200],
            timestamp: Date.now(),
        };

        if (data.actions) {
            options.actions = data.actions;
        }

        if (data.color) {
            options.badge = data.color;
        }

        event.waitUntil(
            self.registration.showNotification(data.title || "SRM", options),
        );
    } catch (error) {
        console.error("[SW] Push notification error:", error);
    }
});

/**
 * NOTIFICATION CLICK HANDLER
 * Handle user interactions with notifications
 */
self.addEventListener("notificationclick", (event) => {
    console.log("[SW] Notification clicked:", event.notification.tag);

    event.notification.close();

    const data = event.notification.data || {};
    const url = data.url || "/";

    event.waitUntil(
        clients.matchAll({ type: "window" }).then((windowClients) => {
            // Check if there's already a window open with the target URL
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                if (client.url === url && "focus" in client) {
                    return client.focus();
                }
            }

            // If not, open a new window
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        }),
    );
});

/**
 * NOTIFICATION CLOSE HANDLER
 * Handle notification dismissal
 */
self.addEventListener("notificationclose", (event) => {
    console.log("[SW] Notification closed:", event.notification.tag);
});

console.log("[SW] Service Worker loaded");
