<?php

namespace Evo\Helper\View\Filter;

use Evo;
use Evo\Helper\View\Js;

class WhereSelect extends DataFilter
{

    protected function html()
    {
        $id = $this->filter->field->model->className() .
            ucfirst($this->filter->field->name) . 'WhereSelect';

        $url = JS::route(null,array_merge($this->getWhereFilters(), [$this->filter->getName() => '$(this).val()']));

        Evo::app()->view->script("$('#$id').change(function(){
            window.location.href = $url;
        });");

        return $this->select(implode(PHP_EOL, $this->options), ['id' => $id]);
    }


    
}