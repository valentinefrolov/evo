<?php

namespace Evo\Filter;

use Evo\Filter;


/**
 * Description of where
 *
 * @author frolov
 */
class Range extends Filter{


    public function id() {
        return 'r';
    }

    public function prepare()
    {
        if(isset($this->value['min']) && !$this->value['min']) {
            unset($this->value['min']);
        }

        if(isset($this->value['max']) && !$this->value['max']) {
            unset($this->value['max']);
        }

        parent::prepare();
    }

    public function sql()
    {
        if(is_array($this->value)) {
            if (!empty($this->value['min'])) {
                $this->field->model->where($this->field->alias . ' >= ?', $this->value['min']);
            }
            if (!empty($this->value['max'])) {
                $this->field->model->where($this->field->alias . ' <= ?', $this->value['max']);
            }
        } else if(!empty($this->value)) {
            $this->field->model->where($this->field->alias . ' = ?', $this->value);
        }
    }

}
