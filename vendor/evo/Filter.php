<?php

namespace Evo;

use Evo;
use Evo\Event;
use Evo\Model;
use Evo\Interfaces\FieldEntity;
use ReflectionClass;

abstract class Filter extends Event
{
    public $field = null;
    public $value = null;
    protected $state = null;

    protected $request = null;

    protected $singleton = false;

    protected $name = '';
    protected $executed = false;

    abstract public function id();
    abstract public function sql();
    
    public function __construct(FieldEntity $field, Model $model, $conf=[])
    {
        $this->request = Evo::app()->request;
        $this->field = $field;

        if($conf) {
            $reflection = new ReflectionClass($this);
            foreach($reflection->getProperties() as $property) {
                $p = $property->name;
                if($property->class == $reflection->name && isset($conf[$p])) {
                    $this->$p = $conf[$p];
                }
            }
            if(isset($conf['name'])) {
                $this->name = $conf['name'];
            }
        }
        if(!$this->name) {
            $this->name = 'f'.hash('crc32b', $this->field->model->className().'_'.$this->field->name.'_'.$this->id());
        }

    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function execute()
    {
        if($this->getState() && !$this->executed) {
            $this->sql();
        }
        $this->executed = true;
    }
    
    public function delete()
    {
        return 'f'.hash('crc32b', $this->getName().'_delete');
    }
    
    public function prepare()
    {
        $value = $this->request->get($this->getName());

        if($this->request->get($this->delete())) {
            $this->request->delete($this->getName());
            $this->request->delete($this->delete());
        } else if(!is_null($value) && ((is_string($value) && strlen($value)) || is_array($value))) {
            $this->value = $this->value ? $this->value : $value;
        }

        if($this->value !== null) {
            $this->state(true);
            if($this->singleton) {
                foreach($this->field->model->getFields() as $field) {
                    foreach($field->getFilter() as $filter) {
                        if($filter != $this) {
                            $filter->state(false);
                        }
                    }
                }
            }
        }

    }

    public function __toString()
    {
        return $this->getName();
    }

    public function singleton($is=true)
    {
        $this->singleton = $is;
    }

    public function state($bool=true)
    {
        if($this->state !== false) {
            $this->state = $bool;
        }
    }

    public function getState()
    {
        return $this->state;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

}