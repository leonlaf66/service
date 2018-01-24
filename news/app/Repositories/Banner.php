<?php
namespace App\Repositories;

class Banner
{
    public function all($areaId)
    {
        $config = app('db')->table('site_setting')
            ->select('value')
            ->where('site_id', $areaId)
            ->where('path', 'app.news.banner.top')
            ->value('value');

        $config = json_decode($config, true);
        $items = $config['childrens'] ?? [];

        return array_map(function ($item) {
            $item['news_id'] = intval(trim($item['url'], '#'));
            unset($item['url']);
            return $item;
        }, $items);
    }
}