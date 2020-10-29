<?php


namespace Evo\Rule;

use Evo\Interfaces\FieldEntity;
use Evo\Rule;
use Evo;

class Tree extends Rule
{
    protected $depend = '';
    protected $parent = '';
    protected $length = 3;

    /**
     * Tree constructor.
     * @param FieldEntity $field
     * @param array|null $params
     * @throws Evo\Exception\BehaviourException
     */
    public function __construct(FieldEntity $field, array $params = null)
    {
        parent::__construct($field, $params);
        if(!$this->parent) {
            throw new Evo\Exception\BehaviourException('no parent field defined on Tree rule');
        }

    }


    protected function check()
    {
        $parentValue = $this->field->model->getField($this->parent)->value();
        $value = $this->field->model->getField($this->depend)->value();
        $value = base_convert(round($value), 10, 36);

        if(strlen($value) > $this->length) {
            $this->makeError();
        } else {
            $value = str_pad($value, $this->length, 0, STR_PAD_LEFT);
        }

        if($parentValue) {
            $className = get_class($this->field->model);
            /** @var Evo\ModelDb $model */
            $model = new $className();
            $model->where("$model->primary = ?", $parentValue)->one();
            $this->field->value($model[$this->field->name].$value);
        } else {
            $this->field->value($value);
        }
    }
}