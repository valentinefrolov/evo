<?php

namespace Evo\Rule;

use Evo\Rule;
use Evo;
/**
 * @author frolov
 */
class Email extends Rule
{
    protected function check()
    {
        $this->checkEmail();
    }

    private function checkEmail()
    {
        if($this->field->value() && !filter_var($this->field->value(), FILTER_VALIDATE_EMAIL)) {
            $this->makeError();
        }
    }
}