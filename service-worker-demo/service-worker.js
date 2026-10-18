const CACHE_NAME = "service-worker-demo-v1";
const FILES_TO_CACHE = [
 "./",
 "./index.html",
 "./style.css",
 "./app.js"
];

self.addEventListener("install", (event) => {
 event.waitUntil(
 caches.open(CACHE_NAME)
 .then((cache) => cache.addAll(FILES_TO_CACHE))
 .then(() => console.log("Files cached successfully"))
 );
});

self.addEventListener("activate", (event) => {
 console.log("Service Worker activated");
});

self.addEventListener("fetch", (event) => {
 event.respondWith(
 caches.match(event.request).then((cachedResponse) => {
 if (cachedResponse) {
 return cachedResponse;
 }
 return fetch(event.request);
 })
 );
});