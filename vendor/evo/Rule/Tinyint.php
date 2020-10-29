<?php

namespace Evo\Rule;

use Evo;
use Evo\Rule;

/**
 * Description of Integer
 *
 * @author frolov
 */
class Tinyint extends Rule{
    
    protected $min = 0;
    protected $max = 255;
    
    protected function check()
    {
        $this->checkTiny();
    }
    
    private function checkTiny() {
        if($this->field->value() < $this->min) {
            $this->makeError([$this->min], 'lessThan');
        }
        
        if($this->field->value() > $this->max) {
            $this->makeError([$this->max], 'greaterThan');
        }
    }

    public function input($val)
    {
        return $val ? $val : 0;
    }
}
