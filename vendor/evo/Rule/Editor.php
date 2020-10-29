<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 10.03.16
 * Time: 18:15
 */

namespace Evo\Rule;

class Editor extends Text
{
    protected function check()
    {
        $this->field->value(htmlspecialchars_decode($this->field->value()));
        parent::check();
    }

}