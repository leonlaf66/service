<?php
return [
    'list_no' => [
        'title' => tt('List no', '房源号'),
        'index' => '@list_no'
    ],
    'name' => [
        'title' => tt('Name', '名称'),
        'value' => function ($d, $m) {
            $names = [
                'left' => [], 'right' => []
            ];
            if ($cityName = $m->getFieldValue('city_name')) {
                $names['left'][] = $cityName;
            }
            if ($propTypeName = $m->getFieldValue('prop_type_name')) {
                $names['left'][] = $propTypeName;
            }
            if ($beds = $m->no_beds) {
                $names['right'][] = is_english() ? $beds.' bed' : $beds.'室';
            }
            if ($baths = $m->no_baths) {
                if ($baths[0] > 0) {
                    $fullBaths = $baths[0];
                    $names['right'][] = is_english() ? $fullBaths.' bath' : $fullBaths . '卫';
                }
            }

            $wordSpace = is_english() ? ' ' : '';
            $names['left'] = implode($wordSpace, $names['left']);
            $names['right'] = implode($wordSpace, $names['right']);

            if ($names['right'] !== '')
                return implode(',', $names);
            return $names['left'];
        }
    ],
    'mls_id' => [
        'value' => function ($d, $m) {
            // 无
        }
    ],
    'location' => [
        'title' => tt('Location', '位置'),
        'value' => function ($d, $m) {

        }
    ],
    'latlon' => [
        'title' => '',
        'value' => function ($d, $m) {
            return $m->latlng;
        }
    ],
    'prop_type_name' => [
        'title' => tt('Property', '类型'),
        'value' => function ($d, $m) {
            return tt(config('house.prop_types.'.$m->prop_type));
        }
    ],
    'photo_url' => [
        'title' => tt('Photo url', '图片网址')
    ],
    'photo_urls' => [
        'title' => tt('Photos', '图片列表')
    ],
    'list_days' => [
        'title' => tt('Days on market', '上市时间'),
        'value' => function ($d, $m) {
            if (!$m->list_date) return null;
            return intval((time() - strtotime($m->list_date)) / 86400);
        }
    ],
    'est_sale' => [
        'title' => tt('UStamite', '米乐居估价'),
        'value' => function ($d, $m) {
            return $m->est_sale;
        }
    ],
    'status_name' => [
        'title' => tt('Status', '标题'),
        'value' => function ($d, $m) {
            $status = $m->status;
            if ($status === 'SLD')
                return is_english() ? 'Sold' : '已销售';

            if($status === 'NEW') {
                if ($m->prop_type === 'LD')
                    return is_english() ? 'New' : '新的';

                return is_english() ? 'New' : '新房源';
            }

            return is_english() ? 'Active' : '销售中';
        }
    ],
    'tags' => [
        'title' => tt('Tags', '标签'),
        'value' => function ($d, $m) {
            $tags = '00000';
            // 学区房
            /*
            $areaCodes = \models\SchoolDistrict::allCodes();
            if (in_array($this->town, $areaCodes)) {
                $tags[0] = '1';
            }*/

            // 卧室
            if (intval($m->no_beds) >= 3) {
                $tags[1] = '1';
            }

            // 车位
            if (intval($m->parking_spaces) >= 2) {
                $tags[2] = '1';
            }

            // 车库
            if (intval($m->garage_spaces) > 0) {
                $tags[3] = '1';
            }

            // 高级豪宅
            if (in_array($m->prop_type, ['CC', 'SF']) && intval($m->list_price) > 1000000) {
                $tags[4] = '1';
            }

            return $tags;
        }
    ],
    'no_bedrooms' => [
        'title' => tt('Bedrooms', '卧室数'),
        'value' => function ($d, $m) {
            return $m->no_beds;
        }
    ],
    'no_full_baths' => [
        'title' => tt('Full Bathrooms', '全卫'),
        'value' => function ($d, $m) {
            return $m->no_baths[0] ?? 0;
        }
    ],
    'no_half_baths' => [
        'title' => tt('Half Bathrooms', '半卫'),
        'value' => function ($d, $m) {
            return $m->no_baths[1] ?? 0;
        }
    ],
    'square_feet' => [
        'title' => tt('Square Feet', '面积'),
        'value' => function ($d, $m) {
            return $m->square_feet;
        },
        'format' => 'sq.ft'
    ],
    'lot_size' => [
        'title' => tt('Lot Size', '面积'),
        'value' => function ($d, $m) {
            return $m->lot_size;
        },
        'format' => 'sq.ft'
    ],
    'list_price' => [
        'title' => tt('Price', '价格'),
        'value' => function ($d, $m) {
            return $m->list_price;
        },
        'format' => 'money'
    ],
    'city_name' => [
        'title' => tt('City', '城市'),
        'value' => function ($d, $m) {
            $resType = $m->area_id === 'ma' ? 'Mls' : 'Listhub';
            return app("App\\Repositories\\{$resType}\\City")->findNameById(state_id(), $m->city_id);
        }
    ],
    'roi' => [
        'title' => tt('Roi', '投资回报率')
    ],
    'polygons' => [
        'title' => 'City Polygons',
        'value' => function ($d, $m) {
            if (!$m->city_id) return [];
            $cityName = null;
            if ($m->area_id === 'ma') {
                $cityName = app('db')->table('town')->select('name')->where('id', $m->city_id)->value('name');
                $cityName = str_replace(' ', '-', $cityName);
                $cityName = strtolower($cityName);
                return get_static_data('polygons/'.strtoupper($m->area_id).'/'.$cityName);
            }
            return [];
        }
    ]
];