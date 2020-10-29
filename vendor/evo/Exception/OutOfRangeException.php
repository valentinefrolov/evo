<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 04.09.2017
 * Time: 16:08
 */

namespace Evo\Exception;

use Evo\Exception;

class OutOfRangeException extends Exception
{
    public function __construct($value, $limit)
    {
        $this->message = 'value is ' . (string)$value . ', limit is ' . (string)$limit;
    }

}