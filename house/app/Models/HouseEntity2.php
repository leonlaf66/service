<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HouseEntity2 extends Model
{
    public $primaryKey = 'list_no';
    public $incrementing = false;
    public $timestamps = false;
    protected $data = null;

    public function getConnection()
    {
        return app('db')->connection('pgsql2');
    }

    /*
    public function getTable()
    {
        return 'house_data_v2';
    }

    public function getDataAttribute()
    {
        if (is_null($this->data)) {
            if ($this->mls_data && $this->mls_data !== '{}') {
                $this->data = json_decode($this->mls_data, true);
            } elseif ($this->listhub_data) {
                $xml = '<?xml version="1.0" encoding="UTF-8"?>'.$this->listhub_data;
                $this->data = @ simplexml_load_string($xml);
            }
        }

        return $this->data;
    }*/
    public function getTable()
    {
        return 'mls_rets_listhub';
    }

    public function getDataAttribute()
    {
        if (is_null($this->data)) {
            $xml = $this->xml;
            
            $clearTags = [' xmlns="http://rets.org/xsd/Syndication/2012-03" xmlns:commons="http://rets.org/xsd/RETSCommons"', 'commons:'];
            foreach ($clearTags as $clearTag) {
                if (false !== strpos($xml, $clearTag)) {
                    $xml = str_replace($clearTag, '', $xml);
                }
            }
            $xml = '<?xml version="1.0" encoding="UTF-8"?>'.$xml;
            $this->data = @ simplexml_load_string($xml);
        }

        return $this->data;
    }
}