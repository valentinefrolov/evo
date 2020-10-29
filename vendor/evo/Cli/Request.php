<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 27.10.16
 * Time: 12:52
 */

namespace Evo\Cli;

use Evo\Request as EvoRequest;
use Evo\Storage;

class Request extends EvoRequest
{
    public function __construct($uri)
    {
        $this->storage  = new Storage();
        $url = !empty($_SERVER['argv']) ? $_SERVER['argv'] : [];
        array_shift($url);
        $url = implode('&', $url);
        parse_str($url, $args);

        foreach($args as $key => $value) {
            if(!$value && strpos($key, '/') !== false) {
                $this->storage->set('route', substr($key, 1));
                unset($args[$key]);
            } else {
                $args[$key] = $this->parseArray($value);
            }
        }
        $this->storage->set('get', array_merge($args, $_GET));
    }

    protected function parseArray(string $string)
    {
        if(preg_match('/^\[.+\]$/', trim($string))) {
            $depth = 0;
            $string = substr(trim($string), 1, strlen($string)-2);
            $_arr = [];
            $i = 0;
            while(isset($string[$i])) {
                if($string[$i] == '[') $depth++;
                if($string[$i] == ']') $depth--;

                if($string[$i] == ',' && $depth === 0) {
                    $_arr[] = substr($string, 0, $i);
                    $string = substr($string, $i+1);
                    $i=0;
                    continue;
                }
                if($i == strlen($string)-1) $_arr[] = $string;
                $i++;
            }

            foreach($_arr as $item) {
                $eqPos = strpos($item, '=');
                $key = substr($item, 0, $eqPos);
                $value = $this->parseArray(substr($item, $eqPos+1));

                $arr[$key] = $value;
            }
            return $arr;
        }
        return $string;
    }


}