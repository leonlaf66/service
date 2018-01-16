<?php
$fieldBase = include(__DIR__.'/house/fields.base.php');

return [
    'mls' => [
        'fields' => array_merge($fieldBase, include(__DIR__.'/house/fields.mls.php')
        ),
    ],
    'listhub' => [
        'fields' => array_merge($fieldBase, include(__DIR__.'/house/fields.listhub.php')),
        'prop_type_maps' => [
            'RN' => function ($propType) {
                return $propType === 'Rental';
            },
            'MF' => function ($propType) {
                return $propType === 'MultiFamily';
            },
            'SF' => function ($_, $propSubType) {
                return in_array($propSubType, ['Single Family Attached', 'Single Family Detached']);
            },
            'CC' => function ($_, $propSubType) {
                return in_array($propSubType, ['Condominium', 'Apartment']);
            },
            'CI' => function ($propType) {
                return $propType === 'Commercial';
            },
            'LD' => function ($propType) {
                return $propType === 'Lots And Land';
            }
        ]
    ],
    'prop_types' => [
        'RN' => ['Rental', '租房'],
        'MF' => ['Multi family', '多家庭'],
        'SF' => ['Single family', '单家庭'],
        'CC' => ['Condominium', '公寓'],
        'CI' => ['Commercial', '商业房'],
        'LD' => ['Land', '土地']
    ],
    'status' => [
        'NEW' => ['New', '新的'],

    ]
];