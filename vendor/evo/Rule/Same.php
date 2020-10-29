<?php

namespace Evo\Rule;

use Evo;
use Evo\Rule;

/**
 * Description of Integer
 *
 * @author frolov
 */
class Same extends Rule
{
    protected $as = null;
    protected $value = null;


    protected function check()
    {
        if($this->value) {
            if($this->field->value() !== $this->value) {
                $this->makeError([$this->value], 'not_the_same');
            }
        } else if($this->as) {
            $sameField = $this->field->model->getField($this->as);
            if($this->field->value() !== $sameField->value()) {
                $this->makeError([$sameField->title], 'not_the_same');
            }
        }
    }
}

