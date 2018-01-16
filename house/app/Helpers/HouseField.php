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
            case 'money':
                if (intval($data['value']) < 10000) {
                    $data = array_merge($data, [
                        'value' => number_format($data['value'], 0),
                        'prefix' => tt('$', ''),
                        'suffix' => tt('', '美元')
                    ]);
                    $data = array_filter($data, function ($d) {
                        return !empty($d);
                    });
                    break;
                }
                $data = array_merge($data, [
                    'value' => tt(number_format($data['value'], 0), number_format($data['value'] / 10000, 2)),
                    'prefix' => tt('$', ''),
                    'suffix' => tt('', '万美元')
                ]);
                $data = array_filter($data, function ($d) {
                    return !empty($d);
                });
                break;
            case 'sq.ft':
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
            case 'money/sq.ft':
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