<?php

namespace Evo;


use Evo;
use Evo\Interfaces\FieldEntity;

// TODO ? избавиться полностью от title
class Field implements FieldEntity
{
    public $model = null;

    protected $cloning = false;

    public $rules = [];
    public $value = null;
    public $name = null;
    public $ruleNS = 'Evo\Rule\\';
    public $filterNS = 'Evo\Filter\NoDB\\';

    /*
    поле может иметь готовый вывод
    к примеру капча
    это связывает html поля с правилами поля
    */
    public $output = '';

    public $errors = [];
    public $data = []; // поле может быть селектом
    protected $filters = [];
    
    public $title = '';

    public function __construct(Model $model, $name, $title = null)
    {
        $this->model = $model;
        $this->title = $title;
        $this->name  = $name;
    }

    public function __clone() {
        if(!$this->cloning) {
            $this->cloning = true;
            foreach ($this->filters as $name => $filter) {
                $filter = clone($filter);
                $this->filters[$name] = $filter;
                $filter->field = $this;
            }
            foreach ($this->rules as $name => $rule) {
                $rule = clone($rule);
                $this->rules[$name] = $rule;
                $rule->field = $this;
            }
            $this->cloning = false;
        }
    }
    
    public function title($title = null)
    {
        if($title) {
            $this->title = $title;
        }
        if($this->title) {
            return $this->title;
        }
        return Evo::app()->lang->t(strtolower("model.{$this->model->name()}.{$this->name}"));
    }
    
    public function value($value=null)
    {
        if($value !== null){
            $this->value = $value;
            $this->model->data[$this->name] = $value;
        }
        return $this->value;
    }

    /**
     * @param $name
     * @return Evo\Rule
     */
    public function getRule($name)
    {
        return isset($this->rules[$name]) ? $this->rules[$name] : false;
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function ruleExists($name)
    {
        return isset($this->rules[$name]);
    }
    
    public function addRule($name, $params = null)
    {
        $rule = $this->ruleNS.ucfirst($name);
        $this->rules[$name] = new $rule($this, $params);
        return $this->rules[$name];
    }

    public function removeRule($name)
    {
        if(isset($this->rules[$name])) {
            unset($this->rules[$name]);
            return true;
        }
        return false;
    }
    
    public function data($data=null) {
        if($data !== null) {
            $this->data = $data;
        }

        return $this->data;
    }


    public function normalize($val)
    {
        foreach($this->rules as $rule) {
            $val = $rule->input($val);
        }
        return $val;
    }

    public function validate()
    {
        foreach($this->rules as $rule) {
            if(!$rule->validate()) {
                $this->errors[]= $rule->getError();
            }
        }

        return (bool)!count($this->errors);
    }

    public function addError($text)
    {
        $this->errors[] = $text;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    /**
     *
     *
     * @param null $name
     * @return Evo\Filter
     */
    public function setFilter($name=null, $conf=[])
    {
        if($name instanceof Filter) {
            $filter = $name;
            $name = get_class($filter);
            if ($pos = strrpos($name, '\\')) {
                $name = substr($name, $pos + 1);
            }
        } else {
            $fns = $this->filterNS . ucfirst($name);
            $filter = new $fns($this, $this->model, $conf);
        }
        $_name = $name;
        $counter = 0;
        while(isset($this->filters[$_name])) {
            $_name = $name . $counter++;
        }
        $this->filters[$_name] = $filter;
        return $this->filters[$_name];
    }

    public function getFilter($name=null)
    {
        if(!$name) {
            return $this->filters;
        }
        return isset($this->filters[$name]) ? $this->filters[$name] : false;
    }

    public function removeFilter($name)
    {
        if(isset($this->filters[$name])) {

            unset($this->filters[$name]);
            return true;
        }
        return false;
    }

    public function pairs($name, $key)
    {
        $value = [];

        if(!$key)
            $key = $this->model->primary;

        $v = (array)$this->value();

        foreach($v as $item) {
            $value[$item[$key]] = $item[$name];
        }
        $this->value($value);
    }

    public function toArray()
    {
        $value = [];

        if(!is_array($this->value()))
            return;


        foreach($this->value() as $item) {
            if(isset($item[$this->origin]) && isset($item[$this->model->primary])) {
                $value[$item[$this->model->primary]] = $item[$this->name];
            }
        }

        $this->value($value);
    }

}
