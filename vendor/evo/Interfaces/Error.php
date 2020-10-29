<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 03.02.16
 * Time: 12:54
 */

namespace Evo\Interfaces;


interface Error
{
    function setException(\Exception $e);
}