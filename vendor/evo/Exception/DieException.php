<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 15.09.2017
 * Time: 14:08
 */

namespace Evo\Exception;

use Evo\Exception;
use Exception as Base;

class DieException extends Exception
{
    public function __construct(Base $exception)
    {
        parent::__construct($exception->getMessage(), $exception->getCode());
    }
}