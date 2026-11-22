<?php
declare(strict_types=1);
script('twofactor_xygrid', 'grid_admin');
$labels = [];
foreach (['title'=>'Grid administration',
'uid'=>'Internal user ID',
'check'=>'Check grid status',
'unlock'=>'Unlock grid',
'unknown'=>'Account not found.',
'inactive'=>'Grid not enabled.',
'open'=>'Grid is not locked. No change needed.',
'minutes'=>'Time remaining (min):',
'warning'=>'Verify the user’s identity first. Unlocking grants three attempts with the existing code.',
'confirm'=>'Unlock the grid for this account?',
'unlocked'=>'Grid unlocked. The code is unchanged.',
'changed'=>'Status changed. Check the account again.',
'denied'=>'Access denied or confirmation required. Sign in again as an administrator and check the account again.',
'error'=>'Result unknown. Check the account before retrying.'] as $key=>$source) { $labels[$key]=$l->t($source); }
?>
<section id="xyg-admin" class="section"
    data-lookup="<?php p($_['lookup']); ?>" data-unlock="<?php p($_['unlock']); ?>"
    data-labels="<?php p(json_encode($labels, JSON_THROW_ON_ERROR)); ?>">
    <h2><?php p($labels['title']); ?></h2>
    <p><?php p($labels['warning']); ?></p>
    <form>
        <label for="xyg-admin-uid"><?php p($labels['uid']); ?></label>
        <input type="text" id="xyg-admin-uid" required autocomplete="off" maxlength="1024">
        <button type="submit"><?php p($labels['check']); ?></button>
    </form>
    <p role="status" aria-live="polite" class="xyg-admin-status"></p>
    <button type="button" class="xyg-admin-unlock" hidden disabled><?php p($labels['unlock']); ?></button>
</section>
