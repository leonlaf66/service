<?php
namespace App\Repositories\Listhub;

class City
{
    public function findNameById($state, $id)
    {
        static $cache = [];
        if (!isset($cache[$state])) {
            $items = app('db')->table('city')->select('id', 'name', 'name_cn')->get();

            foreach ($items as $item) {
                $_id = $item->id;
                $cache[$state][$_id] = $item->name;
                if (is_chinese() && $item->name_cn) {
                    $cache[$state][$_id] = $item->name_cn;
                }
            }
        }

        return $cache[$state][$id] ?? '';
    }

    public function findIdByName($state, $name)
    {
        return app('db')->table('city')
            ->select('id')
            ->where('state', $state)
            ->where(function ($query) use ($name){
                return $query->where('name', $name)
                    ->orWhere('name_cn', $name);
            })
            ->orderBy('type_rule', 'ASC')
            ->orderBy('id', 'ASC')
            ->limit(1)
            ->value('id');
    }

    // 仅ma
    public function findIdByCode($state, $code)
    {
        throw new Exception("本该市代码没有该方法");
    }

    public function findIdByPostalCode($state, $code)
    {
        return app('db')->table('city')
            ->select('id')
            ->where('state', $state)
            ->where('zip_codes', '@>', '{'.$code.'}')
            ->orderBy('type_rule', 'asc')
            ->limit(1)
            ->value('id');
    }

    public function searchOptions($state, $type)
    {
        $nameItems = app('db')->table('city')
            ->select('name', 'name_cn', 'zip_codes')
            ->where('state', $state)
            ->get();

        $enNameValues = []; $cnNameValues = [];$zipValues = [];
        foreach ($nameItems as $item) {
            $enNameValues[$item->name] = $item->name_cn ?? '';
            if ($item->name_cn) {
                $cnNameValues[$item->name_cn] = $item->name;
            }

            $zipCodes = explode(',', substr($item->zip_codes, 1, strlen($item->zip_codes) - 2));
            foreach ($zipCodes as $zipCode) {
                $zipValues[$zipCode] = $item->name.','.$item->name_cn ?? '';
            }
            // ma city postal codes
            if ($state !== 'MA' && $item->zip_codes) {

            }
        }

        $results = [];
        foreach ([$enNameValues, $cnNameValues, $zipValues] as $values) {
            foreach ($values as $title => $desc) {
                $results[$title] = $desc;
            }
        }

        return $results;
    }
}