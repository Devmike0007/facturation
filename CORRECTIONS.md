# Journal des corrections - Système de Caisse

---

## Erreur 1 — Chemins `require_once` invalides dans `includes/header.php`

**Fichier :** `includes/header.php`  
**Lignes originales :**
```php
require_once('/../config/config.php');
require_once('/../auth/session.php');
```

**Problème :**  
Le chemin `'/../config/config.php'` commence par `/..` ce qui est un chemin absolu invalide sur le système de fichiers. PHP ne pouvait pas trouver les fichiers, ce qui causait une erreur fatale au chargement de toutes les pages.

**Correction :**
```php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../auth/session.php');
```

**Explication :**  
`__DIR__` est une constante magique PHP qui retourne le chemin absolu du répertoire du fichier courant (ici `C:\xampp\htdocs\facturation\includes`). En combinant `__DIR__ . '/../'`, on remonte d'un niveau de façon fiable, peu importe d'où la page est appelée.

---

## Erreur 2 — `header()` utilisé dans un attribut HTML `href`

**Fichier :** `includes/header.php`  
**Ligne originale :**
```php
<a href="<?php header('Location: auth/logout.php'); ?>">
```

**Problème :**  
`header()` est une fonction PHP qui envoie des en-têtes HTTP au navigateur. Elle ne peut pas être utilisée à l'intérieur d'un attribut HTML. Ici, elle ne produisait aucune sortie dans le HTML, donc le lien de déconnexion n'avait pas de destination (`href=""`). De plus, appeler `header()` après que du HTML a déjà été envoyé génère une erreur PHP.

**Correction :**
```php
<a href="<?php echo BASE_URL; ?>auth/logout.php">
```

**Explication :**  
Un lien de navigation doit simplement pointer vers l'URL de destination avec `href`. La redirection HTTP via `header()` est réservée au code PHP pur, avant tout affichage HTML.

---

## Erreur 3 — Redirection vers `auth/login.php` avec chemin relatif incorrect

**Fichier :** `includes/header.php`  
**Ligne originale :**
```php
header('Location: auth/login.php');
```

**Problème :**  
Ce chemin relatif est résolu par rapport à l'URL de la page en cours. Quand `header.php` est inclus depuis `modules/facturation/nouvelle-facture.php`, la redirection pointait vers `modules/facturation/auth/login.php` qui n'existe pas, causant une erreur 404.

**Correction :**
```php
header('Location: ' . BASE_URL . 'auth/login.php');
```

**Explication :**  
En utilisant la constante `BASE_URL` (définie à `/facturation/`), le chemin devient absolu et fonctionne depuis n'importe quelle page de l'application.

---

## Erreur 4 — Redirection de timeout sans chemin absolu dans `auth/session.php`

**Fichier :** `auth/session.php`  
**Ligne originale :**
```php
header('Location: login.php?timeout=1');
```

**Problème :**  
Même problème que l'erreur 3. Ce chemin relatif fonctionnait uniquement si la page appelante se trouvait dans le même dossier `auth/`. Depuis n'importe quel autre dossier (`modules/`, `rapports/`, etc.), la redirection échouait avec une erreur 404.

**Correction :**
```php
header('Location: ' . BASE_URL . 'auth/login.php?timeout=1');
```

---

## Erreur 5 — Chemins `require_once` relatifs dans les fichiers `includes/`

**Fichiers concernés :**
- `includes/fonctions-produits.php`
- `includes/fonctions-factures.php`
- `includes/fonctions-auth.php`

**Lignes originales :**
```php
require_once '../config/config.php';
require_once 'fonctions-produits.php'; // dans fonctions-factures.php
```

**Problème :**  
PHP résout les chemins relatifs dans `require_once` par rapport au **répertoire de travail courant** (le dossier du fichier qui a lancé l'exécution), et non par rapport au fichier qui contient le `require_once`.

Exemple : quand `modules/facturation/nouvelle-facture.php` inclut `includes/fonctions-produits.php`, PHP cherche `../config/config.php` depuis `modules/facturation/`, ce qui donne `modules/config/config.php` — un chemin qui n'existe pas.

**Erreur obtenue :**
```
Warning: require_once(../config/config.php): Failed to open stream: No such file or directory
Fatal error: Uncaught Error: Failed opening required '../config/config.php'
```

**Correction :**
```php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/fonctions-produits.php'; // dans fonctions-factures.php
```

**Explication :**  
`__DIR__` pointe toujours vers `C:\xampp\htdocs\facturation\includes`, peu importe quel fichier a déclenché l'inclusion. Le chemin est donc toujours correct.

---

## Erreur 6 — Liens du menu avec chemins relatifs `../`

**Fichier :** `includes/header.php`  
**Lignes originales :**
```php
'url' => '../modules/facturation/nouvelle-facture.php'
'url' => '../modules/produits/enregistrer.php'
// etc.
```

**Problème :**  
Ces chemins relatifs sont interprétés par le navigateur par rapport à l'URL de la page actuelle. Depuis une page dans `modules/facturation/`, `../modules/...` fonctionnait. Mais depuis `rapports/` ou `modules/admin/`, les chemins pointaient vers de mauvaises destinations.

**Correction :**
```php
'url' => BASE_URL . 'modules/facturation/nouvelle-facture.php'
'url' => BASE_URL . 'modules/produits/enregistrer.php'
// etc.
```

---

## Erreur 7 — Lien CSS avec chemin relatif `../`

**Fichier :** `includes/header.php`  
**Ligne originale :**
```html
<link rel="stylesheet" href="../assets/css/style.css">
```

**Problème :**  
Même problème que l'erreur 6. Le chemin `../assets/` était relatif à la page appelante. Depuis certaines pages, le CSS ne se chargeait pas.

**Correction :**
```html
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
```

---

## Correction globale — Ajout de la constante `BASE_URL`

**Fichier :** `config/config.php`  
**Ajout :**
```php
define('BASE_URL', '/facturation/');
```

**Explication :**  
Cette constante centralise l'URL de base de l'application. Tous les liens et redirections l'utilisent désormais, ce qui rend le code indépendant de la profondeur du fichier dans l'arborescence. Si l'application est déplacée (ex: à la racine du serveur), il suffit de changer cette seule ligne.

---

## Résumé

| # | Fichier | Type d'erreur | Cause |
|---|---------|---------------|-------|
| 1 | `includes/header.php` | Chemin invalide `/../` | Chemin absolu mal formé |
| 2 | `includes/header.php` | `header()` dans un `href` | Mauvaise utilisation de la fonction |
| 3 | `includes/header.php` | Redirection relative | Chemin dépendant de la page appelante |
| 4 | `auth/session.php` | Redirection relative | Chemin dépendant de la page appelante |
| 5 | `includes/fonctions-*.php` | `require_once` relatif | PHP résout depuis le répertoire de travail, pas depuis `__DIR__` |
| 6 | `includes/header.php` | Liens menu relatifs | Chemins `../` invalides selon la profondeur |
| 7 | `includes/header.php` | Lien CSS relatif | Chemin `../` invalide selon la profondeur |
