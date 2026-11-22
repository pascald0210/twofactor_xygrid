<?php
script('twofactor_xygrid','grid');
style('twofactor_xygrid','grid');
?>
<?php if ($_['minutes']>0): ?>
<p role="alert">Grille verrouillée. Réessayez dans <?php p($_['minutes']); ?> min ou utilisez une autre méthode active.</p>
<?php elseif (!empty($_['board'])): ?>
<form method="post" class="xyg-form" autocomplete="off">
    <p>Sélectionnez vos six chiffres. Essais disponibles : <?php p($_['remaining']); ?>.</p>
    <input type="hidden" name="requesttoken" value="<?php p($_['requesttoken'] ?? ''); ?>">
    <?php require __DIR__.'/pad.php'; ?>
</form>
<noscript>Activez JavaScript pour cette grille ou choisissez une autre méthode.</noscript>
<?php else: ?>
<p>Grille indisponible. Revenez au choix des méthodes ou contactez votre administrateur.</p>
<?php endif; ?>
