<?php
namespace App\Repositories;

class HouseGet
{
    public function get($id)
    {
        $house = \App\Models\HouseIndex::findOrFail($id);

        $subTypeName = strtolower($house->prop_type).'_type';

        return [
            'id' => $house->list_no,
            'nm' => $house->getFieldValue('name'),
            'loc' => $house->getFieldValue('location'),
            'price' => $house->list_price,
            'prop' => $house->prop_type,
            $subTypeName => $house->getFieldValue($subTypeName),
            'beds' => $house->no_beds,
            'baths' => $house->no_baths,
            'square_feet' => $house->square_feet,
            'lot_size' => $house->lot_size,
            'area' => $house->getFieldValue('area'),
            'status' => $house->status,
            'l_days' => $house->getFieldValue('list_days'),
            'latlng' => d_field_toarr($house->latlng),
            'img_cnt' => $house->getFieldValue('photo_count'),
            'taxes' => $house->getFieldValue('taxes'),
            'roi' => $house->getFieldValue('roi'),
            'details' => $house->getDetails(),
            'mls_id' => $house->getFieldValue('mls_id')
        ];
    }

    public function getSimple($id)
    {
        $house = \App\Models\HouseIndex::findOrFail($id);

        return [
            'id' => $house->list_no,
            'nm' => $house->getFieldValue('name'),
            'loc' => $house->getFieldValue('location'),
            'price' => $house->list_price,
            'prop' => $house->prop_type,
            'no_beds' => $house->no_beds,
            'no_baths' => $house->no_baths,
            'square' => $house->square_feet,
            'status_name' => $house->status,
            'l_days' => $house->getFieldValue('list_days'),
            'mls_id' => $house->getFieldValue('mls_id')
        ];
    }
}