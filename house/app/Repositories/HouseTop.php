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

        $houses = \App\Models\HouseIndex::query()->whereIn('list_no', $ids)->get();

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