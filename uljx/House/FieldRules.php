<?php
namespace Uljx\House;

class FieldRules
{
    public static function parse($maps = [])
    {
        return array_merge([
            'id' => '@list_no',
            'nm' => '@name',
            'loc' => '@location',
            'beds' => 'no_beds',
            'baths' => 'no_baths',
            'square' => 'square_feet',
            'price' => 'list_price',
            'prop' => 'prop_type',
            'l_days' => function ($d) {
                return intval((time() - strtotime($d->list_date)) / 86400);
            },
            'img_cnt' => '@photo_count',
            'taxes' => '@taxes',
            'roi' => '@roi',
            'tags' => '@tags',
            'latlng' => function ($d) {
                if (empty($d->latlng)) {
                    return [];
                }
                return explode(',', trim($d->latlng, '{}'));
            },
            'sub_tnm' => function ($d) {
                $subTypeField = strtolower($d->prop_type).'_type';
                return $d->getFieldValue($subTypeField);
            },
            'details' => function ($d) {
                return $d->getDetails();
            },
            'mls_id' => '@mls_id',
        ], $maps);
    }
}
