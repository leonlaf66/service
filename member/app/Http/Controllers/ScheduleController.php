<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function list(Request $req)
    {
        $userId = $req->user()->id;

        $query = app('db')->table('house_member_tour')
            ->select('id', 'list_no', 'date_start', 'date_end', 'status')
            ->where('user_id', $userId);

        $ScheduleCollec = $query->get();

        $listNos = $ScheduleCollec->map(function ($d) {
            return $d->list_no;
        })->toArray();

        if (empty($listNos)) {
            return [];
        }

        $houseCollec = collect(house_api_get('house/list-by-ids', ['ids' => $listNos]))->keyBy('id');

        $ScheduleCollec = $ScheduleCollec->map(function ($d) use($houseCollec) {
            return [
                'id' => $d->id,
                'house' => $houseCollec->get($d->list_no),
                'day_date'=> substr($d->date_start, 0, 10),
                'time_start' => substr($d->date_start, 11, 5),
                'time_end' => substr($d->date_end, 11, 5),
                'status' => $d->status
            ];
        });

        return $ScheduleCollec->filter(function ($d) {
            return !is_null($d['house']);
        });
    }

    public function remove(Request $req)
    {
        $id = $req->get('id');

        return app('db')->table('house_member_tour')
            ->where('id', $id)
            ->delete();
    }
}
