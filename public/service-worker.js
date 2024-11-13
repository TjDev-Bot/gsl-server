const CACHE_NAME = 'gsl-staff-cache-v1';
const urlsToCache = [
  '/',
  '/themes/modern/css/portal-full.css',
  '/themes/modern/css/portal-crud.css',
  '/logos/icon-192x192.png',
  '/logos/icon-512x512.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        return response || fetch(event.request);
      })
  );
});