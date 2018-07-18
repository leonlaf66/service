<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Dashboard as DashboardRepository;

class DashboardController extends Controller
{
    public function all (Request $req)
    {
        $cacheKey = 'pc.dashboard.data.'.lang_id();
        if ($req->get('cache-flush', 'no') === 'yes') {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 86400, function () {
            $houses = DashboardRepository::houses();
            $newses = DashboardRepository::newses(10);

            $results = [];
            foreach (['ma', 'ny', 'ca', 'ga', 'il'] as $areaId) {
              $results[$areaId] = [
                'houses' => [],
                'houses2' => DashboardRepository::housesIn($areaId),
                'newses' => []
              ];
            }

            foreach ($houses as $areaId => $items) {
              $results[$areaId]['houses'] = array_merge($results[$areaId]['houses'], $items);
            }

            foreach ($newses as $areaId => $items) {
              $results[$areaId]['newses'] = array_merge($results[$areaId]['newses'], $items);
            }

            return $results;
        });
    }
}
