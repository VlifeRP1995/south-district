const CACHE_NAME = 'south-district-v9';
const ASSETS_TO_CACHE = [
  '/manifest.php',
  '/offline.html'
];

// Installation du Service Worker
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
  self.skipWaiting();
});

// Activation et nettoyage des anciens caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Interception des requêtes
self.addEventListener('fetch', (event) => {
  // Ignorer toutes les requêtes non-GET (POST, PUT, PATCH, DELETE)
  if (event.request.method !== 'GET') {
    return;
  }

  const requestUrl = new URL(event.request.url);

  // Exclusion des routes d'administration et de l'API
  if (
    requestUrl.pathname === '/admin' ||
    requestUrl.pathname.startsWith('/admin') ||
    requestUrl.pathname.includes('admin') ||
    requestUrl.pathname.includes('discord-mapping') ||
    requestUrl.pathname.includes('permissions') ||
    requestUrl.pathname.startsWith('/api/')
  ) {
    return;
  }

  // Stratégie Cache-First pour les assets compilés (Vite /dist/) et images/fonts statiques
  // Les scripts JS de /assets/js/ passent en Network-First pour garantir la fraîcheur du code
  const isDynamicAssetJs = requestUrl.pathname.startsWith('/assets/js/');
  if ((requestUrl.pathname.startsWith('/dist/') || requestUrl.pathname.startsWith('/assets/')) && !isDynamicAssetJs) {
    event.respondWith(
      caches.match(event.request).then((cachedResponse) => {
        if (cachedResponse) {
          return cachedResponse;
        }
        return fetch(event.request).then((networkResponse) => {
          // On met en cache la nouvelle ressource pour la prochaine fois
          if (networkResponse && networkResponse.status === 200) {
            const responseToCache = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseToCache);
            });
          }
          return networkResponse;
        }).catch(() => new Response('', { status: 408, statusText: 'Request Timeout' }));
      })
    );
    return;
  }

  // Stratégie Network-First pour les pages PHP HTML avec fallback hors ligne
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).catch(() => {
        return caches.match('/offline.html').then((response) => {
          if (response) return response;
          // Au pire, on renvoie une simple page texte
          return new Response('Mode hors ligne. Veuillez vérifier votre connexion.', {
            headers: { 'Content-Type': 'text/html; charset=utf-8' }
          });
        });
      })
    );
    return;
  }

  // Comportement par défaut (Network-First avec catch sécurisé)
  event.respondWith(
    fetch(event.request).catch(() => caches.match(event.request).then((r) => r || new Response('', { status: 404 })))
  );
});
