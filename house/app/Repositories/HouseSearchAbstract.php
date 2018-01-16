<?php
namespace App\Repositories;

abstract class HouseSearchAbstract
{
    protected function getFilterRules($type)
    {
        $base = [
            'square' => [
                'options' => [
                    '1' => [0, 1000],
                    '2' => [1000, 2000],
                    '3' => [2000, 3000],
                    '4' => [3000, 999999999]
                ],
                'apply' => function ($q, $key, $opts) {
                    if (isset($options[$key])) {
                        $q->whereBetween('square_feet', $opts[$key]);
                    }
                }
            ],
            'beds' => [
                'apply' => function ($q, $cnt) {
                    $cnt = intval($cnt);
                    $q->where('no_beds', '>', $cnt);
                }
            ],
            'baths' => [
                'apply' => function ($q, $cnt) {
                    $cnt = intval($cnt);
                    $q->whereRaw('no_baths[1] + no_baths[2] > '.$cnt);
                }
            ],
            'parking' => [
                'apply' => function ($q, $cnt) {
                    $cnt = intval($cnt);
                    $q->where('parking', '>', $cnt);
                }
            ],
            'marketDays' => [
                'apply' => function ($q, $key) {
                    if (! in_array($key, ['1', '2', '3'])) return;

                    $startTime = date(
                        'Y-m-d',
                        time() - 86400 * ['1' => 2, '2' => 7, '3' => 30][$key]
                    );

                    $q->where('list_date', '>=', $startTime);
                }
            ]
        ];

        // 售房
        if ($type === 'purchase') {
            return array_merge($base, [
                'prop' => [
                    'apply' => function ($q, $vals) {
                        $q->whereIn('prop_type', $vals);
                    }
                ],
                'price' => [
                    'options' => [
                        '1' => [0, 50],
                        '2' => [50, 100],
                        '3' => [100, 150],
                        '4' => [150, 200],
                        '5' => [200, 99999999]
                    ],
                    'apply' => function ($q, $id, $opts) {
                        if (!isset($opts[$id])) return;
                        list($start, $end) = $opts[$id];
                        $q->whereBetween('list_price', [$start * 10000, $end * 10000]);
                    }
                ],
                'agrage' => [
                    'apply' => function ($q, $val) {
                        $value = $val === '1';
                        $q->where('garage_spaces', $value);
                    }
                ]
            ]);
        }

        // 租房
        return array_merge($base, [
            'price' => [
                'options' => [
                    '1' => [0, 1000],
                    '2' => [1000, 1500],
                    '3' => [1500, 2000],
                    '4' => [2000, 99999999999]
                ],
                'apply' => function ($q, $id, $opts) {
                    if (!isset($opts[$id])) return;
                    $q->whereBetween('list_price', $opts[$id]);
                }
            ]
        ]);
    }
}