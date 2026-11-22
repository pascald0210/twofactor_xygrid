<?php $b=$_['board']??[]; ?>
<div class="xyg-pad" data-ticket="<?php p($b['id']??''); ?>">
    <div class="xyg-keys" role="group" aria-label="Clavier de deux lignes et cinq colonnes">
        <?php foreach (($b['digits']??[]) as $i=>$d): ?>
        <button type="button" data-index="<?php p($i); ?>"><?php p($d); ?></button>
        <?php endforeach; ?>
    </div>
    <p>Chiffres sélectionnés : <output aria-live="polite">0 / 6</output></p>
    <input type="hidden" name="challenge" value="">
    <button type="button" class="xyg-back">Effacer le dernier choix</button>
    <button type="submit" class="xyg-submit" disabled>Valider</button>
</div>
