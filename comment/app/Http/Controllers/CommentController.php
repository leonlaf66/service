<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function all(Request $req, $type, $id)
    {
        $path = $type.'/'.$id;

        $itemCollec = app('App\Repositories\Comment')->all($path);
        $userIds = $itemCollec->map(function ($d) {
            return $d->user_id;
        });

        $userCollec = app('App\Repositories\Member')->items($userIds)->keyBy('id');

        $items = $itemCollec->map(function ($d) use ($userCollec) {
            $user = $userCollec->get($d->user_id);
            return [
                'id' => $d->id,
                'rating' => $d->rating,
                'content' => $d->comments,
                'created_at' => date('Y-m-d H:i', strtotime($d->created_at)),
                'user' => [
                    'id' => $user ? $user->id : null,
                    'name' => $user ? explode('@', $user->email)[0] : null
                ]
            ];
        });

        return response()->json($items);
    }

    public function submit(Request $req, $type, $id)
    {
        $data = [
            'rating' => $req->input('rating', 5),
            'content' => $req->input('content', '')
        ];
        $path = $type.'/'.$id;
        $userId = $req->user()->id;

        $result = app('App\Repositories\Comment')->create($path, $data, $userId);

        $user = app('App\Repositories\Member')->items([$userId])->first();

        $result = [
            'id' => $result['id'],
            'rating' => $result['rating'],
            'content' => $result['content'],
            'user' => [
                'id' => $user->id,
                'name' => explode('@', $user->email)[0]
            ],
            'created_at' => date('Y-m-d H:i', strtotime($result['created_at']))
        ];

        return response()->json($result);
    }
}
