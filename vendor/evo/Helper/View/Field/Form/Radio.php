<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 11:03
 */

namespace Evo\Helper\View\Field\Form;

use Evo\Helper\View\Field\FormDataField;

class Radio extends FormDataField
{

    protected $template = '<div class="form-row radio">{label}{html}{error}</div>';


    protected function html()
    {
        return $this->options;
    }


    protected function data()
    {
        $options = [];

        $this->inputAttributes['type'] = 'radio';

        $index = 0;

        foreach($this->data as $value => $title) {

            $attributes = $this->inputAttributes;
            $attributes['value'] = $value;
            $attributes['id'] = $attributes['id'] . '_' . $value;

            if(!is_null($this->value) && (is_string($this->value) && strlen($this->value)) && $this->value == $value) {
                $attributes['checked'] = 'checked';
            }
            unset($attributes['placeholder']);

            $input = $this->input($attributes);
            $options[] = $this->label($input . $this->span('', ['class' => 'button']) . $title, ['class' => 'radio-wrapper']);
        }

        return implode(PHP_EOL, $options);
    }
}