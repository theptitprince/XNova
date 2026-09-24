<div align="center">

# XNova — 0.9 Renaissance

**Le jeu de stratégie spatiale XNova, repris là où l'équipe d'origine s'était arrêtée.**

![Version](https://img.shields.io/badge/version-0.9%20Renaissance-2ea44f)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-10.6%2B-003545?logo=mariadb&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8%2B-4479A1?logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES5-F7DF1E?logo=javascript&logoColor=black)
![HTML5](https://img.shields.io/badge/HTML-templates-E34F26?logo=html5&logoColor=white)
![Licence](https://img.shields.io/badge/licence-AGPL--3.0--or--later-blue)
![Statut](https://img.shields.io/badge/statut-en%20d%C3%A9veloppement-orange)

</div>

---

> [!WARNING]
> **La version 0.9 n'est pas encore sécurisée.** Elle rend le jeu compatible avec PHP 8 mais conserve les failles
> du code de 2008 (injections SQL, mots de passe en md5…). **Ne l'ouvrez pas au public** avant la version 0.9a.
> Pour l'instant, utilisez-la uniquement en local ou pour le développement.

## Sommaire
- [Le jeu](#le-jeu)
- [Histoire du projet](#histoire-du-projet)
- [Pourquoi « Renaissance »](#pourquoi--renaissance-)
- [Feuille de route](#feuille-de-route)
- [Installation](#installation)
- [Organisation du code](#organisation-du-code)
- [Crédits](#crédits)
- [Licence](#licence)
- [Sources](#sources)

---

## Le jeu

XNova est un jeu de stratégie spatiale massivement multijoueur qui se joue dans un simple navigateur web.
Chaque joueur reçoit une planète à l'inscription. Il y développe une base (mines, centrales, usines, laboratoire),
recherche des technologies, construit des flottes et des défenses, colonise d'autres mondes, commerce,
s'allie avec d'autres joueurs… ou les attaque pour piller leurs ressources.

Son gameplay s'inspire ouvertement d'OGame (Gameforge), mais XNova est écrit avec **son propre code**, publié en logiciel libre.

## Histoire du projet

### UGamela, le point de départ (2006)
Vers 2006, un développeur argentin connu sous le pseudo **Perberos** publie **UGamela**, l'un des tout premiers clones
libres d'OGame, écrit en PHP. Le code reste ouvert jusqu'à la version 0.2-r13. Des communautés se forment
autour de lui, en Amérique latine, en Allemagne, en Pologne et en France.

### XNova, l'équipe française (fin 2007 – 2008)
Fin 2007 et début 2008, une équipe française reprend UGamela et en fait **XNova** :
- **Raito**, fondateur et programmeur ;
- **Chlorel**, chef programmeur, auteur de la grande majorité du code ;
- **e-Zobar**, designer et programmeur ;
- **Flousedid**, webmaster ;
- des contributeurs, dont **Bono**, **Tom1991**, **XxmangaxX**, **juju67** et **Prethorian**.

L'équipe ne se contente pas de rafistoler UGamela. Elle **reforge le noyau** version après version : de la 0.5 à la
0.7, puis la **0.8**, qui deviendra l'une des versions d'OGame-like les plus diffusées. Le site officiel est
**xnova.fr**, avec son forum. Au tout début, le projet est **accueilli par le forum Britania**, que la page
de crédits du jeu remercie encore aujourd'hui « pour avoir accueilli XNova à ses débuts ».

### L'éclatement (2008 – 2011)
Le succès d'XNova donne naissance à de nombreux dérivés :
- **XG Proyect** (2008), communauté hispanophone qui corrige les bugs et rapproche le jeu d'OGame, puis **2Moons** ;
- **XNova Legacies** (2009.1 à 2009.4), reprise par une nouvelle équipe, qui se lance ensuite dans une
  réécriture complète sous le nom de **Wootook**… jamais terminée ;
- une multitude de « repacks » (Redesigned, Revolution, SuperNova…).

Puis les sites ferment les uns après les autres. xnova.fr disparaît, les forums aussi, et avec eux une grande partie
du travail des communautés.

### theptitprince et le forum Britania
**theptitprince**, qui porte aujourd'hui cette reprise, a participé à l'aventure dès 2006 au sein du
**forum Britania**, jusqu'à sa fermeture. Les contributions de cette époque ont disparu avec le forum.
XNova Renaissance est aussi une façon de rendre à ce projet ce qui a été perdu.

## Pourquoi « Renaissance »

Les dérivés d'XNova ont presque tous suivi l'un de ces deux chemins : l'empilement de modifications jusqu'à
l'illisible, ou la réécriture complète… jamais achevée. **XNova Renaissance fait le choix inverse :**

- **on repart de la dernière version de l'équipe d'origine, XNova 0.8e**, et non d'un dérivé ;
- **on ne réécrit pas** : la structure du code, son style et le gameplay restent ceux de Chlorel et de son équipe ;
- **on modernise et on sécurise pas à pas**. Chaque version reste jouable, et la numérotation reprend la logique
  d'origine (0.8e → 0.9 → 0.9a → 0.9b…) ;
- **on garde la trace de tous les auteurs** : chaque fichier conserve sa mention d'origine.

« Renaissance » n'est pas un nouveau jeu : c'est le **nom de la version** qui suit la 0.8e. Le jeu, lui, s'appelle toujours XNova.

## Feuille de route

| Version | Contenu | État |
|---|---|---|
| **0.9** | Compatibilité PHP 8.4 / MariaDB, en-têtes et crédits, corrections de bugs d'origine | ✅ Terminée |
| **0.9a** | Sécurité critique (injections SQL, mots de passe `password_hash`, cookies…), bug de production d'énergie, installeur « Mise à jour » | ⏳ Prochaine |
| **0.9b** | Protection des formulaires (XSS, CSRF), formulaire de contact vers l'administration | 🔜 |
| **0.9c** | Nettoyage : UTF-8, conventions de nommage, retouches visuelles | 🔜 |
| **1.0** | Tout propre, sécurisé et testé en jouant | 🎯 |

Au-delà de la 1.0, on pourra envisager l'abandon des *frames* au profit d'une interface moderne.

## Installation

### Prérequis
- **PHP 8.4** (8.1 minimum), avec les extensions `mysqli`, `gd` et `mbstring` ;
- **MariaDB 10.6+** ou **MySQL 8+** ;
- un serveur web (Apache, Nginx), ou le serveur intégré de PHP pour le développement.

### Étapes
1. Copiez les fichiers du dépôt à la racine de votre site.
2. Créez une base de données vide et un utilisateur MySQL qui y a tous les droits.
3. Rendez le fichier `config.php` accessible en écriture au serveur web.
4. Ouvrez le site dans un navigateur : vous êtes redirigé vers l'installeur (`install/`).
5. Renseignez la connexion à la base, puis créez le compte administrateur.
6. **Supprimez ou protégez le dossier `install/`** une fois l'installation terminée.

Pour développer en local :
```bash
php -S 127.0.0.1:8080
```

### Mise à jour et transfert
Les modes « Mise à jour » et « Transfère » de l'installeur ne prennent en charge **que les bases XNova 0.9 et plus récentes**.
Les anciennes versions (UGamela, XNova 0.8, Legacies) ne sont pas migrées.

## Organisation du code

| Dossier | Contenu |
|---|---|
| racine | pages du jeu côté joueur (`overview.php`, `buildings.php`, `fleet.php`…) |
| `admin/` | panneau d'administration |
| `includes/` | fonctions du jeu, formules (`vars.php`), moteur de combat |
| `db/` | accès à la base de données (`doquery`) |
| `language/` | textes du jeu (fr, de, es, it) |
| `templates/` | gabarits HTML (`.tpl`) |
| `skins/`, `images/`, `css/`, `scripts/` | graphismes, styles et JavaScript |
| `install/` | installeur |

Les branches `ref/legacies-2009.x` conservent, pour référence, les versions XNova Legacies. Elles ne sont jamais fusionnées.
Le tag `v0.8e` marque le code d'origine, tel qu'il a été récupéré.

## Crédits

**XNova — équipe d'origine (2007-2008)**
- Raito : fondateur, programmeur
- Chlorel : chef programmeur
- e-Zobar : designer, programmeur
- Flousedid : webmaster
- Bono, Tom1991, XxmangaxX, juju67, Prethorian : contributions

**Bases et code tiers**
- UGamela, par Perberos : base d'XNova
- Moteur de combat (`includes/ataki.php`) par jacekowski
- Code issu de la communauté UGamela allemande (MoF) et de prethOgame (Aleksandar Spasojevic / KGsystem)
- Forum Britania, pour avoir accueilli XNova à ses débuts

**XNova 0.9 Renaissance**
- theptitprince : reprise, modernisation et maintenance (depuis 2026)

## Licence

XNova Renaissance est distribué sous licence **GNU Affero General Public License v3.0 ou ultérieure**
(AGPL-3.0-or-later). Le texte complet est dans le fichier [`LICENSE`](LICENSE) et les conditions d'attribution dans [`NOTICE`](NOTICE).

En résumé :
- vous pouvez utiliser, étudier, modifier et redistribuer le jeu librement ;
- si vous faites tourner une version modifiée sur un serveur accessible à d'autres, vous devez **publier votre code source** ;
- conformément à la section 7(b) de la licence, vous devez **conserver les mentions d'auteur** : les en-têtes des fichiers
  et la page de crédits du jeu, qui citent l'équipe XNova d'origine et theptitprince (XNova Renaissance).

**Exception : le moteur de combat** `includes/ataki.php` (jacekowski) reste sous sa licence d'origine,
**Creative Commons BY-NC-SA 2.5**, qui **interdit tout usage commercial**. Pour un usage commercial du jeu,
il faudrait remplacer ce fichier.

XNova 0.8e était distribué sous GNU GPL sans numéro de version précis. La section 9 de la GPL v2 permet alors de choisir
n'importe quelle version publiée par la Free Software Foundation. Le texte de la GPL v2 fourni à l'origine est conservé
dans [`LICENCE.txt`](LICENCE.txt).

## Sources

- [Clones d'OGame et histoire d'UGamela / XNova — XG Proyect](https://forum.xgproyect.org/forum/archives/archives-aa/xg-proyect-2-x/-2-x-foro-de-soporte/320-historia-de-xnova)
- [Xnova et OGame : légalité et évolution des clones — Kommunauty](https://www.kommunauty.fr/actualite/web/xnova-legal-opendominion-stellaris/)
- [Fil « [ogame clone] Xnova » — SMPFR](https://smpfr.info/viewtopic.php?t=8247)
- [MMO basé sur le projet XNova — OpenClassrooms](https://openclassrooms.com/forum/sujet/sitejeu-mmo-base-sur-le-projet-xnova-28835)
- [Code source XNova 0.8 et Legacies 2009.x — GitHub](https://github.com/xnova-legacies)
- [CVE-2008-6022](https://cyberstrike.io/cve/CVE-2008-6022/) : faille d'inclusion de fichier de la 0.8 SP1, **absente de cette version**
  (le code vulnérable n'existe plus dans la 0.8e et PHP 8 ne connaît plus `register_globals`)
