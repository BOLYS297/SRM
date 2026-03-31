# ✅ CHECKLIST - Préparation pour InfinityFree

## 📋 Avant de préparer l'upload

### Locally (En Local)

- [ ] Ouvrez un terminal dans le dossier `SRM`
- [ ] Lancez: `composer install` (crée/met à jour le dossier `vendor/`)
- [ ] Vérifiez: `ls vendor/autoload.php` doit exister
- [ ] Lancez: `php artisan migrate --env=production` (exécute migrations)
- [ ] Lancez: `php artisan db:seed --class=DatabaseSeeder` (seeders si nécessaire)
- [ ] Compressez: Créez un fichier `SRM.zip` contenant tout le dossier SRM

### Fichiers à Préparer

- [ ] Récupérez `index_infinityfree.php` depuis SRM/
- [ ] Récupérez `.env.infinityfree` depuis SRM/
- [ ] Récupérez `GUIDE_INFINITYFREE.md` pour référence

---

## 🚀 Upload sur InfinityFree (Via FTP)

### Étape 1: Connexion FTP
- [ ] Ouvrez votre client FTP (FileZilla, WinSCP, Cyber Duck, etc.)
- [ ] Connectez-vous avec vos identifiants InfinityFree
- [ ] Naviguez vers le dossier `htdocs` (racine web)

### Étape 2: Upload des Fichiers

**À la racine de htdocs:**
- [ ] Uploadez `index_infinityfree.php`
- [ ] Renommez-le en `index.php` (remplacez l'ancien si présent)

**Dans le dossier SRM/ (que vous crerez/mettrez à jour):**
- [ ] Uploadez le dossier `SRM/` complet 
- [ ] Assurez-vous que `vendor/` est inclus
- [ ] Uploadez `.env.infinityfree` 
- [ ] Renommez-le en `.env`

### Étape 3: Configuration des Permissions

Via le **File Manager de cPanel** d'InfinityFree:
- [ ] Clic droit sur `SRM/storage/` → "Change Permissions" → `755` (ou Make Writable)
- [ ] Clic droit sur `SRM/bootstrap/cache/` → "Change Permissions" → `755`
- [ ] Clic droit sur `SRM/.env` → "Change Permissions" → `644`

### Étape 4: Configuration de la Base de Données

Via **cPanel** → **MySQL Databases**:
- [ ] Créez une nouvelle base de données (ex: `inf_XXXXX_srm`)
- [ ] Créez un utilisateur MySQL (ex: `inf_XXXXX_user`)
- [ ] Définissez un mot de passe fort
- [ ] Assignez cet utilisateur à la base avec tous les privilèges

### Étape 5: Configuration du .env sur le Serveur

Via le **File Manager** → Éditez `SRM/.env`:
- [ ] Changez `APP_ENV=production`
- [ ] Changez `APP_DEBUG=false`
- [ ] Changez `APP_URL=https://votredomaine.infinityfree.com`
- [ ] Remplissez `DB_DATABASE=inf_XXXXX_srm`
- [ ] Remplissez `DB_USERNAME=inf_XXXXX_user`
- [ ] Remplissez `DB_PASSWORD=votreMotDePasse`
- [ ] Sauvegardez le fichier

---

## 🧪 Tests de Connexion

### Test 1: Page d'Accueil
- [ ] Allez à: `https://votredomaine.infinityfree.com/`
- [ ] Vous devriez voir votre application
- [ ] Si erreur 500: consultez étape Dépannage

### Test 2: Vérification des Logs
Via FTP → Dossier `SRM/storage/logs/`:
- [ ] Vérifiez `exceptions.log` pour les erreurs
- [ ] Vérifiez `laravel.log` pour les infos
- [ ] Téléchargez les fichiers pour les examiner

### Test 3: Connexion à la Base de Données
- [ ] Une fois le site ouvert, essayez de vous connecter
- [ ] Si possible, lancez un test d'accès BD
- [ ] Vérifiez que les tables existent

---

## 🐛 Dépannage Erreur 500

### If You See "500 Error":

#### 1. Vérifiez les Logs
```
Allez à: FTP → SRM/storage/logs/
Téléchargez et lisez: exceptions.log
```

#### 2. Erreurs Courantes et Solutions

| Erreur | Cause | Solution |
|--------|-------|----------|
| `vendor/autoload.php not found` | vendor/ manquant | Uploadez le dossier vendor/ complet |
| `Permission denied on storage` | storage/ not writable | CHMOD 755 sur storage/ |
| `SQLSTATE[HY000]` | BD non accessible | Vérifiez identifiants BD dans .env |
| `Class 'X' not found` | Autoloader incomplet | Vérifiez que vendor/ est complet |
| `Storage path does not exist` | Dossiers manquants | Créez manuellement storage/logs/ |

#### 3. Mode Debug Temporaire
Si vous avez besoin de plus d'infos (TEMPORAIRE SEULEMENT):
```
Éditez SRM/.env:
APP_DEBUG=true
APP_ENV=local
```
Rechargez la page pour voir les erreurs détaillées.

⚠️ **IMPORTANT**: Remettez à `false` et `production` après!

---

## 📞 Configuration Avancée

### Activer l'Email
Si vous voulez envoyer des emails (optionnel):

Via cPanel → **Email Accounts**:
- [ ] Créez un compte email (ex: noreply@votredomaine.com)
- [ ] Récupérez les identifiants SMTP

Modifiez `SRM/.env`:
```env
MAIL_DRIVER=smtp
MAIL_HOST=mail.votredomaine.com
MAIL_PORT=465
MAIL_USERNAME=noreply@votredomaine.com
MAIL_PASSWORD=votreMotDePasse
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@votredomaine.com
```

### Sauvegardes Régulières
- [ ] Téléchargez régulièrement votre BD via phpMyAdmin
- [ ] Téléchargez les fichiers importants via FTP
- [ ] Vérifiez les logs régulièrement

---

## ✨ Après la Mise en Ligne

### Sécurité
- [ ] Changez les mots de passe par défaut
- [ ] Vérifiez les permissions des fichiers
- [ ] Mettez à jour APP_DEBUG à `false`
- [ ] Configurez les sessions sécurisées

### Optimisations
- [ ] Clearable caches si accessible: `php artisan cache:clear`
- [ ] Configurez des sauvegardes automatiques
- [ ] Monitrez les logs régulièrement
- [ ] Testez les fonctionnalités critiques

### SSL/HTTPS
- [ ] Vérifiez que HTTPS est activé sur InfinityFree
- [ ] Changez `APP_URL` à `https://...` dans .env
- [ ] Testez que tout fonctionne en HTTPS

---

## 📝 Fichiers Créés pour Vous

Vous avez reçu:

| Fichier | Utilité |
|---------|---------|
| `index_infinityfree.php` | À renommer en `index.php` à la racine de htdocs |
| `.env.infinityfree` | À renommer en `.env` dans le dossier SRM/ |
| `GUIDE_INFINITYFREE.md` | Guide détaillé complet |
| `CHECKLIST_INFINITYFREE.md` | Cette checklist |

---

## 🆘 Si Vous Êtes Bloqué

1. **Vérifiez les logs**: `SRM/storage/logs/exceptions.log`
2. **Activez APP_DEBUG=true** temporairement
3. **Vérifiez la structure**: `htdocs/index.php` + `htdocs/SRM/`
4. **Vérifiez vendor/**: `SRM/vendor/autoload.php` doit exister
5. **Vérifiez .env**: `SRM/.env` doit être configuré
6. **Vérifiez permissions**: `SRM/storage/` doit être writable (chmod 755)
7. **Vérifiez BD**: Vérifiez identifiants dans `.env`

---

## ✅ Statut Final

Une fois cette checklist complète:
- ✅ Votre site devrait être opérationnel
- ✅ Les utilisateurs peuvent se connecter
- ✅ Les requêtes sont sauvegardées correctement
- ✅ Les erreurs sont loggées dans storage/logs/

**Bonne chance! 🚀**
