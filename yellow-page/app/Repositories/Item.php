<?php
namespace App\Repositories;

class Item
{
    /**
     * 获取推荐
     * @param $areaId
     * @return mixed
     */
    public function tops($areaId)
    {
        return app('db')->table('yellow_page as e')
            ->join('yellow_page_type as ypt', 'e.id', '=', 'ypt.yellow_page_id')
            ->select('e.id', 'e.name', 'e.business', 'e.business_cn', 'e.rating', 'e.photo_hash', 'ypt.type_id')
            ->where('e.area_id', $areaId)
            ->where('e.is_top', 1)
            ->limit(10);
    }
    /**
     * 获取指定分类的列表
     * @param null $typeId
     * @return array
     */
    public function all($areaId, $typeId, $page = 1, $pageSize = 15)
    {
        $offset = ($page - 1) * $pageSize;

        return app('db')->table('yellow_page as e')
            ->select('e.id', 'e.name', 'e.business', 'e.business_cn', 'e.rating', 'e.photo_hash', 't.type_id',
                     'e.address', 'e.contact', 'e.license', 'e.phone', 'e.comments', 'e.hits')
            ->join('yellow_page_type as t', function ($join) use ($typeId) {
                $join->on('e.id', '=', 't.yellow_page_id');
                if (intval($typeId)) {
                    $join->where('t.type_id', $typeId);
                }
            })
            ->where('e.area_id', $areaId)
            ->offset($offset)
            ->limit($pageSize);
    }

    /**
     * 详情
     * @param $areaId
     * @param $id
     */
    public function get($areaId, $id)
    {
        $d = app('db')->table('yellow_page as e')
            ->select('e.*', 'tt.id as type_id', 'tt.name as type_name', 'tt.name_zh as type_name_cn')
            ->leftJoin('yellow_page_type as t', 'e.id', '=', 't.yellow_page_id')
            ->leftJoin('taxonomy_term as tt', 't.type_id', '=', 'tt.id')
            ->where('e.area_id', $areaId)
            ->where('e.id', $id)
            ->first();

        $typeName = preg_replace('/\[.*\]/', '', $d->type_name);

        $d->type = [
            'id' => $d->type_id,
            'name' => tt($typeName, $d->type_name_cn)
        ];
        if (empty($d->business_cn)) {
            $d->business_cn = $d->business;
        }
        $d->business = [$d->business, $d->business_cn];
        $d->photo_url = media_url('yellowpage/placeholder.jpg');
        unset($d->business_cn,
            $d->is_top,
            $d->longitude,
            $d->latitude,
            $d->weight,
            $d->area_id,
            $d->photo_hash,
            $d->type_id,
            $d->type_name,
            $d->type_name_cn);

        return $d;
    }
}