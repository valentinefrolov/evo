<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 03.02.16
 * Time: 12:41
 */

namespace Evo\Interfaces;


interface Permission
{
    function checkPermission(AuthModel $model=null, $route=null);
}