<?php
namespace App\Repositories;

class HouseGeneralSearch extends HouseSearchAbstract
{
    public function search($params, $callback)
    {
        $query = \App\Models\HouseIndex::query();

        // 区域
        $query->where('area_id', area_id());
        $query->whereNotNull('prop_type');
        $query->whereNotNull('city_id');

        // 类型
        if ($params['type'] === 'purchase') {
            $query->where('prop_type', '<>', 'RN');
        } else {
            $query->where('prop_type', 'RN');
        }

        // 查询
        if (isset($params['q']) && !empty($params['q'])) {
            $q = $params['q'];

            if (is_numeric($q) && strlen($q) === 5) { // 是邮编
                $query->where('postal_code', $q);
            } elseif (preg_match('/^[A-Z]{0,2}[0-9]{5,10}$/', $q, $sds)) { // 是list_no
                $query->where('list_no', $q);
            } else { // 当做城市名
                $q = ucwords(strtolower($q));
                if ($cityId = get_house_adapter('City')->findIdByName(state_id(), $q)) {
                    if (area_id() === 'ca') {
                        $query->where(function ($query) use ($cityId) {
                            $query->where('city_id', $cityId);
                            $query->orWhere('parent_city_id', $cityId);
                        });
                    } else {
                        $query->where('city_id', $cityId);
                    }
                } else {
                    /*
                    select list_no, info->'loc' as loc 
                        from house_index_v2
                        where skey @@ to_tsquery('english', 'boston&commonwealth&ave')
                        order by skey <=> to_tsquery('english', 'boston&commonwealth&ave')
                        limit 10;
                    */
                    $q = str_replace("'", '', $q);
                    $q = preg_replace('/[\s]/i', '&', $q);
                    $skey = "to_tsquery('english', '{$q}')";
                    $query->whereRaw("skey @@ {$skey}");
                    $query->orderByRaw("{$skey} ASC");
                }
            }
        }

        // 筛选
        if (isset($params['filters']) && !empty($params['filters'])) {
            $filters = $params['filters'];
            if (is_string($filters)) $filters = json_decode($filters, true);
            if (!is_array($filters)) $filters = [];

            if (count($filters) > 0) {
                $filterRules = $this->getFilterRules($params['type'] ?? 'purchase');
                foreach ($filters as $filterId => $filterVal) {
                    if (isset($filterRules[$filterId])) {
                        $filterRule = $filterRules[$filterId];
                        $opts = $filterRule['options'] ?? [];
                        ($filterRule['apply'])($query, $filterVal, $opts);
                    }
                }
            }
        }

        // 其它
        $query->where('list_price', '>', 0);
        $query->where(['is_online_abled' => true]);

        // 排序
        $sortFields = [
            'ldays' => 'list_date',
            'price' => 'list_price',
            'beds' => 'no_beds'
        ];
        list($sortAttr, $sortDir) = $params['sort'] ?? ['ldays', 'desc'];
        if (isset($sortFields[$sortAttr])) {
            $sortDir === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortFields[$sortAttr], $sortDir);
        }
        $query->orderBy('list_no', 'desc');

        return \App\Helpers\Pager::load($query, $params['page'], $params['page_size'], function ($collection) use ($callback) {
            return $collection->map(function ($item) use ($callback) {
                return $callback($item);
            });
        });
    }
}