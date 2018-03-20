<?php
namespace App\Repositories\Mls;

use App\Models\HouseIndex as House;

class HouseRoi
{
    public function getResults(House $house)
    {
        if (!$house->estimation) $house->estimation = '{"est_roi":null, "est_rental":null}';
        $estimation = json_decode($house->estimation);

        if ($estimation->est_roi) {
            $estimation->est_roi = number_format($estimation->est_roi, 4);
        }

        $data = [
            'est_roi_cash' => $estimation->est_roi,
            'ave_roi_cash' => null,
            'est_annual_income_cash' => $estimation->est_rental,
            'ave_annual_income_cash' => null
            ];

        if ($house->postal_code) {
            if ($aveData = $this->getAveRoi($house->postal_code)) {
                $data = array_merge($data, $aveData);
            }
        }

        return $data;
    }

    protected function getAveRoi($zipCode)
    {
        $d = app('db')->table('zipcode_roi_ave')
            ->select('AVE_ROI_CASH as ave_roi_cash', 'AVE_ANNUAL_INCOME_CASH as ave_annual_income_cash')
            ->where('ZIP_CODE', $zipCode)
            ->first();

        if (!$d) {
            return false;
        }

        $d->ave_roi_cash = number_format($d->ave_roi_cash, 4);
        return json_decode(json_encode($d), true);
    }
}