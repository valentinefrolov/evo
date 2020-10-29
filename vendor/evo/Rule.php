<?php

namespace Evo;

use Evo;
use Evo\Interfaces\ViewField;
use Evo\Interfaces\RuleEntity;
use Evo\Interfaces\FieldEntity;

abstract class Rule implements RuleEntity
{
    public $field = [];
    protected $error = null;
    protected $errorText = '';
    
    protected $min = null;
    protected $max = null;
    
    public function __construct(FieldEntity $field, array $params = null)
    {
        $this->field = $field;
        if($params) {
            foreach ($params as $param => $value) {
                if (property_exists($this, $param)) {
                    $this->$param = $value;
                }
            }
        }
    }

    protected function makeError($params=[], $name='')
    {
        $arr = explode('\\', get_class($this));
        $name = $name ? $name : strtolower(array_pop($arr));
        $error = 'common.error.'.$name;

        $this->error = $this->errorText ? $this->errorText : Evo::app()->lang->t($error, $params);
    }
    
    public function output(ViewField $field)
    {

    }
    
    public function input($val)
    {
        return $val;
    }
    
    public function validate() {
        $this->check();
        return (bool)!$this->error;
    }
    
    abstract protected function check();
    
    protected function checkLength()
    {        
        if($this->field->value()){
        
            if($this->min && mb_strlen($this->field->value()) < $this->min){
                $this->makeError([$this->min], 'minLength');
            }

            if($this->max && mb_strlen($this->field->value()) > $this->max){
                $this->makeError([$this->max], 'maxLength');
            }
        }
    }
    
    public function getError()
    {
        return $this->error;
    }
    
    public function getFieldValue()
    {
        return $this->field->value();
    }

    public function setErrorText($text)
    {
        $this->errorText = $text;
    }

    public function forceError() {
        $this->makeError();
    }
}