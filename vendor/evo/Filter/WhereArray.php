<?php

namespace Evo\Filter;

use Evo\Filter;
use Evo\Filter\Where;
use Evo\Request;
use Evo\Interfaces\FieldEntity;
use Evo\Model;
use Evo;
/**
 * Description of where
 *
 * @author frolov
 */
class WhereArray extends Filter{
    
    public $value = [];

    public function prepare()
    {
        parent::prepare();

        $filters = $this->request->get($this->getName());

        if (is_array($filters)) {
            $delete = $this->request->get($this->delete());
            if (!is_array($delete)) $delete = [$delete];
            foreach ($delete as $value) {
                $key = array_search($value, $filters);
                $this->request->delete($this->getName() . '.' . $key);
            }
        }

        if($this->request->get($this->delete())) {
            $this->request->delete($this->delete());
        }
        if($this->request->get($this->getName())) {
            $this->value = $this->request->get($this->getName());
            if(!is_array($this->value)) {
                $this->value = explode(',', $this->value);
            }
            $this->state = true;
        }
    }

    public function id() {
        return 'wareq';
    }
    
    public function sql() 
    {
        if($this->value)
            $this->field->model->where($this->field->alias . ' IN (?)', $this->value);


    }

}