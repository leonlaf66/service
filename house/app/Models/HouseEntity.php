<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HouseEntity extends Model
{
    public $primaryKey = 'list_no';
    public $incrementing = false;
    public $timestamps = false;
    protected $dataCache = null;

    public function getTable()
    {
        return 'house_data';
    }

    public function getDataAttribute()
    {
        if (is_null($this->dataCache)) {
            if (substr($this->orgi_data, 0, 1) === '{') {
                $this->dataCache = json_decode($this->orgi_data, true);
            } else {
                /*$xml = '<?xml version="1.0" encoding="UTF-8"?>'.$this->orgi_data;*/
                $this->dataCache = @ simplexml_load_string($this->orgi_data);
            }
        }

        return $this->dataCache;
    }
}