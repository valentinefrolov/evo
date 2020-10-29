<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 29.01.2016
 * Time: 12:19
 */

namespace Evo\Helper\View;


class Json
{

    /**
     * @param mixed $data
     * @param mixed $escape
     * @return string
     */
    public static function encode($data, $escape = null)
    {
        $encoded = str_replace(['\r', '\n'], ['', '\\\n'], json_encode($data, JSON_UNESCAPED_UNICODE));
        if($escape) {
            $search = (array) $escape;
            $replacement = [];
            foreach($search as &$item) {
                $replacement[] = '\\'.$item;
            }
            $encoded = str_replace($search, $replacement, $encoded);
        }
        return $encoded;
    }

    public static function decode($json, $asArray = true)
    {
        return json_decode($json, $asArray);
    }
}