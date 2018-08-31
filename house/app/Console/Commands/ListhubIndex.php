<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class ListhubIndex extends Command
{
    protected $signature = 'listhub:index {mode=new}';
    protected $description = '索引listhub数据';

    public function handle()
    {
        $mode  = $this->argument('mode', 'new');

        $query = app('db')->connection('pgsql2')
            ->table('mls_rets_listhub')
            ->select('list_no', 'state', 'prop_type', 'xml', 'status', 'latitude', 'longitude', 'last_update_date')
            ->whereIn('state', ['NY', 'GA', 'CA', 'IL'])
            ->orderBy('list_no');

        if ($mode === 'new') {
            $lastUpdateAt = app('db')->table('house_index_v2')
                ->whereIn('area_id', ['ny', 'ga', 'ca', 'il'])
                ->max('update_at');
            if ($lastUpdateAt) {
                $lastUpdateAt = str_replace('+08', '', $lastUpdateAt);
                $lastUpdateAt = date('Y-m-d H:i:s', strtotime($lastUpdateAt) - 8 * 3600);
                
                $query->where('last_update_date', '>=', $lastUpdateAt);
            }
        }

        $self = $this;
        $total = $query->count();
        $query->chunk(1000, function ($rows) use ($self, $total){
            foreach ($rows as $row) {
                $self->processRow($row);
                $self->processMessageOutput($total);
                unset($row);
            }
            unset($rows);
            sleep(3);
        });

        app('db')->connection('pgsql2')->disconnect();
        app('db')->disconnect();
    }

    public function processRow(& $row)
    {
        $fieldMaps = $this->getFieldMaps();
        $xmlString = $this->processXml($row->xml);
        $xmlDoc = @ simplexml_load_string($xmlString);

        $indexData = [];
        foreach ($fieldMaps as $field => $callable) {
            $indexData[$field] = $callable($xmlDoc, $row, $indexData);
        }
        unset($fieldMaps);

        if (!$indexData['prop_type'] || !$indexData['city_id']) {
            unset($indexData);
            return;
        }
        if (floatval($indexData['list_price']) <= 0) {
            unset($indexData);
            return;
        }

        $listNo = object_get($row, 'list_no');

        // 主表
        $table = app('db')->table('house_index_v2');
        
        if ($table->where('list_no', $listNo)->count() > 0) {
            $table->where('list_no', $listNo)->update($indexData);
        } else {
            $table->insert($indexData);
        }
        /*
        $skey = $indexData['skey'];
        unset($indexData['skey']);
        app('db')->update('update house_index_v2 set skey=to_tsvector(?) where list_no=?', [$skey, $listNo]);
        */
        // 附数据
        /*
        $table = app('db')->table('house_data');

        if ($table->where('list_no', $listNo)->count() > 0) {
            $table->where('list_no', $listNo)->update([
                'orgi_data' => $xmlString
            ]);
        } else {
            $table->insert([
                'list_no' => array_get($indexData, 'list_no'),
                'orgi_data' => $xmlString
            ]);
        }
        */

        //$this->processCases($xmlDoc, $row); // 缺失数据汇报给listhub官方

        unset($indexData);
    }

    public function getFieldMaps()
    {
        return [
            'list_no' => function ($d) {
                return get_xml_text($d, 'MlsNumber');
            },
            'list_price' => function ($d) {
                return get_xml_text($d, 'ListPrice');
            },
            'prop_type' => function ($d, $row) {
                $propTypeName = get_xml_text($d, 'PropertyType');
                $propSubTypeName = get_xml_text($d, 'PropertySubType');

                return get_listhub_prop_type($propTypeName, $propSubTypeName);
            },
            'city_id' => function ($d, $row) {
                $state = $row->state;
                $cityName = get_xml_text($d, 'Address/City');
                return app('App\Repositories\Listhub\City')->findIdByName($state, $cityName);
            },
            'mls_id' => function ($d, $row) {
                return get_xml_text($d, 'MlsId');
            }
            /*
            'area_id' => function ($d, $row) {
                return strtolower($row->state);
            },
            'list_date' => function ($d, $row) {
                $listDate = get_xml_text($d, 'ListingDate');
                if (!$listDate || strlen($listDate) === 0) {
                    $listDate = $row->last_update_date;
                }
                
                $listDate = str_replace('+00', '', $listDate);
                return date('Y-m-d H:i:s.u', strtotime($listDate) + 8 * 3600);
            },
            'no_beds' => function ($d) {
                return get_xml_text($d, 'Bedrooms');
            },
            'no_baths' => function ($d) {
                $full = get_xml_text($d, 'FullBathrooms');
                $half = get_xml_text($d, 'HalfBathrooms');
                return "{{$full}, {$half}}";
            },
            'square_feet' => function ($d) {
                return get_xml_text($d, 'LivingArea');
            },
            'lot_size' => function ($d) {
                return get_xml_text($d, 'LotSize');
            },
            'parking_spaces' => function ($d) {
                return get_xml_text($d, 'DetailedCharacteristics/NumParkingSpaces');
            },
            'taxes' => function ($d) {
                return get_xml_text($d, 'Taxes/Tax/Amount');
            },
            'latlng' => function ($d, $row) {
                $lat = object_get($row, 'latitude');
                $lon = object_get($row, 'longitude');
                if ($lat && $lon) {
                    return "{{$lat}, {$lon}}";
                }
            },
            'latlng_rad' => function ($d, $row) {
                $lat = object_get($row, 'latitude');
                $lon = object_get($row, 'longitude');

                if ($lat && $lon) {
                    $latRad = deg2rad($lat);
                    $lonRad = deg2rad($lon);
                    return "{{$latRad}, {$lonRad}}";
                }
            },
            'sale_price' => function ($d) {
                return array_get($d, 'sale_price');
            },
            'status' => function ($_, $row) {
                static $maps = [
                    'Active' => 'ACT',
                    'Cancelled' => 'CAN',
                    'Closed' => 'CLO',
                    'Expired' => 'EXP',
                    'Pending' => 'PEN',
                    'Withdrawn' => 'WDN',
                    'Sold' => 'SLD'
                ];

                $value = object_get($row, 'status');
                return array_get($maps, $value);
            },
            'ant_sold_date' => function ($_, $row, $result) {
                if (array_get($result, 'status') === 'SLD') {
                    return $row->last_update_date;
                }
                return null;
            },
            'order_rule' => function ($_, $_2, $result) {
                switch (array_get($result, 'prop_type')) {
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
            'postal_code' => function ($d, $row) {
                return object_get($d, 'zip_code');
            },
            'parent_city_id' => function ($d, $row) { // 处理CA的子城市
                if ($row->state !== 'CA') {
                    return null;
                }

                $cityName = get_xml_text($d, 'Address/City');
                if (!$cityName) {
                    return 0;
                }

                return app('db')->table('city')
                    ->select('parent_id')
                    ->where('state', $row->state)
                    ->where('name', $cityName)
                    ->orderBy('type_rule', 'asc')
                    ->orderBy('id', 'ASC')
                    ->limit(1)
                    ->value('parent_id');
            },
            'is_online_abled' => function($d, $row, $result) {
                if (!$result['city_id']) return false;
                if (intval($result['list_price']) === 0) return false;
                
                return array_get($result, 'status') === 'ACT';
            },
            'update_at' => function ($d, $row) {
                $lastUpdateAt = $row->last_update_date;
                $lastUpdateAt = str_replace('+00', '', $lastUpdateAt);
                return date('Y-m-d H:i:s.u', strtotime($lastUpdateAt) + 8 * 3600);
            },
            'index_at' => function () {
                return date('Y-m-d H:i:s');
            },
            'info' => function ($d, $row, $indexData) {
                $state = strtoupper(array_get($indexData, 'area_id'));
                $cityId = array_get($indexData, 'city_id');
                $cities = (function ($state) {
                    static $cities = [];
                    if (empty($cities)) {
                        $cities = app('db')->table('city')
                            ->select('id', 'name', 'name_cn')
                            ->where('state', $state)
                            ->get()
                            ->keyBy('id')
                            ->map(function ($_d) {
                                return [$_d->name, $_d->name_cn];
                            });
                    }
                    return $cities;
                })($state);

                $location = (function ($d) {
                    $address = $d->Address;
                    return implode(' ', [
                        $address->FullStreetAddress->__toString().', '.$address->City->__toString(),
                        $address->StateOrProvince->__toString(),
                        $address->PostalCode->__toString()
                    ]);
                })($d);

                $photoCount = count($d->xpath('Photos/Photo'));
                $photoCount = $photoCount > 0 ? $photoCount : 1;

                $data = [
                    'is_sd' => false,
                    'loc' => $location,
                    'city_name' => $cities[$cityId] ?? ['', ''],
                    'area' => null,
                    'photo_count' => $photoCount
                ];

                return json_encode($data);
            },
            'skey' => function ($d, $row, $indexData) {
                $info = json_decode($indexData['info'], true);
                $loc = trim(array_get($info, 'loc', ''));
                return preg_replace('/[^a-zA-Z0-9\s]/i', '', $loc);
            }
            */
        ];
    }

    public function processMessageOutput($total)
    {
        static $current = 0;

        $current ++;
        $this->output->write("{$current}/{$total}\r");
    }

    // 缺失数据汇报给listhub官方
    public function processCases($d, $row)
    {
        static $configs = [];
        if (empty($configs)) {
            $configs = [
                'ListPrice' => 'ListPrice',
                'Address/City' => 'City',
                'ListingDate' => 'ListingDate',
                'Bedrooms' => '!LD:Bedrooms',
                'LivingArea' => '!LD:LivingArea',
                'LotSize' => 'LD:LotSize',
                'Address/PostalCode' => 'PostalCode'
            ];
        }

        $listNo = get_xml_text($d, 'ListingKey');
        if (app('db')->table('listhub_cases')->where('list_no', $listNo)->exists()) {
            return false;
        }

        $propTypeName = get_xml_text($d, 'PropertyType');
        $propSubTypeName = get_xml_text($d, 'PropertySubType');
        $propTypeCode = get_listhub_prop_type($propTypeName, $propSubTypeName);

        $data = [
            'list_no' => get_xml_text($d, 'ListingKey'),
            'prop_type' => $propTypeCode,
            'unkowns' => [],
            'updated_at' => $row->last_update_date
        ];

        foreach ($configs as $xmlPath => $fieldKey) {
            if (is_null(get_xml_text($d, $xmlPath))) {
                if (strpos($fieldKey, ':') !== false) {
                    $exps = explode(':', $fieldKey);
                    $ruld = $exps[0];
                    $fieldKey = $exps[1];
                    if ($ruld === '!'.$propTypeCode) {
                        continue;
                    }
                    if ($ruld !== $propTypeCode) {
                        continue;
                    }
                }
                $data['unkowns'][] = $fieldKey;
            }
        }

        if (empty($data['unkowns'])) {
            return false;
        }

        $data['unkowns'] = '{'.implode(',', $data['unkowns']).'}';

        app('db')->table('listhub_cases')->insert($data);
    }

    public function processXml($xml)
    {
        $clearTags = [' xmlns="http://rets.org/xsd/Syndication/2012-03" xmlns:commons="http://rets.org/xsd/RETSCommons"', 'commons:'];
        foreach ($clearTags as $clearTag) {
            if (false !== strpos($xml, $clearTag)) {
                $xml = str_replace($clearTag, '', $xml);
            }
        }
        return '<?xml version="1.0" encoding="UTF-8"?>'.$xml;
    }
}
