<?php

namespace App\Http\Controllers;

class AreaController extends Controller
{
    public function all ()
    {
        return response()->json([
            'options' => [
                'image_base_url' => env('MEDIA_BASE_URL').'/area/'
            ],
            'items' => get_static_data('areas')
        ]);
    }

    public function hotCities()
    {
        $cityNames = get_static_data('hot-cities/'.area_id());
        
        $groups = [];
        if (area_id() === 'ma') {
            // 获取原始数据
            $rows = app('db')->table('town')
                ->where('state', '=', 'MA')
                ->whereIn('name', $cityNames)
                ->get();
            
            // 按name分组
            $groups = [];
            foreach ($rows as $row) {
                $groups[$row->name] = $row;
            }
        } else {
            // 获取原始数据
            $rows = app('db')->table('city')
                ->where('state', '=', strtoupper(area_id()))
                ->whereIn('name', $cityNames)
                ->get();
            
            // 按name分组
            $groups = [];
            foreach ($rows as $row) {
                $groups[$row->name] = $row;
            }
        }

        // 处理最终结果
        $results = [];
        foreach ($cityNames as $name) {
            if (isset($groups[$name])) {
                $row = $groups[$name];
                $results[$row->id] = is_english() ? $row->name : ($row->name_cn ?? $row->name);
            }   
        }

        return response()->json($results);
    }
}
