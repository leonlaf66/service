<?php
namespace App\Repositories;

class HouseGeneralSearch extends HouseSearchAbstract
{
    public function search($params, $callback)
    {
        $query = \App\Models\HouseIndex::query();

        // 区域
        $query->where('area_id', area_id());

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
            } elseif (preg_match('/[a-zA-Z]{0,2}[0-9]{5,10}/', $q)) { // 是list_no
                $query->where('list_no', $q);
            } else { // 当做城市名
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
                    $query->whereRaw('1=2');
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
                /* return [
                    'id' => $item->list_no,
                    'nm' => $item->getFieldValue('name'),
                    'loc' => $item->getFieldValue('location'),
                    'beds' => $item->no_beds,
                    'baths' => $item->no_baths,
                    'square' => $item->square_feet,
                    'lot_size' => $item->lot_size,
                    'price' => $item->list_price,
                    'prop' => $item->prop_type,
                    'status' => $item->status,
                    'l_days' => intval((time() - strtotime($item->list_date)) / 86400),
                    'tags' => $item->getFieldValue('tags'),
                    'mls_id' => $item->getFieldValue('mls_id')
                ]; */
            });
        });
    }
}