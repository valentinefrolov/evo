<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 17.03.16
 * Time: 12:09
 */

namespace Evo\Rule;

use Evo\Interfaces\FieldEntity;
use Evo\Rule;
use Evo;

// works with field Db
class Sort extends Rule
{
    protected $step = 100;
    protected $depends = [];

    protected static $starter = false;

    protected function check()
    {
        if(!static::$starter) {
            if(!$this->field->value()) {
                $this->field->value($this->getLastSort());
            } else if(is_numeric($this->field->value())) {
                $value = $this->checkUnique($this->field->value());
                $this->field->value($value);
            } else {
                $this->makeError([], 'integer_format');
            }
        }
    }


    private function getLastSort()
    {
        $selects = ['max' => "MAX({$this->field->alias})"];

        $select = Evo::app()->db()->select()
            ->from($this->field->getTableName(), $selects);

        foreach((array)$this->depends as $fieldName) {
            $select->where($fieldName . '= ?', $this->field->model->getField($fieldName)->value());
        }

        $max = Evo::app()->db()->fetchOne($select);

        return ($max ? $max : 0) + $this->step;
    }


    private function checkUnique($value)
    {
        $select = Evo::app()->db()->select()
            ->from($this->field->getTableName())
            ->order($this->field->alias);

        if($this->field->model->id()) {
            $select->where($this->field->model->getField($this->field->model->primary)->alias . ' != ?', $this->field->model->id());
        }

        foreach((array)$this->depends as $fieldName) {
            if($depVal = $this->field->model->getField($fieldName)->value())
                $select->where($fieldName . ' = ?', $depVal);
        }

        $data = [];
        $selectData = Evo::app()->db()->fetchAll($select);
        foreach($selectData as $item){
            $data[$item[$this->field->model->primary]] = $item[$this->field->origin];
        }

        if($id = array_search($value, $data)) {
            // есть совпадение с уже имеющейся записью
            // изменяем текущий индекс
            $value--;
            $curr = $value;
            $className = get_class($this->field->model);
            static::$starter = true;
            while($id = array_search($curr, $data)) {
                // если находятся записи "слева" от текущего индеска, то обновляем их
                $curr--;
                if($curr <= 0) {
                    asort($data);
                    $sort = 0;
                    foreach(array_keys($data) as $_id) {
                        $model = new $className();
                        $model->one($_id);
                        $model->put([$this->field->name => $sort+=$this->step], $this->field->name);
                    }
                    break;
                } else {
                    $model = new $className();
                    $model->one($id);
                    $model->put([$this->field->name => $curr], $this->field->name);
                }
            }
            static::$starter = false;
        }

        // self model value
        return $value;
    }


    public function fullSortUpdate()
    {
        $step = 0;
        $select = Evo::app()->db()->select()
            ->from($this->field->getTableName())
            ->order($this->field->alias);

        $data = [];
        foreach(Evo::app()->db()->fetchAll($select) as $item){
            $data[$item[$this->field->model->primary]] = $item[$this->field->origin];
        }

        foreach($data as $id => $sort) {
            $this->field->model->id($id);
            Evo::app()->db()->update($this->field->getTableName(),
                [$this->field->origin => $step+=$this->step],
                Evo::app()->db()->quoteInto($this->field->model->primary.' = ?', $id)
            );
        }
    }



}