<?php

namespace Evo;

use Closure;
use Evo;
use Evo\Exception\FileNotFoundException;


// TODO сделать разные виды ассетов - к примеру, скрипты, стили и т.п.
class View extends Event
{
    /** @var Evo\Locator  */
    protected $locator    = null;
    /** @var Evo\Request  */
    protected $request    = null;
    /** @var Evo\Lang  */
    protected $lang       = null;
    /** @var Evo\Controller|null  */
    protected $controller = null;
    /** @var array  */
    protected $functions = [];

    protected $html = [];

    protected $layout = '';

    public function __construct(Evo\Controller $ctrl = null)
    {
        $this->locator    = Evo::app()->locator;
        $this->request    = Evo::app()->request;
        $this->lang       = Evo::app()->lang;
        if($ctrl) {
            $this->controller = $ctrl;
        }
    }

    public function __call($name, $arguments)
    {
        return Html::__callStatic($name, $arguments);
    }

    public function setLayout($layout)
    {
        if ($_ = Evo::getSourcePath($layout.'.php')) {
            $this->layout = $_;
        } else {
            throw new FileNotFoundException($layout ?? '');
        }
        $this->event('setLayout');
    }

    /**
     * @param array $config
     * @return object
     * @throws Exception\ClassNotFoundException
     */
    public function ajax(array $config = [])
    {
        //return new Ajax($config);
        return $this->getHelper('ajax', $config);
    }

    public function ajaxBlock($id = '')
    {
        return Evo::app()->view->ajaxBlock($id);
    }

    /**
     * @param $helperName
     * @return object
     */
    protected function getHelper(string $helperName)
    {
        $arguments = func_get_args();
        $arguments[0] = 'view/'.$arguments[0];
        return call_user_func_array([$this, '_getHelper'], $arguments);
    }

    public function decode($text) {
        return htmlspecialchars_decode($text);
    }

    public function encode($text) {
        return htmlspecialchars($text);
    }

    public function registerScriptSrc(string $url, $d = [], $a = '')
    {
        Evo::app()->view->registerScriptSrc($url, $d, $a);
    }

    public function registerInlineScript(string $src, $d = [], $a = '', string $scope=null)
    {
        Evo::app()->view->registerInlineScript($src, $d, $a, $scope);
    }

    public function getSource(string $name)
    {
        if(file_exists(Evo::getWebDir().'/'.$name)) {
            return file_get_contents(Evo::getWebDir().'/'.$name);
        }
        return '';
    }

    public function set($var, $data)
    {
        /*if($var == 'title') {
            \Evo\Debug::dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        }*/

        $this->html[$var] = $data;
    }

    public function get($var, $return = null)
    {
        return !empty($this->html[$var]) ? $this->html[$var] : $return;
    }

    /**
     * @param $__
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function render(array $__)
    {
        if(Evo::app()->view->getBuffer()) {
            ob_start();
            extract($__);
            unset($__);
            //\Evo\Debug::dump($this->layout, false);
            require $this->layout;
            $result = ob_get_clean();
            return $result;
        }
        return '';
    }

    protected function includeView($__, array $data=[])
    {
        extract($data);
        unset($data);

        if(strpos($__, '/') === 0) {
            $__ = 'view'.$__.'.php';
        } else {
            $__ = 'view/'.basename(dirname(debug_backtrace()[0]['file'])).'/'.$__.'.php';
        }

        $path = Evo::getSourcePath(Evo::app()->module->getPath().'/'.$__);

        if(!$path) {
            echo 'View not found ' . $__;
            \Evo\Debug::dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        }

        require $path;
    }

    public function js(string $string) {
        try {
            return $this->getHelper('Js', $string);
        } catch (\Exception $e) {
            return $string;
        }
    }

    public function quote($string) {
        return "'+$string+'";
    }

    private function register($name, callable $function) {
        $this->functions[$name] = $function;
    }

    private function call($name) {
        $args = func_get_args();
        array_shift($args);
        $function = Closure::bind($this->functions[$name], $this, $this);
        return call_user_func_array($function, $args);
    }



}