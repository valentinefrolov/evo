<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 19.12.16
 * Time: 19:05
 */

namespace Evo\Helper\View\Filter\Ajax;

use Evo;
use Evo\Helper\View\Filter;



class WhereCheckbox
{
    public function html()
    {
        /*$name = $this->field->name.$this->filter->getName();

        $ajax = $this->ajax()
            ->pushState(true)
            ->url($this->locator->route($this->request->route(), [$this->filter->getName() => $this->locator->js("$('#$name').val()")], true));

        return $this->div($this->input(['class' => $this->filter->value, 'id' => $name, 'value' => $this->request->get($this->filter->getName())]), ['class' => 'filter-date']);*/
    }
}