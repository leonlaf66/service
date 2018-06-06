<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class Summary extends Command
{
    protected $signature = 'summary:index';
    protected $description = '统计房源信息';

    public function handle()
    {   
        $services = [
            // 分学区统计数据
            'sdDataUpdate' => function ($sdCode, $path, $data) {
                $isExists = app('db')->table('schooldistrict_setting')
                    ->where('code', '=', $sdCode)
                    ->where('path', '=', $path)
                    ->exists();

                if ($isExists) {
                    return app('db')->table('schooldistrict_setting')
                        ->where('code', '=', $sdCode)
                        ->where('path', '=', $path)
                        ->update([
                            'data' => json_encode($data)
                        ]);
                } else {
                    return app('db')->table('schooldistrict_setting')
                        ->insert([
                            'code' => $sdCode,
                            'path' => $path,
                            'data' => json_encode($data)
                        ]);
                }
            },
            // 图形统计数据
            'writeDCharts' => function ($areaId, $rows) {
                app('db')->table('site_chart_setting')
                    ->where('area_id', '=', $areaId)
                    ->delete();

                foreach ($rows as $row) {
                    $row['area_id'] = $areaId;
                    app('db')->table('site_chart_setting')
                        ->insert($row);
                }
            }
        ];

        /*分学区统计*/
        $sdCodes = app('db')->table('schooldistrict')
            ->select('code')
            ->get('code');
        $sdCodes = collect($sdCodes)->map(function ($d) {
            return $d->code;
        });

        $townSummeries = $this->townSummeries();
        foreach ($sdCodes as $sdCode) {
            $towns = explode('/', $sdCode);
            foreach ($townSummeries as $summeryKey => $callable) {
                if ($value = $callable($towns, $this)) {
                    ($services['sdDataUpdate'])($sdCode, $summeryKey, $value);
                }
            }
        }

        /*分区域统计*/
        foreach (['ma', 'ny', 'ga', 'ca', 'il'] as $areaId) {
            $rows = [];
            $areaSummaries = $this->areaSummaries();
            foreach ($areaSummaries as $summeryKey => $callable) {
                if ($data = $callable($areaId, $this)) {
                    $data = json_encode($data);
                    $rows[] = ['path' => $summeryKey, 'data' => $data];
                }
            }

            /*图形统计*/
            ($services['writeDCharts'])($areaId, $rows);
        }

        /*log*/
        file_put_contents(__DIR__.'/../log.log', date('Y-m-d H:i:s').' summary'."\n", FILE_APPEND);

        app('db')->disconnect();
    }

    public function townSummeries()
    {
        return [
            // 平均房价
            'average-price' => function ($cityCodes) {
                return app('db')->table('house_index_v2')
                    ->where('area_id', '=', 'ma')
                    ->whereIn('city_code', $cityCodes)
                    ->whereIn('prop_type', ['SF','CC','MF'])
                    ->whereIn('status', ['ACT','NEW','BOM','PCG','RAC','EXT'])
                    ->where('list_price', '>', 10000)
                    ->avg('list_price');
            },
            // 平均月租
            'avergage-rental-price' => function ($cityCodes) {
                return app('db')->table('house_index_v2')
                    ->where('area_id', '=', 'ma')
                    ->whereIn('city_code', $cityCodes)
                    ->where('prop_type', '=', 'RN')
                    ->whereIn('status', ['ACT','NEW','BOM','PCG','RAC','EXT'])
                    ->where('list_price', '>', 100)
                    ->avg('list_price');
            },
            // 年成交量
            'year-down-total' => function ($cityCodes) {
                return app('db')->table('house_index_v2')
                    ->where('area_id', '=', 'ma')
                    ->whereIn('city_code', $cityCodes)
                    ->where('prop_type', '<>', 'RN')
                    ->where('status', '=', 'SLD')
                    ->whereRaw("ant_sold_date > now() - interval '1 year'")
                    ->count();
            },
            // 近三年学区房季度成交量
            'three-years-charts' => function ($cityCodes) {
                return null;
            },
            // 近五年学区房每季度房价走势
            'five-years-charts' => function ($cityCodes) {
                return null;
            },
            /*学区房源数量*/
            'total' => function ($cityCodes) {
                return app('db')->table('house_index_v2')
                    ->where('area_id', '=', 'ma')
                    ->whereIn('city_code', $cityCodes)
                    ->where('prop_type', '<>', 'RN')
                    ->whereIn('status', ['ACT','NEW','BOM','PCG','RAC','EXT'])
                    ->where('list_price', '>', 0)
                    ->count();
            },

            /* 房源详情 */
            // 近三年房价走势
            'three-years-charts' => function ($cityCodes) {
                return null;
            },
        ];
    }

    public function areaSummaries()
    {
        return [
            /* Home */
            // 房源均价
            'marketing/average-housing-price' => function ($areaId) {
                // 当前平均价格
                $avgPrice = app('db')->table('house_index_v2')
                    ->where('area_id', '=', $areaId)
                    ->whereIn('prop_type', ['SF','CC','MF'])
                    ->where('status', 'ACT')
                    ->where('list_price', '>', 10000)
                    ->avg('list_price');

                // 上月已售出平均价格
                $priorPirce = app('db')->table('house_index_v2')
                    ->where('area_id', '=', $areaId)
                    ->whereIn('prop_type', ['SF','CC','MF'])
                    ->where('status', '=', 'SLD')
                    ->where('list_price', '>', 10000)
                    ->whereRaw("ant_sold_date > now() - interval '1 month'")
                    ->avg('list_price');

                return [
                    'value'=>number_format($avgPrice, 0),
                    'dir' => $avgPrice > $priorPirce ? 'up' : 'down'
                ];
            },
            // 平均环比较上月
            'marketing/month-on-month-change' => function ($areaId) {
                // 2个月前
                $avgPrice1 = app('db')->table('house_index_v2')
                    ->where('area_id', '=', $areaId)
                    ->where('prop_type', '<>', 'RN')
                    ->where('status', '=', 'SLD')
                    ->where('list_price', '>', 10000)
                    ->whereRaw("ant_sold_date > now() - interval '2 month'")
                    ->whereRaw("ant_sold_date < now() - interval '1 month'")
                    ->avg('list_price');

                // 1个月前
                $avgPrice2 = app('db')->table('house_index_v2')
                    ->where('area_id', '=', $areaId)
                    ->where('prop_type', '<>', 'RN')
                    ->where('list_price', '>', 10000)
                    ->where('status', '=', 'SLD')
                    ->whereRaw("ant_sold_date > now() - interval '1 month'")
                    ->avg('list_price');

                if (is_null($avgPrice1)) {
                    return [
                        'value' => '0',
                        'dir' => 'up'
                    ];
                }

                if ($avgPrice2 >= $avgPrice1) { // 涨了多少
                    if (! $avgPrice1) $avgPrice1 = $avgPrice2;
                    return [
                        'value' => number_format($avgPrice2 / $avgPrice1, 2),
                        'dir' => 'up'
                    ];
                } else { // 跌了多少
                    if (! $avgPrice2) $avgPrice2 = $avgPrice1;
                    return [
                        'value' => number_format($avgPrice1 / $avgPrice2, 2),
                        'dir' => 'down'
                    ];
                }
            },
            // 上月成交量
            'marketing/prop-transactions-of-last-month' => function ($areaId) {
                // 2个月前
                $total1 = app('db')->table('house_index_v2')
                    ->where('area_id', '=', $areaId)
                    ->where('prop_type', '<>', 'RN')
                    ->where('status', '=', 'SLD')
                    ->whereRaw("ant_sold_date > now() - interval '2 month'")
                    ->whereRaw("ant_sold_date < now() - interval '1 month'")
                    ->where('list_price', '>', 0)
                    ->count();

                // 1个月前
                $total2 = app('db')->table('house_index_v2')
                    ->where('area_id', '=', $areaId)
                    ->where('prop_type', '<>', 'RN')
                    ->where('status', '=', 'SLD')
                    ->whereRaw("ant_sold_date > now() - interval '1 month'")
                    ->where('list_price', '>', 0)
                    ->count();

                return [
                    'value' => $total2,
                    'dir' => $total2 > $total1 ? 'up' : 'down'
                ];
            },
            // New Listings of this month
            'marketing/new-listings-of-this-month' => function ($areaId) {
                // 当前平均价格
                $count1 = app('db')->table('house_index_v2')
                    ->where('area_id', '=', $areaId)
                    ->whereIn('prop_type', ['SF','CC','MF'])
                    ->where('status', '=', 'ACT')
                    ->where('list_price', '>', 0)
                    ->whereRaw("list_date > now() - interval '1 month'")
                    ->count();

                // 上月已售出平均价格
                $count2 = app('db')->table('house_index_v2')
                    ->where('area_id', '=', $areaId)
                    ->whereIn('prop_type', ['SF','CC','MF'])
                    ->where('status', '=', 'SLD')
                    ->where('list_price', '>', 0)
                    ->whereRaw("ant_sold_date > now() - interval '1 month'")
                    ->count();

                return [
                    'value'=>$count1,
                    'dir' => $count2 > $count1 ? 'up' : 'down'
                ];
            }
        ];
    }
}