<?php

namespace Evo\Rule;

use Evo\Rule;
use Evo\Lang;
use Evo;
use Evo\Interfaces\FieldEntity;

/**
 * @property  $when - усливие "И". правило сработает (выдаст ошибку), если
 * основное условие совпадет и у совпавшего объекта модели также совпадут поля в свойстве $when.
 *
 * @property $whenNot - условие "НЕ". правило сработает (выдаст ошибку), если
 * основное условие совпадет и у совпавшего объекта модели не будут совпадать поля в свойстве $whenNot.
 *
 * свойства суммируются. т.е. если хотя бы одно из дополнительных условий не совпадет, то правило
 * не сработает (не выдаст ошибку)
 *
 * оба свойства (далее prop) имеют следующий синтаксис параметризации:
 * prop => fieldName - значение поля fieldName в текущем объекте сравнивается с совпавшим.
 * prop => [fieldName => value] - значение value сравнивается с совпавшим объектом.
 *
 * @author frolov
 */
class Unique extends Rule
{
    protected $when = [];
    protected $whenNot = [];

    protected $depends = [];

    private $model = null;

    protected function check()
    {
        if($this->field->model instanceof \Evo\ModelDb) {
            $this->checkUnique();
        }
    }
    
    private function checkUnique()
    {
        if(!$this->field->value())
            return;

        $select = Evo::app()->db()->select()
            ->from($this->field->getTableName())
            ->where($this->field->alias . ' = ?', $this->field->value());

        
        $id = $this->field->model->id();

        if($id) {
            $select->where($this->field->model->getField($this->field->model->primary)->alias . ' != ?', $id);
        }

        foreach((array)$this->depends as $field) {
            $select->where($this->field->model->getField($field)->alias . '= ?', $this->field->model->getField($field)->value() ?? 0);
        }

        $result = Evo::app()->db()->fetchRow($select);

        if($result) {
            /*$this->field->model->getField($this->field->model->primary)->value($result[$this->field->model->primary]);

            if($this->when) {
                $when = $this->when;
                if (!is_array($when)) $when = [$when];

                foreach ($when as $fieldName => $value) {
                    if (preg_match('/^\d+$/', $fieldName)) {
                        $fieldName = $value;
                        $value = $this->field->model->getField($fieldName)->value() || 0;
                    }
                    if ($result[$fieldName] != $value) {
                        return;
                    }
                }
            }

            if($this->whenNot) {
                $whenNot = $this->whenNot;
                if (!is_array($whenNot)) $whenNot = [$whenNot];

                foreach ($whenNot as $fieldName => $value) {
                    if (preg_match('/^\d+?/', $fieldName)) {
                        $fieldName = $value;
                        $value = $this->field->model->getField($fieldName)->value() || 0;
                    }
                    if ($result[$fieldName] == $value) {
                        return;
                    }
                }
            }*/

            $this->field->model->getField($this->field->model->primary)->value(null);
            $this->makeError([], 'not_unique');
        }
    }
}