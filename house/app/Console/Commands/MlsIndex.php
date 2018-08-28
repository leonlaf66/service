<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class MlsIndex extends Command
{
    protected $signature = 'mls:index {mode=new}';
    protected $description = '索引mls数据';

    public function handle()
    {
        $mode  = $this->argument('mode', 'new');
        $query = app('db')->connection('pgsql2')
            ->table('mls_rets')
            ->select('update_date', 'est_sale', 'estimation', 'json_data')
            ->orderBy('list_no');

        if ($mode === 'new') {
            $lastUpdateAt = app('db')->table('house_index_v2')
                ->where('area_id', 'ma')
                ->max('update_at');
            if ($lastUpdateAt) {
                $lastUpdateAt = str_replace('+08', '', $lastUpdateAt);
                $lastUpdateAt = date('Y-m-d H:i:s', strtotime($lastUpdateAt) - 8 * 3600);

                $query->where('update_date', '>=', $lastUpdateAt);
            }
        }

        $self = $this;
        $total = $query->count();
        $query->chunk(1000, function ($rows) use ($self, $total){
            foreach ($rows as $row) {
                $self->processRow($row);
                $self->processMessageOutput($total);
            }
        });

        app('db')->connection('pgsql2')->disconnect();
        app('db')->disconnect();
    }

    public function processRow(& $row)
    {
        $jsonData = json_decode($row->json_data, true);
        $fieldMaps = $this->getFieldMaps();
        $indexData = [];
        foreach ($fieldMaps as $field => $callable) {
            $indexData[$field] = $callable($jsonData, $row, $indexData);
        }

        if (empty($indexData['prop_type'])) return;
        if (floatval($indexData['list_price']) <= 0) return;

        $listNo = array_get($jsonData, 'list_no');

        // 主表
        $skey = $indexData['skey'];
        unset($indexData['skey']);

        $table = app('db')->table('house_index_v2');
        if ($table->where('list_no', $listNo)->count() > 0) {
            $table->where('list_no', $listNo)->update($indexData);
        } else {
            $listNo = $table->insertGetId($indexData);
        }

        app('db')->update('update house_index_v2 set skey=to_tsvector(?) where list_no=?', [$skey, $listNo]);

        // 附数据表
        $table = app('db')->table('house_data');
        
        if ($table->where('list_no', $listNo)->count() > 0) {
            $table->where('list_no', $listNo)->update([
                'orgi_data' => object_get($row, 'json_data')
            ]);
        } else {
            $table->insert([
                'list_no' => array_get($indexData, 'list_no'),
                'orgi_data' => object_get($row, 'json_data')
            ]);
        }
    }

    public function getFieldMaps()
    {
        return [
            /*base 任可时候不可缺*/
            'list_no' => function ($d) {
                return array_get($d, 'list_no');
            },
            'list_price' => function ($d) {
                return array_get($d, 'list_price');
            },
            'prop_type' => function ($d) {
                return array_get($d, 'prop_type');
            },
            'city_id' => function ($d) {
                return app('App\Repositories\Mls\City')->findIdByCode('MA', array_get($d, 'town'));
            },
            'list_date' => function ($d) {
                $listDate = array_get($d, 'list_date');
                $listDate = str_replace('+00', '', $listDate);
                return date('Y-m-d H:i:s.u', strtotime($listDate) + 8 * 3600);
            },
            'no_beds' => function ($d) {
                return array_get($d, 'no_bedrooms');
            },
            'no_baths' => function ($d) {
                $full = array_get($d, 'no_full_baths', 0);
                $half = array_get($d, 'no_half_baths', 0);
                return "{{$full}, {$half}}";
            },
            'square_feet' => function ($d) {
                return array_get($d, 'square_feet');
            },
            'lot_size' => function ($d) {
                return array_get($d, 'lot_size');
            },
            'garage_spaces' => function ($d) {
                return array_get($d, 'garage_spaces');
            },
            'parking_spaces' => function ($d) {
                return array_get($d, 'parking_spaces');
            },
            'taxes' => function ($d) {
                return array_get($d, 'taxes');
            },
            'latlng' => function ($d) {
                $lat = array_get($d, 'latitude');
                $lon = array_get($d, 'longitude');
                if ($lat && $lon) {
                    return "{{$lat}, {$lon}}";
                }
            },
            'latlng_rad' => function ($d) {
                $lat = array_get($d, 'latitude');
                $lon = array_get($d, 'longitude');

                if ($lat && $lon) {
                    $latRad = deg2rad($lat);
                    $lonRad = deg2rad($lon);
                    return "{{$latRad}, {$lonRad}}";
                }
            },
            'subway_lines' => function ($d) {
                $lat = array_get($d, 'latitude');
                $lon = array_get($d, 'longitude');

                $subwayLineIds = [];
                if ($lat && $lon) {
                    $subwayLineIds = \App\Helpers\SubwayGeo::getMatchedLines($lon, $lat, 1);
                }
                $subwayLineIds = implode(',', $subwayLineIds);

                return "{{$subwayLineIds}}";
            },
            'subway_stations' => function ($d) {
                $lat = array_get($d, 'latitude');
                $lon = array_get($d, 'longitude');

                $subwayStationIds = [];
                if ($lat && $lon) {
                    $subwayStationIds = \App\Helpers\SubwayGeo::getMatchedStations($lon, $lat, 1);
                }
                $subwayStationIds = implode(',', $subwayStationIds);

                return "{{$subwayStationIds}}";
            },
            'sale_price' => function ($d) {
                return array_get($d, 'sale_price');
            },
            'ant_sold_date' => function ($d) {
                return array_get($d, 'ant_sold_date');
            },
            'status' => function ($d) {
                return array_get($d, 'status');
            },
            'order_rule' => function ($d) {
                switch (array_get($d, 'prop_type')) {
                    case 'RN':
                        return 1;
                    case 'SF':
                    case 'MF':
                    case 'CC':
                        return 2;
                    case 'CI':
                        return 3;
                    case 'BU':
                        return 4;
                    case 'LD':
                        return 5;
                }
                return 99;
            },
            'postal_code' => function ($d) {
                return array_get($d, 'zip_code');
            },
            'city_code' => function ($d) {
                return array_get($d, 'town');
            },
            'est_sale' => function ($d, $row) {
                return $row->est_sale;
            },
            'estimation' => function ($d, $row) {
                if (!$row->estimation) $row->estimation = '{}';
                $data = array_merge([
                    'est_rental' => null,
                    'est_roi' => null
                ], json_decode($row->estimation, true));
                if ($data['est_rental']) {
                    $data['est_rental'] *= 12;
                }
                if ($data['est_roi']) {
                    $data['est_roi'] = number_format($data['est_roi'], 4);
                }
                return json_encode($data);
            },
            'area_id' => function () {
                return 'ma';
            },
            'is_online_abled' => function($d, $row, $result) {
                if (!$result['city_id']) return false;
                if (intval($result['list_price']) === 0) return false; 
                return in_array(array_get($d, 'status'), ['ACT','NEW','BOM','PCG','RAC','EXT']);
            },
            'update_at' => function ($d, $row) {
                $lastUpdateAt = $row->update_date;
                $lastUpdateAt = str_replace('+00', '', $lastUpdateAt);
                return date('Y-m-d H:i:s.u', strtotime($lastUpdateAt) + 8 * 3600);
            },
            'index_at' => function () {
                return date('Y-m-d H:i:s');
            },
            'info' => function ($d, $row, $indexData) {
                $cityId = array_get($indexData, 'city_id');
                $cities = (function () {
                    static $cities = [];
                    if (empty($cities)) {
                        $cities = app('db')->table('town')
                            ->select('id', 'name', 'name_cn')
                            ->where('state', 'MA')
                            ->get()
                            ->keyBy('id')
                            ->map(function ($_d) {
                                return [$_d->name, $_d->name_cn];
                            });
                    }
                    return $cities;
                })();

                $isSd = (function ($cityId) {
                    static $sdCityIds = [];
                    if (empty($sdCityIds)) {
                        $sdCityIds = \App\Helpers\Sd::allCityIds();
                    }
                    return in_array($cityId, $sdCityIds);
                })($cityId);

                $location = (function ($d, $cityId) {
                    $propType = array_get($d, 'prop_type');

                    $fields = in_array($propType, ['RN', 'CC']) ? [
                        'street', 'unit_no', 'town', 'zip_code'
                    ] : [
                        'street', 'town', 'zip_code'
                    ];

                    $results = [];
                    foreach($fields as $field) {
                        $value = array_get($d, $field);
                        if($field == 'street') {
                            $value = array_get($d, 'street_num').' '.ucwords(strtolower(array_get($d, 'street_name')));
                            if (substr($value, strlen($value) - 1, 1) === '.') {
                                $value = substr($value, 0, strlen($value) - 1);
                            }
                            $value .= ',';
                        }
                        if($field == 'town') {
                            $value = app('\App\Repositories\Mls\City')->findNameById('MA', $cityId, true);
                        }
                        if($field == 'zip_code'){
                            $value = 'MA '.array_get($d, 'zip_code');
                        }
                        if($value) $results[] = $value;
                    }

                    return implode(' ', $results);
                })($d, $cityId);

                $data = [
                    'is_sd' => $isSd,
                    'loc' => $location,
                    'sub_prop_name' => '',
                    'city_name' => $cities[$cityId] ?? ['', ''],
                    'area' => $d['area'] ?? null,
                    'photo_count' => $d['photo_count']
                ];

                return json_encode($data);
            },
            'skey' => function ($d, $row, $indexData) {
                $info = json_decode($indexData['info'], true);
                $loc = trim(array_get($info, 'loc', ''));
                return preg_replace('/[^a-zA-Z0-9\s]/i', '', $loc);
            }
        ];
    }

    public function processMessageOutput($total)
    {
        static $current = 0;

        $current ++;
        $this->output->write("{$current}/{$total}\r");
    }
}