<?php
$lang['version']     = 'Version';
$lang['description_label'] = 'Description';
$lang['changelog']   = array(


'0.9g Renaissance' => 'Nettoyage (theptitprince)
- FIX : Mot de passe oubli&eacute; : lien de confirmation avant tout changement (conna&icirc;tre l\'adresse d\'un joueur suffisait), injection SQL corrig&eacute;e, m&ecirc;me message que l\'adresse existe ou non
- FIX : Mots de passe de 8 caract&egrave;res au moins : inscription, Options et compte administrateur de l\'installeur
- FIX : Installeur verrouill&eacute; une fois le jeu install&eacute; (config.php pouvait &ecirc;tre r&eacute;&eacute;crit), code injectable dans son adresse
- FIX : Phalange contr&ocirc;l&eacute;e par le serveur (port&eacute;e, pr&eacute;sence, cible) : une adresse forg&eacute;e scannait tout l\'univers
- FIX : Chat de l\'administration : du code post&eacute; par un joueur s\'ex&eacute;cutait chez l\'administrateur
- FIX : Alliance : plus d\'enr&ocirc;lement forc&eacute;, d\'exclusion ni de changement de rang hors de son alliance ; rang perdu en partant ; administration r&eacute;serv&eacute;e au droit pr&eacute;vu
- FIX : Alliance : dissolution confirm&eacute;e (un simple lien ou une image suffisait), membres lib&eacute;r&eacute;s et pr&eacute;venus ; images des textes en http(s) seulement
- FIX : Mode vacances : plus d\'envoi de flotte ni de missiles, refus&eacute; tant qu\'une flotte vole, pas de sortie avant 48 h, production et revenus r&eacute;tablis au retour
- FIX : Annonces, marchand et notes d&eacute;sactiv&eacute;s par l\'administrateur : ferm&eacute;s aussi par leur adresse
- FIX : Missiles : interception (stock de la cible vid&eacute;, puis n&eacute;gatif), temps de vol, tir sur une plan&egrave;te avec lune, port&eacute;e dans les deux sens, nombre n&eacute;gatif refus&eacute;, rapports traduits
- FIX : Attaque group&eacute;e : la mission supprimait la flotte &agrave; l\'arriv&eacute;e (jamais programm&eacute;e) ; retir&eacute;e en attendant la 0.9h
- FIX : Officier Amiral : bonus de combat enfin appliqu&eacute;, &agrave; l\'attaquant comme au d&eacute;fenseur
- FIX : Espionnage (rapport de destruction invers&eacute;, vraie probabilit&eacute;) et exp&eacute;dition (trou noir, soutes, trouvaille vide)
- FIX : Lune : d&eacute;truire la base lunaire augmentait son niveau ; base lunaire et terraformeur indestructibles, comme OGame
- FIX : Bannissements : dur&eacute;e respect&eacute;e (lev&eacute;e automatique), pseudos longs, un seul bannissement par joueur (robot anti-multi), page du banni traduite
- FIX : Recyclage rapide sans flotte vide (emplacements, carburant) ; mission choisie dans la galaxie pr&eacute;s&eacute;lectionn&eacute;e
- FIX : Page Flotte : heures d&eacute;cal&eacute;es, flotte rappel&eacute;e affich&eacute;e comme retour, erreurs JavaScript ; rapports et messages de retour
- FIX : Petites annonces : page blanche apr&egrave;s publication, colonne cristal vide, suppression de ses annonces
- FIX : Vue de l\'empire : &eacute;nergie restante et cases (terraformeur) justes, intitul&eacute;s corrig&eacute;s
- FIX : Fiches de la centrale &agrave; fusion et de la phalange, porte de saut, abandon de colonie : erreurs et fautes
- NEW : Statistiques recalculables par t&acirc;che planifi&eacute;e ; classement des alliances enfin calcul&eacute;
- NEW : Alliance : cession &agrave; un membre &laquo; Main droite &raquo; (le bouton ne faisait rien) ; alliance transmise au plus ancien membre si le fondateur est supprim&eacute;
- FIX : Alliance : candidatures (alliance ferm&eacute;e, r&eacute;ponses au candidat), liste des membres (rangs, tris, jours d\'inactivit&eacute;), page des droits, textes allemands
- MOD : Vue g&eacute;n&eacute;rale : colonies &agrave; droite de la plan&egrave;te, deux par ligne, comme OGame classique ; annonces de niveaux, rang et fin de construction corrig&eacute;s
- FIX : Galaxie : couleur et tag d\'alliance, port&eacute;e de phalange, bas de page coup&eacute; sur petit &eacute;cran, pied de page, lien &laquo; Espaces infinis &raquo; ; vue g&eacute;n&eacute;rale d\'une lune
- FIX : Petits et grands &eacute;crans : menu de gauche d&eacute;filant, fond d\'&eacute;cran couvrant toute la fen&ecirc;tre
- FIX : Installeur : bandeaux harmonis&eacute;s, erreurs dans son cadre, &laquo; Suivant &raquo; de la mise &agrave; jour, conseil &laquo; CHMOD 777 &raquo; remplac&eacute;, fautes
- FIX : Messagerie : confirmation d\'envoi et erreurs affich&eacute;es, cat&eacute;gorie administration, &eacute;motic&ocirc;nes (:cool:, :perdu:...), couleurs du BBCode, barres obliques conserv&eacute;es
- FIX : Liste d\'amis : demandes re&ccedil;ues de nouveau visibles, ami supprim&eacute; par un double clic, alliance du bon joueur, demande &agrave; soi-m&ecirc;me refus&eacute;e
- FIX : Recherche de joueurs (alliance d&eacute;cal&eacute;e, rang absent) ; marchand : co&ucirc;t recalcul&eacute; apr&egrave;s un collage
- FIX : Inscription : pseudos contenant &laquo; script &raquo; ou &laquo; http &raquo; inutilisables, noms de plan&egrave;te intacts ; e-mails valid&eacute;s (.paris, .app...)
- FIX : D&eacute;claration de multi-compte : textes, confirmation, au moins un joueur exig&eacute;
- FIX : Administration : message &agrave; tous (jamais envoy&eacute;, non &eacute;chapp&eacute;), configuration lisible, vue g&eacute;n&eacute;rale sans d&eacute;filement, listes (messages, lunes, plan&egrave;tes), file du chantier, titres
- MOD : Administration : liste &laquo; multi-comptes &raquo; toujours vide et page &laquo; supprimer un joueur &raquo; inachev&eacute;e retir&eacute;es ; PhpInfo r&eacute;serv&eacute; &agrave; l\'administrateur
- NEW : Langue au choix dans les Options ; allemand, espagnol et italien complets (1 800 textes ajout&eacute;s ou corrig&eacute;s)
- FIX : Textes &eacute;crits en dur rendus traduisibles (flotte, annonces, messages, menu, horloge, cr&eacute;dits), Pilori traduit, nombreuses fautes corrig&eacute;es
- MOD : Base de donn&eacute;es en utf8mb4 : un emoji dans un nom ou un message faisait &eacute;chouer la requ&ecirc;te
- FIX : Aucun avertissement PHP sur les pages du jeu ; plus de page blanche sur une adresse incompl&egrave;te
- MOD : Nettoyage : pages abandonn&eacute;es et 21 mod&egrave;les inutilis&eacute;s supprim&eacute;s, script du portail OGame (mot de passe en cookie) retir&eacute;, chemins UGamela, convention de nommage',

'0.9f Renaissance' => 'Formulaires et contact (theptitprince)
- FIX : Protection CSRF : un site ext&eacute;rieur ne peut plus faire agir un joueur connect&eacute; &agrave; son insu (formulaires et liens d\'action)
- FIX : Protection XSS : noms de plan&egrave;te et d\'alliance, messages, textes d\'alliance, notes, recherches... ne peuvent plus contenir de code
- FIX : Site et image d\'alliance, avatar, skin : adresses http(s) uniquement ; liens du BBCode s&eacute;curis&eacute;s
- NEW : Formulaire de contact : plus aucune adresse e-mail affich&eacute;e, messages lus dans l\'administration (lu / non lu, suppression)
- NEW : Anti-spam du formulaire de contact (champ pi&egrave;ge, d&eacute;lai minimal, 3 messages par heure)
- NEW : Mise &agrave; jour possible depuis la XNova 0.8e d\'origine ; textes des anciennes bases nettoy&eacute;s
- FIX : Chat : les caract&egrave;res + et &amp; cassaient l\'envoi ; lien automatique vers l\'ancien site xnova.fr retir&eacute;
- FIX : Pages du jeu et de l\'administration inaccessibles sans &ecirc;tre connect&eacute; (seules les pages publiques restent ouvertes)
- FIX : Triches de flotte : vitesse, dur&eacute;es de stationnement et d\'exp&eacute;dition, limite d\'exp&eacute;ditions ne sont plus lues dans le formulaire
- FIX : Page orpheline qui effa&ccedil;ait les missiles de n\'importe quelle plan&egrave;te supprim&eacute;e ; annonces et phalange prot&eacute;g&eacute;es
- FIX : Liens vers xnova.fr (domaine repris par des tiers) et vers un site tiers recevant l\'IP des joueurs retir&eacute;s
- FIX : Mail d\'inscription sans mot de passe en clair ; erreur de connexion sur la page de connexion, sans r&eacute;v&eacute;ler si le pseudo existe
- FIX : Le r&eacute;glage &laquo; jeu ferm&eacute; &raquo; ne fonctionnait pas (serveurs existants remis sur &laquo; ouvert &raquo;)
- FIX : Heures au fuseau du serveur partout, format fran&ccedil;ais sur 24 h, horloge de la vue g&eacute;n&eacute;rale &agrave; l\'heure du serveur
- FIX : Lunes : temp&eacute;ratures invers&eacute;es (lunes existantes r&eacute;par&eacute;es), nom choisi repris, lune d&eacute;j&agrave; pr&eacute;sente v&eacute;rifi&eacute;e
- FIX : Recycleurs et vaisseaux de colonisation peuvent transporter ; message de retour de recyclage
- FIX : Phalange et rapports de combat affich&eacute;s dans le jeu (plus de popups bloqu&eacute;es), rapport encadr&eacute; et centr&eacute;
- NEW : Vue g&eacute;n&eacute;rale &agrave; dimensions fixes (bandeau &laquo; Autres plan&egrave;tes &raquo;), bandeau du haut sans d&eacute;filement horizontal
- NEW : Technologies [i] : arbre complet des pr&eacute;requis (jamais termin&eacute; dans l\'original)
- NEW : Administration : ajout de flotte r&eacute;par&eacute; et au menu, protection des d&eacute;butants r&eacute;glable, redirections r&eacute;par&eacute;es
- FIX : Redirections apr&egrave;s un message (notes, options...) bloqu&eacute;es par les navigateurs actuels
- FIX : Galaxie : compteur de flottes et de recycleurs ; statistiques : page courante pr&eacute;s&eacute;lectionn&eacute;e
- FIX : Caract&egrave;res perdus retap&eacute;s, nombreux textes corrig&eacute;s, mail de bienvenue italien encore en polonais traduit',

'0.9e Renaissance' => 'S&eacute;curit&eacute; (theptitprince)
- FIX : Injections SQL dans les pages du jeu et de l\'administration (mot de passe oubli&eacute;, cookie, alliance, messages...)
- FIX : Suppression des extract($_GET) (dont la banni&egrave;re publique, qui permettait d\'&eacute;craser la configuration)
- FIX : Un mod&eacute;rateur pouvait se promouvoir administrateur ou changer le mot de passe de n\'importe qui
- FIX : Quantit&eacute;s n&eacute;gatives refus&eacute;es (chantier, d&eacute;fenses, porte de saut, envoi de flotte), missiles tir&eacute;s depuis sa propre plan&egrave;te uniquement
- FIX : Donn&eacute;es de flotte et cookies du calculateur : plus de cr&eacute;ation d\'objets PHP (unserialize)
- MOD : Mots de passe s&eacute;curis&eacute;s (password_hash), anciens mots de passe convertis &agrave; la connexion
- MOD : Cookie de connexion sign&eacute; et prot&eacute;g&eacute; (HttpOnly, SameSite)
- NEW : Installeur : mode Mise &agrave; jour (&agrave; partir de la 0.9d), mode Transf&egrave;re revu, config.php &eacute;crit de fa&ccedil;on s&ucirc;re
- FIX : Production des mines au prorata de l\'&eacute;nergie disponible, production naturelle compt&eacute;e une seule fois
- FIX : 32 textes qui s\'affichaient vides, pseudo modifiable sans contr&ocirc;le, options qui effa&ccedil;aient les couleurs
- FIX : Statistiques des alliances, d&eacute;bannissement automatique (requ&ecirc;te erron&eacute;e)
- MOD : Tout le code en UTF-8 (74 fichiers convertis) et fins de ligne unifi&eacute;es : textes espagnols, allemands, italiens et \'Erreur n&deg;\' enfin lisibles
- FIX : Rapport de combat et calculateur d&eacute;claraient un encodage ISO ; nom des raccourcis de flotte ; pourcentage de production sans centrale (page Ressources)',

'0.9d Renaissance' => 'Passage &agrave; PHP 8 (theptitprince)
- NEW : XNova 0.9d Renaissance, suite directe de XNova 0.8e (apr&egrave;s les versions communautaires 0.9a &agrave; 0.9c)
- NEW : Nom de version affich&eacute; &agrave; c&ocirc;t&eacute; du num&eacute;ro (0.9d Renaissance)
- NEW : Cr&eacute;dits de la reprise ajout&eacute;s (les cr&eacute;dits d\'origine sont conserv&eacute;s)
- MOD : Compatible PHP 8.4 et MariaDB / MySQL r&eacute;cents (mysqli, fonctions supprim&eacute;es remplac&eacute;es)
- MOD : Seule la derni&egrave;re version est affich&eacute;e en vert dans le changelog
- MOD : Liste d\'amis dans le cadre principal, Notes et Chat dans une fen&ecirc;tre s&eacute;par&eacute;e
- FIX : Liens Marchand et Annonces qui s\'ouvraient hors du cadre, lien Annonces mort, lien Notes cass&eacute;
- FIX : Bouton de d&eacute;connexion des options (mauvais nom de cookie)
- FIX : Robot anti-multicompte (nom de table cod&eacute; en dur)
- FIX : Nom des ressources pill&eacute;es absent du rapport de combat
- FIX : Evolution du classement dans les statistiques',

'0.8e' => '- ADD : Fonction SecureArray() pour les variables POST et GET (Bono)
- ADD : Les administrateurs choisissent desormais le fond de la baniere... (Bono)
- ADD : Mode vacances + Production a 0 + Interdiction de construire(Prethorian)
',

'0.8d' => '- ADD : Les administrateurs voient d&eacute;sormais quelle page est consult&eacute;e par quel joueur (Bono)
- ADD : Bot antimulticompte, personnalisation et activation/d&eacute;sactivation a volont&eacute;...  (Bono)
- ADD : Politique de customisation du serveur entam&eacute;e...  (Bono)
- ADD : Possibilit&eacute; d\'activer/personnaliser/customiser un lien personnalis&eacute; (Bono)
- ADD : Possibilit&eacute; d\'afficher/desactiver des liens dans le menu (Bono)
- FIX : Lien vers les alliances corrig&eacute;
- NEW: Destruction des lunes (juju67)
- NEW: Stationnement chez un alli&eacute; (juju67)
- FIX: Lien vers la galaxie du joueur dans la fonction de recherche',



'0.8c' => 'Modules et corrections (e-Zobar)
- NEW: Fonction copyright &eacute;tendus
- NEW: G&eacute;n&eacute;rateur de banni&egrave;re-profil (signatures pour forum) dans \'Vue g&eacute;n&eacute;rale\' (d&eacute;sactivable)
- ADD: D&eacute;claration des multi-comptes
- FIX: Nombreuses erreurs visuelles: admin chat, r&egrave;gles
- FIX: Variable root_path sur toutes les pages
- FIX: S&eacute;curit&eacute; panneau d\'administration
- FIX: Illustrations officiers manquantes
- ADD: Message d\'accueil &agrave; l\'inscription (Tom1991)
- ADD: Affichage points raids (Tom1991)',

'0.8b' => 'Correction de bugs (Chlorel)
- ADD: Fonction de remise &agrave; z&eacute;ro du joueur qui triche
- FIX: Liste des plan&egrave;tes tri&eacute;es dans la vue empire
- FIX: Liste des plan&egrave;tes tri&eacute;es dans la vue g&eacute;n&eacute;rale aussi
- FIX: Mise &agrave; de toutes les plan&egrave;tes au passage par la vue g&eacute;n&eacute;rale et la vue empire',

'0.8a' => 'Correction de bugs (Chlorel)
- FIX: message.php ne fait plus d\'erreurs SQL quand y pas de message
- FIX: Correction page records pour pouvoir prendre en compte ou pas les admins
- NEW: phalange version recod&eacute;e ... a tester sous toutes les coutures
- FIX: Plus de possibilit&eacute; d\'espionner sans sondes
- MOD: Mise en forme des chiffres dans les rapports de combat (avec des .)
- MOD: Modification du template de login pour qu\'il passe par display avec 1 seul <body>
- FIX: Suppression d\'une cause possible d\'erreurs MySQL
- FIX: Extraction des dernieres chaines de la vue generale
- FIX: Surprise pour les cheater au marchand !
- FIX: Fonction DeleSelectedUser efface aussi les planetes maintenant
- ADD: Page des r&egrave;gles (XxmangaxX)',

'0.8' => 'Infos (Chlorel)
- FIX: Skin sur nouvel installeur
- DIV: Travaux esthetique sur l\'ensemble des fichiers
- FIX: Oublie de modification d\'appel sur quelques functions nouvellement modifiees',

'0.7m' => 'Correction de bugs (Chlorel)
- ADD: Interface d\'activation de protection des plan&egrave;tes
- FIX: Les lunes vont a nouveau au bon joueur et pas a "un" joueur quand elles sont crees depuis l\'administration
- FIX: Overview Evenements de flottes (les personnelles pour le moment) utilisent a present le css (default.css)
- MOD: Adaption de diverses fonctions a l\'utilisation du css
- FIX: Chat interne (divers ajustements) (e-Zobar)',

'0.7k' => 'Correction de bugs (Chlorel)
- FIX: Retour de flotte en transport
- ADD: Protection des planetes d\'administration
- MOD: Liste des joueurs dans la section admin liens sur les ent&ecirc;tes pour tri
- MOD: Page g&eacute;n&eacute;rale section admin avec liens sur les ent&ecirc;tes pour tri
- FIX: Lors de l\'utilisation d\'un skin autre que celui d\'XNova, il s\'applique aussi en section admin
- FIX: Ajout du lune dans le panneau d\'administration (e-Zobar)
- ADD: Mode transf&egrave;re dans l\'installateur (e-Zobar)',

'0.7j' => 'Correction de bugs (Chlorel)
- FIX: On peut a nouveau retirer une construction de la queue de fabrication
- FIX: On peut a nouveau envoyer une flotte en transport entre deux planetes
- FIX: La liste des raccourcis dans la selection de la cible fonctionne a nouveau
- FIX: On ne peut plus detruire un batiment que l\'on ne possede pas
- ADD: Tout beau tout nouveau installeur (e-Zobar)
- FIX: Rarcellage de hieroglyphes (e-Zobar)',

'0.7i' => 'Correction de bugs (Chlorel)
- Suppression cheat +1
- Ajustement des dur&eacute;e de vols / consommation des flottes entre le code PHP et le code JAVA
- Tri des colonies par le joueur dans options
- Preparation du multiskin dans options
- Divers amenagements dans le code pour les Administrateurs (Liste de messages, Liste de Joueurs)
- Travaux sur le skin (e-Zobar)
- Travaux sur l\'installeur (e-Zobar)',

'0.7h' => 'Correction de bugs (Chlorel)
- Interface Officier refaite
- Ajout blocage des "refresh meta"
- Ajustement de divers Bugs
- Correction de divers textes (flousedid)
- Correction de defauts visuels (e-Zobar)',

'0.7g' => 'Correction diverses (Chlorel)
- Modification de l\'ordre du traitement de la liste de construction de batiments
- Mise en conformit&eacute; du code pour une seule commande "echo"
- Quelques modules de r&eacute;&eacute;crits
- Correction bug de d&eacute;doublement de flotte
- Mise &agrave; jour dynamique de la taille des silos, production des mines et de l\'&eacute;nergie
- Divers adaptations dans la section admin (e-Zobar)
- Modification lourde du style XNova (e-Zobar)',

'0.7f' => 'Informations et porte de saut: (Chlorel)
- Nouvelle page d\'information completement repens&eacute;e
- Nouvelle interface porte de saut int&eacute,gr&eacute;e a la page d\'information
- Nouvelle gestion de l\'affichage des rapid fire dans la page d\'information
- Multitude de correction faites par e-Zobar',

'0.7e' => 'Partout et nulle part : (Chlorel)
- Nouvelle page registration (mise au standard)
- Nouvelle page records (mise en conformit&eacute; avec le site)
- Modif kernel (y en a pas mal mais pas possible de toutes les expliquer l&agrave; et de toutes maniere pas
  grand monde ne serait capable de les comprendre',

'0.7d' => 'Partie admin : (e-Zobar)
- menage dans pas mal de modules
- alignement du menu au style de fonctionnement du site
- traduction complete de ce qui n\'etait pas encore en francais',

'0.7c' => 'Statistiques : (Chlorel)
- Suppression des appels base de donn&eacute;es de l\ancien systeme de Statistiques
- Bug Impossibilit&eacute; de fabriquer des defenses ou des elements de flotte n\'utilisant pas de metal
- Bug Comme certains petits rigolos s\'amusent a lancer des quantit&eacute;es enormes de vaisseau dans
  une meme ligne de la queue de construction vaisseau, nous en sommes arriv&eacute;s a limiter le nombre
  d\'element fabriquable par ligne donc maximum 1000 vaisseaux ou defenses a la fois !!
- Bug erreur lors de la selection planete par la combo
- Mise a jour de l\'installeur',

'0.7b' => 'Statistiques : (Chlorel)
- Reecriture de la page de Statistique (appell&eacute;e par l\'utilisateur)
- Les stat alliance s\'affichent !
- Ecriture du generateur admin des stats
- Separation des stats de l\'enregistrement utilisateur (les stats on leur propre base de donn&eacute;es)',

'0.7a' => 'Divers : (Chlorel)
- Bug Technologies (la duree de recherche apparait a nouveau quand on revient dans le laboratoire
- Bug Missiles (mis a plat de la port&eacute;e des missiles interplanetaires, et mise en place de la limite de fabrication par rapport a la taille du silo)
- Bug Port&eacute;e des phalange corrig&eacute; (on ne peut plus phalanger toute la galaxie)
- Bug Correction de la conssomation de deuterium quand on passe par le menu galaxie',

'0.7' => 'Building :
- Reecriture de la page
- Modularisation
- Correction bugs de statistiques
- Debugage de la liste de construction batiments
- Diverses retouches (Chlorel)
- Divers debug (au fil de l\'eau) (e-Zobar)
- Ajout de fonction sur la vue principale (Tom1991)',

'0.6b' => 'Divers :
- Correction & Ajouts de fonctions pour les officiers (Tom1991)
- Menage dans les scripts java inclus (Chlorel)
- Correction divers bug (Chlorel)
- Mise en place version 0.5 de la liste de construction batiments (Chlorel)',

'0.6a' => 'Graphisme :
- Ajout Skin XNova (e-Zobar)
- Correction d\'effets nefastes (e-Zobar)
- Ajout de bugs involotaires (Chlorel)',

'0.6' => 'Galaxy (suite): (by Chlorel)
- Modification et reecriture de flottenajax.php
- Modification des routine javascript et ajax pour permettre les modification dynamiques de la galaxie
- Corrections bug dans certains liens des popups
- Definition nouveau protocole d\'appel, dorenavant meme sur une lune, la galaxie s\'affiche a partir de la bonne position
- Correction des appels de recyclage
- Ajout module "Officier" (by Tom1991)',

'0.5' => 'Galaxy: (by Chlorel)
- Decoupage ancien module
- Modification systeme de generation des popup dans la vue de la galaxie
- Modularisation de la generation de page',

'0.4' => 'Overview: (by Chlorel)
- Mise en forme ancien module
- Gestion de l\'affichage des flotte personnelle 100%
- Modification affichage des lunes quand presentes
- Correction bug renommer les lunes (pour qu\'elles soient effectivement renomm&eacute;es)',

'0.3' => 'Gestion de flottes: (by Chlorel)
- Modification / modularisation / documentation de la boucle de gestion des vols 100%
- Modification Mission d\'espionnage 100%
- Modification Mission de Colonisation 100%
- Modification Mission Transport 100%
- Modification Mission Stationnement 100%
- Modification Mission Recyclage 100%',

'0.2' => 'Corrections
- Ajouts de la version 0.5 des Exploration (by Tom1991)
- Modification de la boucle de controle des flottes 10% (by Chlorel)',

'0.1' => 'Merge des version flotte:
- Mise en place de la strat&eacute;gie de developpement
- Mise en place de nouvelles pages de gestion de flotte',

'0.0' => 'Version de depart:
- Base du repack a Tom1991',
);

?>
