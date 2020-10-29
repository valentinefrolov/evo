<?php

namespace Evo\Helper\View\Filter;

use Evo\Helper\View\Filter;

class Search extends Filter
{
    protected function html()
    {
        $title = isset($this->attributes['title']) ? $this->attributes['title'] : '';
        $value = $this->getValue();
        $data = $value ? [$this->filter->getName() => $this->getValue()] : [$this->filter->delete() => true];

        $ajax = $this->ajax()->data($data)->pushState(true)
            ->url($this->request->get(null,null,true));

        return $this->div($ajax->a($title . $this->getInnerHtml($value), $this->attributes), ['class' => 'filter-search']);
    }

    protected function getFilter($field)
    {
        return $field->getFilter('search');
    }
}