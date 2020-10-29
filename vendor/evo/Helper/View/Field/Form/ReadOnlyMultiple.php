<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 11:03
 */

namespace Evo\Helper\View\Field\Form;

use Evo\Helper\View\Field\FormDataField;

class ReadOnlyMultiple extends FormDataField
{
    protected $readOnlyValue = '';

    protected function html()
    {
        $this->inputAttributes['type'] = 'hidden';

        return $this->input($this->inputAttributes).$this->input(['value' => $this->readOnlyValue, 'readonly']);
    }

    protected function data()
    {
        foreach($this->data as $id => $name) {
            if($this->value == $id) {
                $this->readOnlyValue = $name;
                break;
            }
        }

    }
}