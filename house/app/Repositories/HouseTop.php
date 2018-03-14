<?php
namespace App\Repositories;

use App\Models\AreaSetting;

class HouseTop
{
    public function all($areaId, $limit = 10)
    {
        $items = AreaSetting::get('home.luxury.houses', $areaId, []);

        $ids = collect($items)->map(function ($d) {
            return $d['id'];
        })->toArray();

        return \App\Models\HouseIndex::query()->whereIn('list_no', $ids)->get();
    }
}