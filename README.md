# Code personnel sur grille — twofactor_xygrid 1.0.0

Application locale Nextcloud, version candidate de recette. Interface et documentation françaises.
Aucune dépendance Composer ou npm à télécharger pour construire le paquet.

- Installation, commandes OCC, modèle de sécurité, dépannage et recette : `docs/ADMINISTRATEUR.md`.
- Activation, connexion et récupération : `docs/UTILISATEUR.md`.
- Test sans Nextcloud : `php tests/codec.php`.
- Fournisseur natif : `xygrid`. Application : `twofactor_xygrid`.
- Licence déclarée : GNU Affero General Public License, version 3 ou ultérieure
  (SPDX : AGPL-3.0-or-later).

Ce projet autonome n’utilise aucun fichier des versions précédemment proposées sous le nom
`twofactor_grid`. Il ne migre pas leurs secrets ni leurs comptes. Si un prototype a effectivement
été installé, organiser son retrait et vérifier les solutions de récupération séparément.

## Mise à jour 1.2.0
Déverrouillage administratif : `docs/DEVERROUILLAGE_ADMIN.md`.
