<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 24.10.16
 * Time: 19:20
 */

namespace Evo\Cli;

use Evo;
use Evo\App as EvoApp;
use Evo\Cache;
use Evo\File;


class App extends EvoApp
{
    protected function getModule()
    {
        $config = $this->config;
        if(!empty($config['modules'])) {
            foreach($config['modules'] as $moduleName => $data) {
                if(strpos($data['host'], $config['host']) === 0) {
                    $this->module = new \CliModule(array_merge($config, $data), $moduleName);
                    if(!empty($_SERVER['argv'])) {
                        $cut = substr($data['host'], strlen($config['host']));
                        foreach ($_SERVER['argv'] as $i => $value) {
                            if(strpos($value, '/') === 0 && strpos($value, $cut) === 0) {
                                $_SERVER['argv'][$i] = substr($value, strlen($cut));
                                $this->module->setPath($moduleName);
                                return $_SERVER['argv'][$i];
                            }
                        }
                    }
                }
            }
        }

        $this->module = new \CliModule($this->config, '');
        return '';
    }

    protected function getRequest($requestString)
    {
        $this->request = new Request($requestString);
    }

    public function start()
    {
        if(!$task = $this->request->get('task')) return;
        if(!$path = Evo::getRootPath("task/$task.php")) return;

        require $path;

    }

    protected function cacheInit()
    {
        Cache::configure('none');
    }
}