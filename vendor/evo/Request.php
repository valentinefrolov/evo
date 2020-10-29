<?php

namespace Evo;
use Evo;
use Evo\Helper\View\Ajax;


/**
 * INPUT FORMAT controller/action
 *
 * NOT:
 *
 * /controller/action
 *
 * AND NOT:
 *
 * controller/action/
 *
 *
 * @method string route();
 * @method array|string session(string $name = null, $value = null);
 * @method array|string cookie(string $name = null, $value = null, $ttl = null, $httpOnly = false);
 * @method array|string server(string $name = null);
 */
class Request
{
    public $ajax = false;
    public $type = 'get';

    protected $_ttl    = 60*60*24*365; // 1 year
    protected $storage = null;
    protected $secure = false;


    public function __construct($uri)
    {
        if (
            (
                defined(PHP_SESSION_NONE) &&
                function_exists('session_status') &&
                session_status() == PHP_SESSION_NONE
            )
            ||
            session_id() == ''
        ) {
            session_start();
        }

        $this->type = strtolower($_SERVER['REQUEST_METHOD']);

        $this->storage  = new Storage();

        $this->storage->set('route', preg_replace('/^\/|\/$/', '', preg_split('/[?&]/', $uri)[0]));

        $this->storage->set('post', $this->safe($_POST));
        $this->storage->set('get', $this->safe($_GET));
        $this->storage->set('request', $this->safe($_REQUEST));
        $this->storage->set('session', $this->safe($_SESSION));
        $this->storage->set('cookie', $this->safe($_COOKIE));
        $this->storage->set('files', $this->prepareFiles($_FILES));
        $this->storage->set('server', $_SERVER);

        $this->deleteSession('system.flash.0');

        if($this->session('system.flash.1')) {
            foreach(array_keys($this->session('system.flash.1')) as $key) {
                $this->session("system.flash.0.$key", $this->session("system.flash.1.$key"));
            }
        }

        $this->deleteSession('system.flash.1');

        $config = Evo::getConfig('app', '');
        if($config['protocol'] == 'https://') {
            $this->secure = true;
        }

        Evo::app()->on('getRequest', function() {
            $this->ajax = Ajax::is();
        });

        Evo::app()->on('finish', function() {
            $_SESSION = $this->storage->get('session');
        });
    }

    protected function safe($array) {
        $safe = [];
        foreach($array as $key => $value) {
            if(!is_array($value)) {
                $safe[$key] = htmlspecialchars($value);
            } else {
                $safe[$key] = $this->safe($value);
            }
        }
        return $safe;
    }

    public function decode($entry) {

        if(is_array($entry)) {
            $result = [];
            foreach($entry as $key => $value) $result[$key] = $this->decode($value);
        } else {
            $result = htmlspecialchars_decode($entry);
        }
        return $result;
    }


    public function form($name = '') {

        $files = (array)$this->files($name);

        if($data = $this->get($name)){

            $key = array_keys($data)[0];
            if(preg_match('/^\d+$/', $key)){
                return array_merge($files, $data[$key]);
            }
            return array_merge($files, $data);
        }
        if($files) {
            return $files;
        }
        return null;
    }

    public function flash($param = null, $value = null)
    {
        if($value) {
            $this->setSession("system.flash.1.$param", $value);
            return true;
        } else if($param){
            return $this->session("system.flash.0.$param");
        } else {
            return $this->session('system.flash.0');
        }
    }

    public function inject($name, $value)
    {
        if($this->type == 'post') {
            $params = explode('.', $name);
            if($params[0] == 'get') {
                $params[0] = 'request';
                $this->storage->set(implode('.', $params), $value);
            }
        }
        $this->storage->set($name, $value);
    }

    protected function setCookie($param, $value, $time = null, $httpOnly = false)
    {
        setcookie($param, $value, time()+($time ? $time : $this->_ttl), '/', '', $this->secure, $httpOnly);
        $this->storage->set('cookie.'.$param, $value);
        $this->storage->set('cookie.'.$param.'ttl', $time ? $time : $this->_ttl);
    }

    protected function setSession($param, $value)
    {
        //\Evo\Debug::log($param);
        $this->storage->set('session.'.$param, $value);
        //\Evo\Debug::log($this->storage->get('session'));
    }

    public function post($var=null,$value=null)
    {
        if($var === null) {
            return $this->storage->get('post');
        } else if($value !== null) {
            $this->storage->set("post.$var", $value);
        }
        return $this->storage->get("post.$var");

    }

    public function get($var=null,$value=null,$onlyGet=false)
    {
        $return = $this->storage->get('get');

        if(!$onlyGet && $value === null && $this->type == 'post') {
            $return = $var ? $this->storage->get("request.$var") : $this->storage->get('request');
        } else if($var) {
            if($value!==null) {
                $this->storage->set("get.$var", $value);
                if($this->type == 'post') {
                    $this->storage->set("request.$var", $value);
                }
            }
            if($this->type == 'post') {
                $return = $this->storage->get("request.$var");
            } else {
                $return = $this->storage->get("get.$var");
            }
        }

        return $return;
    }

    public function __call($name, $args)
    {
        if(isset($args[0])) {
            if(isset($args[1])) {
                if($name == 'session' || $name == 'cookie') {
                    $method = 'set' . $name;
                    call_user_func_array([$this, $method], $args);
                } else {
                    $this->storage->set($name.'.'.$args[0], $args[1]);
                }
                return true;
            }
            return $this->storage->get($name.'.'.$args[0]);
        } else {
            return $this->storage->get($name);
        }
    }

    public function delete($param = null)
    {
        $method = 'delete' . $this->type;
        $this->$method($param);
    }

    public function deleteGet($param = null)
    {
        if($param) {
            $this->storage->delete('get.'.$param);
            $this->storage->delete('request.'.$param);
        } else {
            $this->storage->delete('get');
            $this->storage->delete('request');
        }
    }

    public function deletePost($param = null)
    {
        if($param) {
            $this->storage->delete('post.'.$param);
            $this->storage->delete('request.'.$param);
        } else {
            $this->storage->delete('post');
            $this->storage->delete('request');
        }
    }

    public function deleteCookie($param = null)
    {
        if($param) {
            setcookie($param, '', time() - $this->_ttl, '/');
            $this->storage->delete('cookie.'.$param);
        } else {
            foreach(array_keys($this->cookie()) as $key) {
                setcookie($key, '', time() - $this->_ttl, '/');
            }
            $this->storage->delete('cookie');
        }
    }

    public function deleteSession($param = null)
    {
        if($param) {
            $this->storage->delete('session.'.$param);
            $_SESSION = $this->storage->get('session');
        } else {
            session_unset();
            $this->storage->delete('session');
        }
    }

    /*public function deleteFiles($model=null, $field=null)
    {
        if(!$model) {
            $this->storage->delete('files');
        } else if (!$field) {
            $this->storage->delete('files.'.$model);
        } else {
            $data = $this->storage->get('files.' . $model);
            foreach (array_keys($data) as $key) {
                $this->storage->delete('files.' . $model . '.' . $key . '.' . $field);
            }
        }
    }*/


    protected function prepareFiles(array $files) {
        $_files = $files;
        foreach ($files as $form => $items) {
            if(
                !empty($items['name']) && is_array($items['name'])
                &&
                !empty($items['type']) && is_array($items['type'])
                &&
                !empty($items['tmp_name']) && is_array($items['tmp_name'])
                &&
                !empty($items['error']) && is_array($items['error'])
                &&
                !empty($items['size']) && is_array($items['size'])
            ) {
                // вложенные файлы в название модели
                unset($_files[$form]);
                $_files[$form] = [];

                foreach($items['name'] as $prop => $_form) {
                    if($items['name'][$prop]) {
                        if (is_array($_form)) {
                            foreach ($_form as $_prop => $data) {
                                $_files[$form][$prop]['name'] = $items['name'][$prop];
                                $_files[$form][$prop]['type'] = $items['type'][$prop];
                                $_files[$form][$prop]['tmp_name'] = $items['tmp_name'][$prop];
                                $_files[$form][$prop]['error'] = $items['error'][$prop];
                                $_files[$form][$prop]['size'] = $items['size'][$prop];
                            }
                        } else {
                            $_files[$form][$prop]['name'] = $items['name'][$prop];
                            $_files[$form][$prop]['type'] = $items['type'][$prop];
                            $_files[$form][$prop]['tmp_name'] = $items['tmp_name'][$prop];
                            $_files[$form][$prop]['error'] = $items['error'][$prop];
                            $_files[$form][$prop]['size'] = $items['size'][$prop];
                        }
                    }
                }
            }
        }
        return $_files;

    }

    /**
     *
     * Standartizer to files output
     *
     *
     * @param null $name
     * @param mixed $data
     * @return array|null
     */

    public function files($name = null, $data = null)
    {
        if($data) {
            $path = $name ? "files.$name" : "files";
            $this->storage->set($path, $data);
        }
        if (!$name) {
            return $this->storage->get('files');
        }
        return $this->storage->get("files.$name");

        /*if (!$files = $this->storage->get("files.$name")) {
            $path = explode('.', $name);
            $name = $path[count($path) - 1];
            unset($path[count($path) - 1]);
            $path = implode('.', $path);

            if (($tmp_name = $this->storage->get("files.$path.tmp_name.$name")) &&
                ($type = $this->storage->get("files.$path.type.$name"))
            ) {
                $files = [
                    'name' => $this->storage->get("files.$path.name.$name"),
                    'tmp_name' => $tmp_name,
                    'type' => $type,
                    'error' => $this->storage->get("files.$path.error.$name"),
                    'size' => $this->storage->get("files.$path.size.$name")
                ];
            }
        } else if(
            ($files = $this->storage->get("files.$name")) &&
            !empty($files['tmp_name']) && is_array($files['tmp_name']) &&
            ($key = array_keys($files['tmp_name'])[0]) &&
            preg_match('/^\d+$/', $key)
        ) {
            $temp = $files;
            $files = [];
            $names = array_keys($temp['tmp_name'][$key]);
            foreach($names as $name) {
                $files[$name] = [
                    'name' => $temp['name'][$key][$name],
                    'tmp_name' => $temp['tmp_name'][$key][$name],
                    'type' => $temp['type'][$key][$name],
                    'error' => $temp['error'][$key][$name],
                    'size' => $temp['size'][$key][$name],
                ];
            }
        } else if($files && isset($files['tmp_name']) && isset($files['type'])){

            $temp = $files;
            $files = [];
            $names = array_keys($temp['tmp_name']);

            foreach($names as $name) {
                $files[$name] = [
                    'name' => $temp['name'][$name],
                    'tmp_name' => $temp['tmp_name'][$name],
                    'type' => $temp['type'][$name],
                    'error' => $temp['error'][$name],
                    'size' => $temp['size'][$name],
                ];
            }
        }


        return $files;*/
    }

}