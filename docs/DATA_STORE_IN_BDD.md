xyg_account — la grille activée
Champ	Information stockée
subject	Empreinte SHA-256 de l’identifiant interne (UID). L’UID n’est pas stocké en clair dans cette table, mais une personne qui connaît un UID peut calculer son empreinte et retrouver la ligne correspondante.
verifier	Empreinte de vérification du code (HMAC-SHA-256), liée au compte et calculée avec le secret d’instance.
revision	Valeur aléatoire renouvelée à chaque mise à jour pour éviter que deux opérations simultanées s’écrasent.
failures	Compteur d’échecs de saisie du code.
until_ts	Date et heure de fin du verrouillage, au format Unix. La valeur 0 indique l’absence de verrou ; une date passée indique un verrou expiré.
xyg_ticket — les demandes temporaires
Champ	Information stockée
subject	Même empreinte de l’UID, pour rattacher le ticket au compte.
binding	Empreinte SHA-256 d’une valeur aléatoire conservée dans la session. Elle lie le ticket à cette session sans enregistrer l’identifiant de session dans la table.
purpose	Motif du ticket : enrol pour l’activation ou login pour la connexion.
ticket	Empreinte SHA-256 de l’identifiant aléatoire du ticket, et non l’identifiant remis à l’interface.
layout	Disposition des chiffres affichés sur la grille. Ce n’est pas la suite de six chiffres que l’utilisateur doit sélectionner.
proof	Empreinte de vérification du code proposé à l’activation ou, pour une connexion, copie du verifier du compte.
expires_ts	Date et heure d’expiration du ticket, fixées à cinq minutes après sa création.
