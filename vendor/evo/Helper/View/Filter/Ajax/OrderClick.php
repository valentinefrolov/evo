<?php

namespace Evo\Helper\View\Filter\Ajax;

use Evo;
use Evo\Helper\View\Filter\OrderClick as Order;

class OrderClick extends Order
{
    protected function html()
    {
        $ajax = $this->ajax()
            ->pushState(true)
            ->refresh()
            ->block()
            ->url($this->locator->route($this->request->route(), [$this->filter->getName() => $this->getValue()], null, true));

        return $this->div($ajax->a('', ['class' => $this->filter->value]), ['class' => 'filter-order']);
    }
}
