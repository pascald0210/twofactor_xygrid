<?php
declare(strict_types=1);
namespace OCA\TwoFactorXYGrid\Service;

final class Codec {
    public static function layout(): string {
        $a=range(0,9);
        for ($i=9;$i>0;--$i) {
            $j=random_int(0,$i);
            [$a[$i],$a[$j]]=[$a[$j],$a[$i]];
        }
        return implode('',$a);
    }
    public static function decode(string $layout,string $coords): ?string {
        if (strlen($layout)!==10 || strspn($layout,'0123456789')!==10
            || count(array_unique(str_split($layout)))!==10) { return null; }
        if (!preg_match('/\A[0-4],[01](?:;[0-4],[01]){5}\z/',$coords)) { return null; }
        $code='';
        foreach (explode(';',$coords) as $xy) {
            $code.=$layout[(int)$xy[0]+5*(int)$xy[2]];
        }
        return $code;
    }
}
