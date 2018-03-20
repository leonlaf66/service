<?php
namespace App\Repositories\Listhub;

use App\Models\HouseIndex as House;

class HouseRoi
{
    public function getResults(House $house)
    {
        return [
            'est_roi_cash' => null,
            'ave_roi_cash' => null,
            'est_annual_income_cash' => null,
            'ave_annual_income_cash' => null
        ];
    }
}