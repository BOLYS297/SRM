import "./bootstrap";

// Register Service Worker for PWA
if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker
            .register("/sw.js")
            .then((registration) => {
                console.log("[PWA] Service Worker registered:", registration);
            })
            .catch((error) => {
                console.warn(
                    "[PWA] Service Worker registration failed:",
                    error,
                );
            });
    });
}

// Handle PWA install prompt
let deferredPrompt;
window.addEventListener("beforeinstallprompt", (e) => {
    e.preventDefault();
    deferredPrompt = e;
});
