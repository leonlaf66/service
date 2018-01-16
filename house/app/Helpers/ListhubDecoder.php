<?php
namespace App\Helpers;

class ListhubDecoder
{
    public static function toModel($xml)
    {
        $clearTags = [' xmlns="http://rets.org/xsd/Syndication/2012-03" xmlns:commons="http://rets.org/xsd/RETSCommons"', 'commons:'];
        foreach ($clearTags as $clearTag) {
            if (false !== strpos($xml, $clearTag)) {
                $xml = str_replace($clearTag, '', $xml);
            }
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.$xml;

        return @ simplexml_load_string($xml, '\common\core\xml\Element');
    }
}