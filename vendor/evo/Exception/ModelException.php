<?php


namespace Evo\Exception;

use Evo\Exception;
use Evo\Model;

class ModelException extends Exception
{
    public function __construct(Model $model)
    {

    }
}