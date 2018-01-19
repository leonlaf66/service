<?php
namespace App\Helpers;

class HouseField
{
    public static function filter($value, $type)
    {
        switch ($type) {
            case 'count':
                $value = count($value);
                break;
        }
        return $value;
    }

    public static function format(& $data, $opt = [])
    {
        switch ($opt['format']) {
            case 'date':
                $data['value'] = date('Y-m-d', strtotime($data['value']));
                break;
            case 'datetime':
                $data['value'] = date('Y-m-d H:i', strtotime($data['value']));
                break;
            case 'price':
            case 'rental.total.price':
            case 'sell.total.price':
            case 'money':
                if (floatval($data['value']) === 0.0) {
                    $data['is_empty'] = true;
                    break;
                }
                if (intval($data['value']) < 10000) {
                    $data = array_merge($data, [
                        'value' => number_format($data['value'], 0),
                        'prefix' => tt('$', ''),
                        'suffix' => tt('', '美元')
                    ]);
                } else {
                    $data = array_merge($data, [
                        'value' => tt(number_format($data['value'], 0), number_format($data['value'] / 10000, 2)),
                        'prefix' => tt('$', ''),
                        'suffix' => tt('', '万美元')
                    ]);
                }
                if ($data['prefix'] === '') unset($data['prefix']);
                if ($data['suffix'] === '') unset($data['suffix']);
                break;
            case 'area':
            case 'sq.ft':
                if (intval($data['value']) === 0) {
                    $data['is_empty'] = true;
                    break;
                }
                if (is_chinese()) {
                    $data = array_merge($data, [
                        'value' => number_format(intval(floatval($data['value']) * 0.092903), 0),
                        'suffix' => '平方米'
                    ]);
                } else {
                    $data = array_merge($data, [
                        'value' => number_format(floatval($data['value']), 0),
                        'suffix' => 'Sq.Ft'
                    ]);
                }
                break;
            case 'price.per.sq-ft':
            case 'money/sq.ft':
                if (intval($data['value']) === 0) {
                    $data['is_empty'] = true;
                    break;
                }
                if (is_chinese()) {
                    $data['value'] = number_format(floatval($data['value']) / 0.092903, 0);
                    $data['suffix'] = '美元/平方米';
                } else {
                    $data['value'] = number_format(floatval($data['value']), 0);
                }
                break;
            case 'yes/no':
                $data['value'] = $data['value'] === 'true' ? tt('Yes', '是') : tt('No', '否');
                return;
            case 'have/not':
                $data['value'] = $data['value'] === 'true' ? tt('Yes', '有') : tt('No', '无');
                break;
        }
    }
}