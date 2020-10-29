<?php

namespace Evo\Rule;


use Evo\Rule;

/**
 * TODO rename class
 *
 * @author frolov
 */
class Str extends Rule{
    
    protected $max = 255;
    
    protected function check()
    {
        $this->checkLength();
    }
}

