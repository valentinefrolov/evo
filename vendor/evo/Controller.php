<?php

namespace Evo;

use Evo;
use Evo\Event;
use Evo\Response;


abstract class Controller extends Event
{
    /** @var string  */
    public $action = '';
    /** @var string  */
    public $actionRoot = '';
    /** @var string $this->defaultAction */
    protected $defaultAction = 'index';
    /** @var Evo\Request $this->request */
    protected $request = null;
    /** @var Evo\Response $this->response */
    protected $response = null;
    /** @var Evo\Locator $this->locator */
    protected $locator = null;
    /** @var Evo\Layout */
    protected $layout = null;
    /** @var Evo\Lang  */
    protected $lang = null;
    /** @var Evo\Module  */
    protected $module = null;
    /** @var Evo\View  */
    protected $view = null;

    protected $path = '';

    private static function getName(string $name, Module $module = null)
    {
        $path = $module !== null ? $module->getPath() : Evo::app()->module->getPath();

        $capitalize = ucfirst(preg_replace_callback(
            '/(_[^_]|\-[^\-]){1}/',
            function($matches){
                if(strpos($matches[0], '_') === 0) {
                    return '\\'.strtoupper(substr($matches[0], 1));
                } else {
                    return strtoupper(substr($matches[0], 1));
                }

            }, $name));

        return  '\\'.($path ? $path . '\\' : '') . 'Controller\\' . $capitalize . 'Controller';
    }


    /**
     * @param $route
     * @param $module
     * @return Controller
     * @throws Exception\ClassNotFoundException
     */
    public static function createFromRoute($route, $module = null)
    {
        list($name, $action) = array_pad(explode('/', $route), 2, null);
        $ctrl = static::create($name, $module);
        $ctrl->setAction($action);
        return $ctrl;
    }

    /**
     * @param $name
     * @param $module
     * @return Controller
     * @throws Exception\ClassNotFoundException
     */
    public static function create($name, $module = null) : Controller
    {
        $module = $module !== null ? Evo::app()->getAlterModule($module) : Evo::app()->module;
        $ctrlName = static::getName($name, $module);
        if(!class_exists($ctrlName)) {
            throw new Evo\Exception\ClassNotFoundException($name);
        }
        return new $ctrlName(null, $module);
    }

    public function setAction($action)
    {
        if($action)
            $this->action = preg_replace('/([^_])_{1}([^_])/',  '$1$2', $action);
    }

    public function __construct($action = null, $module = null)
    {
        $this->action   = $this->defaultAction;


        $this->response = Evo::app()->response;
        $this->request  = Evo::app()->request;
        $this->locator  = Evo::app()->locator;
        $this->layout   = Evo::app()->view;
        $this->lang     = Evo::app()->lang;
        $this->module   = $module ? $module : Evo::app()->module;

        $this->view     = new View($this);

        preg_match('/Controller\\\\(.+?)Controller/', get_class($this), $matches);

        $this->path = $this->className();

        if($action) {
            $this->setAction($action);
        }

        $this->event('init');

    }

    /**
     * @return bool|mixed
     * @throws Exception\FunctionNotExistsException
     */
    public function action()
    {
        $args = func_get_args();

        if(
            method_exists($this, 'beforeAction')
            &&
            (false === call_user_func_array([$this, 'beforeAction'], $args))
        ) {
            return false;
        }

        $action = $this->action ? $this->action : $this->defaultAction;
        $method = 'before'.$action;

        if(
            method_exists($this, $method)
            &&
            (false === call_user_func_array([$this, $method], $args))
        ) {
            return false;
        }

        $method = "action$action";

        if(!method_exists($this, $method)) {
            throw new Evo\Exception\FunctionNotExistsException($method);
        }

        $result = call_user_func_array([$this, $method], $args);
        $this->event('afterAction');

        return $result;
    }

    public function className($short=false)
    {
        $baseName = substr(get_class($this), stripos(get_class($this), 'Controller\\')+strlen('Controller\\'));

        $baseName = substr($baseName, 0, strripos($baseName, 'Controller'));

        $classes = explode('\\', $baseName);

        foreach ($classes as &$item) {
            $item = strtolower(substr(preg_replace('/[A-Z]{1}/', '-$0', $item), 1));
        }

        if($short) $classes = [$classes[count($classes)-1]];

        return implode('/', $classes);
    }

    public function urlName() {
        $baseName = substr(get_class($this), stripos(get_class($this), 'Controller\\')+strlen('Controller\\'));

        $baseName = substr($baseName, 0, strripos($baseName, 'Controller'));

        $classes = explode('\\', $baseName);

        foreach ($classes as &$item) {
            $item = strtolower(substr(preg_replace('/[A-Z]{1}/', '-$0', $item), 1));
        }

        return implode('_', $classes);
    }


    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param $name
     * @param string $module
     * @return \Evo\ModelDb
     */
    protected function loadModel($name, $module=false)
    {
        $name = str_replace('/', '\\', $name);

        $pos = stripos(get_class($this), 'controller\\');

        if($module === false) {
            if ($pos === 0) {
                $path = '\\';
            } else {
                $path = substr(get_class($this), 0, $pos);
            }
        } else {
            $module = Evo::app()->getAlterModule($module);
            $path = $module->getPath();
        }

        $model = "{$path}Model\\$name";
        $model = new $model();
        return $model;
    }

    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param null $view
     * @param array $data
     * @param Evo\Module $module
     * @return string
     */
    public function render($view = null, $data = array(), $module = null)
    {
        $path = $this->getPath();
        $module = $module ? $module->getPath() : $this->module->getPath();

        if(is_array($view)) {
            $data = $view;
            $view = strtolower($this->action);
        }

        if(!$view) {
            $view = strtolower($this->action);
        }

        $view = strpos($view, '/') !== 0 ? "view/$path/$view" : "view/$view";



        try {
            $this->view->setLayout($module.'/'.$view);
            return $this->view->render($data);
            //return Evo::app()->view->renderPartial($module.'/'.$view, $data);
        } catch (Evo\Exception\FileNotFoundException $e) {
            echo 'View not found ' . $this->className() . ' ' . $view;
            \Evo\Debug::dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        }
    }

    /**
     * @param $view
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function renderAjax($view, $data = array())
    {
        $path = $this->getPath();
        $module = Evo::app()->module->getPath();
        if(is_array($view)) {
            $data = $view;
            $view = strtolower($this->action);
        }

        if(!$view) {
            $view = strtolower($this->action);
        }

        $view = strpos($view, '/') !== 0 ? "view/$path/$view" : "view/$view";

        try {
            $this->view->setLayout($module.'/'.$view);
            return Evo::app()->view->returnAjax($this->view->render($data), true);

            //return Evo::app()->view->renderPartial($module.'/'.$view, $data);
        } catch (Evo\Exception\FileNotFoundException $e) {
            echo 'View not found ' . $this->className() . ' ' . $view;
            \Evo\Debug::dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        }

    }

    protected function event($eventName)
    {
        if(method_exists($this, $eventName)) {
            $this->$eventName();
        }

        parent::event($eventName);
    }

    public function getTemplates()
    {
        // TODO
    }

    /**
     * @param $helperName
     * @return object
     */
    protected function getHelper(string $helperName)
    {
        $arguments = func_get_args();
        $arguments[0] = 'controller/'.$arguments[0];
        return call_user_func_array([$this, '_getHelper'], $arguments);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws Exception\BehaviourException
     */
    public function __call($name, $arguments)
    {
        if(method_exists($this, 'action'.$name)) {
            if(!$this->actionRoot && $this->action) {
                $this->actionRoot = $this->action;
            }
            $this->setAction($name);
            $result = call_user_func_array([$this, 'action'], $arguments);
            if($this->actionRoot) {
                $this->action = $this->actionRoot;
            }
            return $result;
        }
        throw new Evo\Exception\BehaviourException("Method $name not found in Controller {$this->className()}");
    }


}