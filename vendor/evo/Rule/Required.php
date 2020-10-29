<?php
namespace Evo\Rule;

use Evo;
use Evo\Rule;

/**
 * Description of required
 *
 * @author frolov
 */
class Required extends Rule{
    
    protected function check()
    {
        if(!$this->field->value() && $this->field->value() !== '0') {
            $this->makeError();
        } else if(is_array($this->field->value())) {
            foreach($this->field->value() as $item) {
                if(!$item && $item !== '0') {
                    $this->makeError();
                }
            }
        }
    }
}
