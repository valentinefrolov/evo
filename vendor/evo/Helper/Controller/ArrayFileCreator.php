<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 28.03.16
 * Time: 10:06
 */

namespace Evo\Helper\Controller;

use Evo;
use Evo\File;

final class ArrayFileCreator
{
    protected static function quote($item)
    {
        if(is_array($item)) {
            foreach($item as $key => $value) {
                $item[$key] = static::quote($value);
            }
            return $item;
        }
        return htmlspecialchars(htmlspecialchars_decode($item));
    }

    public static function read($filename)
    {

        if(is_file($filename)) {
            $data = require $filename;
            foreach($data as $key => $item) {
                $data[$key] = static::quote($item);
            }
            return $data;
        }
        return [];
    }

    private static function level($name,$value,$level=0)
    {
        $offset = PHP_EOL;
        if($level !== 0) {
            for ($i = 0; $i < $level; $i++) {
                $offset .= '    ';
            }
        }
        $string = $offset . $name . ' => ';

        if(!is_array($value)) {
            $string .= $value;
        } else {
            $string .= '[';
            foreach($value as $k => $v) {
                $string .= static::level($k,$v,$level+1);
            }
            $string .= $offset . ']';
        }
        return $string;

    }

    private static function levelText($text, $level = 0)
    {
        $data = [];
        while(true) {
            $text = trim($text);
            if(preg_match('/^(\])*\s*$/', $text)) {
                break;
            }
            // нашли открывающую скобку
            else if (preg_match('/^([^\s]+)\s*\=\>\s*\[/', $text, $matches)) {
                $text = mb_substr($text, mb_strpos($text, '[') + 1);

                $i = 0;
                $nested = 0;
                $end = -1;
                $arr = preg_split('//u', $text);

                while(isset($arr[++$i])) {
                    if($arr[$i] == '[') {
                        $nested++;
                    }
                    else if($arr[$i] == ']') {
                        if($nested === 0) {
                            $end = $i;
                            break;
                        } else {
                            $nested--;
                        }
                    }
                }
                if($end !== -1) {
                    $data[$matches[1]] = static::levelText(mb_substr($text, 0, $end), $level+1);

                    if($data[$matches[1]] === false) {
                        $data = false;
                        break;
                    }

                    $text = mb_substr($text, $end+1);
                } else {
                    $data = false;
                    break;
                }
            }
            // пустое значение
            else if(preg_match('/^([^\s]+)\s*\=\>\s*\n/', $text, $matches)) {
                $data[$matches[1]] = null;
                $text = preg_replace('/^([^\n]+)/', '', $text);
            }
            // законченное key => value
            else if (preg_match('/^([^\s]+)\s*\=\>\s*([^\[\n]+)/', $text, $matches)) {
                $data[$matches[1]] = trim($matches[2]);
                $text = preg_replace('/^(\s*)/', '', trim(mb_substr($text, mb_strpos($text, $matches[2]) + mb_strlen($matches[2]))));
            }
            // error
            else {
                $data = false;
                break;
            }
        }
        return $data;
    }

    public static function decode($text)
    {
        return static::levelText(htmlspecialchars_decode($text));
    }

    public static function encode(array $data)
    {
        $string = '';
        foreach($data as $key => $value) {
            $string .= static::level($key,$value);
        }

        return $string;
    }

    public static function write($filename, $data = [])
    {

        $content = '';
        $offset = 2;

        $content .= '<?php' . PHP_EOL . '    return [' . PHP_EOL;
        $content .= self::_write($data, $offset);
        $content .= '    ];' . PHP_EOL;

        $handler = fopen($filename, 'w');

        if(fwrite($handler, $content)){
            fclose($handler);
            if(function_exists('opcache_invalidate'))
                opcache_invalidate($filename);
            return true;
        }

        return false;
    }

    private static function _write($array, $offset = 1)
    {

        $pre = ''; for($i = 1; $i <= $offset; $i++) $pre .= '    ';

        $content = '';

        foreach($array as $key => $value) {
            if( !in_array(gettype($key), ['string','integer','double'])) {
                throw new \Exception('ArrayFileCreator: keys types must be only strings, integers or doubles. '.gettype($key));
            } else if(!in_array(gettype($value), ['string','integer','array','boolean','NULL', 'double'])) {
                throw new \Exception('ArrayFileCreator: values types must be only strings, integers, doubles, arrays, boolean or NULL. '.gettype($value));
            }
            $k = is_string($key) || is_double($key) ? "'$key'" : $key;
            $content .= $pre.$k.' => ';
            if(is_array($value)) {
                $content .= '[' . PHP_EOL;
                $content .= self::_write($value, $offset + 1);
                $content .= $pre . '],' . PHP_EOL;
            } else if (is_bool($value)) {
                $bool = $value ? 'true' : 'false';
                $content .= $bool.','.PHP_EOL;
            } else if(is_string($value) || is_double($value)) {
                $content .= "'".str_replace("'", "\'", $value)."'," . PHP_EOL;
            } else if(is_null($value)) {
                $content .= 'null,'.PHP_EOL;
            } else {
                $content .= "$value,".PHP_EOL;
            }
        }
        return $content;
    }

}