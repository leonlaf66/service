<?php
namespace App\Helpers;

class HousePhoto
{
    public static function getRule($areaId = null)
    {
        if (!$areaId) $areaId = area_id();

        return $areaId === 'ma'
            ? 'http://media.mlspin.com/Photo.aspx?mls={@id}&n={@index}&w={@width}&h={@height}'
            : 'http://photos.listhub.net/MREDIL/{@id}/{@index}';
    }
}