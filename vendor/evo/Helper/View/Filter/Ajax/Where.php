<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 19.12.16
 * Time: 21:17
 */

namespace Evo\Helper\View\Filter\Ajax;


class Where extends Search
{

    protected function getFilter($field)
    {
        return $field->getFilter('where');
    }
}