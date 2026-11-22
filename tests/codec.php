<?php
declare(strict_types=1);
require dirname(__DIR__).'/lib/Service/Codec.php';
use OCA\TwoFactorXYGrid\Service\Codec;
function check(bool $v,string $m): void {
    if (!$v) { fwrite(STDERR,"ÉCHEC : $m\n"); exit(1); }
}
$l='0123456789';
check(Codec::decode($l,'0,0;0,0;4,0;2,1;2,1;1,0')==='004771','répétitions et zéros');
check(Codec::decode($l,'4,1;4,1;4,1;4,1;4,1;4,1')==='999999','angle inférieur droit');
foreach (['','004771','5,0;0,0;0,0;0,0;0,0;0,0','0,2;0,0;0,0;0,0;0,0;0,0',
          '0,0;0,0;0,0;0,0;0,0','0,0;0,0;0,0;0,0;0,0;0,0;0,0'] as $bad) {
    check(Codec::decode($l,$bad)===null,'format interdit');
}
for ($i=0;$i<100;$i++) {
    $p=Codec::layout(); $a=str_split($p); sort($a,SORT_STRING);
    check(implode('',$a)===$l,'permutation complète');
    $expected='008119'; $coords=[];
    foreach (str_split($expected) as $d) {
        $pos=strpos($p,$d); $coords[]=($pos%5).','.intdiv($pos,5);
    }
    check(Codec::decode($p,implode(';',$coords))===$expected,'aller-retour');
}
echo "OK : tests du codec et des permutations.\n";
