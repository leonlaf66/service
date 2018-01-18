<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class ListhubIndex extends Command
{
    protected $signature = 'listhub:index {mode=new}';
    protected $description = '索引listhub数据';

    public function handle()
    {
        $mode  = $this->argument('mode');

        $query = app('db')->connection('pgsql2')
            ->table('mls_rets_listhub')
            ->select('list_no', 'state', 'prop_type', 'xml', 'status', 'latitude', 'longitude', 'last_update_date')
            ->whereIn('state', ['NY', 'GA', 'CA', 'IL'])
            ->orderBy('list_no');

        if ($mode !== 'all') {
            $lastUpdateAt = app('db')->table('house_index_v2')
                ->where('area_id', 'ma')
                ->max('update_at');
            if ($lastUpdateAt) {
                $query->where('last_update_date', '>', $lastUpdateAt);
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

        $listNo = object_get($row, 'list_no');

        // 主表
        $table = app('db')->table('house_index_v2');
        if ($table->where('list_no', $listNo)->count() > 0) {
            $table->where('list_no', $listNo)->update($indexData);
        } else {
            $table->insert($indexData);
        }

        // 附数据
        $table = app('db')->table('house_data');
        $addiData = [
            'list_no' => array_get($indexData, 'list_no'),
            'listhub_data' => $xmlString
        ];
        if ($table->where('list_no', $listNo)->count() > 0) {
            $table->where('list_no', $listNo)->update($addiData);
        } else {
            $table->insert($addiData);
        }

        unset($addiData);
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
            'list_date' => function ($d, $row) {
                $listDate = get_xml_text($d, 'ListingDate');
                if (!$listDate || strlen($listDate) === 0) {
                    $listDate = $row->last_update_date;
                }
                return $listDate;
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
            'prop_type' => function ($d, $row) {
                $propTypeName = get_xml_text($d, 'PropertyType');
                $propSubTypeName = get_xml_text($d, 'PropertySubType');

                return get_listhub_prop_type($propTypeName, $propSubTypeName);
            },
            'latlon' => function ($d, $row) {
                $lat = object_get($row, 'latitude');
                $lon = object_get($row, 'longitude');
                if ($lat && $lon) {
                    return "{{$lat}, {$lon}}";
                }
            },
            'latlon_rad' => function ($d, $row) {
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
            'city_id' => function ($d, $row) {
                $state = $row->state;
                $cityName = get_xml_text($d, 'Address/City');
                return app('App\Repositories\Listhub\City')->findIdByName($state, $cityName);
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
            'area_id' => function ($d, $row) {
                return strtolower($row->state);
            },
            'is_online_abled' => function($d, $row, $result) {
                return array_get($result, 'status') === 'SLD';
            },
            'update_at' => function ($d, $row) {
                return $row->last_update_date;
            },
            'index_at' => function () {
                return date('Y-m-d H:i:s');
            }
        ];
    }

    public function processMessageOutput($total)
    {
        static $current = 0;

        $current ++;
        $this->output->write("{$current}/{$total}\r");
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
