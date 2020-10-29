<?php

namespace Evo\Filter;

use Evo\Filter;
use Evo\Request;


/**
 * Description of where
 *
 * @author frolov
 */
class Search extends Filter{

    protected $start = true;
    protected $end = true;

    public function id() {
        return 's';
    }
    
    public function sql() 
    {
        $values = preg_split('/\s+/', $this->value);

        $select = [];

        foreach($values as $value) {
            if(mb_strlen($value) > 2) {
                $value = str_replace("'", "\\'", ($this->end ? '%' : '') . $value . ($this->start ? '%' : ''));
                $select[] = $this->field->alias . " LIKE '$value'";
            }
        }

        $this->field->model->where(implode(' OR ', $select));
    }

}
