<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class HouseNearbiy
{
    public function all($houseId, $limit = 10)
    {
        $masterHouse = \App\Models\HouseIndex::findOrFail($houseId);
        $db = app('db');

        $id = $masterHouse->id;
        $cityId = $masterHouse->city_id;
        $price = $masterHouse->list_price;
        $propTypeId = $masterHouse->prop_type; //SF/CC归为一类

        $query =  \App\Models\HouseIndex::query()
            ->select('*')
            ->addSelect(DB::raw("abs(list_price - {$price}) as diff_price"))
            ->where('area_id', area_id())
            ->where('city_id', $cityId)
            ->where('list_no', '<>', $houseId)
            ->orderBy('diff_price', 'ASC');

        if (in_array($propTypeId, ['SF', 'CC'])) {
            $query->whereIn('prop_type', ['SF', 'CC']);
        } else {
            $query->where('prop_type', $propTypeId);
        }
        $query->where('list_no', '<>', $id);
        $query->take($limit);

        return $query->get();
    }
}