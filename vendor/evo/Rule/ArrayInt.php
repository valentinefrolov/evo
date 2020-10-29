<?php

namespace Evo\Rule;


use Evo;
use Evo\Rule;
use Evo\Interfaces\FieldEntity;

/**
 * Description of Integer
 *
 * @author frolov
 */
class ArrayInt extends Rule{

    protected $via = null;

    public function __construct(FieldEntity $field, array $params = null)
    {
        parent::__construct($field, $params);
    }

    protected function check()
    {
        $this->checkArrayInt();
    }
    
    private function checkArrayInt()
    {
        foreach($this->field->value() as $value) {
            if(!preg_match('/^\d+$/', $value)) {
                $this->makeError();
                break;
            }
        }
    }
}