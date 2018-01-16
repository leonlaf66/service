<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HouseIndex extends Model
{
    public $primaryKey = 'list_no';
    public $incrementing = false;
    public $timestamps = false;
    protected $hidden = ['is_online_abled', 'order_rule', 'index_at'];
    protected $shareData = [];

    /**
     * 数据表
     */
    public function getTable()
    {
        return 'house_index_v2';
    }

    /**
     * 数据实体
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function entity()
    {
        return $this->hasOne('App\Models\HouseEntity', 'list_no', 'list_no');
    }

    /**
     * 共享
     * @param $key
     * @param null $value
     * @return mixed|null
     */
    public function share($key, $value = null) {
        if (is_null($value)) {
            return $this->shareData[$key] ?? null;
        }
        $this->shareData[$key] = $value;
    }

    /**
     * 获取字段的value
     * @param $name
     * @param array $opt
     * @return mixed
     */
    public function getFieldValue($name, $opt = [])
    {
        return $this->repositoryAdapter('HouseField')
            ->getValue($this, $name, $opt);
    }

    /**
     * 获取字段的数据
     * @param $name
     * @param array $opt
     * @return mixed
     */
    public function getFieldData($name, $opt = [])
    {
        return $this->repositoryAdapter('HouseField')
            ->getEntity($this, $name, $opt);
    }

    /**
     * 获取详情字段字列表
     */
    public function getDetails()
    {
        return $this->repositoryAdapter('HouseField')
            ->getDetails($this);
    }

    /**
     * latlon字段转换
     * @param $value
     * @return array
     */
    public function getLatlonAttribute($value)
    {
        return $this->arrayAttribute($value);
    }

    /**
     * latlonRad字段转换
     * @param $value
     * @return array
     */
    public function getLatlonRadAttribute($value)
    {
        return $this->arrayAttribute($value);
    }

    /**
     * no_baths字段转换
     * @param $value
     * @return array
     */
    public function getNoBathsAttribute($value)
    {
        return $this->arrayAttribute($value);
    }

    /**
     * db数据数据转换
     * @param $value
     * @return array
     */
    protected function arrayAttribute($value)
    {
        if ($value) {
            $value = substr($value, 1, strlen($value) - 2);
        }
        return explode(',', $value);
    }

    /**
     * 获取适配器
     * @param $name
     * @return \Laravel\Lumen\Application|mixed
     */
    public function repositoryAdapter($name)
    {
        $typePath = $this->area_id === 'ma' ? 'Mls' : 'Listhub';
        return app("App\Repositories\\{$typePath}\\{$name}");
    }
}