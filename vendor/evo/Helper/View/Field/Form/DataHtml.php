<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 11:03
 */

namespace Evo\Helper\View\Field\Form;

use Evo\Helper\View\Field\FormDataField;

class DataHtml extends FormDataField
{
    protected $template = '';

    protected function html()
    {
        return $this->value;
    }

    protected function data()
    {
        return [];
    }
}