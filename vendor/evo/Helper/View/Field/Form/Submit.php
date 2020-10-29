<?php

namespace Evo\Helper\View\Field\Form;

use Evo;

class Submit extends Manage
{
    protected $raw = true;

    protected function html()
    {
        $this->inputAttributes['type'] = 'submit';
        if($this->title) {
            $this->inputAttributes['value'] = $this->title;
        }
        return $this->input($this->inputAttributes);
    }
}