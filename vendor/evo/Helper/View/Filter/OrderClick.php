<?php

namespace Evo\Helper\View\Filter;


use Evo\Field;
use Evo\Helper\View\Filter;
/**
 * Description of Order
 *
 * @author frolov
 */
class OrderClick extends Filter
{
    protected function getValue()
    {
        switch($this->filter->value) {
            case 'ASC':
                return 'DESC';
            case 'DESC':
                return null;
            default:
                return 'ASC';
        }
    }



    protected function html()
    {
        return '';
    }

    protected function getFilter($field)
    {
        return $field->getFilter('order');
    }
}
