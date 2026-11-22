<?php
script('twofactor_xygrid','grid');
style('twofactor_xygrid','grid');
?>
<section class="section" id="xyg-personal"
    data-begin="<?php p($_['begin']); ?>" data-confirm="<?php p($_['confirm']); ?>"
    data-disable="<?php p($_['disable']); ?>">
    <h2>Code personnel sur grille</h2>
    <p>Option facultative. Vos autres méthodes restent disponibles.</p>
    <label><input type="checkbox" class="xyg-toggle" <?php if ($_['active']) { print 'checked'; } ?>>
        Activer la grille pour mon compte</label>
    <div class="xyg-enrol" hidden>
        <p>Mémorisez votre code : <strong class="xyg-secret"></strong>.
            Il ne sera pas réaffiché après activation.</p>
        <button type="button" class="xyg-hide">Masquer le code</button>
        <p>Confirmez le code en sélectionnant ses six chiffres sur cette grille.</p>
        <form class="xyg-form" autocomplete="off"><?php require __DIR__.'/pad.php'; ?></form>
    </div>
    <p class="xyg-message" role="status" aria-live="polite"></p>
    <noscript>JavaScript est nécessaire pour activer cette méthode.</noscript>
</section>
