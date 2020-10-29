<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 11:03
 */

namespace Evo\Helper\View\Field\Form;

use Evo\Helper\View\Field\FormField;

class Text extends FormField
{
    protected function html()
    {
        return $this->input($this->inputAttributes);
    }
}