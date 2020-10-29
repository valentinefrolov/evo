<?php

namespace Evo;

use Evo;

/**
 * Класс локации
 */
class Locator
{
    const ROUTE_DATA_ID = '__routeData__';

    /** @var bool|string  */
    protected $moduleName = '';
    /** @var Module  */
    protected $module = null;
    /** @var Evo\Cache  */
    protected $cache = null;
    /** @var array  */
    protected $routeData = [];
    /** @var array Evo\Cache */
    protected $children = [];
    /** @var Locator */
    protected $parent = null;
    /** @var array */
    protected $injected = [];
    /** @var int  */
    protected $routeIndex = -1;

    public function __construct(string $module)
    {
        $this->moduleName = $module;

        if(Evo::app()->module->getName() == $module) {
            $this->module = Evo::app()->module;
        } else {
            $this->module = Evo::app()->getAlterModule($module);
            $this->module->init();
        }

        $this->cache = new Cache('loc_'.($module?'_'.$module:''));
        $this->routeData = $this->prepareRoutes($this->module->getRoutes());

        $item = $this->getItemFromUrl(Evo::app()->request->route());

        if($item) {
            $this->routeIndex = $item[5];
            foreach($item[3] as $name => $value) {
                $this->injected[$name] = $value;
                Evo::app()->request->inject("get.$name", $value);
            }
            foreach($item[4] as $name => $value) {
                $this->injected[$name] = $value;
                Evo::app()->request->inject("get.$name", $value);
            }
            Evo::app()->request->inject('route', $item[0]);
        }

        //\Evo\Debug::log($this->routeData);
    }

    public function getRouteIndex()
    {
        return $this->routeIndex;
    }

    public function getInjected()
    {
        $injected = $this->injected;

        $initial = Evo::app()->request->ajax ?
            Evo::app()->request->get() :
            Evo::app()->request->get(null,null,true);

        foreach($initial as $key => $value) {
            if(empty($injected[$key])) {
                $injected[$key] = $value;
            }
        }

        return $injected;
    }

    protected function _getCurrent(string $module = null) : Locator
    {
        if($module === null) {
            $module = Evo::app()->module->getName();
        }
        return $this->getCurrent($module);
    }

    protected function getItemFromUrl($url)
    {
        if($this->cache->has('link_'.$url)) {
            return $this->cache->get('link_'.$url);
        }
        foreach($this->routeData as $index => $item) {

            // if we got variable in pattern, we don't get it in $url
            // adding them to $url
            // can't move this code to preparer of routeData, coz of different params
            if(($pos = strpos($item[1], '?')) !== false) {
                preg_match_all('/([^=]+)=<([^>]+)>/', substr($item[1], $pos+1), $matches);
                preg_match('/\/*\?/', $item[1], $sep);
                $sep = $sep[0];
                foreach ($matches[2] as $i => $getVar) {
                    if(($value = Evo::app()->request->get($getVar)) &&
                        preg_match('/^'.$item[3][$matches[2][$i]].'$/', $value)
                    ) {
                        $url .= $sep . $matches[1][$i].'='.$value; $sep = '&';
                    }
                }
            }
            if(preg_match('/^'.$item[2].'$/', $url, $matches)) {
                for($i=1;$i<count($matches);$i++) {
                    if(!empty(array_keys($item[3])[$i-1]))
                    $item[3][array_keys($item[3])[$i-1]]= $matches[$i];
                }
                $this->cache->set('link_'.$url, $item);
                return $item;
            }
        }
        return null;
    }

    protected function getAbsolutePrefix() {
        $config = Evo::getConfig('app', $this->moduleName);
        if(!$config) {
            return '';
        }
        if(empty($config['protocol'])) {
            $config['protocol'] = Evo::getConfig('app', '')['protocol'];
        }
        return $config['protocol'].$config['host'].'/';
    }
    
    protected function _route($route, $vars = [], $preserve = false) : string
    {
        $moduleUrl = $this->module->getUrl();
        if($moduleUrl && $moduleUrl != '/' && strpos($route, $moduleUrl) === 0) {
            $from = '/^'.preg_quote($moduleUrl, '/').'/';
            $route = preg_replace($from, '', $route, 1);
        }

        if($route === true) $route = Evo::app()->request->route();

        $data = preg_split('/[?&]/', $route);

        if($data) {
            $route = $data[0];
            for($i = 1; $i < count($data); $i++) {
                list($name, $value) = array_pad(explode('=', $data[$i]), 2, '');
                $vars[$name] = empty($vars[$name]) ? $value : $vars[$name];
            }
        }

        if(preg_match_all('/<([\w_\-]+):([^>]+)>/', $route, $matches)) {
            for($i=0; $i<count($matches[0]); $i++) {
                $route = str_replace($matches[0][$i], '', $route);
                $vars[$matches[1][$i]] = $matches[2][$i];
            }
        }

        if($preserve)
            $vars = array_merge(Evo::app()->request->get(null, null, true), (array)$vars);

        $cacheKey = 'r_'.$route.($vars?'?'.$this->buildQuery($vars):'');

        if($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }


        foreach($this->routeData as $index => $item) {

            if($route != $item[0]) continue;

            // compare static values
            if(count($item[4]))
                foreach ($item[4] as $key => $value) if (isset($vars[$key]) && $vars[$key] != $value) continue 2;

            // compare dynamic keys
            if(!array_diff(array_keys($item[3]), array_keys($vars))) {
                foreach ($item[3] as $key => $regex)
                    if (!isset($vars[$key]) || !preg_match('/^' . $regex . '$/', $vars[$key])) continue;

                foreach($item[4] as $key => $value)
                    if(isset($vars[$key]) && $vars[$key] != $value) continue;

                $route = $item[1];
                foreach($vars as $key => $value) {
                    if(isset($item[3][$key])) {
                        $route = str_replace('<'.$key.'>', $value, $route);
                        unset($vars[$key]);
                    }
                    if(isset($item[4][$key])) {
                        unset($vars[$key]);
                    }
                }
            }
        }

        $route = $route.($vars?'?'.$this->buildQuery($vars):'');

        $this->cache->set($cacheKey, $route);
        return $route;
    }

    public function prepareRoutes(array $routes) : array
    {
        $result = [];
        $routeIndex = 0;

        foreach($routes as $url => $route) {
            $params = $inject = $replace = [];

            // parse left side (dynamic values)
            preg_match_all('/<([^>]+)>/', $url, $entries);
            foreach($entries[1] as $index => $entry) {
                list($name, $regex) = explode(':', $entry);
                $params[$name] = $regex;
                $replace[0][] = '<'.$name.'>';
                $replace[1][] = '('.$regex.')';
            }
            if(!$entries[0]) {
                $urlMatch = str_replace(['\<','\>'], ['<', '>'], preg_quote($url, '/'));
            } else {
                $url = str_replace($entries[0], $replace[0], $url);
                $urlMatch = str_replace($replace[0], $replace[1], str_replace(['\<','\>'], ['<', '>'], preg_quote($url, '/')));
            }
            // parse right side (static)
            preg_match_all('/<([^>]+)>/', $route, $entries);
            foreach($entries[1] as $index => $entry) {
                list($name, $value) = explode(':', $entry);
                $inject[$name] = $value;
            }
            if($entries && $entries[0]) {
                $route = str_replace(array_merge($entries[0], ['?','&']), '', $route);
            }
            $result[] = [
                0 => $route,
                1 => $url,
                2 => $urlMatch,
                3 => $params,
                4 => $inject,
                5 => $routeIndex++
            ];
        }

        uasort($result, function($a, $b) {
            return strlen($b[2]) <=> strlen($a[2]);
        });

        return $result;
    }

    /**
     * @param string|null $module
     * @return Locator
     */
    public function getCurrent(string $module = null) : Locator {
        if($module === null) $module = $this->moduleName;

        if($module !== $this->moduleName) {
            if(!isset($this->children[$module])) {
                $this->children[$module] = new Locator($module);
            }
            return $this->children[$module];
        } else {
            return $this;
        }
    }

    public function route($route, $vars = [], $module = null, $preserve = false) : string
    {
        $locator = $this->_getCurrent($module);
        return preg_replace('/\/+/', '/', $locator->module->getUrl().$locator->_route($route, $vars, $preserve));
    }

    public function absoluteRoute($route = null, $vars = [], string $module = null) : string
    {
        $locator = $this->_getCurrent($module);
        $route = $locator->_route($route, $vars);
        if(strpos($route, '/') === 0) {
            $route = substr($route, 1);
        }
        return $locator->getAbsolutePrefix().$route;
    }

    public function redirect($route, $vars = [], $module = null)
    {
        ob_clean();
        if($route == '/' || strpos($route, '/') !== 0) {
            $route = $this->route($route, $vars, $module);
        }
        $_SESSION = Evo::app()->request->session();
        header('Location: ' . $route);
        exit();
    }

    public function getFromUrl(string $url, string $module = null) : array
    {
        $locator = $this->_getCurrent($module);
        $url = htmlspecialchars_decode($url);
        if (($prefix = $locator->getAbsolutePrefix()) && strpos($url, $prefix) === 0){
            $url = substr($url, strlen($prefix));
        }
        $vars = [];
        if(($pos = strpos($url, '?')) !== false) {
            foreach(explode('&', substr($url, $pos+1)) as $param) {
                list($key, $value) = array_pad(explode('=', $param), 2, '');
                $vars[$key] = $value;
            }
        }

        $item = $locator->getItemFromUrl($url);

        if($item) {
            $result = [
                'route' => $item[0],
                'vars' => [],
                'module' => $module,
            ];

            foreach($item[3] as $name => $value)
                $result['vars'][$name] = $value;

            foreach($item[4] as $name => $value)
                $result['vars'][$name] = $value;


            $result['vars'] = array_merge($result['vars'], $vars);
            return $result;
        }
        if($vars) $url = substr($url, 0, $pos);

        $result = [
            'route' => $url,
            'vars' => $vars,
            'module' => $module,
        ];
        return $result;
    }

    public function buildQuery(array $data)
    {
        foreach($data as $key => $item) {
            if(is_array($item)) {
                foreach($item as $k => $v) {
                    $data[$key.'['.$k.']'] = $this->buildQuery([$key.'['.$k.']' => $v]);
                }
                unset($data[$key]);
            } else {
                if(is_numeric($key)) {
                    $data[$key] = $item;
                } else {
                    $data[$key] = $key.'='.$item;
                }
            }
        }

        return implode('&', $data);
    }

    public function checkIsAbsolute(string $url) : bool
    {
        if(
            strpos($url, '//') === 0 ||
            strpos($url, 'http:') === 0 ||
            strpos($url, 'www') === 0
        ) return true;
        return false;
    }

    public function parseCondition(string $condition) : array
    {
        preg_match_all('/<([\w_\-]+):([^>]*)>/', $condition, $matches);
        $return = [];
        foreach($matches[1] as $i => $key) $return[$key] = $matches[2][$i];
        return $return;
    }

    public function buildCondition(array $condition) : string
    {
        $string = '';
        foreach ($condition as $key => $value) {
            $string .= "<$key:$value>";
        }
        return $string;
    }

    public function cleanRoute(string $route) {
        if(preg_match('/^[^\/]+\/.+/', $route)) {
            if($pos = strpos($route, '<'))
                return substr($route, 0, $pos);
            return $route;
        }
        return '';
    }

    public function clearCache($module = null)
    {
        $locator = $this->_getCurrent($module);
        $locator->cache->clear();
    }

}