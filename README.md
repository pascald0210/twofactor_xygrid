# Code personnel sur grille — twofactor_xygrid 1.2.0
App Nextcloud de double authentification (MFA) basée sur une grille de 10 chiffres de 0 à 9 générée aléatoirement à chaque login.
L'utilisateur sélectionne ce MFA dans ses "Paramètres/Sécruité" et obtient un code à 6 chiffres qu'il utilisera après avoir rentré
son identifiant et son mot de passe. Au bout de 3 tentatives de code erroné, il est banni 1h. L'administrateur Nextcloud peut le
débloquer sans modifier son code à 6 chiffres.
Application locale Nextcloud, version candidate de recette. Interface et documentation françaises pour le moment en français.
Aucune dépendance Composer ou npm à télécharger pour construire le paquet.

- Installation, commandes OCC, modèle de sécurité, dépannage et recette : `docs/ADMINISTRATEUR.md`.
- Activation, connexion et récupération : `docs/UTILISATEUR.md`.
- Déverrouillage administratif : `docs/DEVERROUILLAGE_ADMIN.md`.
- Test sans Nextcloud : `php tests/codec.php`.
- Fournisseur natif : `xygrid`. Application : `twofactor_xygrid`.
- Licence déclarée : GNU Affero General Public License, version 3 ou ultérieure
  (SPDX : AGPL-3.0-or-later).
