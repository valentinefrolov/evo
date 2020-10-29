<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 04.03.16
 * Time: 11:04
 */

namespace Evo\Helper\View\Filter\Ajax;


use Evo\Helper\View\Filter\WhereSelect as Filter;

class WhereSelect extends Filter
{
    protected function html()
    {
        return $this->div($this->ajax()
            ->pushState()
            ->refresh()
            ->block()
            ->url($this->locator->route($this->request->route(), [$this->filter->getName() => $this->quote("$(this).val()")], null, true))
            ->select(implode(PHP_EOL, $this->options)), ['class' => 'filter-where']);
    }
}