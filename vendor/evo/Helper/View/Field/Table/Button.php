<?php
/**
 * Created by Valentin Frolov valentinefrolov@gmail.com
 * For Aplex
 * Project Evo Engine Framework / Aplex Framework / Aplex CMS
 * Date: 27.03.2016, time: 18:55
 */

namespace Evo\Helper\View\Field\Table;

use Evo\Helper\View\Field\TableField;

class Button extends TableField
{
    protected function html()
    {
        $this->inputAttributes['type'] = 'button';

        return $this->button($this->value, $this->inputAttributes);
    }
}