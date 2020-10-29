<?php

namespace Evo\Rule;


use Evo;
use Evo\Rule;
use Evo\Lang;

/**
 * Description of Integer
 *
 * @author frolov
 */
class Integer extends Rule{
    
    protected function check()
    {
        $this->checkLength();
        $this->checkInt();
    }
    
    protected function checkInt()
    {
        if($this->field->value() && !preg_match('/^\d+$/', $this->field->value())) {
            $this->makeError([], 'integerFormat');
        }
    }

    protected function checkLength()
    {
        if($this->field->value()){

            if($this->min && $this->field->value() < $this->min){
                $this->makeError([$this->min], 'minLength');
            }

            if($this->max && $this->field->value()
                > $this->max){
                $this->makeError([$this->max], 'maxLength');
            }
        }
    }

}
