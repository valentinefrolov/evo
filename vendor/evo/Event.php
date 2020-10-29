<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 21.01.16
 * Time: 16:28
 */

namespace Evo;

use Evo;
use Evo\Exception\ClassNotFoundException;

abstract class Event
{
    // associative array : eventName => array(method1, method2, ... )
    protected $_events = [];
    protected $_executed = [];
    protected $_event;

    /**
     * Регистрирует какой-либо метод или функцию на событие
     *
     * @param string $eventName - имя события
     * @param callable $method - функция или метод
     *
     * @return void
     */
    public function on($eventName, callable $method)
    {
        if(!is_callable($method)) {
            // TODO debug + log
            return;
        }

        if(!isset($this->_events[$eventName])) {
            $this->_events[$eventName] = [];
        }

        $this->_events[$eventName][] = $method;
        $this->_lateEvent($eventName, $method);
    }

    public function bindOn($eventName, callable $method)
    {
        if(!isset($this->_events[$eventName])) {
            $this->_events[$eventName] = [];
        }

        $method = $method->bindTo($this, $this);

        $this->_events[$eventName][] = $method;
        $this->_lateEvent($eventName, $method);
    }


    /**
     * Указывает сценарию на событие $eventName экземпляра
     *
     * @param string $eventName
     *
     * @return void
     */
    protected function event($eventName)
    {
        $this->_executed[] = $eventName;
        $this->_event = $eventName;
        if(isset($this->_events[$eventName])) {
            foreach($this->_events[$eventName] as $method) {
                // TODO log + debug using reflector to detect if exists class of method
                call_user_func($method);
            }
        }
    }

    private function _lateEvent($name, callable $method) {
        if(in_array($name, $this->_executed)) {
            call_user_func($method);
        }
    }


    public function getEvent()
    {
        return $this->_event;
    }

    /**
     * @param string $helperName
     * @return object
     * @throws ClassNotFoundException
     */
    protected function _getHelper(string $helperName)
    {
        $helperName = preg_replace_callback('/^\w{1}|\/\w{1}/', function($matches){
            return strtoupper($matches[0]);
        }, $helperName);

        $arguments = array_slice(func_get_args(), 1);
        $path = ($p = Evo::app()->module->getPath()) ? $p . '/': '';
        $searchNs = [str_replace('/', '\\', $path.'Helper/'.$helperName) => Evo::getSourcePath($path.'/Helper/'.$helperName.'.php')];
        if($path) {
            $searchNs[str_replace('/', '\\', 'Helper/'.$helperName)] = Evo::getSourcePath('/Helper/'.$helperName.'.php');
        }
        $searchNs[str_replace('/', '\\', 'Evo/Helper/'.$helperName)] =  Evo::getVendorPath('evo/Helper/'.$helperName.'.php');

        foreach($searchNs as $nameSpace => $found) {
            if($found) {
                return (new \ReflectionClass('\\' . $nameSpace))->newInstanceArgs($arguments);
            }
        }
        return null;
        //throw new ClassNotFoundException('Can not load a helper '.$helperName);
    }

}