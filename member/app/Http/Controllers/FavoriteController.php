<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function list(Request $req)
    {
        $userId = $req->user()->id;

        $query = app('db')->table('house_member_favority')
            ->select('id', 'list_no', 'created_at')
            ->where('user_id', $userId);

        $favoriteCollec = $query->get();

        $listNos = $favoriteCollec->map(function ($d) {
            return $d->list_no;
        })->toArray();

        if (empty($listNos)) {
            return [];
        }

        $houseCollec = collect(house_api_get('house/list-by-ids', ['ids' => $listNos]))->keyBy('id');

        $favoriteItems = $favoriteCollec->map(function ($d) use($houseCollec) {
            return [
                'id' => $d->id,
                'house' => $houseCollec->get($d->list_no),
                'created_at' => $d->created_at
            ];
        });

        return $favoriteItems->filter(function ($d) {
            return !is_null($d['house']);
        });
    }

    public function remove(Request $req)
    {
        $id = $req->get('id');

        return app('db')->table('house_member_favority')
            ->where('id', $id)
            ->delete();
    }
}
