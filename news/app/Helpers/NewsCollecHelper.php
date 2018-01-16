<?php
namespace App\Helpers;

class NewsCollecHelper
{
    public static function build($itemCollec)
    {
        return $itemCollec->map(function ($d) {
            $d->image_url = media_url(env('NEWS_DEFAULT_IMAGE'));
            if (preg_match('/<img.*?src=[\"|\']?(.*?)[\"|\']?\s.*?>/i', $d->content, $matchs)) {
                $d->image_url = $matchs[1];
            }
            unset($d->content);
            return $d;
        });
    }
}