<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 19.12.16
 * Time: 18:54
 */

namespace Evo\Filter;

use Evo\Filter;

class NoSql extends Filter
{
    public function id()
    {
        return 'ns';
    }

    public function sql(){}
}