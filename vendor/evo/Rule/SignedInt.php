<?php


namespace Evo\Rule;


class SignedInt extends Integer
{
    protected function checkInt()
    {
        if($this->field->value() && !preg_match('/^-?\d+$/', $this->field->value())) {
            $this->makeError([], 'integerFormat');
        }
    }
}