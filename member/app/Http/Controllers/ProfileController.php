<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function get(Request $req)
    {
        $userId = $req->user()->id;
        $userInfo = app('db')->table('member_profile')
            ->where('user_id', $userId)
            ->first();

        unset($userInfo->user_id);

        $userInfo = array_merge([
            'id' => $userId
        ], (array)$userInfo);

        return response()->json($userInfo);
    }

    public function save(Request $req)
    {
        $userId = $req->user()->id;
        $data = [
            'name' => $req->input('name'),
            'phone_number' => $req->input('phone_number'),
            'job_name' => $req->input('job_name'),
            'where_from' => $req->input('where_from')
        ];

        $profileTable = app('db')->table('member_profile');
        if ($profileTable->where('user_id', $userId)->exists()) {
            $profileTable->where('user_id', $userId)->update($data);
        } else {
            $data['user_id'] = $userId;
            $profileTable ->insert($data);
        }
    }
}
