<?php
namespace App\Helpers;

class TypeCollecHelper
{
    public static function build($items)
    {
        return $items->map(function ($d) {
            if (preg_match('/\[(.*)\]/', $d->name, $match)) {
                $d->icon = media_url('yellowpage/types/'.$match[1]);
            }

            $nameEn = preg_replace('/\[.*\]/', '', $d->name);
            $d->name = tt($nameEn, $d->name_zh);

            unset($d->name_zh);

            return $d;
        });
    }
}