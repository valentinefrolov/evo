<?php


final class Evo
{
    /** @var Evo\App */
    private static $app = null;
    private static $type = 'default';
    private static $windows = !!(DIRECTORY_SEPARATOR === '\\');

    /**
     * @return Evo\App
     */
    public static function app()
    {
        return static::$app;
    }

    public static function appType()
    {
        return static::$type;
    }

    /**
     * @param string $appType
     * @throws Exception
     */
    public static function init($appType='')
    {
        static::$type = $appType;
        require_once __DIR__ . '/vendor/autoload.php';

        if(!static::$app) {
            static::$app = call_user_func(array('\Evo\\' . (strlen($appType) ? ucfirst($appType) . '\\' : '') . 'App', 'getInstance'));
            static::$app->init();
        }
    }

    public static function getConfig($name, $module='')
    {
        $name = strtolower($name);
        $path = $module ? __DIR__ ."/config/$module/$name.php" : __DIR__ ."/config/$name.php";

        if(is_file($path)) {
            return require $path;
        }
        else if($module){
            $path = __DIR__ ."/config/$name.php";
            if(is_file($path)) {
                $config = require $path;
                if(!empty($config['modules'][$module])) {
                    return $config['modules'][$module];
                }
            }
        }

        return [];
    }

    public static function getConfigDir()
    {
        return __DIR__ .'/config';
    }


    public static function getWebDir()
    {
        return __DIR__ . '/' . static::getConfig('app')['public'];
    }

    public static function getWebPath($file)
    {
        if(is_file(static::getWebDir().'/' .$file) || is_dir(static::getWebDir().'/' .$file)) {
            return static::getWebDir().'/' .$file;
        }
        return false;
    }

    public static function getLogDir()
    {
        $conf = static::getConfig('app');
        $logs = !empty($conf['logs']) ? $conf['logs'] : 'logs';

        return __DIR__ . '/' . $logs;
    }

    public static function getVendorDir()
    {
        return __DIR__ . '/vendor';
    }

    public static function getVendorPath($file)
    {
        if(is_file(static::getVendorDir().'/' .$file) || is_dir(static::getVendorDir().'/' .$file)) {
            return static::getVendorDir().'/' .$file;
        }
        return false;
    }

    public static function getSourceDir()
    {
        return __DIR__ . '/' . static::getConfig('app')['src'];
    }

    public static function getSourcePath($file)
    {
        if(is_file(static::getSourceDir().'/' .$file) || is_dir(static::getSourceDir().'/' .$file)) {
            return static::getSourceDir().'/' .$file;
        }
        return false;
    }

    public static function getRootDir()
    {
        return __DIR__;
    }

    public static function getRootPath($file)
    {
        if(is_file(static::getRootDir().'/' .$file) || is_dir(static::getRootDir().'/' .$file)) {
            return static::getRootDir().'/' .$file;
        }
        return false;
    }

    public static function task(string $task, $params = [], $processAsync = true)
    {
        $addition = '';
        foreach($params as $key => $value) {
            if(preg_match('/^\d+$/', $key)) {
                $addition .= "$value ";
            } else {
                $addition .= "$key=$value ";
            }
        }
        $dir = static::getRootDir() . DIRECTORY_SEPARATOR . 'cli.php';
        $config = static::getConfig('app');
        $cli = empty($config['interpreter']) ? 'php' : $config['interpreter'];
        $cmd = "$cli $dir task=$task $addition";
        if($processAsync) {
            if(static::$windows) {
                pclose(popen("start /B $cmd", 'r'));
            } else {
                exec($cmd .' > /dev/null &');
            }
        }
    }





}


