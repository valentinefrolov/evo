<?php

namespace Evo\Rule;

use Evo\Rule;
use Evo;
/**
 * Description of regexp
 *
 * @author frolov
 */
class Time extends Rule
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
        if($this->field->value() && !preg_match('/\d{2}\:\d{2}\:\d{2}/', $this->field->value())) {
            $this->makeError();
        }
    }
}