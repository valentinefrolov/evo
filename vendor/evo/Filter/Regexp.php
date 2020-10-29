<?php


namespace Evo\Filter;

use Evo\Filter;

class Regexp extends Filter
{

    public function id()
    {
        return 'x';
    }

    public function sql()
    {
        $this->field->model->where($this->field->alias . ' REGEXP ?', $this->value);

        //echo $this->field->model->select; die();

    }
}