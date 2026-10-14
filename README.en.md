<div align="center">

# XNova — 0.9k Renaissance

**The XNova space strategy game, picked up where the original team left off.**

![Version](https://img.shields.io/badge/version-0.9k%20Renaissance-2ea44f)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-10.6%2B-003545?logo=mariadb&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8%2B-4479A1?logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES5-F7DF1E?logo=javascript&logoColor=black)
![Platform](https://img.shields.io/badge/platform-web-0078D6)
![License](https://img.shields.io/badge/license-AGPL--3.0--or--later-blue)
![Status](https://img.shields.io/badge/status-in%20development-orange)

</div>

[Français](README.md) · **English**

> The game's interface is in French by default (German, Spanish and Italian translations are included, but no English one); its labels are quoted below as they appear on screen.

---

> [!NOTE]
> **Secured and slimmed down, but still in development ahead of 1.0.** The known vulnerabilities of the 2008 code
> have been fixed since 0.9f, and 0.9k added a full security audit of the code: SQL injection, cheating through a
> forged form or simultaneous clicks, access to other players' data, form tokens, an installer restricted to the
> administrator. Every vulnerability found has been fixed and is covered by automated tests; pages stay fast on a
> large universe (thousands of players and of fleets in flight).
> Running a public server is feasible: follow the [Production deployment](#production-deployment) section, and keep in
> mind that the game is still a development version. Testing was done on MariaDB; MySQL 8 is supported but not tested.

XNova Renaissance takes up **XNova 0.8e**, the last version by the original French team, and makes it run on a
present-day server (PHP 8, MariaDB or MySQL) without rewriting it: the code structure and the gameplay remain the
original ones, while the code is modernized and secured step by step. It is meant for those who want to host their
own XNova universe, locally or on an online server; players, for their part, only need a web browser.

Current version: 0.9k Renaissance — the release history is in the
[repository's release notes](https://github.com/theptitprince/XNova/releases). In the game, the version number
shown in the left-hand menu opens the change log (`changelog.php`).

## Contents
- [1. The game](#1-the-game)
- [2. Project history](#2-project-history)
- [3. Why "Renaissance"](#3-why-renaissance)
- [4. Roadmap](#4-roadmap)
- [5. Installation](#5-installation)
- [6. Code layout](#6-code-layout)
- [7. Credits](#7-credits)
- [8. License](#8-license)
- [9. Sources](#9-sources)

---

## 1. The game

XNova is a massively multiplayer space strategy game played in nothing more than a web browser.
Each player is given a planet on signing up. There they build up a base (mines, power plants, factories, laboratory),
research technologies, build fleets and defenses, colonize other worlds, trade,
ally with other players… or attack them to plunder their resources.

Its gameplay is openly inspired by OGame (Gameforge), but XNova is written with **its own code**, released as free software.

---

## 2. Project history

### UGamela, the starting point (2006)
On **June 26, 2006**, an Argentine student known by the handle **Perberos** launched **UGamela**, his graduation project:
one of the very first free OGame clones, written in PHP. His server had around **22,000 active players in 2007**.
The code remained open up to version 0.2, then a German community took it over up to 0.5.
Other communities formed in Latin America, Poland and France.

### XNova, the French team (late 2007 – 2008)
In late 2007 and early 2008, a French team took over UGamela and turned it into **XNova**:
- **Raito**, founder and programmer;
- **Chlorel**, lead programmer, author of the vast majority of the code;
- **e-Zobar**, designer and programmer;
- **Flousedid**, webmaster;
- contributors, including **Bono**, **Tom1991**, **XxmangaxX**, **juju67** and **Prethorian**.

The team did not just patch UGamela up. It **reforged the core** version after version: from 0.5 to
0.7, then **0.8**, which would become one of the most widely distributed OGame-like versions. The official site was
**xnova.fr**, with its forum. From its beginnings until spring 2008, the project was also **hosted by the Britania forum** (britania.ws),
which the game's credits page thanks "pour avoir accueilli XNova à ses débuts" (for hosting XNova in its early days). Britania then kept an
XNova section until it closed, in 2016.

### The split (2008 – 2014)
In October 2008, Chlorel left the scene. The project then split into several branches, which moved forward in parallel:
- **XNova** (2008 – 2011): the game as it was played on xnova.fr;
- **XNova NG** ("NextGen", 2009 – 2011): a complete rewrite announced by the French team;
- **XNova Legacies** (2009 – 2014): taken over by a new team (versions 2009.1 to 2009.4);
- **Wootook** (from 2011): the complete rewrite that grew out of Legacies, never finished.

Elsewhere, the code spread: **XG Proyect** (2008), a Spanish-speaking community that fixed the bugs and brought the game closer to OGame,
then **2Moons**, and a multitude of "repacks" (Redesigned, Revolution, SuperNova…).

Then the servers and the forums closed one after another, and with them a large part of the communities' work
disappeared.

### theptitprince and the Britania forum
**theptitprince**, who is behind this revival today, took part in the XNova adventure on the **Britania forum**
from 2008 onwards. Web archives keep a few traces of it:
- in March 2008, a first message thanking the XNova team and Chlorel for version 0.8;
- **the "XNova 0.9b" community distribution** (February 2009), which he put together from 0.8 by
  integrating many tutorials and fixes into it. It was offered on his site alongside 0.9a and 0.9c, then
  republished by Britania in 2009;
- a "Captcha à l'inscription" (captcha at sign-up) tutorial for XNova;
- exchanges with Bono, in 2010, about the "XNova Studio" tool.

This is why the Renaissance numbering picks up at **0.9d**: it extends these community
versions instead of overwriting them.

The rest of the contributions from that era disappeared with the forum. XNova Renaissance is also a way of giving
back to this project what was lost.

---

## 3. Why "Renaissance"

Almost all XNova derivatives followed one of two paths: piling up modifications until the code became
unreadable, or the complete rewrite… never finished. **XNova Renaissance makes the opposite choice:**

- **we start again from the last version by the original team, XNova 0.8e**, and not from a derivative. The first commit
  of this repository (tag `v0.8e`) is identical, file for file, to the archive
  [xnova-legacies/xnova-0.8](https://github.com/xnova-legacies/xnova-0.8/tree/af4e32c45bb99e8c47f5317f143bacf041c988b2)
  put online in 2016;
- **we do not rewrite**: the code structure, its style and the gameplay remain those of Chlorel and his team;
- **we modernize and secure step by step**. Every version stays playable, and the numbering follows the original
  logic (0.8e → 0.9d → 0.9e → 0.9f…).
  The numbering starts at **0.9d**, following on from the community versions "0.9a" to "0.9c" of 2008-2009;
- **we keep track of all the authors**: every file keeps its original notice.

"Renaissance" is not a new game: it is the **name of the version** that follows 0.8e. The game itself is still called XNova.

---

## 4. Roadmap

| Version | Content | Status |
|---|---|---|
| **0.9d** | PHP 8.4 / MariaDB compatibility, headers and credits, fixes for original bugs | ✅ Completed |
| **0.9e** | Critical security (SQL injection, `password_hash` passwords, cookies, privileges), energy production bug, "Mise à jour" (update) installer mode | ✅ Completed |
| **0.9f** | Form protection (XSS, CSRF), contact form to the administration, update from the original 0.8e, full playthrough tested up to moon creation (original bugs fixed) | ✅ Completed |
| **0.9g** | Remaining bugs and PHP warnings, forgotten password through a confirmation link, colony display; complete translations (German, Spanish, Italian), utf8mb4 database, installer locked and harmonized, cleanup (naming conventions, texts, pages); full playthrough retested (moon, missiles, alliance, administration) | ✅ Completed |
| **0.9h** | Features left unfinished in the original: "Supprimer un joueur" (delete a player) and the multi-account list, account deletion requested by the player, officers with no effect, fleet arrival times, merchant, reporting; live resource counter, "Max" buttons, captcha; beginner protection completed, combat debris fixed; full playthrough retested on a fresh installation | ✅ Completed |
| **0.9i** | Group attack: planned in 0.8e but never programmed (group, invitations, synchronized fleets); group defense by stationed allied fleets; new combat engine with several participants, same results as the original (proven over 40,000 combats), the whole game under AGPL; Protection des vaisseaux (armor technology) finally taken into account; full playthrough retested in the browser | ✅ Completed |
| **0.9j** | The last officers, Bunker, Raideur (raider) and Empereur (emperor), finally useful, along with their units: Protecteur planétaire (planetary protector), SuperNova, Destructeur planétaire (planet destroyer), with colony destruction; Dépôt de ravitaillement (supply depot) finished as in OGame; complete expeditions (pirates, aliens, delayed or early returns); OGame values for the Grand bouclier (large shield dome), the petit transporteur (small cargo ship) and the satellite solaire (solar satellite); Bouclier (shielding) and Protection (armor) technologies put back in their proper place in combat | ✅ Completed |
| **0.9k** | Full security audit (injections, cheating, simultaneous requests, access, installer restricted to the administrator); performance: database indexes, fleets processed only when needed, duplicate queries removed, statistics in a few seconds, game results proven identical; chat that can be disabled, server information and automatic statistics in the administration | ✅ Completed |
| **1.0** | Everything clean, secure and tested by playing | 🎯 |

---

<a id="installation"></a>

## 5. Installation

### Requirements
- **PHP 8.4** (8.1 minimum), with the `mysqli`, `gd` and `mbstring` extensions;
- **MariaDB 10.6+** or **MySQL 8+**;
- a web server (Apache, Nginx), or PHP's built-in server for development.

### Steps
1. Copy the repository files to the root of your site.
2. Create an empty database and a MySQL user with all privileges on it.
3. Make the `config.php` file writable by the web server.
4. Open the site in a browser: you are redirected to the installer (`install/`).
5. Enter the database connection details, then create the administrator account, in the same browser: only whoever
   wrote `config.php` can create this account, and none can be created any more once the installation is finished.
6. **Delete or protect the `install/` folder** once the installation is finished. The installer locks itself
   as soon as `config.php` is filled in: since 0.9k, updating and transferring are reserved for a game
   administrator, who enters their username and password in the installer.

For local development:
```bash
php -S 127.0.0.1:8080
```

### Automatic statistics
The player and alliance rankings are recalculated from the administration (the "Statistiques" (statistics) page,
opened by the "Actualiser points" (refresh points) link in the menu), or automatically by a scheduled task that runs
`php tools/stats.php` from the game root:
- Linux (cron, every hour): `0 * * * * cd /chemin/du/jeu && php tools/stats.php >> /var/log/xnova-stats.log`
  (log outside the site root);
- Windows (Task Scheduler): program `php.exe`, argument `tools\stats.php`, start-in folder
  = the game root.

Without a scheduled task, the game can also recalculate the statistics by itself, when a player comes by, once the
last calculation is more than N hours old (0.9k: "Statistiques" page of the administration, a setting reserved for
operators and administrators, disabled by default). Only one calculation runs at a time. The player who triggers it waits for it
to finish: on a large universe, prefer the scheduled task.

### Update and transfer
The installer's "Mise à jour" and "Transfert" (transfer) modes support:
- **the original XNova 0.8e** (same database structure as 0.9d): md5 passwords are converted at each player's first
  login, and the texts stored back then (names, messages, alliance texts…) are cleaned up;
- **all versions of XNova Renaissance** (0.9d and later).

Other databases (UGamela, community versions 0.9a to 0.9c, Legacies and other derivatives) are not migrated.
Always back up your database before an update. If the accented characters of a very old database
display incorrectly (encoding wrongly declared at the time), report it: a repair update is still possible.

Since 0.9k, on an installed game (`config.php` filled in), these two modes are reserved for a **game
administrator**: open `install/` and enter the username and password of your administrator account in the installer
form. There is no need to log in to the game: after copying the new files, only open its pages once the database
has been updated (the new version's code is not made for the old database). As on the login page, 5 failed
attempts for the same username from the same address lock that username for 15 minutes. Transferring to a new
server, whose `config.php` is still empty, stays open like the installation: if you copied the whole site, old
`config.php` included, empty that file first. The update also replaces the weak secret word written by the 0.8e
and 0.9d installers (it signs the login cookies) with a random secret word: everyone, administrator included, logs
in again once.

### Hosting: OPcache
OPcache, shipped with PHP, keeps already compiled PHP files in memory: without it, every page recompiles about a
hundred files (game functions, texts, `config.php`). The game works with or without it; it is recommended in
production. Recommended settings in `php.ini`:
```ini
zend_extension=opcache
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.validate_timestamps=1
opcache.revalidate_freq=2
```
With `validate_timestamps=1`, a modified file (new version of the game) is read again at most `revalidate_freq`
seconds later. With `validate_timestamps=0` (slightly faster), clear the cache, by restarting PHP, after each copy
of new files; the installer itself invalidates the cached copy of the `config.php` it has just written.
OPcache only speeds up compilation: the number of SQL queries and the fleet processing do not change.
Its status (memory used, hit rate) is shown in the administration, on the "Informations du serveur" (server
information) page.

### Production deployment
- **Publish only the game**: no `.git/`, no working notes, no backups (`*.sql`, `*.bak`, `config.php~`…)
  in the site root. A site installed with `git clone` must deny access to `.git/` (Apache:
  `RedirectMatch 404 /\.git`, Nginx: `location ~ /\.git { deny all; }`).
- **`config.php`** (database connection and secret word): writable by the web server only during the
  installation, then read-only (for example `chmod 440`, readable by the web server's group). Make it
  writable for the duration of an update from 0.8e or 0.9d (renewal of the secret word). When updating the
  files, never replace it with the repository's empty `config.php`: the game would go back to the installer.
- **`install/` folder**: delete it, or restrict access to it to your IP address, once the installation is finished;
  put it back only for the duration of an update. The administration's "Informations du serveur" page flags
  a writable `config.php` and an `install/` folder that is still present.
- **Adresse du jeu** (game address), in the administration, "Configuration" page, "Paramètre du jeu" (game
  settings) section: the links in e-mails (forgotten password, welcome) are built with it, never with the address
  sent by the visitor. It is filled in at installation and at update; change it after a change of domain or the
  switch to HTTPS.
- **Database**: a MySQL user limited to the game's database, without the `FILE` privilege.
- **PHP**: `display_errors = Off` (errors go to the server log) and `expose_php = Off`; HTTPS
  recommended (the login cookie is then restricted to encrypted pages). The `tools/` folder is only used from the
  command line (it refuses visits).

---

## 6. Code layout

| Folder | Contents |
|---|---|
| root | player-side game pages (`overview.php`, `buildings.php`, `fleet.php`…) |
| `admin/` | administration panel |
| `includes/` | game functions, formulas (`vars.php`), combat engine |
| `db/` | database access (`doquery`) |
| `language/` | game texts (fr, de, es, it) |
| `templates/` | HTML templates (`.tpl`) |
| `skins/`, `images/`, `css/`, `scripts/` | graphics, styles and JavaScript |
| `install/` | installer |
| `tools/` | command-line tools (`stats.php`, `check_lang.php`) |

The `v0.8e` tag marks the original code of the XNova team, as it was recovered; each Renaissance
version then has its own tag (`v0.9d`, `v0.9e`…).

---

## 7. Credits

**XNova — original team (2007-2008)**
- Raito: founder, programmer
- Chlorel: lead programmer
- e-Zobar: designer, programmer
- Flousedid: webmaster
- Bono, Tom1991, XxmangaxX, juju67, Prethorian: contributions

**Foundations and third-party code**
- UGamela, by Perberos: the base of XNova
- Original combat engine (`includes/ataki.php`, used up to 0.9h) by jacekowski
- Code from the German UGamela community (MoF) and from prethOgame (Aleksandar Spasojevic / KGsystem)
- Britania forum, for hosting XNova in its early days

**XNova Renaissance** (since 0.9d)
- theptitprince: revival, modernization and maintenance (since 2026)

---

## 8. License

XNova Renaissance is **free software**, distributed under the **GNU Affero General Public License v3.0 or
later** (AGPL-3.0-or-later). The full text is in the [`LICENSE`](LICENSE) file and at
<https://www.gnu.org/licenses/agpl-3.0.html>; the attribution terms are in [`NOTICE`](NOTICE).

Copyright © 2026 ETDEL for XNova Renaissance. Original work: XNova Team (Raito, Chlorel, e-Zobar, Flousedid and
contributors), 2007-2008, based on UGamela (Perberos).

In short:
- you may use, study, modify and redistribute the game freely;
- if you run a modified version on a server that others can access, you must **publish your source code**;
- in accordance with section 7(b) of the license, you must **preserve the author notices**: the file headers
  and the game's credits page, which name the original XNova team and theptitprince (XNova Renaissance);
- the game is provided **without any warranty**.

Up to 0.9h, the combat engine `includes/ataki.php` (jacekowski) was under the **Creative Commons
BY-NC-SA 2.5** license, which forbids any commercial use. Since 0.9i, it has been replaced by an engine written for
Renaissance (`includes/functions/CombatEngine.php`), which gives exactly the same results: the whole game is
now under the AGPL.

XNova 0.8e was distributed under the GNU GPL without a specific version number. Section 9 of the GPL v2 then allows
you to choose any version ever published by the Free Software Foundation. The GPL v2 text originally supplied is kept
in [`LICENCE.txt`](LICENCE.txt).

---

## 9. Sources

- [Ugamela — perberos.me](https://perberos.me/ugamela/about.php) · [Ugamela — Emilio Márquez's blog (2007)](https://emiliomarquez.com/2007/07/14/ugamela/)
- [XNova News (de+fr), October 2008 — UGamela Blog](http://ugamela-blog.pheelgood.net/2008/10/01/xnova-news-defr-tratsch/)
- Britania forum archives, XNova section (Wayback Machine): [the XNova section in 2008](https://web.archive.org/web/20080505201053/http://www.britania.ws:80/board/archive/index.php/forum-222.html),
  ["Captcha à l'inscription" tutorial](https://web.archive.org/web/20091031185340/http://britania.ws:80/forum/archive/index.php/thread-68.html),
  [XNova 0.9b distribution](https://web.archive.org/web/20091031151313/http://britania.ws:80/forum/Thread-Fichier-XNova-0-9b--71),
  [the theptitprince.fr site in 2009](https://web.archive.org/web/20090330225905/http://www.theptitprince.fr:80/xnova/)
- Page "XNova — le jeu auquel vous avez joué" ("XNova — the game you played", xnova.fr, accessed in September 2026): the project's four names and their dates
- [OGame clones and the history of UGamela / XNova — XG Proyect](https://forum.xgproyect.org/forum/archives/archives-aa/xg-proyect-2-x/-2-x-foro-de-soporte/320-historia-de-xnova)
- [Xnova and OGame: legality and evolution of the clones — Kommunauty](https://www.kommunauty.fr/actualite/web/xnova-legal-opendominion-stellaris/)
- [Thread "[ogame clone] Xnova" — SMPFR](https://smpfr.info/viewtopic.php?t=8247)
- [MMO based on the XNova project — OpenClassrooms](https://openclassrooms.com/forum/sujet/sitejeu-mmo-base-sur-le-projet-xnova-28835)
- [XNova 0.8 and Legacies 2009.x source code — GitHub](https://github.com/xnova-legacies)
- [CVE-2008-6022](https://cyberstrike.io/cve/CVE-2008-6022/): file inclusion vulnerability in 0.8 SP1, **not present in this version**
  (the vulnerable code no longer exists in 0.8e, and PHP 8 no longer has `register_globals`)

---

*XNova Renaissance — ETDEL 2026 — AGPL-3.0-or-later*
