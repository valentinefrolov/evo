<?php

namespace Evo;

use Evo;
use Evo\Lang;

abstract class ModelDbLang extends ModelDb
{
    public $langTable = null;
    protected $langId = null;
    
    abstract protected function langFields();
    
    public function __construct()
    {
        if(!$this->tableName) {
            $this->tableName = strtolower($this->className());
        }
        $this->langTable = $this->tableName . '_lang';
        $this->addField('id_lang');
        parent::__construct();

    }
    
    public function getFieldAlias($name)
    {
        return in_array($name, $this->langFields()) || $name == 'id_lang'?
                $this->langTable.'.'.$name :
                $this->tableName.'.'.$name;
    }

    public function getAlterAlias($tableName, $model)
    {
        if(in_array($tableName, $this->getJoined()->usageTables)) {
            $i = 0;
            while(in_array($tableName."_$i", $this->getJoined()->usageTables)) {
                $i++;
            }
            $tn = $tableName."_$i";

            if($tableName == $model->tableName) {
                foreach($model->fields as $field) {
                    if(!in_array($field->origin, $this->langFields())) {
                        $field->alias = $tn . '.' . $field->origin;
                    }
                }
            } else {
                foreach($model->fields as $field) {
                    if(in_array($field->origin, $this->langFields())) {
                        $field->alias = $tn . '.' . $field->origin;
                    }
                }
            }

            $tableName = [$tn => $tableName];
        }

        return $tableName;
    }

    protected function exclude($cond, $langExclude = true) 
    {
        if($cond === true) {
            return true;
        }

        if(!is_array($cond)) {
            $cond = [$cond];
        }
        
        $return = [];
        
        foreach($cond as $key => $field) {
            if(!in_array($field, $this->langFields()) && $langExclude) {
                $return[$key] = $field;
            } else if(in_array($field, $this->langFields()) && !$langExclude){
                $return[$key] = $field;
            }
        }

        return $return;
    }
    
    public function joinTo($join, $model, $condition = true, $selection = true, $via = null, $viaSelection = [])
    {
        list($join, $model, $condition, $selection, $via) = 
                $this->joinShift($join, $model, $condition, $selection, $via, $viaSelection);

        $this->joined = $model->getJoined();
        $this->joined->select();
        $this->select = $this->joined->select;

        $table = $model->getAlterAlias($this->tableName, $this);
        $langTable = $model->getAlterAlias($this->langTable, $this);

        $mainCond = $this->exclude($condition);
        $langCond = $this->exclude($condition, false);

        $lt = is_array($langTable) ? key($langTable) : $this->langTable;
        $mt = is_array($table) ? key($table) : $this->tableName;

        $langCond[] = $lt.'.'.$this->primary.' = '.$mt.'.'.$this->primary;
        $langCond[] = Evo::app()->db()->quoteInto($lt.'.id_lang = ?', $this->getLangId());

        $mainSelection = $this->exclude($selection);
        $langSelection = $this->exclude($selection, false);

        if(is_string($via)) {
            $this->join('left', $via, $via.'.'.$model->primary.' = '.$model->tableName.'.'.$model->primary, []);
            $mainCond[] = $mt.'.'.$this->primary.' = '.$via.'.'.$this->primary;
        }


        $this->join($join, $table, $this->getCondition($model, $mainCond, $via), $mainSelection);
        $this->join($join, $langTable, $this->getCondition($model, $langCond, $via), $langSelection);

        $this->attachFromJoin($model, $selection);
    }

    public function getLangId()
    {
        if(!$this->langId) {
            if($this->joined && $this->joined instanceof ModelDbLang) {
                return $this->joined->getLangId();
            } else {
                return Evo::app()->lang->getId();
            }
        } else {
            return $this->langId;
        }

    }
    
    public function setLangId($id)
    {
        $this->langId = $id;
        return $this;
    }

    protected function prepareInput()
    {
        $fields = [];
        foreach($this->fields() as $key => $field) {
            $fields[] = !preg_match('/^\d+$/', $key) ? $key : $field;
        }
        $result = [];
        foreach($this->usageFields as $name) {

            if(in_array($name, $fields) && !in_array($name, $this->langFields()) && ($this->getField($name) && false !== ($value = $this->getField($name)->value()))) {
                $result[$name] = $value;
            }
        }

        return $result;
    }

    protected function prepareLangInput()
    {
        $result = [];
        foreach($this->usageFields as $name) {
            if(in_array($name, $this->langFields()) && ($this->getField($name) && false !== ($value = $this->getField($name)->value()))) {
                $result[$name] = $value;
            }
        }
        if(empty($result['id_lang'])) {
            $result['id_lang'] = $this->getLangId();
        }
        if(empty($result[$this->primary])) {
            $result[$this->primary] = $this->id();
        }

        return $result;
    }
    
    protected function input($is_lang = false)
    {
        $fields = [];
        foreach(array_unique($this->usageFields) as $key) {
            if($key == $this->primary) {
                $fields[] = $key;
            } else if(($is_lang && in_array($key, $this->langFields()))) {
                $fields[] = $key;
            } else if(!$is_lang && !in_array($key, $this->langFields())) {
                $fields[] = $key;
            }
        }
        $result = [];
        foreach($fields as $key) {
            $result[$key] = $this->fields[$key]->value();
        }
        return $result;
    }
        
    // создание
    public function post($data = [], $keys = null)
    {
        if(false === $this->_beforeSave($data, $keys)) {
            return false;
        }
        if(false === $this->beforePost($keys)) {
            return false;
        }
        if($data = $this->prepareInput()) {
            $this->lock();
            if($this->primary && $data[$this->primary]) unset($data[$this->primary]);
            Evo::app()->db()->insert($this->tableName, $data);
            $this->getField($this->primary)->value(Evo::app()->db()->lastInsertId());
            Evo::app()->db()->insert($this->langTable, $this->prepareLangInput());
            $this->unlock();
        }
        $this->afterPost($keys);
        return $this->_afterSave($keys);
    }
    
    // изменение
    public function put($data = [], $keys = null)
    {
        if(false === $this->_beforeSave($data, $keys)) {
            return false;
        }
        if(false === $this->beforePut($keys)) {
            return false;
        }
        $flag = 0;
        $this->lock();
        if($data = $this->prepareInput()) {
            $flag += 1;
            Evo::app()->db()->update($this->tableName, $data, Evo::app()->db()->quoteInto($this->primary . ' = ?', $this->id()));
        }
        if($data = $this->prepareLangInput()) {
            $flag += 2;
            $select = Evo::app()->db()->select();
            $select->from($this->langTable, $this->primary)
                ->where(Evo::app()->db()->quoteInto($this->primary . ' = ?', $this->id()))
                ->where(Evo::app()->db()->quoteInto('id_lang = ?', $this->getLangId()));
            $exists = Evo::app()->db()->fetchOne($select);

            $exists ? Evo::app()->db()->update($this->langTable, $data, [Evo::app()->db()->quoteInto($this->primary . ' = ?', $this->id()), Evo::app()->db()->quoteInto('id_lang = ?', $this->getLangId())]) : Evo::app()->db()->insert($this->langTable, $data);
        }
        $this->unlock();
        switch($flag) {
            case 0:
                return false;
            case 1:
                $keys = array_diff($keys, array_keys($this->getLangFields()));
                break;
            case 2:
                $keys = array_diff($keys, array_keys($this->getMainFields()));
                break;
        }
        $this->afterPut($keys);
        return $this->_afterSave($keys);
    }

    public function delete($id = null, $cacheRefresh = true)
    {
        if($id) {
            $this->id($id);
        }
        if($this->id()) {
            $this->beforeDelete();
            $this->lock();
            $prefix = is_array($id) ? ' IN(?)' : ' = ?';

            Evo::app()->db()->delete(
                $this->tableName, 
                Evo::app()->db()->quoteInto($this->primary.$prefix, $this->id())
            );
            Evo::app()->db()->delete(
                $this->langTable, 
                Evo::app()->db()->quoteInto($this->primary.$prefix, $this->id())
            );

            $this->unlock();
            // вызывается только здесь, т.к. в getPart'е не принимаются id
            $this->afterDelete();
        } else if($this->part('where')) {
            // это работать не будет
            $this->lock();
            Evo::app()->db('DELETE '.$this->langTable.', '.$this->tableName.' FROM '.$this->langTable.' JOIN ' . $this->tableName . ' ON '.$this->langTable.'.'.$this->primary.' = '.$this->tableName.'.'.$this->primary. ' WHERE ' . implode(' ', $this->part('where')));
            Evo::app()->db()->delete(
                $this->tableName, 
                implode(' ', $this->part('where'))
            );
            $this->unlock();

        } else {
            return false;
        }

        foreach($this->relateArrayFields as $field) {
            if($field->via) {
                $this->lock($field->via);
                Evo::app()->db()->delete(
                    $field->via,
                    Evo::app()->db()->quoteInto($this->primary.' = ?', $this->id())
                );
            }
        }


        return true;
    }
    
    public function select($selection=false, $tableName = null)
    {
        if($tableName) {
            return parent::select($selection, $tableName);
        }

        if(!$this->select) {
            if($selection !== false) {
                $this->select = Evo::app()->db()->select()->from($this->tableName, $this->exclude($selection));
                $this->select->joinLeft($this->langTable,
                $this->langTable.'.'.$this->primary.' = '.$this->tableName.'.'.$this->primary.''
                . ' AND '
                . Evo::app()->db()->quoteInto($this->langTable.'.id_lang = ?', $this->getLangId()), $this->exclude($selection, false));
            } else {
                $this->select = Evo::app()->db()->select()->from($this->tableName);

                $this->select->joinLeft($this->langTable,
                $this->langTable.'.'.$this->primary.' = '.$this->tableName.'.'.$this->primary.''
                . ' AND '
                . Evo::app()->db()->quoteInto($this->langTable.'.id_lang = ?', $this->getLangId()), array_merge(['id_lang'], $this->langFields()));
            }

            $this->usageTables = [$this->tableName, $this->langTable];
            $this->setMode('default');
        }

        return $this;
    }


    protected function lock($tableName = null)
    {
        if($tableName) {
            $table = $tableName . ' WRITE';
        } else {
            $table = implode(', ', [$this->tableName . ' WRITE', $this->langTable . ' WRITE']);
        }
        Evo::app()->db('LOCK TABLES ' . $table);
        ModelDb::$locked = true;
        return $this;
    }


    public function getLangFields() {

        $langFields = $this->langFields();
        $return = ['id_lang' => $this->getLangId()];
        foreach($this->getFields() as $name => $field) {
            if(in_array($name, $langFields)) {
                $return[$name] = $field;
            }
        }
        return $return;
    }

    public function getMainFields() {

        $return = [];
        $langFields = $this->langFields();
        foreach($this->getFields() as $name => $field) {
            if(!in_array($name, $langFields)) {
                $return[$name] = $field;
            }
        }
        return $return;
    }
    
}