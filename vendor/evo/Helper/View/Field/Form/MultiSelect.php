<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 18.03.16
 * Time: 13:23
 */

namespace Evo\Helper\View\Field\Form;

use Evo\Helper\View\Field\FormMultiDataField;

class MultiSelect extends FormMultiDataField
{
    protected $template = '<div class="form-row multi-select">{label}{html}{error}</div>';

    protected function data()
    {
        $options = [];

        foreach($this->data as $value => $label) {

            $attributes = [
                'type' => 'checkbox',
                'value' => $value,
                'name' => $this->inputAttributes['name'] . '['.$value.']',
                'id' => $this->inputAttributes['id'] . 'Item' . $value,
            ];
            if(isset($this->value[$value])) {
                $attributes['checked'] = 'checked';
            }

            $options[] = $this->div(
                $this->input($attributes) .
                $this->label($label, ['for' => $this->inputAttributes['id'] . 'Item' . $value]),
            ['class' => 'multi-select-item']);
        }

        return implode(PHP_EOL, $options);
    }

    protected function html()
    {

        if(!isset($this->attributes['id'])) {
            $this->attributes['id'] = $this->inputAttributes['id'];
        }
        return $this->div($this->options, $this->attributes);
    }
}