<?php

namespace Evo;

use Evo;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class Debug
{
    private static $_startTime = 0;
    private static $_stopwatch = 0;
    private static $_fileWrite = [];
    private static $_breakPoint = false;
    private static $_breakPointCounter = 0;

    public static function start()
    {
        self::$_startTime = microtime(true);
    }

    public static function get()
    {
        $time =  microtime(true) - self::$_startTime;

        @list($s, $ms) = explode('.', $time);
        $s = ($s == 0) ? '' : $s . ' сек';
        $ms = substr($ms, 0, 3);
        return [$s, $ms];
    }
    
    public static function stop()
    {
        self::$_stopwatch =  microtime(true) - self::$_startTime;
        @list($s, $ms) = static::get();
        return Html::div('Время генерации страницы: ' . $s . ' ' . $ms . ' мс; Выделено ОЗУ: ' . self::memoryUsage(true), array('style' => array(
            'position' => 'fixed',
            'bottom' => 0,
            'left' => 0,
            'width' => '100%',
            'background' => '#dddddd',
            'text-align' => 'center',
            'color' => '#000000',
        )));
    }
    
    public static function memoryUsage($mb = false)
    {
        $value = memory_get_usage() / 1000;
        return $mb ? number_format($value / 1000, 2) . 'MB' : number_format($value, 2) . 'KB'; 
    }
    
    public static function dump($what, $return = false, $depth = 5)
    {
        $result = [];
        $_depth = $depth-1;

        if(is_object($what)) {
            $class = get_class($what);
            $result[$class] = [];
            try {
                $reflect = new ReflectionClass($what);
                $data = $reflect->getProperties(ReflectionProperty::IS_PUBLIC
                    | ReflectionProperty::IS_PROTECTED
                    | ReflectionProperty::IS_PRIVATE
                    | ReflectionProperty::IS_STATIC);

                foreach($data as $prop) {
                    /** @var ReflectionProperty $prop */
                    $access = 'public';
                    if($prop->isProtected()) $access = 'protected';
                    else if($prop->isPrivate()) $access = 'private';
                    if($prop->isStatic()) $access .= ':static';

                    $prop->setAccessible(true);
                    $value = $prop->getValue($what);
                    $type = is_object($value) ? get_class($value) : gettype($value);
                    $result[$class][$prop->getName().':'.$access.':'.$type] = $_depth ? static::dump($value, true, $_depth) : 'depth limit';
                }
            } catch (ReflectionException $e) {
                echo $e->getMessage();
            }
        } else if(is_array($what)) {
            foreach($what as $key => $item) {
                $type = is_object($item) ? get_class($item) : gettype($item);
                $result[$key.':'.$type] = $_depth ? static::dump($item,true, $_depth) : 'depth limit';
            }
        } else {
            $result = $what;
        }
        
        if(!$return) {
            echo '<pre>';
            print_r($result);
            echo '</pre>';
            exit();
        }

        return $result;
    }


    public static function log($what, $filename='debug.log', $delete = true)
    {
        if(!static::$_startTime) {
            static::start();
        }

        if(!isset(static::$_fileWrite[$filename])) {
            static::$_fileWrite[$filename] = Evo::getLogDir() .'/'. $filename;
            if(is_file(static::$_fileWrite[$filename]) && $delete) {
                unlink(static::$_fileWrite[$filename]);
            }
        }

        @list($s, $ms) = static::get();
        $handler = fopen(static::$_fileWrite[$filename], 'a+');

        $text = print_r(static::dump($what, true), true);

        $text = date('Y-m-d H:i:s'). ' ' .$s.'.'.$ms . PHP_EOL . $text . PHP_EOL . PHP_EOL;

        fwrite($handler, $text);
        fclose($handler);
        chmod(static::$_fileWrite[$filename], 0664);
    }


    public static function php()
    {
        while(ob_get_length()) ob_end_clean();
        phpinfo();
    }

    public static function breakPoint()
    {
        static::$_breakPoint = !static::$_breakPoint;
        static::$_breakPointCounter++;
    }

    public static function isBreakPoint($index=1)
    {
        if($index == static::$_breakPointCounter) {
            return true;
        }
        return false;
    }

    public static function getCaller($count = 5)
    {
        $result = '';
        $trace = debug_backtrace();
        for($i = 1; $i <= $count; $i++) {
            if(empty($trace[$i]['file'])) break;
            $result .= "$i: <b>{$trace[$i]['class']}</b> {$trace[$i]['file']} <b>{$trace[$i]['function']}:{$trace[$i]['line']}</b>" . PHP_EOL;
        }
        return "<pre>$result</pre>";
    }

    public static function trace($data)
    {
        $result = '';
        $i=0;
        foreach($data as $trace) {
            $result .= @"++$i: <b>{$trace[$i]['class']}</b> {$trace[$i]['file']} <b>{$trace[$i]['function']}:{$trace[$i]['line']}</b>" . PHP_EOL;
        }
        return "<pre>$result</pre>";
    }

}