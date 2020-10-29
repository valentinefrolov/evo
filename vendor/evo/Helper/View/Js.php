<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 01.02.16
 * Time: 14:38
 */

namespace Evo\Helper\View;

use Evo;

final class Js
{
    private $variables = [];
    private $string = '';

    public function __construct(string $string)
    {
        $this->string = $string;
    }

    public static function echo($string)
    {
        if($string instanceof Js) {
            return $string;
        } else {
            return "'$string'";
        }
    }

    public static function parseScript($string)
    {
        // TODO сделать парсер глобальной зоны видимости
    }

    public static function route($r = null, $vars = array(), $leave = false)
    {
        $jsParams = [];
        foreach($vars as $key => $value) {
            if(static::checkIsScript($value)) {
                $jsParams[$key] = $value;
                unset($vars[$key]);
            }
        }
        if($leave) {
            $old = Evo::app()->request->get();
            foreach($old as $key => $value) {
                if(array_key_exists($key, $jsParams)) {
                    unset($old[$key]);
                }
            }
            $vars = array_merge($vars, $old);
        }
        $route = Evo::app()->locator->route($r, $vars);
        $parse = parse_url(Evo::app()->locator->route($r, $vars));
        $i = 0;
        foreach($jsParams as $name => $js) {
            $route .= ($i == 0 && empty($parse['query'])) ? '?' : '&';
            $route .= "$name='+$js";
            if(++$i < count($jsParams))
                $route .= "+'";
        }
        return "'$route";
    }

    public function __toString()
    {
        return $this->string;
    }

}