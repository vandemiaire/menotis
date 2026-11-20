# Ménotis

Application web de prise de notes, simple et auto-hébergée, pour un seul utilisateur.
Sans base de données : chaque note est un fichier JSON.

## Fonctionnalités

- Notes avec titre et contenu en markdown simplifié
- Épingler, archiver, rechercher
- Cases à cocher cliquables
- Couleur ou fond d'écran par note
- Ajout d'images (PNG, JPEG)
- Thème clair / sombre
- Sauvegarde et restauration (fichier ZIP)
- Installable comme application sur mobile (PWA)
- Interface en français et en anglais

## Prérequis

- Un serveur web avec PHP 7.4 ou plus récent
- Extensions PHP activées dans `php.ini` : `gettext`, `fileinfo`, `zip`
- Le dossier `datas/` accessible en écriture par le serveur web

## Installation

1. Copier les fichiers dans un dossier du serveur web.
2. Créer le dossier `datas/` s'il n'existe pas, et donner les droits d'écriture au serveur web.
3. Ouvrir le site dans un navigateur : l'assistant de premier lancement vérifie la configuration
   puis demande l'identifiant, le mot de passe, la langue et le fuseau horaire.

Pour la taille maximale des images envoyées, ajuster `upload_max_filesize` et `post_max_size` dans `php.ini` (10 Mo maximum par image).

## Syntaxe des notes

| Écrire | Résultat |
|---|---|
| `# Titre`, `## Titre`, `### Titre` | Titres de trois tailles |
| `**gras**` ou `*gras*` | **gras** |
| `_italique_` | _italique_ |
| `[ ]` / `[x]` | Case à cocher (vide / cochée) |
| `> texte` | Ligne de code |
| `![légende](datas/image.png)` | Image |
| `https://...` | Lien cliquable |

## Données

Tout est stocké dans `datas/` (exclu de Git) :

- `config.php` : identifiant, mot de passe et réglages
- `note*.json` : une note par fichier
- les images envoyées

Pour repartir de zéro, supprimer `datas/config.php` : l'assistant de premier lancement réapparaît.

> **Attention** : le dossier `datas/` est servi directement par le serveur web. Sur un serveur accessible depuis Internet, son contenu peut être lu sans mot de passe par quelqu'un qui connaît ou devine le nom d'un fichier.
