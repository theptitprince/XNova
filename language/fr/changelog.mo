<?php
$lang['version']     = 'Version';
$lang['description_label'] = 'Description';
$lang['changelog']   = array(


'0.9g Renaissance' => 'Nettoyage (theptitprince)
- FIX : D&eacute;claration de multi-compte : titre, textes traduits, message de confirmation du jeu ; au moins un joueur exig&eacute;
- FIX : Administration : liste &laquo; multi-comptes &raquo; toujours vide supprim&eacute;e (second syst&egrave;me jamais fonctionnel), page &laquo; supprimer un joueur &raquo; inachev&eacute;e retir&eacute;e
- FIX : Robot anti-multi : un seul bannissement par joueur (au lieu d\'un par compte partageant l\'adresse IP)
- FIX : Bannissements : pseudo, auteur et e-mail tronqu&eacute;s &agrave; 11 et 20 caract&egrave;res (le d&eacute;bannissement ratait les pseudos longs)
- FIX : Options, cadre du jeu, destruction de lune : chemin du jeu lu dans une ancienne variable UGamela
- FIX : Phalange : port&eacute;e, pr&eacute;sence d\'une phalange et cible v&eacute;rifi&eacute;es par le serveur (une adresse forg&eacute;e scannait tout l\'univers)
- FIX : Mot de passe oubli&eacute; r&eacute;par&eacute; et s&eacute;curis&eacute; (injection SQL, mot de passe fort, m&ecirc;me message que l\'adresse existe ou non)
- FIX : Inscription : les pseudos contenant &laquo; script &raquo; ou &laquo; http &raquo; donnaient un compte inutilisable ; noms de plan&egrave;te intacts
- FIX : Recyclage rapide de la vue g&eacute;n&eacute;rale : plus de flotte vide, emplacements de flotte et carburant v&eacute;rifi&eacute;s
- FIX : Officier Amiral : son bonus de combat s\'applique enfin (niveau de l\'attaquant et du d&eacute;fenseur)
- FIX : Envoi de flotte : la mission choisie dans la galaxie est de nouveau pr&eacute;s&eacute;lectionn&eacute;e
- FIX : Galaxie : couleur des joueurs sans alliance, tag de son alliance, port&eacute;e de phalange ; vue g&eacute;n&eacute;rale d\'une lune
- FIX : Vue g&eacute;n&eacute;rale : l\'annonce de niveau de raideur n\'efface plus celle de mineur ; rang du joueur dans le menu
- MOD : Pages abandonn&eacute;es supprim&eacute;es (calculatrice UGamela, doublon de fiche d\'alliance) ; PhpInfo r&eacute;serv&eacute; &agrave; l\'administrateur
- FIX : Avertissements PHP : toutes les pages et une partie compl&egrave;te sans aucun message
- MOD : Vue g&eacute;n&eacute;rale : colonies de nouveau &agrave; droite de la plan&egrave;te, deux par ligne, comme OGame classique (largeurs fig&eacute;es)
- FIX : Espionnage : le rapport annon&ccedil;ait la flotte d&eacute;truite quand elle survivait (messages invers&eacute;s), vraie probabilit&eacute; affich&eacute;e
- FIX : Exp&eacute;dition : trou noir total jamais atteint, capacit&eacute; des soutes mal compt&eacute;e, trouvaille vide annonc&eacute;e, tirage affich&eacute;
- FIX : Page Flotte : heures d\'envoi et d\'arriv&eacute;e d&eacute;cal&eacute;es d\'une colonne, flotte rappel&eacute;e affich&eacute;e comme retour
- FIX : Rapports : dur&eacute;e du calcul lisible, message de retour de flotte, fautes des rapports d\'exp&eacute;dition
- FIX : Petites annonces : page blanche apr&egrave;s publication (PHP 8), colonne cristal vide, suppression de ses annonces (jamais &eacute;crite)
- FIX : Recherche de joueurs : alliance affich&eacute;e sur la ligne suivante, rang jamais affich&eacute;
- FIX : Erreurs JavaScript de la page Flotte et de la connexion ; script orphelin du portail OGame (mot de passe en cookie) supprim&eacute;
- FIX : Textes : demandes d\'ami, discussion, taille des notes en caract&egrave;res, titre des Options
- FIX : Alliance : page des droits en erreur fatale pour une alliance neuve (PHP 8), droits des rangs, fautes
- FIX : Alliance : un chef ne peut plus enr&ocirc;ler de force un joueur ni exclure le membre d\'une autre alliance, ni changer le rang d\'un joueur ext&eacute;rieur
- FIX : Alliance : un membre qui part ou est exclu perd son rang (il gardait ses droits dans sa nouvelle alliance) ; administration r&eacute;serv&eacute;e au droit pr&eacute;vu
- FIX : Alliance : dissolution confirm&eacute;e et prot&eacute;g&eacute;e (un lien ou une image suffisait), membres lib&eacute;r&eacute;s et pr&eacute;venus ; images des textes limit&eacute;es aux adresses http(s)
- NEW : Alliance : cession &agrave; un membre ayant le droit &laquo; Main droite &raquo; (le bouton ne faisait rien) ; un joueur supprim&eacute; transmet son alliance au plus ancien membre
- FIX : Alliance : candidature impossible quand l\'alliance est ferm&eacute;e, candidat pr&eacute;venu en fran&ccedil;ais (accept&eacute;, refus&eacute;, exclu), onglet du mod&egrave;le de candidature
- FIX : Alliance : rangs d&eacute;cal&eacute;s dans la liste des membres, tri par rang et par points, jours d\'inactivit&eacute; (c\'&eacute;taient des heures), textes allemands traduits
- NEW : Langue au choix dans les Options (fran&ccedil;ais, allemand, espagnol, italien) ; allemand, espagnol et italien complets (1 800 textes ajout&eacute;s ou corrig&eacute;s, restes de fran&ccedil;ais et de polonais traduits)
- FIX : Textes &eacute;crits en dur rendus traduisibles : raccourcis de flotte, petites annonces, formulaire de message, menu, horloge, cr&eacute;dits, pluriel de la galaxie
- FIX : &Eacute;motic&ocirc;nes des messages cass&eacute;es depuis la 0.9f, et les mots &laquo; cool &raquo;, &laquo; perdu &raquo;... remplac&eacute;s par une image au milieu des phrases : codes :cool:, :perdu:... ; couleur du BBCode limit&eacute;e aux vraies couleurs
- FIX : Annonces, marchand et notes d&eacute;sactiv&eacute;s par l\'administrateur : plus accessibles par leur adresse (seul le lien disparaissait)
- FIX : Mot de passe : 8 caract&egrave;res au moins, &agrave; l\'inscription comme au changement (la page Options l\'annon&ccedil;ait sans le v&eacute;rifier)
- FIX : Fiche de la centrale &agrave; fusion (production mal lue), statistiques recalculables par t&acirc;che planifi&eacute;e, classement des alliances enfin calcul&eacute;
- FIX : Administration : configuration du serveur lisible (vitesses tronqu&eacute;es, revenus limit&eacute;s &agrave; 2 chiffres, cases &agrave; cocher), vue g&eacute;n&eacute;rale sans d&eacute;filement horizontal
- FIX : E-mails valid&eacute;s par PHP (.paris, .app... &eacute;taient refus&eacute;s) ; Pilori traduit, &laquo; 1 joueur banni &raquo;
- MOD : Base de donn&eacute;es en utf8mb4 (colonnes latin1 : un emoji dans un nom, un message ou un rang faisait &eacute;chouer la requ&ecirc;te) ; conversion faite par la mise &agrave; jour, donn&eacute;es v&eacute;rifi&eacute;es identiques
- FIX : Flottes : la mission &laquo; Attaque group&eacute;e &raquo; (propos&eacute;e aux destructeurs vers une lune) supprimait la flotte &agrave; l\'arriv&eacute;e, vaisseaux compris : retir&eacute;e (jamais programm&eacute;e), bouton &laquo; Associer &raquo; retir&eacute;
- FIX : Bannissements : la dur&eacute;e &eacute;tait ignor&eacute;e (sanction d&eacute;finitive jusqu\'au d&eacute;bannissement manuel) : lev&eacute;e automatique &agrave; l\'&eacute;ch&eacute;ance, page du joueur banni traduite avec la date de fin
- FIX : Administration : message &agrave; tous (sujet ou texte vide = page blanche), listes des lunes et des plan&egrave;tes, d&eacute;bannissement traduits
- FIX : Missiles : une interception retirait les missiles interplan&eacute;taires de la cible (intercepteurs jamais consomm&eacute;s, puis stock n&eacute;gatif) ; temps de vol n&eacute;gatif vers un syst&egrave;me de num&eacute;ro plus &eacute;lev&eacute; (impact imm&eacute;diat) ; noms de d&eacute;fenses faux dans le rapport ; textes traduits
- FIX : Installeur verrouill&eacute; une fois le jeu install&eacute; : on pouvait r&eacute;&eacute;crire config.php et brancher le jeu sur une autre base (seule la mise &agrave; jour reste ouverte)
- FIX : Mode vacances : plus d\'envoi de flotte ni de missiles (on attaquait en restant intouchable), refus&eacute; tant qu\'une flotte vole, production remise &agrave; 100 % au retour
- FIX : Mode vacances : un formulaire forg&eacute; n\'en fait plus sortir avant les 48 heures ; revenus de base du cristal et du deut&eacute;rium
- FIX : Lune : d&eacute;truire la base lunaire augmentait son niveau et les cases (destruction de la base lunaire et du terraformeur refus&eacute;e, comme OGame)
- FIX : Fiche de la phalange : avertissements PHP (calcul de production inutile) ; accord &laquo; 1 case libre &raquo; ; messages laboratoire / chantier requis
- FIX : Administration : le message &agrave; tous les joueurs ne partait jamais ; texte d&eacute;sormais &eacute;chapp&eacute; (injection de code)
- FIX : Messagerie : cat&eacute;gorie des messages de l\'administration absente, couleur illisible des rapports d\'exp&eacute;dition
- FIX : Missiles : tir sans effet ni rapport sur une plan&egrave;te ayant une lune, textes du jeu effac&eacute;s au moment de l\'impact
- FIX : Missiles : port&eacute;e contr&ocirc;l&eacute;e dans les deux sens, nombre n&eacute;gatif refus&eacute; (il cr&eacute;ait des missiles), rapport en fran&ccedil;ais
- FIX : Porte de saut et bouton d\'abandon de colonie : avertissements et fautes
- MOD : Convention de nommage : toutes les cl&eacute;s de langue et balises des templates en minuscules (a-z, 0-9, _), contr&ocirc;le ajout&eacute; au v&eacute;rificateur
- FIX : Administration : liste des messages (le plus r&eacute;cent n\'apparaissait jamais), titre d\'erreur des Options
- FIX : Galaxie : bas de page coup&eacute; sans barre de d&eacute;filement sur un &eacute;cran peu haut ; pied de page juste au singulier comme au pluriel (&laquo; Recycleurs disponibles : 0 &raquo;) ; lien &laquo; Espaces infinis &raquo; r&eacute;par&eacute;
- FIX : Menu de gauche : R&egrave;gles, Contact, Options et D&eacute;connexion hors d\'atteinte sur un &eacute;cran peu haut (menu d&eacute;filant)
- FIX : Fond d\'&eacute;cran : sur un grand &eacute;cran, l\'image couvre toute la fen&ecirc;tre au lieu de s\'arr&ecirc;ter net sur un aplat bleu
- FIX : Liste d\'amis : demandes re&ccedil;ues invisibles (liens jamais affich&eacute;s depuis le passage &agrave; PHP 8), ami tout juste accept&eacute; supprim&eacute; par un double clic, alliance du mauvais joueur, lien retour inop&eacute;rant ; demande &agrave; soi-m&ecirc;me ou &agrave; un compte inexistant refus&eacute;e
- FIX : Adresses incompl&egrave;tes : page blanche des petites annonces, avertissements de la fiche d\'information ; titres de la bo&icirc;te de r&eacute;ception et de l\'administration (erreurs, remise &agrave; z&eacute;ro)
- MOD : 21 mod&egrave;les de pages inutilis&eacute;s supprim&eacute;s, balises cass&eacute;es de l\'arbre technologique et de la porte de saut r&eacute;par&eacute;es
- FIX : Installeur : bandeaux du haut harmonis&eacute;s (m&ecirc;me hauteur, &eacute;tape &agrave; droite), menu et contenu align&eacute;s ; verrou et erreurs affich&eacute;s dans le cadre de l\'installeur (formulaire conserv&eacute; sous l\'erreur) ; &laquo; Suivant &raquo; de la mise &agrave; jour ramenait &agrave; l\'&eacute;tape 1 ; param&egrave;tre recopi&eacute; tel quel dans la page (injection de code)
- FIX : Installeur : mot de passe administrateur d\'au moins 8 caract&egrave;res et adresse e-mail valide, comme &agrave; l\'inscription ; conseil &laquo; CHMOD 777 &raquo; remplac&eacute; (le droit d\'&eacute;criture suffit) ; fautes du fran&ccedil;ais, titre de la page traduit
- FIX : Administration : nettoyage de la file du chantier spatial (avertissements PHP sur les files vides)
- FIX : Vue de l&rsquo;empire : &eacute;nergie restante fausse (consommation ajout&eacute;e au lieu d&rsquo;&ecirc;tre retir&eacute;e), cases maximum sans le terraformeur, titre de page vide, fautes des intitul&eacute;s',

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
