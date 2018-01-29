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

        $data = [
            'user_id' => $req->user()->id,
            'list_no' => $id,
            'date_start' => $day.' '.$timeStart.':00',
            'date_end' => $day.' '.$timeEnd.':00',
            'status' => 0,
            'area_id' => $house->area_id
        ];

        $result = app('db')->table('house_member_tour')->insert($data);

        return response()->json($result);
    }
}
