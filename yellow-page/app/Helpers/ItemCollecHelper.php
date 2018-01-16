<?php
namespace App\Helpers;

class ItemCollecHelper
{
    public static function build($collec)
    {
        $types = app('App\Repositories\Type')->all()->get()->keyBy('id');
        $typeNames = $types->map(function ($d) {
            $nameEn = preg_replace('/\[.*\]/', '', $d->name);
            return tt($nameEn, $d->name_zh);
        });

        return $collec->map(function ($d) use ($typeNames) {
            $d->business = tt($d->business, $d->business_cn);
            $d->photo_url = media_url('yellowpage/placeholder.jpg');
            $d->type = [
                'id' => $d->type_id,
                'name' => $typeNames[$d->type_id] ?? tt('Unknown', '未知')
            ];
            unset($d->type_id, $d->business_cn, $d->photo_hash);
            return $d;
        });
    }
}