<?php
namespace App\Repositories;

class Dashboard
{
    public static function houses($limit = 3)
    {
        // 获取分组数据
        $groups = app('db')->table('site_setting')
            ->select('site_id', 'value')
            ->where('path', 'home.luxury.houses')
            ->get()
            ->keyBy('site_id')
            ->map(function ($d) use ($limit){
                $items = json_decode($d->value);
                // $items = array_slice($items, 0, $limit);
                return array_map(function ($d) {
                    return $d->id;
                }, $items);
            })
            ->toArray();

        // 所有listno
        $allListNos = [];
        foreach ($groups as $items) {
            foreach ($items as $listNo) {
                $allListNos[] = $listNo;
            }
        }

        // 取房源数据
        $fields = 'id, nm, prop, mls_id, area_id';
        $query = \App\Models\HouseIndex::query();
        $query->whereIn('list_no', $allListNos);

        $collec = $query->get();
        $allItems = $collec->map(function ($d) use ($fields) {
            $fieldRules = \Uljx\House\FieldRules::parse();
            return \Uljx\House\FieldRender::process($fields, $fieldRules, $d);
        })->keyBy('id');

        // 合并到分组
        return array_map(function ($items) use ($allItems) {
            return array_map(function ($listNo) use ($allItems) {
                return $allItems[$listNo] ?? [];
            }, $items);
        }, $groups);
    }

    public static function housesIn($areaId, $limit = 5)
    {
        $results = [];
        $fields = 'id, nm, prop, mls_id, area_id';

        $query = \App\Models\HouseIndex::query();
        $query->where('area_id', $areaId);
        $query->whereIn('prop_type', ['MF', 'SF', 'CC']);
        $query->whereNotNull('city_id');
        $query->where('list_price', '>', 30000);
        $query->where(['is_online_abled' => true]);
        $query->orderBy('update_at', 'desc');
        $query->limit($limit);

        $collec = $query->get();
        return $collec->map(function ($d) use ($fields) {
            $fieldRules = \Uljx\House\FieldRules::parse();
            return \Uljx\House\FieldRender::process($fields, $fieldRules, $d);
        })->keyBy('id')->toArray();
    }

    public static function newses($limit = 10)
    {
        $results = [];
        foreach (['ma', 'ny', 'ca', 'il', 'ga'] as $areaId) {
            $results[$areaId] = app('db')->table('news')
                ->select('id', 'title')
                ->where('area_id', '@>', '{'.$areaId.'}')
                ->where('status', '1')
                ->orderBy('updated_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        }
        return $results;
    }
}