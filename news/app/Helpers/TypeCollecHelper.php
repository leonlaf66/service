<?php
namespace App\Helpers;

class TypeCollecHelper
{
    public static function build($itemCollec)
    {
        return $itemCollec->map(function ($d) {
            if (is_chinese()) {
                $d->name = $d->name_zh;
                unset($d->name_zh);
            }
            unset($d->name_zh);
            return $d;
        });
    }
}