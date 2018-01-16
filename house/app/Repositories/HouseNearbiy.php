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
            ->orderBy('diff_price', 'ASC');

        if (in_array($propTypeId, ['SF', 'CC'])) {
            $query->whereIn('prop_type', ['SF', 'CC']);
        } else {
            $query->where('prop_type', $propTypeId);
        }
        $query->where('list_no', '<>', $id);
        $query->take($limit);

        $houses = $query->get();

        return $houses->map(function ($d) {
            return [
                'id' => $d->list_no,
                'nm' => $d->getFieldValue('name'),
                'loc' => $d->getFieldValue('location'),
                'beds' => $d->no_beds,
                'baths' => $d->no_baths,
                'square' => $d->square_feet,
                'price' => $d->list_price,
                'prop' => $d->prop_type,
                'status' => $d->status,
                'l_days' => intval((time() - strtotime($d->list_date)) / 86400),
                'tags' => $d->getFieldValue('tags'),
                'mls_id' => $d->getFieldValue('mls_id')
            ];
        });
    }
}