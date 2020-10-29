<?php

namespace Evo\Helper\View;

use Evo;
use Evo\Event;
use Evo\Interfaces\Configurable;

/**
 * Описание:
 *
 * использование id для каждого ajax запроса отключено, т.к. большой размер массива с сессиями на разных серверах может привести к тормозам
 *
 * Также можно создавать блоки, нужно это для того чтобы не прописывать каждый раз success callback вручную, а также автоматом обрезать html до и после блока
 * example (Evo\View scope):
 * $this->ajaxBegin();
 * ... some changing content with ajax action...
 * $this->ajaxEnd();
 * После выполнения аякса, будет заменено содержимое внутри методов ajaxBegin и ajaxEnd, в том числе и множественные аяксы
 *
 * Class Ajax
 * @package Evo\Helper
 */

class Ajax extends Event implements Configurable
{
    const IS = '__ai';
    const CODE = '__as';
    //const INDEX = '__an';
    const BLOCK_ID = '__abi';
    const EXTERNAL = '__aex';
    const CHAIN_TARGET = '__act';
    const CHAIN_CALLBACK = '__acc';
    const CHAIN_CALLBACK_DATA = '__acd';

    protected $config = [
        'accepts' => '',
        'async' => '',
        'beforeSend' => '',
        'cache' => '',
        'complete' => '',
        'contents' => '',
        'contentType' => '',
        'context' => '',
        'converters' => '',
        'crossDomain' => '',
        'dataFilter' => '',
        'dataType' => '',
        'error' => '',
        'global' => '',
        'headers' => '',
        'ifModified' => '',
        'isLocal' => '',
        'jsonp' => '',
        'jsonpCallback' => '',
        'method' => 'POST',
        'mimeType' => '',
        'password' => '',
        'processData' => '',
        'scriptCharset' => '',
        'statusCode' => '',
        'success' => '',
        'timeout' => '',
        'traditional' => '',
        'url' => '',
        'username' => '',
        'xhr' => ''
    ];

    protected $elementConfig = [];

    protected $data = [];
    protected $jsData = [];
    protected $getData = [];
    protected $files = [];
    protected $ajaxData = '';
    protected $stringConfig = '';
    protected $codeLength = 10;
    protected $success = [];
    protected $debug = false;
    protected $condition = '';
    protected $outer = '';

    public $refresh = false;

    public $id = '';
    public $elementTagName = '';
    public $selector = '';
    public $event = '';
    public $forme = '';
    public $break = true;
    public $external = false;
    public $pushState = false;

    public static $isInBlock = false;
    public static $activeBlock = false;

    public $index = 0;

    public static $code = '';

    /** @var int  */
    protected static $counter = 0;
    protected static $blocks = [];

    //private $executed = false;
    
    public static function is()
    {
        $request = Evo::app()->request;

        if($request->session('system.ajax')) {
            static::$code = $request->session('system.ajax');
        }

        if ($request->get(static::IS) && $request->session('system.ajax'))
        {
            return true;
        }

        return false;
    }

    public static function begin($id='')
    {
        if(
            Evo::app()->request->ajax
            &&
            (
                ( $id && Evo::app()->request->get(static::BLOCK_ID) == $id )
                ||
                ( !$id && Evo::app()->request->get(static::BLOCK_ID) == 'AjaxBlock_'.(count(static::$blocks)+1) )
            )
        ) {
            static::$activeBlock = true;
            $id = Evo::app()->request->get(static::BLOCK_ID);
        } else {
            static::$activeBlock = false;
            if(!$id) {
                $id = 'AjaxBlock_'.(count(static::$blocks)+1);
            }
        }
        static::$isInBlock = true;
        static::$blocks[] = $id;

        if(!Evo::app()->request->ajax || Evo::app()->request->get(static::EXTERNAL)) {
            return "<div id=\"$id\">";
        }
        return '';
    }


    public static function getBlock()
    {
        return Evo::app()->request->get(static::BLOCK_ID);
    }


    public static function end()
    {
        array_pop(static::$blocks);

        static::$isInBlock = false;

        if(static::$activeBlock) {
            static::$activeBlock = (bool)count(static::$blocks);
        }
        // TODO если referer != текущему - выводить блок
        if(!Evo::app()->request->ajax || Evo::app()->request->get(static::EXTERNAL)) {
            return "</div>";
        }

        return '';
    }

    /**
     * Ajax constructor.
     * @param array $config
     */
    public function __construct(array $config = null)
    {
        if(!static::$counter) {
            if(Evo::app()->request->ajax) {
                if(Evo::app()->request->session('view.route') == Evo::app()->request->route()) {
                    static::$counter = 0;
                } else {
                    static::$counter = Evo::app()->request->session('view.ajax.counter');
                }
            } else {
                Evo::app()->request->deleteSession('view.ajax.counter');
            }
        }

        $this->index = ++static::$counter;
        Evo::app()->request->session('view.ajax.counter', $this->index);

        if($config) {
            foreach ($config as $key => $value) {
                $this->_configItem($key, $value);
            }
        }
    }


    protected function _configItem($key, $value)
    {
        switch(strtolower($key)) {
            case 'id':
                $this->id = $value;
                break;
            case 'debug':
                $this->debug = true;
                break;
            case 'data':
                $this->data = array_merge($this->data, $value);
                break;
            case 'jsdata':
                $this->jsData = array_merge($this->jsData, $value);
                break;
            case 'dataget':
                $this->getData = array_merge($this->getData, $value);
                break;
            case 'condition':
                $this->condition = $value;
                break;
            case 'selector':
                $this->selector = $value;
                break;
            case 'forme':
                $this->forme = $value ? $value : '$(this).closest(\'form\')';
                break;
            case 'files':
                $this->files[] = $value;
                break;
            case 'jsurl':
                $this->config['url'] = $value;
                break;
            case 'url':
                $this->config['url'] = "'$value'";
                break;
            case 'onevent':
                $this->event = $value;
                break;
            case 'block':
                $this->data = array_merge($this->data, [static::BLOCK_ID => $value]);
                break;
            case 'pushstate':
                if($value === NULL) {
                    $value = true;
                }
                $this->pushState = $value;
                break;
            case 'external':
                $this->external = $value;
                break;
            case 'break':
                $this->break = $value? $value : !$this->break;
                break;
            case 'outer':
                $this->outer .= $value;
                break;
            case 'refresh':
                $this->refresh = $value ? $value : !$this->refresh;
                break;
            default:
                if(array_key_exists($key, $this->config)) {
                    $this->config[$key] = $value;
                } else {
                    return false;
                }
                break;
        }
        return true;
    }

    /**
     * Метод являетс адаптером для Evo\Html
     * позволяет из объекта Ajax создавать html теги
     *
     * @param string $name - имя метода
     * @param $arguments -
     * @return $this|mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if($this->_configItem($name, isset($arguments[0])?$arguments[0]:null)) return $this;

        $this->elementTagName = strtolower($name) == 'tag' ? $arguments[0] : $name;

        $i=count($arguments)-1;

        while(isset($arguments[$i])) {
            if(is_array($arguments[$i])){
                $this->elementConfig = $arguments[$i];
                break;
            }
            $i--;
        }

        $this->showElement();
        if($i===0 && !$arguments[0]) {
            $arguments[1] = $this->elementConfig;
        } else {
            $arguments[$i] = $this->elementConfig;
        }

        $result = call_user_func_array(array(Evo::app()->view, $name), $arguments);

        return $result;
    }

    // TODO неправильно парсит элемент, если открывающий тег содержит одну букву(возможно если не содержит пробелов) <a>
    public function element($html)
    {

        preg_match('/^<([a-z]+)/', $html, $matches);
        $name = $this->elementTagName = $matches[1];
        $arguments = [];
        if(preg_match('/<\/\s*'.$name.'\s*>$/', $html, $matches)) {
            // тег закрывается - вырезаем середину
            preg_match('/^<' . $name . ' ([^>]+)/', $html, $matches);
            $arguments[0] = preg_replace('/<\/\s*'.$name.'\s*>$/', '', preg_replace('/^<'.$name.' [^>]+>/', '', $html));
        } else {
            preg_match('/^<' . $name . ' (.*)\/>$/', $html, $matches);
        }

        $tag = new \SimpleXMLElement('<'.$name. ' ' . $matches[1] .'/>');
        $attributes = [];
        foreach($tag->attributes() as $a => $value) {
            $attributes[$a] = (string)$value;
        }
        $this->elementConfig = $attributes;
        $this->event('finish');
        array_push($arguments, $this->elementConfig);

        return call_user_func_array(array(Evo::app()->view, $name), $arguments);
    }

    public function get()
    {

        $this->checkConfig();
        $this->getCode();
        $this->getData();
        $this->prepareConfig();
        return $this;
    }



    public function checkConfig(array $config = [])
    {
        // TODO exceptions!
        $counter = static::$counter;

        $this->getSelector();

        if(strtolower($this->elementTagName) == 'form') {
            $this->forme = $this->selector;
        }

        if(empty($this->config['url'])) {

            $this->config['url'] = "'" . Evo::app()->locator->route(
                    Evo::app()->request->route(),
                    Evo::app()->request->get(null, null, true)
                ) . "'";
        }

        if(!$this->event) {
            if(!empty($this->elementConfig['on'])) {
                $this->event = $this->elementConfig['on'];
            } else {
                $this->getEventByTagName();
            }
        }
    }

    protected function getEventByTagName()
    {

        switch(strtolower($this->elementTagName)) {
            case 'select':
                $this->event = 'change';
                break;
            case 'form':
                $this->event = 'submit';
                break;
            case 'input':
                switch($this->elementConfig['type']) {
                    case 'file':
                        $this->event = 'change';
                        break;
                    case 'submit':
                    case 'button':
                        $this->event = 'click';
                        break;
                    default:
                        $this->event = 'blur';
                        break;
                }
                break;
            case '':
                break;
            case 'a':
            case 'button':
            default:
                $this->event = 'click';
                break;
        }
    }

    protected function getSelector()
    {
        if($this->selector) return;

        if(!$this->elementConfig && $this->forme) {
            $this->selector = $this->forme;
            $this->elementTagName = 'form';
        } else if(!empty($this->elementConfig['id'])) {
            $this->selector = "#{$this->elementConfig['id']}";
        } else if(!empty($this->elementConfig['name'])) {
            $this->selector = "{$this->elementTagName}[name={$this->elementConfig['name']}]";
        } else {
            $counter = static::$counter;
            $this->selector = "{$this->elementTagName}[data-ajax-item=$counter]";
            $this->elementConfig['data-ajax-item'] = $counter;
        }
    }

    protected function getCode()
    {
        if(!static::$code) {
            $symbols = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuVwWxXyYzZ0123456789';
            for ($i = 0; $i < $this->codeLength; $i++) static::$code .= $symbols[rand(0, strlen($symbols) - 1)];
            Evo::app()->request->session('system.ajax', static::$code);
        }
    }

    protected function getData()
    {
        $this->data[static::IS] = true;
        $this->data[static::CODE] = static::$code;

        if(static::$isInBlock && !isset($this->data[static::BLOCK_ID])) {
            $this->data[static::BLOCK_ID] = static::$blocks[count(static::$blocks)-1];
        }

        $this->data[static::EXTERNAL] = (int) $this->external;


        if($this->files) {
            $this->config['type'] = 'POST';
            $this->config['processData'] = false;
            $this->config['contentType'] = false;
            if($this->forme) {
                $this->ajaxData = "
                    var data = new FormData({$this->forme}.get(0));
                ";
            } else {
                $this->ajaxData = "
                    var data = new FormData();
                ";
                foreach($this->files as $selector) {
                    $this->ajaxData .= "
                        var files = $selector.get(0).files;
                        for(var i=0;i<files.length;i++){
                            data.append($selector.attr('name'), files[i]);
                        }
                    ";
                }
            }
            foreach($this->data as $key => $value) {
                if(is_array($value)) {
                    array_walk($value, function(&$item){$item = !Js::echo($item);});
                    $value = '['.implode(', ', $value).']';
                } else {
                    $value = Js::echo($value);
                }
                $this->ajaxData .= "
                    data.append('$key', $value);";
            }
            foreach($this->jsData as $key => $value) {
                $this->ajaxData .= "
                    data.append($key, $value);";
            }
        } else {
            if($this->forme) {
                $this->ajaxData = "var data = {$this->forme}.serializeArray();";
                foreach($this->data as $key => $value) {
                    if(is_array($value)) {
                        array_walk($value, function(&$item, $key){$item = "'$key':" . Js::echo($item);});
                        $value = '{'.implode(', ', $value).'};';
                    } else {
                        $value = Js::echo($value);
                    }
                    $this->ajaxData .= "
                        data.push({name:'$key',value:$value});
                    ";
                }
                foreach($this->jsData as $key => $value) {
                    $this->ajaxData .= "
                        data.push({name:$key,value:$value});
                    ";
                }
            } else {
                $this->ajaxData = "var data = {";
                $i = 0;
                foreach($this->data as $key => $value) {
                    $i++;
                    if(is_array($value)) {
                        array_walk($value, function(&$item, $key){
                            $item = "'$key':" . Js::echo($item);
                        });
                        $value = '{'.implode(', ',$value).'}';
                    } else {
                        $value = Js::echo($value);
                    }
                    $this->ajaxData .= $key . ': ' . $value;
                    if($i < count($this->data) || $this->jsData) {
                        $this->ajaxData .= ', ';
                    }
                }
                $i=0;
                foreach($this->jsData as $key => $value) {
                    $this->ajaxData .= $key . ': ' . $value;
                    if($i < count($this->data)) {
                        $this->ajaxData .= ', ';
                    }
                }
                $this->ajaxData .= '};';
            }
        }

        $this->config['data'] = 'data';
    }

    protected function prepareConfig()
    {
        if($this->pushState) {
            /*$url = $this->config['url'];
            if(is_string($this->pushState)) {
                $url = '('.$this->pushState.')()';
            }*/
            $this->addSuccess("history.pushState({}, '', this.url);");
        }

        $configArray = [];
        if(
            (static::$isInBlock || isset($this->data[static::BLOCK_ID]))
            &&
            !$this->config['success']
            &&
            $this->config['success'] !== false
            &&
            !$this->config['complete']
        )
        {

            $blockId
                = isset($this->data[static::BLOCK_ID]) ?
                $this->data[static::BLOCK_ID] : (isset(static::$blocks[count(static::$blocks)-1]) ? static::$blocks[count(static::$blocks)-1] : '');

            $success .= ($success = implode(';'.PHP_EOL, $this->success)) ? ';'.PHP_EOL : '';

            $this->config['success'] = "function(data){
                var html = $.parseHTML($.trim(data), document, true);
                var block = $('#$blockId');
                block.empty();
                block.append(html);
                $success
            }";
        }

        foreach(array_filter($this->config, function($value){return $value !== '';}) as $param => $value) {
            switch($param) {
                case 'dataType':
                case 'jsonp':
                case 'method':
                case 'mimeType':
                case 'password':
                case 'scriptCharset':
                case 'type':
                case 'username':
                    $configArray[] = "$param:'$value'";
                    break;
                case 'url':
                    $configArray[] = "$param:$value";
                    if($value != "'".Evo::app()->locator->route(Evo::app()->request->route(), Evo::app()->request->get(null,null,true))."'") {
                        $this->external = true;
                    }
                    break;
                case 'jsonpCallback':
                    if(preg_match('/^\w+$/', $value)) {
                        $configArray[] = "$param:'$value'";
                    } else {
                        $configArray[] = "$param:$value";
                    }
                    break;
                case 'contentType':
                    if(is_bool($value)) {
                        $value = $value ? 'true' : 'false';
                        $configArray[] = "$param:$value";
                    } else {
                        $configArray[] = "$param:'$value'";
                    }
                    break;
                case 'async':
                case 'cache':
                case 'crossDomain':
                case 'global':
                case 'ifModified':
                case 'processData':
                case 'traditional':
                    $value = $value ? 'true' : 'false';
                    $configArray[] = "$param:$value";
                    break;
                default:
                    $configArray[] = "$param:$value";
                    break;
            }
        }

        $this->stringConfig = implode(',', $configArray);

    }

    public function showElement()
    {
        $script = $this->script();

        if($this->break) {
            $script = 'e.preventDefault();' . $script;
            $script .= "return false;";
        }

        if($this->condition) {
            $script = 'if('.$this->condition.') {' . PHP_EOL . $script . PHP_EOL . '}' . PHP_EOL;
        }

        if($this->refresh) {
            Evo::app()->view->registerInlineScript("$('{$this->selector}').off('{$this->event}').on('{$this->event}', function(e){{$script}});",'*');} else {
            Evo::app()->view->registerInlineScript("$(document).on('{$this->event}', '{$this->selector}', function(e){{$script}});", '*');
        }

        if(isset($this->elementConfig['on'])) {
            unset($this->elementConfig['on']);
        }
    }

    public function script()
    {
        $this->get();

        $script = "
        {$this->outer}
        {$this->ajaxData}
        $.ajax({
            {$this->stringConfig}
        });
        ";

        return $script;

        //return Evo::app()->request->ajax ? str_replace('"', '&quot;', $script) : $script;
    }

    public function addSuccess($js)
    {
        if(!empty($this->config['success'])) {
            $after = strrpos($this->config['success'], ';');
            if($after !== false ) {
                $this->config['success'] = substr($this->config['success'], 0, $after + 1) . $js .
                    substr($this->config['success'], $after + 1);
            } else if($before = strrpos($this->config['success'], '}')) {
                $this->config['success'] = substr($this->config['success'], 0, $before) . $js .
                    substr($this->config['success'], $before);
            }
        } else {
            $this->success[] = preg_replace('/;\s*$/', '', $js);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->script();
    }


}