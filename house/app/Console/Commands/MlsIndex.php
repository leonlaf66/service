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
            ->select('update_date', 'json_data')
            ->orderBy('list_no');

        if ($mode === 'new') {
            $lastUpdateAt = app('db')->table('house_index_v2')
                ->where('area_id', 'ma')
                ->max('update_at');
            if ($lastUpdateAt) {
                $query->where('update_date', '>', $lastUpdateAt);
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
    }

    public function processRow(& $row)
    {
        $jsonData = json_decode($row->json_data, true);
        $fieldMaps = $this->getFieldMaps();
        $indexData = [];
        foreach ($fieldMaps as $field => $callable) {
            $indexData[$field] = $callable($jsonData, $row);
        }

        $listNo = array_get($jsonData, 'list_no');

        // 主表
        $table = app('db')->table('house_index_v2');
        if ($table->where('list_no', $listNo)->count() > 0) {
            $table->where('list_no', $listNo)->update($indexData);
        } else {
            $table->insert($indexData);
        }

        // 主数据表
        $table = app('db')->table('house_data');
        $indexData = [
            'list_no' => array_get($indexData, 'list_no'),
            'mls_data' => object_get($row, 'json_data')
        ];
        if ($table->where('list_no', $listNo)->count() > 0) {
            $table->where('list_no', $listNo)->update($indexData);
        } else {
            $table->insert($indexData);
        }
    }

    public function getFieldMaps()
    {
        return [
            'list_no' => function ($d) {
                return array_get($d, 'list_no');
            },
            'list_price' => function ($d) {
                return array_get($d, 'list_price');
            },
            'list_date' => function ($d) {
                return array_get($d, 'list_date');
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
            'prop_type' => function ($d) {
                return array_get($d, 'prop_type');
            },
            'latlon' => function ($d) {
                $lat = array_get($d, 'latitude');
                $lon = array_get($d, 'longitude');
                if ($lat && $lon) {
                    return "{{$lat}, {$lon}}";
                }
            },
            'latlon_rad' => function ($d) {
                $lat = array_get($d, 'latitude');
                $lon = array_get($d, 'longitude');

                if ($lat && $lon) {
                    $latRad = deg2rad($lat);
                    $lonRad = deg2rad($lon);
                    return "{{$latRad}, {$lonRad}}";
                }
            },
            'subway_lines' => function ($d) {
                return '{}';
            },
            'subway_stations' => function ($d) {
                return '{}';
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
            'city_id' => function ($d) {
                return app('App\Repositories\Mls\City')->findIdByCode('MA', array_get($d, 'town'));
            },
            'area_id' => function () {
                return 'ma';
            },
            'is_online_abled' => function($d) {
                return in_array(array_get($d, 'status'), ['ACT','NEW','BOM','PCG','RAC','EXT']);
            },
            'update_at' => function ($d, $row) {
                return $row->update_date;
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
}