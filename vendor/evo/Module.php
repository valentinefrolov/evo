<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 27.01.16
 * Time: 11:35
 */

namespace Evo;

use Evo;
use ReflectionClass;
use Evo\Interfaces\ModuleEntity;
use Evo\Interfaces\Configurable;
use Throwable;

abstract class Module extends Event implements ModuleEntity, Configurable
{
    protected $config = [];
    protected $path = '';
    protected $url = '';
    protected $name = '';


    abstract public function error(Throwable $e);
    abstract public function getLayout();

    /**
     * Module constructor.
     * @param $config
     * @throws \Exception
     */
    public function __construct($config, $name){
        $this->checkConfig($config);
        $this->config = $config;
        $this->name = $name;

        $reflection = new ReflectionClass($this);
        $this->path = substr(dirname($reflection->getFileName()), strlen(Evo::getSourceDir())+1);

        preg_match('/\/.+$/', $this->config['host'], $matches);

        $this->url = (!empty($matches[0])?$matches[0]:'') . '/';

    }

    public function action() {}
    public function afterAction() {}
    public function init() {}

    /**
     * @return Controller
     * @throws Exception\ClassNotFoundException
     */
    public function getController()
    {
        $route = Evo::app()->request->route();
        if(!$route) {
            $ctrl = Controller::create($this->config['defaultController']);
            $action = $ctrl->action;
            Evo::app()->request->inject('route', $ctrl->urlName().'/'.$action);
            return $ctrl;
        }
        return Controller::createFromRoute($route);
    }

    /**
     * @param array $config
     * @throws \Exception
     */
    public function checkConfig(array $config = [])
    {
        $important = ['host', 'defaultController'];

        foreach($important as $item) {
            if(!isset($config[$item])) {
                // TODO Config exception
                throw new \Exception("The '$item' must exists on config file for Module");
            }
        }
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getAbsolutePath()
    {
        return Evo::getSourceDir().'/'.$this->getPath();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRoutes()
    {
        $path = Evo::getSourceDir().'/'.Evo::app()->module->getPath().'/routes.php';
        return is_file($path) ? require $path : [];
    }

    public function performRequest(string $request) {
        return $request;
    }




}