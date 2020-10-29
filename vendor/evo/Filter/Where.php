<?php

namespace Evo\Filter;

use Evo\Filter;
use Evo\Request;
use Evo\Model;
use Evo\Interfaces\FieldEntity;


/**
 * Description of where
 *
 * @author frolov
 */
class Where extends Filter{

    protected $compare = '=';

    public function id() {
        return 'weq';
    }
    
    public function sql()
    {
        $this->field->model->where($this->field->name . " {$this->compare} ?", $this->value);
    }
    
    public function prepare()
    {
        parent::prepare();

        $whereArray = $this->field->getFilter('whereArray');

        if($this->state && $whereArray) {
            $whereArray->value[] = $this->value;
            $this->state = false;
            $whereArray->state = true;
            $this->request->delete($this->getName());
            $this->request->add($whereArray->getName().'.'.$this->value, $this->value);
        }
    }
}
