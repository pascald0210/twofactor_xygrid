<?php
declare(strict_types=1);
return ['routes' => [
    ['name'=>'gridAdmin#lookup','url'=>'/admin/grid/status','verb'=>'POST'],
    ['name'=>'gridAdmin#unlock','url'=>'/admin/grid/unlock','verb'=>'POST'],
    ['name'=>'settings#begin',   'url'=>'/enrol/start',   'verb'=>'POST'],
    ['name'=>'settings#confirm', 'url'=>'/enrol/confirm', 'verb'=>'POST'],
    ['name'=>'settings#disable', 'url'=>'/enrol/disable', 'verb'=>'POST'],
]];
