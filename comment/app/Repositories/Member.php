<?php
namespace App\Repositories;

class Member
{
    public function items($ids)
    {
        return app('db')->table('member')
            ->whereIn('id', $ids)
            ->get();
    }
}