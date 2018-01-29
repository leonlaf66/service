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

    public function listByIds(Request $req)
    {
        $ids = $req->input('ids');

        $query = \App\Models\HouseIndex::query();
        $query->whereIn('list_no', $ids);

        $houseCollec = $query->get();
        return $houseCollec->map(function ($d) {
            return [
                'id' => $d->list_no,
                'nm' => $d->getFieldValue('name'),
                'loc' => $d->getFieldValue('location'),
                'beds' => $d->no_beds,
                'baths' => $d->no_baths,
                'square' => $d->square_feet,
                'lot_size' => $d->lot_size,
                'price' => $d->list_price,
                'prop' => $d->prop_type,
                'status' => $d->status,
                'l_days' => intval((time() - strtotime($d->list_date)) / 86400),
                'tags' => $d->getFieldValue('tags'),
                'mls_id' => $d->getFieldValue('mls_id'),
                'area_id' => $d->area_id
            ];
        });
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
            $userId = $req->user() ? $req->user()->id : null;
            $results = $houseGet->get($id, $userId);
        }

        return response()->json($results);
    }

    public function like(Request $req, $id)
    {
        $result = false;
        $status = $req->get('status'); // 1 or 0
        if ($req->user()) {
            $house = \App\Models\HouseIndex::findOrFail($id);
            if ($house) {
                $userId = $req->user()->id;
                if ($status === '1') {
                    if (!$house->hasLike($userId)) {
                        $result = $house->addLike($userId);
                    }
                } else {
                    $result = $house->removeLike($userId);
                }
            }
        }

        return response()->json($result);
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
