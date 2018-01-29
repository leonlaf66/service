<?php
function lang_id ()
{
    return config('app.locale');
}

function is_chinese()
{
    return lang_id() === 'zh-CN';
}

function is_english()
{
    return lang_id() === 'en-US';
}

function area_id ()
{
    return config('app.area_id');
}

function state_id()
{
    return strtoupper(area_id());
}

function tt()
{
    $texts = func_get_args();
    if (isset($texts[0]) && is_array($texts[0])) {
        $texts = $texts[0];
    }
    if (count($texts) === '') return '';

    if (lang_id() === 'en-US')
        return $texts[0];
    elseif (count($texts) === 1 || is_null($texts[1]))
        return $texts[0];
    else
        return count($texts) > 1 ? $texts[1] : $texts[1];
}

function array_mult_merge()
{
    $args = func_get_args();
    $res = array_shift($args);
    while (!empty($args)) {
        $next = array_shift($args);
        foreach ($next as $k => $v) {
            if (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                $res[$k] = array_mult_merge($res[$k], $v);
            } else {
                $res[$k] = $v;
            }
        }
    }

    return $res;
}

function xml_get($xml, $path)
{
    $element = $xml->xpath($path);
    if (empty($element)) {
        $element = new \SimpleXMLElement('<empty></empty>');
    }
    return isset($element[0]) ? $element[0] : $element;
}

function get_xml_text($xml, $path, $defValue = null)
{
    $element = xml_get($xml, $path);

    $value = $element->__toString();
    return $value === '' ? $defValue : $value;
}

function get_listhub_prop_type($propType, $propSubType)
{
    static $maps = [];
    if (empty($maps)) {
        $maps = config('house.listhub.prop_type_maps');
    }
    foreach ($maps as $key => $callable) {
        if ($callable($propType, $propSubType)) {
            return $key;
        }
    }
}

function d_field_toarr($d)
{
   if (empty($d)) {
       return [];
   }
   return explode(',', trim($d, '{}'));
}

function get_house_adapter($name, $areaId = null)
{
    if (!$areaId) $areaId = area_id();
    $typePath = $areaId === 'ma' ? 'Mls' : 'Listhub';
    return app("App\Repositories\\{$typePath}\\{$name}");
}

function get_static_data($name)
{
    $file = base_path('data').'/'.$name.'.php';
    return include($file);
}

function media_url($url) {
    return env('MEDIA_BASE_URL').'/'.$url;
}

