# Déverrouillage administratif — 1.2.0

Dans Paramètres d’administration → Sécurité → Administration de la grille,
saisir l’UID exact et consulter l’état. Après vérification de l’identité,
confirmer le déverrouillage uniquement si la grille est bloquée et si la
personne connaît toujours son code. Si le code est compromis, suivre la
procédure de révocation. Si le code est oublié, utiliser la récupération.

Le bouton ne modifie aucune autre méthode, ne crée pas de session et ne révèle
pas le code. Les échecs partiels sur une grille non verrouillée ne peuvent pas
être effacés par cet écran. Le déverrouillage OCC existant reste inchangé.
Le journal applicatif enregistre admin_unlock, l’UID administrateur, la cible
et le résultat pour les opérations parvenues à l’action de déverrouillage.
Un refus par la couche d’accès Nextcloud peut intervenir avant cette action.

## Recette sur une instance de test

- Compte inconnu, grille inactive, grille ouverte avec deux échecs :
  aucun bouton de déverrouillage et aucun changement de compteur.
- Verrouiller une grille jetable par trois erreurs. Vérifier UID et durée.
  Annuler la confirmation : rien ne change. Confirmer : vérifier le journal,
  le statut OCC, une connexion avec le même code et l’autre méthode de secours.
- Ouvrir le même verrou dans deux fenêtres : une seule confirmation doit réussir.
  Une confirmation périmée ne doit pas effacer un verrou ultérieur.
- Vérifier le refus pour un utilisateur ordinaire et un administrateur de groupe,
  la protection CSRF et l’exigence de confirmation récente sur la modification.
- Contrôler les cinq langues, le clavier et la lecture du message de résultat.
- En cas de réponse réseau incertaine, consulter de nouveau le statut plutôt
  que répéter l’action. En cas de confirmation expirée, se réauthentifier.
