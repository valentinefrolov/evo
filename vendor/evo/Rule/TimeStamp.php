<?php

namespace Evo\Rule;

use Evo\Rule;
use Evo;

class TimeStamp extends Rule
{
    protected function check()
    {
        if(!$this->field->value()) {
            $this->field->value(null);
        }
        $this->checkDate();
    }

    private function checkDate()
    {
        if(!preg_match('/^\d+$/', $this->field->value())) {
            $this->makeError();
        }
    }
}