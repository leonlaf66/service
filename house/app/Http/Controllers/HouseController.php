<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HouseController extends Controller
{
    public function search (Request $req)
    {
        $params = array_merge([
            'type' => 'purchase',
            'page' => 1,
            'page_size' => 15,
            'filters' => [],
            'order' => ['list_date', 'desc']
        ], $req->all());

        $results = app('App\Repositories\HouseGeneralSearch')->search($params);

        return response()->json($results);
    }

    public function mapSearch(Request $req)
    {
        $params = array_merge([
            'type' => 'purchase',
            'limit' => 2000,
            'filters' => []
        ], $req->all());

        $results = app('App\Repositories\HouseMapSearch')->search($params);

        return response()->json($results);
    }

    public function get(Request $req, $id)
    {
        $results = null;

        $houseGet = app('App\Repositories\HouseGet');
        if ($req->get('simple') === '1') {
            $results = $houseGet->getSimple($id);
        } else {
            $results = $houseGet->get($id);
        }

        return response()->json($results);
    }

    public function nearbiy($id)
    {
        return response()->json(
            app('App\Repositories\HouseNearbiy')->all($id, 10)
        );
    }

    public function searchOptions($type)
    {
        $cacheKey = serialize([
            'search-options:',
            area_id(),
            $type
        ]);

        // Cache::forget($cacheKey);
        return Cache::remember($cacheKey, $cacheKey, function () use ($type) {
            $resType = area_id() === 'ma' ? 'Mls' : 'Listhub';
            $rawOptions = app("App\Repositories\\{$resType}\\City")->searchOptions(state_id(), $type);
            $options = [];
            foreach ($rawOptions as $text => $desc) {
                $options[] = ['title' => $text, 'desc' => $desc, 'flag' => false];
            }

            return response()->json([
                'hots' => get_static_data('hot-cities/'.area_id()),
                'options' => $options
            ]);
        });
    }

    public function top()
    {
        $results = app('App\Repositories\HouseTop')->all(area_id());
        return response()->json($results);
    }
}
