<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 24.08.2017
 * Time: 11:48
 */

namespace Evo\Helper\View;


class Text
{
    public static function crop($text, $length, $stripTags=true, $postfix = '...')
    {
        if($stripTags) {
            $text = strip_tags($text);
        }

        if(mb_strlen($text) <= $length) {
            return $text;
        }

        $index = $length;

        while($index && !preg_match('/\w{1}[ ,\.\?\!\-\:]/u', mb_substr($text, $index-1, 2))) {
            --$index;
        }

        return mb_substr($text, 0, $index) . $postfix;
    }
}