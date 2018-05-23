<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Uljx\Helpers\FieldRender;

class HouseController extends Controller
{
    public function search (Request $req)
    {
        $params = array_merge([
            'type' => 'purchase',
            'page' => 1,
            'page_size' => 10,
            'filters' => [],
            'order' => ['ldays', 'desc']
        ], $req->all());

        $outFields = $req->get('fields', 'id, nm, loc, beds, baths, square, lot_size, price, prop,status, l_days, tags, mls_id, area_id');

        $userId = $req->user() ? $req->user()->id : null;
        $results = app('App\Repositories\HouseGeneralSearch')->search($params, function ($d) use ($userId, $outFields) {
            $fieldRules = \Uljx\House\FieldRules::parse([
                'liked' => function ($d) use ($userId) {
                    return $userId ? $d->hasLike($userId) : false;
                }
            ]);
            return \Uljx\House\FieldRender::process($outFields, $fieldRules, $d);
        });

        return response()->json($results);
    }

    public function listByIds(Request $req)
    {
        $ids = $req->input('ids');
        $outFields = $req->get('fields', 'id, nm, loc, beds, baths, square, lot_size, price, prop, status, l_days, tags, mls_id, area_id');

        $query = \App\Models\HouseIndex::query();
        $query->whereIn('list_no', $ids);

        $collec = $query->get();
        return $collec->map(function ($d) use ($outFields) {
            $fieldRules = \Uljx\House\FieldRules::parse();
            return \Uljx\House\FieldRender::process($outFields, $fieldRules, $d);
        });
    }

    public function mapSearch(Request $req)
    {
        $params = array_merge([
            'type' => 'purchase',
            'limit' => 2000,
            'filters' => []
        ], $req->all());

        $results = app('App\Repositories\HouseMapSearch')->search($params, function ($d) {
            return implode('|', [
                $d->list_no,
                $d->prop_type,
                $d->list_price * 1.0 / 10000,
                $d->latlon ? substr($d->latlon, 1, strlen($d->latlon) - 2) : ''
            ]);
        });

        return response()->json($results);
    }

    public function get(Request $req, $id)
    {
        $defFields = 'id, nm, loc, price, prop, sub_tnm, beds, baths, square, lot_size, area, status,
                      l_days, latlng, img_cnt, est_sale, taxes, roi, details, liked, tour, mls_id';

        if ($req->get('simple', '0') === '1') {
            $defFields = 'id, nm, loc, price, prop, beds, baths, square, status, l_days, mls_id';
        }

        $outFields = $req->get('fields', $defFields);

        $userId = $req->user() ? $req->user()->id : null;

        $fieldRuels = \Uljx\House\FieldRules::parse([
            'liked' => function ($d) use ($userId) {
                return $userId ? $d->hasLike($userId) : false;
            },
            'tour' => function ($d) use($userId) {
                return $userId ? $d->getTour($userId, 0) : false;
            },
            'recommends' => function ($d) use ($req) {
                $recommendsOptions = $req->get('recommends_options', []);
                $outFields = array_get($recommendsOptions, 'fields', 'id, nm, loc, beds, baths, square, lot_size, price, prop, status, l_days, tags, area_id, mls_id');
                $limit = array_get($recommendsOptions, 'limit', 10);

                $collec = app('App\Repositories\HouseNearbiy')->all($d->list_no, $limit);
                return $collec->map(function ($d) use ($outFields) {
                    $fieldRules = \Uljx\House\FieldRules::parse();
                    return \Uljx\House\FieldRender::process($outFields, $fieldRules, $d);
                });
            },
            'polygons' => function ($d) {
                return $d->getFieldValue('polygons');
            }
        ]);

        $house = \App\Models\HouseIndex::findOrFail($id);
        $results = \Uljx\House\FieldRender::process($outFields, $fieldRuels, $house);

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

    public function nearbiy(Request $req, $id)
    {
        $outFields = $req->get('fields', 'id, nm, loc, beds, baths, square, price, prop, status, l_days, tags, mls_id');

        $collec = app('App\Repositories\HouseNearbiy')->all($id, 10);
        $results = $collec->map(function ($d) use ($outFields) {
            $fieldRules = \Uljx\House\FieldRules::parse();
            return \Uljx\House\FieldRender::process($outFields, $fieldRules, $d);
        });

        return response()->json($results);
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

    public function top(Request $req)
    {
        $areaId = area_id();
        $outFields = $req->get('fields', 'id, nm, loc, beds, baths, square, lot_size, price, prop, status, l_days, tags, mls_id');

        $collec = $results = app('App\Repositories\HouseTop')->all($areaId);

        $results = $collec->map(function ($d) use ($outFields) {
            $fieldRules = \Uljx\House\FieldRules::parse();
            return \Uljx\House\FieldRender::process($outFields, $fieldRules, $d);
        });

        return response()->json($results);
    }

    public function source($id)
    {
        $results = null;
        if (is_numeric($id)) {
            $results = app('db')->connection('pgsql2')
                ->table('mls_rets')
                ->select('list_no', 'prop_type', 'status', 'update_date')
                ->where(['list_no' => $id])
                ->first();
            if ($results) {
                $results->area_id = 'ma';
            }
        }
        
        if (!$results) {
            $results = app('db')->connection('pgsql2')
                ->table('mls_rets_listhub')
                ->select('list_no', 'state', 'status', 'last_update_date')
                ->where(['list_no' => $id])
                ->first();
        }

        return response()->json($results);
    }
}
