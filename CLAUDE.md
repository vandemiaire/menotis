# Ménotis

Application web de prise de notes (style Google Keep), mono-utilisateur, en PHP procédural sans framework ni base de données. Installable en PWA (`manifest.json`). Version définie dans `const.inc.php` (`APP_VERSION`).

## Stack
- PHP pur (pas de Composer), extensions requises : `gettext`, `fileinfo`, `zip` (vérifiées par `firstlaunch.php`).
- Front : Bootstrap 5 local (`bootstrap/`), JS vanilla (`functions.js`, `events.js`, `theme.js`), police Nunito (`fonts/`).
- i18n : gettext, chaînes sources en anglais via `_("...")`, traduction dans `locale/fr_FR/LC_MESSAGES/messages.po` (recompiler le `.mo` après modification).
- Pas de build, pas de tests, pas de linter. Se lance sous n'importe quel serveur web PHP à la racine du dossier.

## Stockage — dossier `datas/` (ignoré par Git, ne jamais le commiter)
- `datas/config.php` : constantes `LOGIN`, `PASSWORD` (sha1), `LOCALE`, `COOKIE_NAME`, `COOKIE_VALUE`, `TIME_ZONE`, `TIME_FORMAT` + init locale/gettext. Créé par `firstlaunch.php` / `security.ajax.php` (action `new`) et modifié par `settings.ajax.php`. Son absence déclenche l'assistant de premier lancement.
- Une note = un fichier `datas/note<id>.json` ; l'UID de la note = nom du fichier sans `.json`.
  Champs : `title`, `content` (markdown simplifié), `sorting` (int), `bgcolor`, `bgimage` (fichier de `wallpapers/`), `pinned`, `archived`, `modified` (timestamp).
- Images uploadées : `datas/<md5>.png|jpg...`, référencées dans le contenu par `![alt](datas/xxx.png)`.
- `_dev/` (aussi ignoré) : notes perso du dev (`_notes.txt` : config php.ini), fichiers de travail.

## Architecture
- `index.php` : page unique. Inclut `const.inc.php`, `functions.inc.php`, `security.inc.php`, puis `datas/config.php` ; sinon `firstlaunch.php` ; si non authentifié `login.php`. Affiche épinglées / autres, ou archives avec `?archives`.
- `*.inc.php` : fragments inclus (modales Bootstrap `modal*.inc.php`, `toasts.inc.php`) et bibliothèques (`functions.inc.php`, `security.inc.php`, `const.inc.php`).
- `*.ajax.php` : endpoints appelés en POST par `postData()` (`functions.js`), dispatch via `$_POST["action"]` :
  - `notes.ajax.php` : pin, archive, gettitle, getcontent, save, infos, delete, background
  - `checkbox.ajax.php` : add, remove, check (cases `[ ]`/`[x]` dans le contenu, indexées)
  - `settings.ajax.php`, `security.ajax.php` (new/login/logout), `backup.ajax.php` (backup/restore en zip), `images.ajax.php`, `uploadimage.ajax.php`, `modalimages.ajax.php`, `palettes.ajax.php`, `theme.ajax.php`
- Chaque endpoint doit commencer par `include const/config/security` puis `if (!checkCookie()) ...`.
- `functions.inc.php` : `notesDatasLoad()` (charge tous les JSON dans `$notesdatas`, tri par `sorting`, compteurs en `$_SESSION`), `notesDataShow()` (rendu HTML des cartes), `markdownReallyBasic()` (rendu maison ligne par ligne, `\n` ou `\r\n` : `#`/`##`/`###` → h3/h4/h5, lignes `>` = code sans mise en forme) + `markdownInline()` (images, URLs cliquables et cases à cocher protégées par des marqueurs `\0n\0` avant `**gras**`/`*gras*`/`_italique_`, ce dernier jamais au milieu d'un mot).
  **Invariant** : le compteur `data-index` des cases doit compter chaque `[ ]`/`[x]`/`[X]` du texte brut dans l'ordre (y compris dans les lignes de code et les alt d'images), comme l'action `check` de `checkbox.ajax.php`.
- Thème clair/sombre : `theme.js` (côté client) synchronisé en `$_SESSION["theme"]` via `theme.ajax.php`.

## Authentification
Cookie statique : `COOKIE_NAME` = `COOKIE_VALUE` (configurés), renouvelé à chaque requête, durée `COOKIE_RETENTION` jours. Mot de passe comparé en sha1.

## Règles de sécurité (à respecter dans tout nouveau code)
- **CSRF** : chaque `*.ajax.php` commence par `include("csrf.inc.php");` (exige POST + en-tête `X-Menotis: 1`). Côté client, passer par `postData()` ; sinon ajouter `MENOTIS_HEADERS` (fetch) ou `xhr.setRequestHeader("X-Menotis", "1")`.
- **Chemins** : tout UID reçu du client passe par `isValidNoteUid()` (`^note[a-zA-Z0-9]{1,64}$`) avant de construire un chemin.
- **XSS** : tout texte utilisateur affiché en HTML passe par `escHtml()`. `markdownReallyBasic()` échappe tout en premier puis ajoute ses balises (le `>` de ligne de code y est donc `&gt;`). Le titre renvoyé en JSON (`save`/`infos`) est échappé car le client fait `innerHTML`. `bgcolor`/`bgimage` validés par `sanitizeBgColor()` / `sanitizeBgImage()` à l'écriture et au rendu.
- **Restauration** (`backup.ajax.php`) : ne jamais utiliser `extractTo()`. Le zip est lu depuis le fichier temporaire d'upload, chaque entrée est vérifiée (nom `note*.json` ou image `[a-zA-Z0-9_-].png|jpg`, contenu JSON/MIME réel, tailles max) avant toute écriture ; un seul fichier refusé = zip entier refusé.
- **Cookie** : `cookieOptions()` dans `security.inc.php` (path `/`, HttpOnly, SameSite=Strict, Secure si HTTPS).

## Points faibles connus restants
- sha1 non salé pour le mot de passe ; valeur de cookie fixe (point « 2 », reporté par l'utilisateur).
- `datas/` est servi directement par le serveur web : notes JSON, images et zips de sauvegarde (nom prévisible `menotis_backup_YYYYMMDD_HHMMSS.zip`) lisibles sans authentification.
- `palettes.ajax.php` : palette du thème clair inachevée (TODO).

## Conventions
- Commentaires de code majoritairement en anglais (quelques-uns en français) ; l'utilisateur échange en français.
- Indentation 4 espaces, HTML généré via heredoc `<<<HTML`.
