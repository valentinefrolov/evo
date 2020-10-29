<?php
/**
 * Created by Valentin Frolov valentinefrolov@gmail.com
 * For Aplex
 * Project Evo Engine Framework / Aplex Framework / Aplex CMS
 * Date: 27.03.2016, time: 18:51
 */

namespace Evo\Helper\View\Field\Table;

use Evo\Helper\View\Field\TableField;

class Time extends TableField
{
    public $today = false;

    protected function html()
    {
        $helper = $this->getHelper('Date');
        return !$this->value && !$this->today ? '' :$helper::regexp('H:i:s', $this->value, 'p');

    }
}