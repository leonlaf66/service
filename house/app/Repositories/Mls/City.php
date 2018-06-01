<?php
namespace App\Repositories\Mls;

class City
{
    public function findNameById($state, $id, $isOrig = false)
    {
        static $cache = [];
        $cacheKey = $state.($isOrig ? '1' : '0');

        if (!isset($cache[$cacheKey])) {
            $items = app('db')->table('town')->select('id', 'name', 'name_cn')->get();
            foreach ($items as $item) {
                $_id = $item->id;
                $cache[$cacheKey][$_id] = $item->name;
                if (false === $isOrig) {
                    if (is_chinese() && $item->name_cn) {
                        $cache[$cacheKey][$_id] = $item->name_cn;
                    }
                }
            }
        }

        return $cache[$cacheKey][$id] ?? '';
    }

    public function findIdByName($state, $name)
    {
        return app('db')->table('town')
            ->select('id')
            ->where('state', $state)
            ->where(function ($query) use ($name){
                return $query->where('name', $name)
                    ->orWhere('name_cn', $name);
            })
            ->limit(1)
            ->value('id');
    }

    public function findIdByCode($state, $code)
    {
        return app('db')->table('town')
            ->select('id')
            ->where('state', $state)
            ->where('short_name', $code)
            ->limit(1)
            ->value('id');
    }

    public function findIdByPostalCode($state, $code)
    {
        $townShortCode = app('db')->table('zipcode_town')
            ->select('city_short_name')
            ->where('zip', $code)
            ->limit(1)
            ->value('city_short_name');

        $cityId = null;
        if ($townShortCode) {
            $cityId = $this->findIdByCode($state, $townShortCode);
        }
        return $cityId;
    }

    public function searchOptions($state, $type)
    {
        $nameItems = app('db')->table('town')
            ->select('name', 'name_cn')
            ->get();


        foreach ($nameItems as $item) {
            $enNameValues[$item->name] = $item->name_cn ?? '';
            if ($item->name_cn) {
                $cnNameValues[$item->name_cn] = $item->name;
            }
        }

        $enNameValues = []; $cnNameValues = [];$zipValues = [];
        $items = app('db')->table('zipcode_town')
            ->select('zip', 'city_name', 'city_name_cn')
            ->get();
        foreach ($items as $item) {
            $zip = $item->zip;
            $name = $item->city_name;
            $nameCn = $item->city_name_cn;

            if (!isset($enNameValues[$name])) {
                $enNameValues[$name] = $nameCn ?? '';
            }
            if ($nameCn && !isset($cnNameValues[$nameCn])) {
                $cnNameValues[$nameCn] = $name;
            }
            $zipResults[$zip] = $name.','.$nameCn ?? '';
        }

        $results = [];
        foreach ([$enNameValues, $cnNameValues, $zipResults] as $values) {
            foreach ($values as $title => $desc) {
                $results[$title] = $desc;
            }
        }

        return $results;
    }
}