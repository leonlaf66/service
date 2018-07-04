<?php
namespace App\Helpers;

class Sd
{
    // only ma
    public static function allCityIds()
    {
        static $ids = null;
        if (!is_null($ids)) return $ids;

        $codes = app('db')->table('schooldistrict')
            ->select('code')
            ->get('code');

        $allCodes = [];
        foreach ($codes as $d) {
            $code = $d->code;
            if (strpos($code, '/') !== false) {
                foreach (explode('/', $code) as $_code) {
                    $allCodes[] = $_code;
                }
            } else {
                $allCodes[] = $code;
            }
        }

        return $ids = app('db')->table('town')
            ->select('id')
            ->where('state', 'MA')
            ->whereIn('short_name', $allCodes)
            ->get('id')
            ->map(function ($d) {
                return $d->id;
            })
            ->toArray();
    }
}