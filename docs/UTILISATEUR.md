# Code personnel sur grille — Guide utilisateur

## À quoi sert la grille ?

Après le mot de passe, vous sélectionnez six chiffres connus de vous sur un clavier dont les
touches changent de place à chaque nouvelle grille. Votre code reste le même jusqu’à la
désactivation de la méthode. L’activation est volontaire. Si vous avez déjà une autre méthode
comme TOTP, vous la conservez et pouvez la choisir à la connexion.

La grille utilise un second secret mémorisé, pas un appareil indépendant. Si votre organisation
vous demande d’utiliser une clé de sécurité ou une application d’authentification, suivez sa consigne.

## Avant de commencer

Utilisez un navigateur de confiance avec JavaScript. Choisissez un endroit où personne ne voit
votre écran. Avant de quitter votre session actuelle, assurez-vous de disposer d’une autre
méthode ou du moyen de récupération prévu par votre organisation.

## Activer la méthode

1. Connectez-vous à Nextcloud et ouvrez vos paramètres personnels, rubrique **Sécurité**.
2. Dans **Code personnel sur grille**, cochez **Activer la grille pour mon compte**.
3. Un nombre de six chiffres apparaît. Un zéro au début compte ; un chiffre peut apparaître
   plusieurs fois. Mémorisez le nombre dans l’ordre ou conservez-le dans un emplacement sûr.
4. Vous pouvez cliquer sur **Masquer le code** dès que vous l’avez retenu.
5. Sélectionnez ses six chiffres sur la grille. Pour un chiffre répété, appuyez plusieurs
   fois sur la même touche. Le compteur indique le nombre de choix ; il ne dévoile pas votre saisie.
6. Après le sixième choix, appuyez sur **Entrée** ou cliquez sur **Valider**.
7. Attendez le message **Grille activée**. Sans ce message, rechargez la page pour vérifier
   l’état avant de fermer votre session.

Vous avez cinq minutes pour confirmer. Si la grille expire ou si vous abandonnez avant
confirmation, elle n’est pas activée. Recommencer peut afficher un nouveau code : utilisez
uniquement le dernier code présenté. Après activation, l’ancien code n’est pas réaffiché.

## Se connecter

1. Saisissez votre identifiant et votre mot de passe sur votre instance habituelle.
2. Si plusieurs méthodes sont proposées, choisissez **Code personnel sur grille** ou votre autre méthode.
3. Repérez les chiffres sur cette grille, sans vous fier à leur position lors de la dernière connexion.
4. Sélectionnez les six chiffres de votre code dans l’ordre. Les boutons ne bougent pas entre deux clics.
5. Appuyez sur **Entrée** après le sixième choix, ou sur **Valider**.

La grille comporte toujours deux lignes et cinq colonnes. Vous n’avez pas à calculer les coordonnées :
le navigateur s’en charge. Au clavier, utilisez Tab ou Maj+Tab pour parcourir les boutons,
puis Espace ou Entrée pour choisir une touche. Après le sixième choix, le focus passe sur **Valider**.
Évitez d’ouvrir la même procédure dans plusieurs onglets.

## Corriger une saisie ou gérer un blocage

Avant validation, **Effacer le dernier choix** retire un chiffre et ne coûte pas d’essai.
Une grille expirée nécessite un nouveau chargement. Un choix incorrect validé compte comme un échec.
Une réussite remet le compteur à zéro.

Après **trois échecs**, la grille est verrouillée **une heure**. Il n’existe pas de quatrième
essai pendant ce délai. Attendre ne change pas votre code. En attendant, vous pouvez employer
une autre méthode déjà activée. Le verrou de la grille ne ferme pas vos sessions ouvertes.
Si vous n’avez aucune alternative, contactez l’assistance habituelle de votre organisation.

## Désactiver, remplacer ou récupérer

- Pour désactiver : dans **Sécurité**, décochez la case et confirmez. Les autres méthodes ne changent pas.
- Pour changer de code : assurez-vous d’abord qu’une autre méthode fonctionne, désactivez la grille,
  puis réactivez-la et confirmez le nouveau code. Il n’y a pas de bouton pour afficher l’ancien.
- Code oublié : utilisez votre autre méthode ou votre procédure de récupération.
  L’administrateur peut révoquer la grille après vérification d’identité, mais ne peut pas lire votre code
  avec les commandes du plugin. Un simple déverrouillage ne remplace pas un code oublié.
- Si vous suspectez qu’une personne a vu votre code ou filmé votre saisie, cessez de l’utiliser,
  signalez l’incident et demandez sa révocation si vous ne pouvez pas le faire vous-même.

## Questions fréquentes

**La case est cochée mais je vois encore le code et la grille de confirmation.**
L’activation n’est pas terminée. Il faut sélectionner les six chiffres et obtenir le message de réussite.

**La page demande de me reconnecter pendant l’activation.**
Reconnectez-vous à votre instance puis revenez directement à la rubrique Sécurité.
Ne communiquez jamais votre mot de passe ou votre code au support.

**Quel autre choix ai-je si JavaScript est bloqué ?**
Revenez au sélecteur et utilisez une méthode déjà disponible. Si vous n’en avez pas, contactez
l’assistance. Ne désactivez pas globalement les protections de votre navigateur pour un site inconnu.

**Pourquoi ne pas envoyer directement le code ?**
Le formulaire envoie les positions des touches à la place. Cela ne rend pas une capture de l’écran
inoffensive : une personne voyant à la fois les chiffres et vos clics peut retrouver le code.
N’envoyez ni capture d’écran, ni enregistrement, ni trace réseau de votre connexion.
