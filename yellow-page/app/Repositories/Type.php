<?php
namespace App\Repositories;

class Type
{
    const TAXONMY_ID = 2;
    const STATUS_ENABLED = 0;

    /**
     * 获取所有分类
     * @return mixed
     */
    public function all()
    {
        return app('db')->table('taxonomy_term')
            ->select('id', 'name', 'name_zh')
            ->where('taxonomy_id', self::TAXONMY_ID)
            ->where('status', self::STATUS_ENABLED)
            ->where('parent_id', 0)
            ->orderBy('sort_order', 'ASC');
    }
}