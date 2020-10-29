<?php

namespace Evo\Helper\View\Field\Table;

use Evo\Helper\View\Field\TableField;

class DateTime extends TableField
{
    public $today = false;

    protected function html()
    {
        $helper = $this->getHelper('Date');

        return !$this->value && !$this->today ? '' :$helper::regexp('d M Y, H:i:s', $this->value, 'p');
    }
}