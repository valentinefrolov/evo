<?php

namespace Evo;

use DB;
use Evo;
use Evo\Interfaces\Configurable;
use Throwable;


class App extends Event implements Configurable
{
    /** @var static  */
    public static $instance = null;
    /** @var string  */
    public $host = '';
    public $secure = false;
    /** @var DB  */
    public $db = null;
    /** @var Evo\Lang  */
    public $lang = null;
    /** @var Evo\Module  */
    public $module = null;
    /** @var Evo\Controller  */
    public $controller = null;
    /** @var string  */
    public $action = '';
    /**
     * @var Evo\Locator $this->locator
     */
    public $locator = null;
    /**
     * @var Evo\Request $this->request
     */
    public $request = null;
    /**
     * @var Evo\Response $this->response
     */
    public $response = null;
    /**
     * @var Evo\Layout $this->view
     */
    public $view = null;

    protected $config = [];

    /**
     * @return static
     * @throws \Exception
     */
    public static function getInstance()
    {
        if(!static::$instance) {
            $config = Evo::getConfig('app');
            if(!empty($config[Evo::appType()])) {
                $config = array_merge($config, $config[Evo::appType()]);
                unset($config[Evo::appType()]);
            }
            static::$instance = new static();
            static::$instance->checkConfig($config);
            static::$instance->config = $config;
        }
        return static::$instance;
    }

    /**
     * @param array $config
     * @throws \Exception
     */
    public function checkConfig(array $config = [])
    {
        $important = ['host', 'defaultController', 'adminEmail'];

        foreach($important as $item) {
            if(!isset($config[$item])) {
                // TODO Config exception
                throw new \Exception("The '$item' must exists on config file for Application");
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function init()
    {
        $this->host = $this->config['host'];
        $this->secure = !empty($this->config['secure']) && $this->config['secure'] ? true: false;
        $this->dbInit();

        $this->cacheInit();

        $this->getRequest($this->getModule());
        $this->event('getModule');
        $this->event('getRequest');

        $this->getResponse();
        $this->event('getResponse');

        $this->getLanguage();
        $this->event('getLang');

        $this->module->init();
        $this->event('init');

        $this->getLocator();
        $this->event('getLocator');

        $this->getView();
        $this->event('getView');

    }

    /**
     * @throws \Exception
     */
    public function start()
    {
        try {
            if($this->request->ajax) {
                while($this->request->session('system.lock') === true) {
                    usleep(10);
                }
                $this->request->session('system.lock', true);
            }

            $this->view->setLayout($this->module->getLayout());
            $this->event('setLayout');

            $this->module->action();
            $this->event('moduleAction');

            $this->controller = $this->module->getController();
            $this->event('getController');

            $result = call_user_func_array([$this->controller, 'action'], $this->locator->getInjected());
            $this->event('action');

            $this->module->afterAction();

        } catch (Throwable $e) {
            $result = $this->module->error($e);
        }

        $this->event('beforeDraw');
        $this->view->render(['content' => $result]);
        $this->event('afterDraw');
        $this->request->session('system.lock', false);
        $this->event('finish');
    }

    /**
     * @param null $query
     * @return DB
     */
    public function db($query = null)
    {
        if($query) {
            DB::QUERY($query);
        }
        return $this->db;
    }

    protected function getResponse()
    {
        $this->response = new Response();
    }

    protected function getLocator()
    {
        $this->locator = new Locator($this->module->getName());
    }

    protected function getLanguage()
    {
        $locale = defined('LANG') ? LANG : $this->config['locale'];
        $path = ($p = $this->module->getPath()) ? $p.'/' : $p;
        $this->lang = new Lang(
            $locale,
            Evo::getSourceDir().'/' . $path . 'dict/'.strtolower($locale).'.php'
        );
    }

    protected function getView()
    {
        $this->view = new Layout();
    }

    /**
     * @param string $name
     * @return \Module|null
     * @throws \Exception
     */
    public function getAlterModule(string $name)
    {
        $config = Evo::getConfig('app', '');

        if(!$name) {
            return new \Module($config, '');
        }

        if(!empty($config['modules']) && !empty($config['modules'][$name])) {
            $uc = ucfirst($name);
            $module = "$uc\\{$uc}Module";
            return new $module($config['modules'][$name], $name);
        }
        return null;
    }

    protected function getModule()
    {
        $config = $this->config;
        $module = '\Module';
        $request = preg_replace('/(\/)*(\?)*$/', '', $_SERVER['REQUEST_URI']);

        if(!empty($this->config['modules'])) {

            $url = !empty($_SERVER['HTTP_HOST']) ?
                "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" :
                "$_SERVER[SERVER_NAME]$_SERVER[REQUEST_URI]";

            foreach($this->config['modules'] as $moduleName => $conf){
                if(strpos($url, $conf['host']) === 0) {
                    $request = substr($url, strlen($conf['host']));
                    $uc = ucfirst($moduleName);
                    $module = "$uc\\{$uc}Module";
                    $config = $this->config['modules'][$moduleName];
                    $this->module = new $module($config, $moduleName);
                    break;
                }
            }
        }

        if(!$this->module)
            $this->module = new $module($config, '');

        return $this->module->performRequest($request);
    }

    protected function getRequest($requestString)
    {
        $this->request = new Request($requestString);
    }

    /**
     * @throws \Exception
     */
    protected function dbInit()
    {
        require_once Evo::getVendorDir() . '/levin/db.php';

        $config = Evo::getConfig('db');

        foreach(['host', 'port', 'user', 'password', 'database'] as $item) {
            if(!isset($config[$item])) {
                throw new \Exception("The '$item' must exists on config file for Db");
            }
        }
        /** @var \Zend_Db \DB db */
        $this->db = DB::get_instance(
            $config['host'],
            $config['port'],
            $config['user'],
            $config['password'],
            $config['database']
        );
    }

    protected function cacheInit()
    {
        Cache::configure(Evo::getConfig('cache') || null);
    }

}