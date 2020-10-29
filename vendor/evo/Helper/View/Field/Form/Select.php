<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 11:03
 */

namespace Evo\Helper\View\Field\Form;

use Evo\Helper\View\Field\FormDataField;

class Select extends FormDataField
{

    protected $template = '<div class="form-row select">{label}{html}{error}</div>';

    protected function html()
    {
        if(!empty($this->inputAttributes['placeholder'])) {
            $this->inputAttributes['title'] = $this->inputAttributes['placeholder'];
            $this->inputAttributes['data-placeholder'] = $this->inputAttributes['placeholder'];
            unset($this->inputAttributes['placeholder']);
            unset($this->inputAttributes['value']);
        }

        return $this->select($this->options, $this->inputAttributes);
    }

    protected function data()
    {
        $options = [];

        $index = 0;

        foreach($this->data as $id => $title) {
            $config = [];
            $config['value'] = $id;
            if(is_int($this->selected) && $index++ == $this->selected) {
                $config['selected'] = 'selected';
            }
            $options[] = $this->option($title, $config);
        }

        return implode(PHP_EOL, $options);
    }
}