<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 01.03.16
 * Time: 14:09
 */

namespace Evo\Helper\View\Field\Table;

use Evo\Helper\View\Field\TableField;

class Text extends TableField
{

    protected function html()
    {
        return $this->value;
    }
}