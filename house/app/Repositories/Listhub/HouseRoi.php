<?php
namespace App\Repositories\Listhub;

use App\Models\HouseIndex as House;

class HouseRoi
{
    public function getResults(House $house)
    {
        return [
            'est_roi_cash' => '0.00%',
            'ave_roi_cash' => '0.00%',
            'est_annual_income_cash' => '$0.00',
            'ave_annual_income_cash' => '$0.00'
        ];
    }
}