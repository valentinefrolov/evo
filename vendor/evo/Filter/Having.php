<?php

namespace Evo\Filter;

use Evo\Filter;


/**
 * Description of where
 *
 * @author frolov
 */
class Having extends Filter{

    protected $compare = '=';

    public function id() {
        return 'weq';
    }

    public function sql()
    {
        $this->field->model->having($this->field->name . " {$this->compare} ?", $this->value);
    }

}
