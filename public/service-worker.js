const CACHE_NAME = 'larachat-v1';
const urlsToCache = [
  '/',
  '/build/assets/app.css',
  '/build/assets/app.js',
  '/favicon.ico',
  '/favicon.svg',
  '/apple-touch-icon.png'
];

// Install event - cache resources
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
      .catch(error => {
        console.error('Failed to cache resources during install:', error);
      })
  );
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', event => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  // Skip API requests to allow fresh data
  if (event.request.url.includes('/api/') || 
      event.request.url.includes('/broadcasting/') ||
      event.request.url.includes('/sanctum/')) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Cache hit - return response
        if (response) {
          return response;
        }

        // Clone the request
        const fetchRequest = event.request.clone();

        return fetch(fetchRequest).then(response => {
          // Check if valid response
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }

          // Clone the response
          const responseToCache = response.clone();

          // Cache the fetched response for future use
          caches.open(CACHE_NAME)
            .then(cache => {
              // Only cache successful responses for specific file types
              const url = event.request.url;
              if (url.endsWith('.js') || 
                  url.endsWith('.css') || 
                  url.endsWith('.png') || 
                  url.endsWith('.jpg') || 
                  url.endsWith('.jpeg') || 
                  url.endsWith('.svg') || 
                  url.endsWith('.ico') ||
                  url.endsWith('.woff') ||
                  url.endsWith('.woff2') ||
                  url.endsWith('/')) {
                cache.put(event.request, responseToCache);
              }
            });

          return response;
        }).catch(error => {
          // Network request failed, try to serve offline page if available
          console.error('Fetch failed:', error);
          if (event.request.destination === 'document') {
            return caches.match('/');
          }
        });
      })
  );
});

// Handle messages from the client
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});