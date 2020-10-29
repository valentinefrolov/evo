<?php


namespace Evo\Rule;

/**
 * Description of checkbox
 *
 * @author frolov
 */
class Checkbox extends Tinyint
{
    protected $min = 0;
    protected $max = 1;
    
    public function input($val)
    {
        return $val ? 1 : 0;
    }
}
