<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 28.01.16
 * Time: 14:35
 */

namespace Evo\Interfaces;


interface Configurable
{
    function checkConfig(array $config = []);
}