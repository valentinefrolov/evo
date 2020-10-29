<?php

namespace Evo\Rule;

use Evo\Rule;
use Evo;

use Evo\Interfaces\FieldEntity;
/**
 * Description of regexp
 *
 * @author frolov
 */
class Date extends Rule
{
    public function __construct(FieldEntity $field, array $params =[])
    {
        parent::__construct($field, $params);
    }

    protected function check()
    {
        $this->checkDate();

        if(!$this->field->value()) {
            $this->field->value(null);
        }
    }


    private function checkDate()
    {
        if($this->field->value()) {
            $value = date('Y-m-d', strtotime($this->field->value()));
            if(!preg_match('/\d{4}\-\d{2}\-\d{2}/', $value)) {
                $this->makeError();
            } else {
                if($this->min && $value < $this->min) {
                    $this->makeError(null, 'minDate');
                }
                if($this->max && $value > $this->max) {
                    $this->makeError(null, 'maxDate');
                }
                $this->field->value($value);
            }
        }
    }
}