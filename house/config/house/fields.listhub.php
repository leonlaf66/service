<?php
return [
    'photo_count' => [
        'value' => function ($d) {
            $cnt = count($d->xpath('Photos/Photo'));
            return $cnt > 0 ? $cnt : 1;
        }
    ],
    'location' => [
        'value' => function ($d, $m) {
            $address = $d->Address;
            return implode(' ', [
                $address->FullStreetAddress->__toString().', '.$address->City->__toString(),
                $address->StateOrProvince->__toString(),
                $address->PostalCode->__toString()
            ]);
        }
    ],
    'mls_id' => [
        'value' => function ($d, $m) {
            return get_xml_text($d, 'MlsId');
        }
    ],
    /*字段增强*/
    'elementary_schools' => [
        'value' => function ($d, $m) {
            $cacheKey = 'listhub_grouped_school_names';
            $names = $m->share($cacheKey);
            if (!$names) {
                $names = ['Elementary' => [], 'Middle' => [], 'High' => []];
                $schools = $d->xpath('Location/Community/Schools/School');

                foreach ($schools as $school) {
                    $catName = get_xml_text($school, 'SchoolCategory');
                    if ($catName === 'Primary') $catName = 'Elementary';

                    if (in_array($catName, ['Elementary', 'Middle', 'High'])) {
                        if ($name = get_xml_text($school, 'Name')) {
                            $names[$catName][] = $name;
                        }
                    }
                }
                $m->share($cacheKey, $names);
            }
            return $names;
        }
    ],
    'elementary_school_names' => [
        'value' => function ($d, $m) {
            $names = $m->getFieldValue('elementary_schools')['Elementary'] ?? [];
            return implode(',', $names);
        }
    ],
    'middle_school_names' => [
        'value' => function ($d, $m) {
            $names = $m->getFieldValue('elementary_schools')['Middle'] ?? [];
            return implode(',', $names);
        }
    ],
    'high_school_names' => [
        'value' => function ($d, $m) {
            $names = $m->getFieldValue('elementary_schools')['High'] ?? [];
            return implode(',', $names);
        }
    ],
    'taxes' => [
        'title' => tt('Taxes', '房产税'),
        'value' => function ($d) {
            return get_xml_text($d, 'Taxes/Tax/Amount');
        }
    ],
    'expenses' => [
        'flat' => true, // 展开标记
        'value' => function ($d) {
            $values = [];
            $expenses = $d->xpath('Expenses/Expense');
            foreach ($expenses as $expense) {
                if ($catName = get_xml_text($expense, 'ExpenseCategory')) {
                    if (is_chinese()) {
                        $langs = app('\App\Repositories\Listhub\HouseField')->getChineseValues('ExpenseType');
                        if (isset($langs[$catName]) && $langs[$catName] !== '') {
                            $catName = $langs[$catName];
                        }
                    }

                    $money = get_xml_text($expense, 'ExpenseValue');
                    if (is_chinese()) {
                        if ($money > 10000) {
                            $money = number_format($money / 10000.0, 2).'万美元';
                        } else {
                            $money = number_format($money, 0).'美元';
                        }
                    } else {
                        $money = '$'.number_format($money);
                    }

                    $values[$catName] = $money;
                }
            }

            return $values;
        }
    ],
    'roi' => [
        'value' => function ($d, $m) {
            return app('App\Repositories\Listhub\HouseRoi')->getResults($m);
        }
    ]
];