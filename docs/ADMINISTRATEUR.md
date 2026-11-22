# Code personnel sur grille — Manuel administrateur

Version applicative : 1.0.0. Identifiant de l’application : `twofactor_xygrid`.
Identifiant du fournisseur : `xygrid`. Commande spécifique : `xygrid:manage`.

## 1. Périmètre et décision de déploiement

Le plugin propose aux utilisateurs volontaires une vérification supplémentaire après le mot de passe.
Il ne possède ni option d’enrôlement forcé ni commande pour activer un compte à sa place.
Si d’autres fournisseurs sont disponibles, l’utilisateur choisit dans le parcours natif de Nextcloud.
L’application ne modifie pas la politique globale de l’instance.

Le secret permanent est un nombre de six chiffres, zéros initiaux compris.
Il ne change pas à chaque connexion ; seule la permutation change.
Mot de passe et code mémorisé ne constituent pas deux facteurs indépendants.
Ne déployez pas cette alternative pour des comptes dont la politique exige un facteur de possession.
Une grille n’est pas une protection contre un navigateur compromis, l’hameçonnage en temps réel,
une caméra qui filme l’écran et les clics, ou un serveur compromis.

## 2. Prérequis et préparation

- Manifeste ciblant Nextcloud 33 et 34 ; tester le correctif exact, le backend utilisateur,
  le moteur de base et le thème réellement utilisés. Ne pas forcer l’installation sur une autre majeure.
- Utiliser HTTPS, interdire le cache partagé sur la connexion et les paramètres personnels.
- En cluster : mêmes données applicatives, même secret d’instance, sessions cohérentes et horloges synchronisées.
- Vérifier les sauvegardes de la base, de la configuration (notamment `secret`) et des sources.
- Prévoir un administrateur de secours, une méthode alternative déjà testée et une procédure de vérification
  d’identité avant déverrouillage ou révocation.
- Python 3.8+ n’est requis que pour construire. Le serveur utilise le PHP de son installation Nextcloud.
- Aucun service distant ni paquet JavaScript externe n’est nécessaire. JavaScript est requis côté navigateur.
- Paquet local non signé et non audité. Faire revoir le code avant utilisation sur des comptes réels.

## 3. Construction et contrôles hors serveur

Dans un dossier vide, placer uniquement `generer_grille.py`, puis lancer :

    python3 generer_grille.py
    php twofactor_xygrid/tests/codec.php
    find twofactor_xygrid -name '*.php' -exec php -l {} \;
    tar -tzf twofactor_xygrid-1.0.0.tar.gz

Le générateur refuse d’écraser ses sorties. Le test du codec n’est pas un test Nextcloud.
Le contrôle PHP doit être fait avec une version de PHP compatible avec la cible.
Un contrôle optionnel du JavaScript est `node --check twofactor_xygrid/js/grid.js`.

## 4. Installation en recette

Exemple pour une installation classique sous `/var/www/nextcloud`, avec `custom_apps`
comme chemin autorisé et `www-data` comme compte d’exécution. Adapter ces valeurs ;
pour un conteneur, exécuter OCC à l’intérieur du conteneur applicatif.

    tar -xzf twofactor_xygrid-1.0.0.tar.gz -C /var/www/nextcloud/custom_apps
    cd /var/www/nextcloud
    sudo -u www-data php occ status
    sudo -u www-data php occ app:enable twofactor_xygrid
    sudo -u www-data php occ help xygrid:manage

Vérifier que `custom_apps/twofactor_xygrid/appinfo/info.xml` existe. Appliquer les permissions
de lecture et d’exploitation habituelles de l’instance, jamais chmod 777. L’activation lance
la migration. Ne pas créer les tables à la main. Installer les mêmes fichiers sur tous les nœuds.
L’activation du logiciel ne doit enrôler aucun utilisateur.

## 5. Commandes OCC

Dans les exemples suivants, `occ` désigne `sudo -u www-data php occ` depuis la racine de Nextcloud.
`alice` est l’UID interne, pas nécessairement le nom affiché ni l’adresse électronique.

| Commande | Effet |
|---|---|
| `occ help xygrid:manage` | Syntaxe et options de la commande. |
| `occ xygrid:manage status alice` | État local de la grille en JSON, sans code ni vérificateur. |
| `occ xygrid:manage unlock alice --yes` | Remet à zéro essais et verrou ; conserve le code. |
| `occ xygrid:manage revoke alice --yes` | Supprime la grille et les tickets du compte, notifie le registre. |
| `occ xygrid:manage purge --yes` | Supprime les tickets expirés de tous les comptes, sans toucher aux codes. |
| `occ twofactorauth:state alice` | Contrôle complémentaire de l’état des fournisseurs vu par Nextcloud. |
| `occ app:disable twofactor_xygrid` | Désactive le logiciel pour toute l’instance, pas seulement pour Alice. |

Exemple de résultat individuel pendant un verrou :
`{"active":true,"locked":true,"failed_attempts":3,"locked_until_utc":"2026-11-21T16:00:00+00:00"}`.
La date ci-dessus est illustrative.

Codes retour de la commande spécifique : `0` opération réussie, `1` compte inconnu ou
déverrouillage impossible, `2` arguments ou confirmation incorrects. Une exception d’infrastructure
peut aussi donner un code non nul. La commande de révocation peut être répétée pour remettre le
registre à l’état désactivé. Elle ne supprime pas les autres fournisseurs.

Il n’existe aucune commande pour afficher un code, le fixer arbitrairement ou imposer la grille.
Le déverrouillage permet trois nouveaux essais : vérifier l’identité du demandeur et documenter
l’intervention. Un déverrouillage n’est pas approprié si le secret est compromis ; révoquer plutôt.

## 6. Protocole et stockage

Grille : cinq colonnes x=0..4, deux lignes y=0..1, origine en haut à gauche.
Chaque chiffre apparaît une fois. Les répétitions dans le code impliquent des clics répétés
sur une même case. Le champ natif `challenge` contient l’identifiant opaque de la tentative,
un deux-points, puis six paires `x,y` séparées par des points-virgules. Le formulaire de
connexion inclut aussi le jeton CSRF. La permutation n’est pas acceptée du navigateur.

La génération utilise `random_int` et le mélange de Fisher-Yates. Le serveur décode les paires.
Les tickets sont liés au compte, à une valeur aléatoire de session et à l’usage
(enrôlement ou connexion). Ils expirent après cinq minutes. Une suppression conditionnelle
en base empêche deux consommateurs d’utiliser le même ticket. Un nouvel affichage remplace
normalement le ticket précédent de même session et de même usage : ne pas utiliser deux
onglets de connexion dans la même session.

Enrôlement : le code est envoyé une fois **du serveur vers le navigateur**, car l’utilisateur
doit le découvrir. La confirmation et la connexion renvoient des coordonnées dans l’autre sens.
La réponse d’enrôlement est marquée `no-store`. Aucun code n’est placé dans une URL.

Tables avec le préfixe de l’instance :
- `xyg_account` : empreinte de l’UID, vérificateur HMAC lié au compte et au secret d’instance,
  échecs, fin du verrou et révision pour mises à jour concurrentes.
- `xyg_ticket` : empreinte du ticket, lien de session, compte, usage, permutation temporaire,
  vérificateur et expiration.

Le vérificateur n’est pas un chiffrement réversible. Le code n’a qu’un million de valeurs :
la compromission simultanée de la base et du secret d’instance permet une recherche hors ligne.
Protéger les sauvegardes en conséquence. Une rotation du secret rend les vérificateurs existants
inutilisables ; prévoir une révocation et un nouvel enrôlement, et évaluer les autres effets
sur l’instance avant toute rotation.

Le nettoyage intervient à chaque émission de ticket et à la demande par `purge`. Sans trafic,
une ligne expirée peut rester en base mais n’est plus acceptée. Planifier `purge` si une durée
de rétention physique stricte est nécessaire. La suppression d’un utilisateur déclenche
le nettoyage de ses lignes si le plugin est actif.

## 7. Essais et verrou

Trois codes sont vérifiés au maximum avant verrou. Au troisième échec consécutif,
la grille du compte est bloquée 3 600 secondes ; un quatrième essai n’est pas vérifié.
Un succès remet le compteur à zéro. Le délai est calculé côté serveur. Une requête pendant
le verrou ne prolonge pas le délai. À expiration, l’utilisateur retrouve trois essais.
Les autres méthodes et les sessions déjà ouvertes ne sont pas bloquées par ce plugin.

Un ticket absent, expiré ou déjà consommé est rejeté sans pénaliser le compte. Un ticket
valide portant des coordonnées mal formées est consommé et compte comme un échec au login.
Une confirmation d’enrôlement incorrecte n’active rien. Les modifications de compteur utilisent
une révision conditionnelle en base ; en cas de contention persistante, l’accès est refusé.

## 8. Journalisation, données personnelles et supervision

Canal applicatif : `twofactor_xygrid`. Événements de niveau information : `enabled`, `login_ok`.
Avertissements : `login_failed`, `locked_1h`, `concurrent_update_denied`, `disabled_by_user`,
`occ_revoke`, `occ_unlock`. Les lignes applicatives associent l’événement à l’UID ; le backend
de journalisation fournit son horodatage. Le journal n’identifie pas l’opérateur système d’OCC :
utiliser aussi les journaux d’administration du serveur si cette traçabilité est nécessaire.

Les événements informationnels peuvent ne pas être conservés au seuil configuré.
Vérifier leur visibilité avec un compte de recette. Les tickets périmés ne produisent pas
d’avertissement spécifique pour éviter le bruit. Alerter sur les verrous répétés et les
révocations inhabituelles. Restreindre les destinataires et appliquer une durée de conservation.

Ne pas activer la capture des corps HTTP ni les captures vidéo de session sur ces écrans
(proxy, WAF, APM, outil d’assistance, navigateur administré). Ne jamais demander un export HAR
non expurgé d’une session réelle. Ne jamais journaliser le code, la grille, les coordonnées,
les jetons, les paramètres SQL sensibles ou le secret d’instance.

## 9. Recette de bout en bout avant production

Effectuer ces contrôles sur des comptes jetables, jamais avec le compte administrateur principal.

1. Activer l’app ; un compte neuf reste inactif selon OCC. Sa connexion habituelle ne change pas.
2. Avec un compte ayant déjà TOTP, ouvrir Sécurité, commencer puis abandonner l’enrôlement.
   Recharger : la grille doit rester inactive ; TOTP doit fonctionner.
3. Confirmer correctement dans les cinq minutes. Vérifier l’état local et le registre natif.
4. Ouvrir une session privée. Vérifier que le choix entre TOTP et la grille fonctionne ;
   vérifier séparément une connexion réussie par chacune des méthodes.
5. Contrôler deux rangées de cinq chiffres distincts, le tactile, Tab, Espace, Entrée,
   l’effacement du dernier choix et le focus après le sixième choix.
6. Sur un compte de test uniquement, inspecter le POST : `challenge` porte un ticket et six
   coordonnées, aucun champ ne transmet le code permanent ni la permutation.
7. Faire deux erreurs, vérifier le compteur, puis réussir : le compteur revient à zéro.
8. Faire trois erreurs réparties entre deux sessions. Vérifier le verrou dans les deux,
   l’absence de prolongation et le fonctionnement de TOTP. Tester l’expiration réelle d’une heure.
9. Tester `unlock`, puis réussir avec le même code. Tester `revoke`, puis vérifier la disparition
   de la méthode et la conservation de TOTP. Tester un nouvel enrôlement après révocation.
10. Vérifier en intégration qu’un ticket expiré, un ticket déjà utilisé, ou une grille d’un autre
    compte, d’une autre session ou d’un autre usage ne permet pas une connexion.
11. Tester deux soumissions simultanées du même ticket : une seule peut être consommée. Tester
    aussi deux grilles de sessions distinctes lors du dernier essai disponible.
12. Vérifier les journaux à chaque étape, l’absence de secrets, le nettoyage après suppression
    d’un compte et la commande `purge`.
13. Répéter sur chaque majeure, moteur SQL, backend SSO et topologie effectivement déployés.
    Ne pas déclarer la version compatible tant que ces contrôles ne passent pas.

## 10. Incidents et dépannage

- Section absente : vérifier activation, emplacement du dossier et journaux PHP. Recharger le navigateur.
- Échec 401/403 pendant l’enrôlement : réauthentifier le compte, revenir immédiatement dans Sécurité.
  Sur un backend sans confirmation de mot de passe compatible, ne pas supprimer la protection ;
  garder la méthode désactivée jusqu’à validation d’une intégration adaptée.
- Grille expirée ou remplacée : fermer les onglets en double et repartir d’une grille neuve.
- Code oublié : autre méthode ou récupération selon votre procédure ; après contrôle d’identité,
  révoquer et laisser l’utilisateur s’enrôler lui-même. Ne pas chercher à lui restituer le code.
- Suspicion de capture : révoquer, examiner les sessions et autres identifiants selon la procédure
  de réponse à incident. La révocation de la grille ne ferme pas les sessions déjà ouvertes.
- État incohérent après une erreur système : comparer `status` et `twofactorauth:state`,
  consulter les journaux, puis révoquer et réenrôler si nécessaire. Ne pas éditer les tables à la main.
- Si l’application est désactivée pendant la suppression d’utilisateurs, le listener ne tourne pas.
  Avant de réutiliser un ancien UID, contrôler son état et révoquer toute grille résiduelle.

## 11. Mise à jour, retrait et restauration

Sauvegarder ensemble base, configuration et code ; tester la restauration hors production.
En cas de future mise à jour, suivre le processus de migration Nextcloud et la recette ci-dessus.
Ne pas déployer simultanément deux versions différentes du plugin sur les nœuds.

Avant un retrait, identifier les utilisateurs concernés, leur faire activer et tester une
alternative, puis révoquer individuellement les grilles avant `app:disable`.
Prévoir un accès de secours au cas où Nextcloud signalerait un fournisseur manquant.
La désactivation du logiciel n’est pas une procédure de purge des données personnelles.
Ne pas supprimer les tables tant qu’une restauration, un audit ou une migration reste nécessaire.
Une restauration ancienne peut rétablir des codes révoqués : revoir les révocations après reprise.

## Nouveauté 1.2.0
Voir `DEVERROUILLAGE_ADMIN.md` pour le panneau admin et sa recette.
