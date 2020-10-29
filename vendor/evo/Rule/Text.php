<?php

namespace Evo\Rule;


use Evo\Rule;

/**
 * Description of Integer
 *
 * @author frolov
 */
class Text extends Rule{
    
    protected function check()
    {
        $this->checkLength();
    }
}

