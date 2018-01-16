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

function tt($en, $cn = null)
{
    if (is_english()) { // 取原始
        if (is_array($en)) {
            return $en[0] ?? null;
        } else {
            return $en;
        }
    }

    if (is_null($cn) && is_array($en)) {
        $cn = $en[1] ?? null;
    }

    return empty($cn) ? $en : $cn ;
}

function get_static_data($name)
{
    $file = base_path('data').'/'.$name.'.php';
    return include($file);
}

function media_url($url) {
    return env('MEDIA_BASE_URL').'/'.$url;
}