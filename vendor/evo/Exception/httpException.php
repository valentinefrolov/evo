<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 03.02.16
 * Time: 16:12
 */

namespace Evo\Exception;

use Evo\Exception;

class HttpException extends Exception
{
    protected $stateCode = 404;

    public function getStateCode()
    {
        return $this->stateCode;
    }
}