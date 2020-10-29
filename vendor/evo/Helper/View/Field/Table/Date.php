<?php

namespace Evo\Helper\View\Field\Table;

use Evo\Helper\View\Field\TableField;

class Date extends TableField
{
    public $today = false;

    protected function html()
    {
        $helper = $this->getHelper('Date');

        return !$this->value && !$this->today ? '' :$helper::regexp('d M Y', $this->value, 'p');
    }
}