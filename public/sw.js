// Enhanced Service Worker for Echara Voting Platform
const CACHE_VERSION = '2.0';
const STATIC_CACHE = `echara-static-v${CACHE_VERSION}`;
const DYNAMIC_CACHE = `echara-dynamic-v${CACHE_VERSION}`;
const IMAGE_CACHE = `echara-images-v${CACHE_VERSION}`;
const API_CACHE = `echara-api-v${CACHE_VERSION}`;

// Critical resources to cache immediately
const CRITICAL_RESOURCES = [
  '/',
  '/manifest.json',
  '/images/icon-192.png',
  '/images/icon-512.png'
];

// Cache duration settings (in milliseconds)
const CACHE_DURATION = {
  STATIC: 7 * 24 * 60 * 60 * 1000, // 7 days
  DYNAMIC: 24 * 60 * 60 * 1000,    // 1 day
  API: 5 * 60 * 1000,              // 5 minutes
  IMAGES: 30 * 24 * 60 * 60 * 1000 // 30 days
};

// Resource patterns
const PATTERNS = {
  STATIC: /\.(css|js|woff2?|ttf|eot)$/,
  IMAGES: /\.(png|jpg|jpeg|gif|webp|svg|ico)$/,
  API: /\/(api|livewire)\//,
  FONTS: /\.(woff2?|ttf|eot|otf)$/
};

// Install event - cache critical resources
self.addEventListener('install', event => {
  console.log('Service Worker: Installing v' + CACHE_VERSION);
  event.waitUntil(
    Promise.all([
      caches.open(STATIC_CACHE).then(cache => {
        console.log('Service Worker: Caching critical resources');
        return cache.addAll(CRITICAL_RESOURCES);
      }),
      self.skipWaiting()
    ])
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('Service Worker: Activating v' + CACHE_VERSION);
  event.waitUntil(
    Promise.all([
      caches.keys().then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (![
              STATIC_CACHE,
              DYNAMIC_CACHE,
              IMAGE_CACHE,
              API_CACHE
            ].includes(cacheName)) {
              console.log('Service Worker: Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      }),
      self.clients.claim()
    ])
  );
});

// Enhanced fetch event with intelligent caching strategies
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests and external origins
  if (request.method !== 'GET' || !url.origin.includes(self.location.origin)) {
    return;
  }

  // Route to appropriate caching strategy
  if (PATTERNS.API.test(url.pathname)) {
    event.respondWith(handleApiRequest(request));
  } else if (PATTERNS.STATIC.test(url.pathname)) {
    event.respondWith(handleStaticAsset(request));
  } else if (PATTERNS.IMAGES.test(url.pathname)) {
    event.respondWith(handleImageAsset(request));
  } else {
    event.respondWith(handlePageRequest(request));
  }
});

// API request handler with network-first strategy
async function handleApiRequest(request) {
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(API_CACHE);
      const responseWithTimestamp = new Response(networkResponse.body, {
        status: networkResponse.status,
        statusText: networkResponse.statusText,
        headers: {
          ...Object.fromEntries(networkResponse.headers.entries()),
          'sw-cache-timestamp': Date.now().toString()
        }
      });
      cache.put(request, responseWithTimestamp.clone());
      return networkResponse;
    }
    
    return networkResponse;
  } catch (error) {
    const cache = await caches.open(API_CACHE);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse && !isExpired(cachedResponse, CACHE_DURATION.API)) {
      return cachedResponse;
    }
    
    return new Response(JSON.stringify({
      error: 'Network unavailable and no cached data',
      offline: true,
      timestamp: Date.now()
    }), {
      status: 503,
      headers: { 'Content-Type': 'application/json' }
    });
  }
}

// Static asset handler with cache-first strategy
async function handleStaticAsset(request) {
  try {
    const cache = await caches.open(STATIC_CACHE);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse && !isExpired(cachedResponse, CACHE_DURATION.STATIC)) {
      // Update cache in background
      fetch(request).then(response => {
        if (response.ok) {
          cache.put(request, addTimestamp(response));
        }
      }).catch(() => {});
      
      return cachedResponse;
    }
    
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      cache.put(request, addTimestamp(networkResponse.clone()));
    }
    
    return networkResponse;
  } catch (error) {
    const cache = await caches.open(STATIC_CACHE);
    const cachedResponse = await cache.match(request);
    return cachedResponse || new Response('Asset unavailable', { status: 503 });
  }
}

// Image asset handler with stale-while-revalidate
async function handleImageAsset(request) {
  try {
    const cache = await caches.open(IMAGE_CACHE);
    const cachedResponse = await cache.match(request);
    
    const fetchPromise = fetch(request).then(response => {
      if (response.ok) {
        cache.put(request, addTimestamp(response.clone()));
      }
      return response;
    }).catch(() => null);
    
    return cachedResponse || await fetchPromise || new Response('Image unavailable', { status: 503 });
  } catch (error) {
    return new Response('Image unavailable', { status: 503 });
  }
}

// Page request handler with network-first strategy
async function handlePageRequest(request) {
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, addTimestamp(networkResponse.clone()));
    }
    
    return networkResponse;
  } catch (error) {
    const cache = await caches.open(DYNAMIC_CACHE);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Return enhanced offline page for navigation requests
    if (request.mode === 'navigate') {
      return new Response(getOfflinePage(), {
        headers: { 'Content-Type': 'text/html' }
      });
    }
    
    return new Response('Page unavailable offline', { status: 503 });
  }
}

// Utility functions
function addTimestamp(response) {
  const headers = new Headers(response.headers);
  headers.set('sw-cache-timestamp', Date.now().toString());
  return new Response(response.body, {
    status: response.status,
    statusText: response.statusText,
    headers: headers
  });
}

function isExpired(response, maxAge) {
  const timestamp = response.headers.get('sw-cache-timestamp');
  if (!timestamp) return false;
  return Date.now() - parseInt(timestamp) > maxAge;
}

function getOfflinePage() {
  return `
    <!DOCTYPE html>
    <html lang="en">
      <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Offline - Echara Vote</title>
        <style>
          * { margin: 0; padding: 0; box-sizing: border-box; }
          body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
          }
          .container {
            text-align: center;
            max-width: 400px;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
          }
          h1 { font-size: 2rem; margin-bottom: 1rem; }
          p { margin-bottom: 2rem; opacity: 0.9; }
          button {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
          }
          button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
          }
          .icon { font-size: 4rem; margin-bottom: 1rem; }
        </style>
      </head>
      <body>
        <div class="container">
          <div class="icon">📡</div>
          <h1>You're Offline</h1>
          <p>Please check your internet connection and try again.</p>
          <button onclick="window.location.reload()">Retry Connection</button>
        </div>
      </body>
    </html>
  `;
}

// Background sync for offline actions
self.addEventListener('sync', event => {
  console.log('Service Worker: Background sync triggered');
  if (event.tag === 'background-sync') {
    event.waitUntil(doBackgroundSync());
  }
});

function doBackgroundSync() {
  // Handle any pending offline actions here
  console.log('Service Worker: Performing background sync');
  // This would typically sync any queued votes or actions
}

// Push notifications (if implemented)
self.addEventListener('push', event => {
  console.log('Service Worker: Push received');
  if (event.data) {
    const data = event.data.json();
    const options = {
      body: data.body,
      icon: '/images/icon-192.png',
      badge: '/images/icon-192.png',
      vibrate: [100, 50, 100],
      data: {
        dateOfArrival: Date.now(),
        primaryKey: data.primaryKey
      }
    };
    event.waitUntil(
      self.registration.showNotification(data.title, options)
    );
  }
});

// Notification click handler
self.addEventListener('notificationclick', event => {
  console.log('Service Worker: Notification clicked');
  event.notification.close();

  event.waitUntil(
    clients.openWindow('/')
  );
});