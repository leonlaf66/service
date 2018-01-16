<?php
namespace App\Repositories;

class HouseMapSearch extends HouseSearchAbstract
{
    public function search($params)
    {
        $query = app('db')->table('house_index_v2')
            ->select('list_no', 'list_price', 'prop_type', 'latlon');

        // 区域
        $query->where('area_id', area_id());

        // 类型
        if ($params['type'] === 'purchase') {
            $query->where('prop_type', '<>', 'RN');
        } else {
            $query->where('prop_type', 'RN');
        }

        // 筛选
        $filterRules = $this->getFilterRules($params['type']);
        foreach ($params['filters'] as $filterId => $filterVal) {
            if (isset($filterRules[$filterId])) {
                $filterRule = $filterRules[$filterId];
                $opts = $filterRule['options'] ?? [];
                ($filterRule['apply'])($query, $filterVal, $opts);
            }
        }

        // 其它
        $query->where('list_price', '>', 0);
        $query->whereNotNull('latlon');

        $query->orderBy('list_no', 'ASC');
        $query->limit($params['limit']);

        return $query->get()->map(function ($item) {
            return implode('|', [
                $item->list_no,
                $item->prop_type,
                $item->list_price * 1.0 / 10000,
                $item->latlon ? substr($item->latlon, 1, strlen($item->latlon) - 2) : ''
            ]);
            return 2;
        });
    }
}