<?php
namespace App\Helpers;

use App\Helpers\Geo;

class SubwayGeo
{
    public static function getMatchedStations($longitude, $latitude, $distance = 1)
    {
        static $stations = [];
        if (empty($stations)) {
            $rows = app('db')->table('subway_station')->select('id', 'longitude', 'latitude')->get();
            foreach ($rows as $row) {
                $id = $row->id;
                $stations[$id] = ['longitude'=>$row->longitude, 'latitude'=>$row->latitude];
            }
        }

        $resultStationIds = [];
        $unit = 2; //公里
        $decimal = 2; //小数数量
        foreach ($stations as $stationId=>$pointer) {
            if(Geo::getDistance($longitude, $latitude, $pointer['longitude'], $pointer['latitude'], $unit, $decimal) < $distance) {
                $resultStationIds[] = $stationId;
                break; //匹配到一个就忽悠后面的站点
            }
        }
        return $resultStationIds;
    }

    public static function getMatchedLines($longitude, $latitude, $distance = 1)
    {
        $resultLineIds = [];
        $unit = 2; //公里
        $decimal = 2; //小数数量

        $linePointers = self::getLinesPointers();
        foreach($linePointers as $lineId=>$pointers) {
            foreach($pointers as $pointer) {
                if(Geo::getDistance($longitude, $latitude, $pointer['longitude'], $pointer['latitude'], $unit, $decimal) < $distance) {
                    $resultLineIds[] = $lineId;
                    break; //匹配到一个就忽悠后面的站点
                }
            }
        }

        return $resultLineIds;
    }

    public static function getLinesPointers()
    {
        static $results = [];

        if(empty($results)) {
            $gettgerSql = '
                select line.id as line_id,station.longitude, station.latitude 
                    from subway_station as station 
                        inner join subway_line as line on station.line_code = line.code 
                        order by line.sort_order asc, station.sort_order asc';

            $rows = app('db')->select($gettgerSql);

            $results = [];
            foreach ($rows as $row) {
                $lineId = $row->line_id;
                if(!isset($results[$lineId])) $results[$lineId] = [];
                $results[$lineId][] = ['longitude'=>$row->longitude, 'latitude'=>$row->latitude];
            }
        }

        return $results;
    }
}