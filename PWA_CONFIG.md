# PWA Configuration - SRM

## ✅ Status

Votre application est maintenant configurée comme **Progressive Web App (PWA)**.

## 📦 Fichiers Configurés

- ✅ `manifest.json` - Métadonnées PWA
- ✅ `public/sw.js` - Service Worker complet
- ✅ `public/offline.html` - Page hors ligne
- ✅ `resources/views/welcome.blade.php` - Meta tags PWA
- ✅ `resources/js/app.js` - Enregistrement Service Worker
- ✅ `generate-pwa-icons.php` - Générateur d'icônes

## 🚀 Setup Initial

```bash
# 1. Générer les icônes PWA
php generate-pwa-icons.php

# 2. Installer les dépendances (si nécessaire)
npm install

# 3. Builder les assets
npm run build

# 4. Démarrer le serveur
php artisan serve
```

Ou sur Windows:

```bash
setup-pwa.bat
```

## 📱 Installation sur Appareil Mobile

### Android (Chrome)

1. Ouvrir l'app sur Chrome
2. Menu (⋮) → **Installer l'app**
3. Confirmer

### iOS (Safari)

1. Ouvrir l'app sur Safari
2. Bouton Partage → **Sur l'écran d'accueil**
3. Confirmer

## 🛠 Fonctionnalités PWA

### Service Worker

- ✅ Caching stratégies (network-first, cache-first, stale-while-revalidate)
- ✅ Offline support avec page `/offline.html`
- ✅ Background sync (quand la connexion revient)
- ✅ Push notifications (préparé)

### Manifest

- ✅ Icons en 96x96, 192x192, 512x512
- ✅ Apple touch icon
- ✅ Screenshots pour app store
- ✅ Thème et couleurs personnalisés

### Meta Tags

- ✅ Apple mobile web app capable
- ✅ Status bar noir translucide (iOS)
- ✅ Theme color personnalisée

## 🗂 Structure des Icônes

```
public/images/icons/
├── icon-96x96.png
├── icon-192x192.png
├── icon-192x192-maskable.png
├── icon-512x512.png
├── apple-touch-icon.png
├── screenshot-540x720.png
└── screenshot-1280x720.png
```

## 🔄 Stratégies de Cache

| Type de Requête     | Stratégie              | Comportement                                 |
| ------------------- | ---------------------- | -------------------------------------------- |
| `/api/*`            | Network-first          | Cherche réseau d'abord, cache en fallback    |
| `.js, .css, images` | Cache-first            | Cache d'abord, réseau en fallback            |
| Pages HTML          | Stale-while-revalidate | Cache immédiat + mise à jour en arrière-plan |

## 📊 Tester la PWA

### Lighthouse Audit

```bash
# Dans Chrome DevTools
1. F12 → Lighthouse
2. Select "Progressive Web App"
3. Run audit
```

### Offline Testing

1. Network tab → Offline
2. Rafraîchir la page → Voit la page offline
3. Retirer données réseau

### Cache Inspection

1. DevTools → Application → Service Workers
2. Voir registrations et cache

## 🔧 Configuration Avancée

### Background Sync

Le Service Worker supporte la synchronisation en arrière-plan:

```javascript
// Enregistrer une synchronisation
registration.sync.register("sync-requetes");
```

### Push Notifications

Préparé dans le SW, à implémenter:

```javascript
self.addEventListener("push", (event) => {
    // Handle push notifications
});
```

## 📝 Checklist Production

- [ ] Générer les icônes: `php generate-pwa-icons.php`
- [ ] Tester Service Worker dans DevTools
- [ ] Vérifier offline.html
- [ ] Vérifier manifest.json
- [ ] Tester sur Android et iOS
- [ ] Exécuter Lighthouse audit
- [ ] HTTPS activé (obligatoire pour PWA)
- [ ] Favicon configurée

## 🐛 Troubleshooting

### "Service Worker won't register"

1. Vérifier HTTPS (http://localhost fonctionne pour dev)
2. Vérifier console.log: `[PWA]...`
3. Vérifier `/sw.js` est accessible

### "App not installable"

1. Vérifier manifest.json valide
2. Vérifier icons existent
3. Vérifier start_url = "/"

### "Offline page not showing"

1. Vérifier `/offline.html` existe
2. Vérifier cache dans DevTools
3. Vérifier Service Worker activated

## 📚 Resources

- [MDN PWA](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps)
- [Web.dev PWA](https://web.dev/pwa/)
- [Manifest Validator](https://manifest-validator.appspot.com/)

---

**PWA Status**: ✅ Ready for testing and deployment
