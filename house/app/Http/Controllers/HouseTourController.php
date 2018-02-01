<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HouseTourController extends Controller
{
    public function submit (Request $req, $id)
    {
        $day = $req->input('day');
        $timeStart = $req->input('time_start');
        $timeEnd = $req->input('time_end');

        $house = \App\Models\HouseIndex::findOrFail($id);

        $oldId = app('db')->table('house_member_tour')
            ->select('id')
            ->where('user_id', $req->user()->id)
            ->where('list_no', $id)
            ->where('status', 0)
            ->value('id');

        $result = false;
        if ($oldId) {
            $result = app('db')->table('house_member_tour')
                ->where('id', $oldId)
                ->update([
                    'date_start' => $day.' '.$timeStart.':00',
                    'date_end' => $day.' '.$timeEnd.':00'
                ]);
        } else {
            $result = app('db')->table('house_member_tour')->insert([
                'user_id' => $req->user()->id,
                'list_no' => $id,
                'date_start' => $day.' '.$timeStart.':00',
                'date_end' => $day.' '.$timeEnd.':00',
                'status' => 0,
                'area_id' => $house->area_id
            ]);
        }

        return response()->json($result);
    }
}
