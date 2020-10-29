<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 16.12.16
 * Time: 17:11
 */

namespace Evo\Helper\View\Filter\Ajax;

use Evo;
use Evo\Helper\View\Filter;

class WhereDate extends Filter
{
    protected function html()
    {
        $this->registerScriptSrc('/asset/datetimepicker/build/jquery.datetimepicker.full.js', "jquery", 'datetimepicker-js');
        Evo::app()->view->addScript('$.datetimepicker.setLocale("'.strtolower($this->lang->getLocale()).'");', null, 'datetimepicker-js');
        Evo::app()->view->addStyle('<link rel="stylesheet" type="text/css" href="/asset/datetimepicker/build/jquery.datetimepicker.min.css">', 'datetimepicker-css');

        $name = $this->field->name.$this->filter->getName();

        $ajax = $this->ajax()
            ->pushState(true)
            ->block()
            ->url($this->locator->route($this->request->route(), [$this->filter->getName() => $this->locator->js("$('#$name').val()")], null, true));

        Evo::app()->view->addScript("
                var picker = $('#$name').datetimepicker({timepicker:false, format:'Y-m-d', onSelectDate: function(e){{$ajax}}});
                $('#$name').blur(function(){{$ajax}});
            ");



        return $this->div($this->input(['class' => $this->filter->value, 'id' => $name, 'value' => $this->request->get($this->filter->getName())]), ['class' => 'filter-date']);
    }

    protected function getFilter($field)
    {
        $filter = $field->getFilter('where');

        if(!$filter) {
            $filter = $field->getFilter('having');
        }

        return $filter;
    }
}