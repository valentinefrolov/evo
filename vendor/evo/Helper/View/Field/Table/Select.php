<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 14.12.16
 * Time: 11:15
 */

namespace Evo\Helper\View\Field\Table;

use Evo\Helper\View\Field\TableDataField;

class Select extends TableDataField
{
    protected function html()
    {
        $value = $this->value ?? '0';
        return isset($this->data[$value]) ? $this->data[$value] : '';
    }
}