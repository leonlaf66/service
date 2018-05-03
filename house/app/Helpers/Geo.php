<?php
namespace App\Helpers;

class Geo
{
    CONST EARTH_RADIUS = 6370.996; // 地球半径系数
    CONST PI = 3.1415926;

    public static function getDistance($longitude1, $latitude1, $longitude2, $latitude2, $unit=1, $decimal=0)
    {
        $radLat1 = bcmul($latitude1, self::PI / 180.0, 20);
        $radLat2 = bcmul($latitude2, self::PI / 180.0, 20);

        $radLng1 = bcmul($longitude1, self::PI / 180.0, 20);
        $radLng2 = bcmul($longitude2, self::PI /180.0, 20);

        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;

        $distance = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
        $distance = $distance * self::EARTH_RADIUS * 1000;

        if($unit==2){
            $distance = $distance / 1000;
        }

        return round($distance, $decimal);
    }
}