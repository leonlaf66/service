<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaSetting extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    public function getTable()
    {
        return 'site_setting';
    }

    public static function get($path, $areaId, $defValue = null)
    {
        static $cache = null;
        if (is_null($cache)) {
            $cache = self::_loadAllData($areaId);
        }
        return $cache[$path] ?? $defValue;
    }

    protected static function _loadAllData($areaId)
    {
        $items = app('db')->table('site_setting')
            ->select('path', 'value')
            ->where('site_id', $areaId)
            ->get()
            ->keyBy('path');

        return $items->map(function ($item) {
            return json_decode($item->value, true);
        });
    }
}