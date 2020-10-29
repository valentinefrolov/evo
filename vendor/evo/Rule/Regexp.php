<?php

namespace Evo\Rule;

use Evo;
use Evo\Rule;
/**
 * Description of regexp
 *
 * @author frolov
 */
class Regexp extends Rule
{
    protected $pattern = '';
    
    protected function check()
    {
        $this->checkRegexp();
    }
    
    protected function checkRegexp()
    {
        if($this->field->value() && !preg_match($this->pattern, $this->field->value())) {
            $this->makeError([], 'wrong_regexp');
        }
    }
}
