<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 11:03
 */

namespace Evo\Helper\View\Field\Form;

use Evo\Helper\View\Field\FormField;

class Textarea extends FormField
{
    protected function html()
    {
        $attributes = $this->inputAttributes;
        unset($attributes['value'], $attributes['type']);

        return $this->textarea($this->value, $attributes);
    }
}