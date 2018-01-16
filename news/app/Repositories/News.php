<?php
namespace App\Repositories;

class News
{
    const STATUS_ENABLED = 1;

    public function all($areaId, $typeId = null, $page = 1, $pageSize = 15)
    {
        $offset = ($page - 1) * $pageSize;

        $query = app('db')->table('news')
            ->select('id', 'title', 'content', 'created_at as publish_at', 'hits')
            ->where('area_id', '@>', '{'.$areaId.'}')
            ->where('status', self::STATUS_ENABLED)
            ->orderBy('created_at', 'DESC')
            ->skip($offset)
            ->take($pageSize);

        if ($typeId) {
            $query->where('type_id', $typeId);
        }

        return $query;
    }

    public function hots($areaId, $limit = 10)
    {
        return $this->all($areaId, null, 1, $limit)
            ->where('is_hot', true);
    }

    public function get($areaId, $id)
    {
        return app('db')->table('news')
            ->select('title', 'content', 'created_at as publish_at', 'hits')
            ->where('area_id', '@>', '{'.$areaId.'}')
            ->where('id', $id)
            ->first();
    }
}