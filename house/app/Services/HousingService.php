<?php
namespace App\Services;

use App\Contracts\HousingContract;

class HousingService implements HousingContract
{
    public function search($q, $filters, $sorts)
    {
        return [];
    }

    public function items($ids)
    {
        return [];
    }

    public function get($id)
    {
        return [];
    }
}