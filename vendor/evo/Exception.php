<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 03.02.16
 * Time: 15:59
 */

namespace Evo;

use Evo;
use Exception as StdException;
use Throwable;

abstract class Exception extends StdException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $stack = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 0, 5);
        array_unshift($stack, get_class($this) . ' ' . $message);

        Evo\Debug::log($stack, 'exception.log', false);
    }
}