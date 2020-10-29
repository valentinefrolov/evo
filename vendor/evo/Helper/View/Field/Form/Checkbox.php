<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 11:03
 */

namespace Evo\Helper\View\Field\Form;

use Evo;
use Evo\Helper\View\Field\FormField;

class Checkbox extends FormField
{
    protected $template = '<div class="form-row checkbox">{html}<label for="{id}">{title}</label>{error}</div>';

    protected function html()
    {
        $this->inputAttributes['type'] = 'checkbox';

        unset($this->inputAttributes['value']);

        if($this->value) {
            $this->inputAttributes['checked'] = 'checked';
        }

        return $this->input($this->inputAttributes);
    }
}