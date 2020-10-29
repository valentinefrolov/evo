<?php

namespace Evo\Rule;

use Evo\Rule;
use Evo;
/**
 * Description of regexp
 *
 * @author frolov
 */
class DateTime extends Rule
{
    protected function check()
    {
        $this->checkDate();

        if(!$this->field->value()) {
            $this->field->value(null);
        }
    }

    private function checkDate()
    {
        $value = $this->field->value();

        if($value) {
            if(!preg_match('/\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}/', $value)) {
                $this->makeError();
            } else {
                if($this->min && $value < $this->min) {
                    $this->makeError(null, 'minDate');
                }
                if($this->max && $value > $this->max) {
                    $this->makeError(null, 'maxDate');
                }
            }
        }
    }
}