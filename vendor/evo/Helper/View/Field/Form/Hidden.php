<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 11:03
 */

namespace Evo\Helper\View\Field\Form;

use Evo\Helper\View\Field\FormField;

class Hidden extends FormField
{
    protected $template = '{html}';

    protected function html()
    {
        $this->inputAttributes['type'] = 'hidden';

        if(isset($this->inputAttributes['placeholder'])) {
            unset($this->inputAttributes['placeholder']);
        }

        return $this->input($this->inputAttributes);
    }
}