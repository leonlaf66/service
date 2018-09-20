<?php
namespace App\Helpers;

class HtmlContent
{
    public static function subString($content, $length, $suffix='...')
    {
        $content = strip_tags($content);
        $originLength = mb_strlen($content, 'utf-8');

        $content = mb_substr($content, 0, $length, 'utf-8');
        if(mb_strlen($content, 'utf-8') < $originLength) {
            $content .= $suffix;
        }

        return $content;
    }
}
