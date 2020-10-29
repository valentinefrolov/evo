<?php


namespace Evo\Filter;

use Evo\Filter;

class Exec extends Filter
{
    /** @var callable  */
    protected $function = null;

    public function id()
    {
        return 'e';
    }

    public function sql()
    {
        $method = $this->function;
        $method($this);
    }
}