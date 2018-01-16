<?php
return [
    'location' => [
        'value' => function ($d, $m) {
            $propType = array_get($d, 'prop_type', '');

            $fields = in_array($propType, ['RN', 'CC']) ? [
                'street', 'unit_no', 'town', 'zip_code'
            ] : [
                'street', 'town', 'zip_code'
            ];

            $result = [];
            foreach($fields as $field) {
                $value = array_get($d, $field);
                if($field == 'street') {
                    $value = array_get($d, 'street_num').' '.ucwords(strtolower(array_get($d, 'street_name')));
                    if (substr($value, strlen($value) - 1, 1) === '.') {
                        $value = substr($value, 0, strlen($value) - 1);
                    }
                    $value .= ', ';
                }
                if($field == 'town') {
                    $value = app('\App\Repositories\Mls\City')->findIdByCode('MA', array_get($d, 'town'));
                }
                if($field == 'zip_code'){
                    $value = 'MA '.array_get($d, 'zip_code');
                }
                if($value) $result[] = $value;
            }

            return implode(' ', $result);
        }
    ],
    'photo_count' => [
        'index' => 'photo_count'
    ],
    'area' => [
        'index' => 'area',
        'filter' => function ($val) {
            static $cache = null;
            if (is_null($cache)) {
                $cache = get_static_data('mls/area');
            }
            return $cache[$val] ?? '';
        }
    ],
    'rn_type' => [
        'index' => 'rn_type',
        'map' => '1'
    ],
    'sf_type' => [
        'index' => 'sf_type',
        'map' => '1'
    ],
    'mf_type' => [
        'index' => 'mf_type',
        'map' => '1'
    ],
    'cc_type' => [
        'index' => 'cc_type',
        'map' => '1'
    ],
    'ci_type' => [
        'index' => 'ci_type',
        'map' => '1'
    ],
    'bu_type' => [
        'index' => 'bu_type',
        'map' => '1'
    ],
    'ld_type' => [
        'index' => 'ld_type',
        'map' => '1'
    ],
    'roi' => [
        'value' => function ($d, $m) {
            return app('App\Repositories\MLs\HouseRoi')->getResults($m);
        }
    ]
];