<?php

namespace Evo\Rule;

use Evo;

class Password extends Regexp
{
    protected $pattern = '';

    protected function check()
    {
        if(!$this->pattern) {
            $this->pattern = '/^.{8,}$/';
        }

        $this->checkRegexp();
    }


}
