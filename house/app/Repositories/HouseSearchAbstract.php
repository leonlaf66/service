<?php
namespace App\Repositories;

abstract class HouseSearchAbstract
{
    protected function getFilterRules($type)
    {
        $base = [
            'latlon' => [
                'apply' => function ($q, $vals) {
                    list($lat, $lon) = explode(',', $vals);
                    $q->whereRaw('earth_box(ll_to_earth(latlng[1]::double precision, latlng[2]::double precision),2000::double precision) @> ll_to_earth(?, ?)', [$lat, $lon]);
                }
            ],
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
            'square-range' => [
                'apply' => function ($q, $range) {
                    list($start, $end) = array_values(array_merge([
                        'from' => 0,
                        'to' => 9999999999
                    ], $range));

                    $q->whereBetween('square_feet', [$start, $end]);
                }
            ],
            'price-range' => [
                'apply' => function ($q, $range) {
                    list($start, $end) = array_values(array_merge([
                        'from' => 0,
                        'to' => 9999999999
                    ], $range));

                    $q->whereBetween('list_price', [$start, $end]);
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
                    $q->where('parking_spaces', '>', $cnt);
                }
            ],
            'market-days' => [
                'apply' => function ($q, $key) {
                    if (! in_array($key, ['1', '2', '3'])) return;

                    $startTime = date(
                        'Y-m-d',
                        time() - 86400 * ['1' => 2, '2' => 7, '3' => 30][$key]
                    );

                    $q->where('list_date', '>=', $startTime);
                }
            ],
            'city-id' => [
                'apply' => function ($q, $id) {
                    if (is_array($id)) {
                        $q->whereIn('city_id', $id);
                    } else {
                        $q->where('city_id', $id);
                    }
                }
            ],
            'subway-line' => [
                'apply' => function ($q, $lineId) {
                    $lineId = intval($lineId);
                    $q->where('subway_lines', '@>', "{{$lineId}}");
                }
            ],
            'subway-stations' => [
                'apply' => function ($q, $stationIds) {
                    $stationIds = array_map(function ($id) {
                        return intval($id);
                    }, $stationIds);
                    $stationIds = '{'.implode(',', $stationIds).'}';
                    $q->whereRaw("subway_stations && '{{$stationIds}}'");
                }
            ]
        ];

        // 售房
        if ($type === 'purchase') {
            return array_merge($base, [
                'prop' => [
                    'apply' => function ($q, $vals) {
                        $vals = array_map(function ($v) {
                            return strtoupper($v);
                        }, $vals);

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
                        $start = 0; $end = 0;

                        if (is_array($id)) { // 定制值
                            if (count($id) === 0) $id = [0, 0];
                            if (count($id) === 1) $id[] = 0;
                            $id[0] = intval($id[0]);
                            $id[1] = intval($id[1]);
                            list($start, $end) = $id;
                        } else {
                            if (!isset($opts[$id])) return;
                            list($start, $end) = $opts[$id];
                            $start *= 10000;
                            $end *= 10000;
                        }

                        $q->whereBetween('list_price', [$start, $end]);
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
                    $start = 0; $end = 0;

                    if (is_array($id)) { // 定制值
                        if (count($id) === 0) $id = [0, 0];
                        if (count($id) === 1) $id[] = 0;
                        $id[0] = intval($id[0]);
                        $id[1] = intval($id[1]);
                        list($start, $end) = $id;
                    } else {
                        if (!isset($opts[$id])) return;
                        list($start, $end) = $opts[$id];
                    }

                    $q->whereBetween('list_price', [$start, $end]);
                }
            ]
        ]);
    }
}