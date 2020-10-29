<?php

namespace Evo;

use Evo;
use Zend_Db_Select;
use Zend_Db_Expr;

// TODO correct refactor join via table
// TODO ынести DB из App в модель

abstract class ModelDb extends Model
{
    /** @var string $this->tableName */
    public $tableName  = null;
    /** @var string $this->tableName */
    public $tableAlias = null;
    /** @var string $this->primary */
    public $primary    = null;

    public $limit      = null; // ?
    /** @var Evo\ModelDb $this->joined */
    public $joined     = null;
    /** @var Evo\Pagination $this->paginate */
    public $paginate   = null;
    /** @var array $this->after */
    public $after      = [];
    /** @var array $this->before */
    public $before     = [];
    /** @var string $this->alterTable */
    public $alterTable = null;
    /** @var \Zend_Db_Select $this->select */
    public $select  = null;
    /** @var string $this->_tableName */
    protected $_tableName = null;
    /** @var string $this->_primary */
    protected $_primary = null;

    protected $order   = []; // ?
    /** @var array $this->usageTables */
    protected $usageTables = [];
    /** @var bool $this->transactionStarter */
    protected $transactionStarter = false;
    /** @var array $this->joinTypes */
    protected $joinTypes = ['inner', 'left', 'right', 'full', 'cross', 'natural'];
    /** @var @var bool static::$transaction */
    protected static $transaction = false;

    public static $locked = false;

    public function __clone() {

        if(!$this->cloning) {

            $this->cloning = true;
            $this->select = clone($this->select);

            if ($this->joined) {
                $this->joined = clone($this->joined);
            }

            if ($this->paginate) {
                $this->paginate = clone($this->paginate);
                $this->paginate->model = clone($this);
            }

            foreach ($this->relateArrayFields as $name => $field) {
                $field = clone($field);
                $this->relateArrayFields[$name] = $field;
                $field->model = $this;
            }

            foreach ($this->relateFields as $name => $field) {
                $field = clone($field);
                $this->relateFields[$name] = $field;
                $field->model = $this;
            }

            foreach ($this->fields as $name => $field) {
                $field = clone($field);
                $this->fields[$name] = $field;
                $field->model = $this;
            }
            $this->cloning = false;
        }

    }

    public function __construct(array $array = [])
    {
        if(!$this->tableName) {
            $this->tableName = $this->name();
        }

        $this->_tableName = $this->tableName;

        if(!$this->primary) {
            $this->primary = 'id';
        }

        $this->_primary = $this->primary;

        parent::__construct($array);
    }

    public function select($selection=false, $tableName = null)
    {
        if($this->select) return $this;

        $_tableName = $tableName ? $tableName : $this->tableName;

        if($tableName) {
            if(is_array($tableName)) {
                $this->tableName = key($tableName);
                foreach($this->getFields() as $field) {
                    $field->alias = $this->tableName.'.'.$field->name;
                }
            } else {
                $this->tableName = $tableName;
            }
        }

        $this->usageTables[] = $this->tableName;

        $this->executed = false;

        if($selection !== false) {

            $selection = (array)$selection;
            foreach($selection as $key => $value) {
                if($value instanceof \Zend_Db_Select) {
                    $selection[$key] = new Zend_Db_Expr('('.$value.')');
                } else if($value instanceof ModelDb) {
                    $selection[$key] = $this->expr('('.$value->select.')');
                }
                if(preg_match('/^\d+$/', $key)) {
                    if(!in_array($value, array_keys($this->getFields())) && $value != '*') {
                        $this->addField($value);
                    }
                } else if(!in_array($key, array_keys($this->getFields())) && $key != '*') {
                    $this->addField($key);
                }
            }
            $this->select = Evo::app()->db()->select()->from($_tableName, $selection);
            $this->setMode('default');
        } else {
            $this->select = Evo::app()->db()->select()->from($_tableName);
            $this->setMode('default');
        }

        return $this;
    }

    public function expr($smth)
    {
        return new Zend_Db_Expr($smth);
    }

    protected function prepareInput()
    {
        $result = [];

        $fields = [];
        foreach($this->fields() as $key => $field) {
            $fields[] = !preg_match('/^\d+$/', $key) ? $key : $field;
        }

        foreach($this->usageFields as $name) {
            if(in_array($name, $fields) && ($this->getField($name) && false !== ($value = $this->getField($name)->value()))) {
                $result[$name] = $value;
            }
        }

        return $result;
    }

    /**
     * Отвечает за уникальность добавляемых таблиц
     *
     *
     * @param $tableName
     * @param $model
     * @return array
     */
    public function getAlterAlias($tableName, $model)
    {
        if(in_array($tableName, $this->getJoined()->usageTables)) {

            $i = 0;
            while(in_array($tableName."_$i", $this->getJoined()->usageTables)) {
                $i++;
            }
            $tn = $tableName."_$i";
            $tableName = [$tn => $tableName];
            $model->tableName = $tn;
            foreach($model->fields as $field) {
                $field->alias = $tn.'.'.$field->origin;
            }
        }

        return $tableName;
    }

    public function getFieldAlias($name)
    {
        return $this->tableName.'.'.$name;
    }

    public function id($value = null)
    {
        if($value) {
            $this->getField($this->primary)->value($value);
        }

        return $this->getField($this->primary)->value();
    }

    public function relateTable() { return []; }

    public function getJoined()
    {
        if($this->joined) {
            return $this->joined;
        }
        return $this;
    }


    public function paginate($limit, $groupWidth = null, $url = null)
    {
        $this->paginate = new Pagination($this, $limit, $groupWidth, $url);
        return $this;
    }

    public function paginateSynthetic($limit, array $data, $groupWidth = null, $url = null)
    {
        $this->paginate = new PaginationSynthetic($this, $data, $limit, $groupWidth, $url);
        return $this;
    }

    protected function joinShift($join, $model, $condition, $selection, $via, $viaSelection)
    {
        $shift = [];

        if($join instanceof ModelDb) {
            $shift[5] = $via ? $via : [];
            $shift[4] = $selection ? $selection : null;
            $shift[3] = $condition ? $condition : false;
            $shift[2] = $model;
            $shift[1] = $join;
            $shift[0] = $this->joinTypes[0];
        } else {
            $shift[5] = $viaSelection;
            $shift[4] = $via;
            $shift[3] = $selection;
            $shift[2] = $condition;
            $shift[1] = $model;
            $shift[0] = $join;
        }

        return $shift;
    }

    /**
     *
     * TODO Возможно придется переработать, под более широкий круг использоваия
     *
     * @param $model
     * @param $condition
     * @param $via
     * @return string
     */
    protected function getCondition($model, $condition, $via)
    {
        if(!is_array($condition)) $condition = [$condition];

        $c = [];
        foreach($condition as $modelField => $field) {

            if(preg_match('/\s*([\w.]+)[^\?]*\?\s*/', $modelField, $matches)) {

                if(strpos($matches[1], '.') === false) {
                    $matches[0] = str_replace($matches[1], $this->getField($matches[1])->alias, $matches[0]);
                }
                $c[] = Evo::app()->db()->quoteInto($matches[0], $field);
                continue;
            }
            if(strpos($field, '.') !== false) {
                $c[] = $field;
                continue;
            } else if(strpos($field, ' ') !== false) {
                $chars = explode(' ', $field);
                $chars[0] = $this->getField($chars[0])->alias;
                $c[] = implode(' ', $chars);
                continue;
            }
            if(preg_match('/^\d+$/', $modelField)) {
                $modelField = $field; // using
            }
            if(is_string($via) && ($field == $this->primary || $modelField == $model->primary)) {
                continue;
            }
            if(preg_match('/^[\w_]+$/', $modelField)) {
                $modelField = $model->getField($modelField)->alias;
            }
            if(preg_match('/^[\w_]+$/', $field)) {
                $field = $this->getField($field)->alias;
            }
            $c[] = $field.' = '.$modelField;
        }

        return implode(' AND ', $c);
    }

    /**
     * Метод добавляет к основной модели поля добавленной через JoinTo
     *
     * @param $model
     * @param $attach
     */
    protected function attachFromJoin($model, $attach)
    {
        if($attach === true) {
            $attach = array_keys($this->fields);
        } else if(!is_array($attach)) {
            $attach = [$attach];
        } else if ($attach === false) {
            return;
        }

        foreach($attach as $key => $sel) {
            if(preg_match('/^\d+$/', $key)) {
                $attach[$sel] = $sel;
                unset($attach[$key]);
            }
        }

        if(is_array($attach)) {
            foreach ($attach as $modelKey => $selfKey) {
                if (!isset($model->getJoined()->fields[$modelKey]) && isset($this->fields[$selfKey])) {
                    $model->getJoined()->attachField($modelKey, $this->fields[$selfKey]);
                }
            }
        }
    }

    protected function attachArrayFields(ModelDb $model, $selection=null, $via=null)
    {

        if(is_bool($selection) && $selection) {
            $selection = array_keys($this->getFields());
        }

        if(!is_array($selection)) {
            $selection = [$selection];
        }



        foreach($selection as $key => $value) {
            $modelField = !preg_match('/^\d+$/', $key) ? $key : $value;

            $field = $this->getField($value, true) ? $this->getField($value) : $this->addField($modelField);
            $model->getJoined()->attachArrayField($modelField, $field, $via);
        }
    }

    public function join($type, $tableName, $condition = true, $selection = true, $isModelJoin = false)
    {
        if(is_string($type) && in_array($type, $this->joinTypes)) {
            $method = 'join'.ucfirst($type);
        } else {
            $selection = $condition;
            $condition = $tableName;
            $tableName = $type;
            $method = 'join';
        }

        if($condition && !preg_match('/[ \=\.]/', $condition)) {
            $method = 'joinUsing';
        }


        if($selection !== false) {
            if (!$isModelJoin && $selection === true) {
                $selection = array_keys(Evo::app()->db()->describeTable($tableName));
            }
            if (!is_array($selection) && $selection !== true) {
                $selection = [$selection];
            }
            if(is_array($selection)) {

                foreach ($selection as $key => $value) {
                    if(preg_match('/^\d+$/', $key)) {
                        if($value != '*') {
                            $this->addField($value);
                        }
                    } else {
                        $this->addField($key);
                    }
                }
            }
        }

        $this->select();

        if($type == 'cross' || $type == 'natural') {
            $this->select->$method($tableName, $selection);
        } else if ($selection === true) {
            $this->select->$method($tableName, $condition);
        } else {
            $this->select->$method($tableName, $condition, $selection);
        }

        $tn = is_array($tableName) ? key($tableName) : $tableName;

        if(!in_array($tableName, $this->usageTables))
            $this->getJoined()->usageTables[] = $tn;

        return $this;
    }


    /**
     * Метод джоинит текущую модель к $model
     *
     *
     * @param $join
     * @param $model
     * @param bool|true $condition
     * @param bool|true $selection
     * @param null $via
     */
    public function joinTo($join, $model, $condition = true, $selection = true, $via = null, $viaSelection = [])
    {
        list($join, $model, $condition, $selection, $via, $viaSelection) =
            $this->joinShift($join, $model, $condition, $selection, $via, $viaSelection);

        $model->select();
        $this->joined = $model->getJoined();
        $this->joined->select();
        $this->select = $this->joined->select;

        $table = $model->getAlterAlias($this->tableName, $this);

        $condition = $condition && $condition !== true ? $this->getCondition($model, $condition, $via) : null;

        if(is_string($via)) {
            $this->join('left', $via, $via.'.'.$model->primary.' = '.$model->tableName.'.'.$model->primary, $viaSelection);
            $condition.= $this->tableName.'.'.$this->primary.' = '.$via.'.'.$this->primary;
            $this->attachArrayFields($model, $this->primary, $via);
        }

        $model->join($join, $table, $condition, $selection, true);
        $this->attachFromJoin($model, $selection);
    }

    public function columns(array $data) {
        foreach($data as $field => &$smth) {
            if(preg_match('/^\d+$/', $field)) {
                if(strpos($smth, '.') === false && ($field = $this->getField($smth))) {
                    $smth = $field->alias;
                }
            } else {
                $this->addField($field);
            }
        }
        $this->select();
        $this->select->columns($data);
        return $this;
    }


    protected function update($arg0,$arg1=null,$arg2=null)
    {
        if(is_array($arg0)) {
            $data = $arg0;
            $tableName = $this->tableName;
        } else {
            $data = $arg1;
            $tableName = $arg0;
        }

        $this->lock($tableName);

        if(!$arg2) {
            if(!$this->id()) {
                throw new \Exception('no condition defined form update');
            }
            $whereArray = ["$this->primary" =>  $this->id()];
        } else {
            $whereArray = $arg2;
        }

        $where = [];

        foreach($whereArray as $key => $value) {
            $where[] = Evo::app()->db()->quoteInto("$key = ?", $value);
        }

        if(Evo::app()->db()->update($tableName, $data, $where)) {
            $this->unlock();
            return true;
        }
        $this->unlock();
        return false;
    }

    protected function insert($arg0,$arg1=null)
    {
        if(is_array($arg0)) {
            $data = $arg0;
            $tableName = $this->tableName;
        } else {
            $data = $arg1;
            $tableName = $arg0;
        }
        $this->lock($tableName);
        if(Evo::app()->db()->insert($tableName, $data)) {
            $this->unlock();
            return true;
        }
        $this->unlock();
        return false;
    }

    public function remove($arg0, $arg1=null)
    {
        if(is_array($arg0)) {
            $tableName = $this->tableName;
            $whereArray = $arg0;
        } else {
            $tableName = $arg0;
            $whereArray = $arg1;
        }
        $this->lock($tableName);

        $where = [];

        foreach($whereArray as $key => $value) {
            $where[] = Evo::app()->db()->quoteInto("$key = ?", $value);
        }

        if(Evo::app()->db()->delete($tableName, $where)) {
            $this->unlock();
            return true;
        }

        return false;
    }

    public function union()
    {
        $args = func_get_args();
        $selects = [];
        foreach($args as $arg) {
            if($arg instanceof ModelDB) {
                $selects[] = $arg->select;
            } else if($arg instanceof static::$db) {
                $selects[] = $arg;
            } else {
                throw new \Exception('UNION arguments must be instance of Evo\ModelDb or DB');
            }
        }
        array_unshift($selects, $this->select);

        foreach($selects as &$select) {
            $select = '('. $select .  ')';
        }

        $this->select = Evo::app()->db()->select()->union($selects);
    }

    public function unionAll()
    {
        $args = func_get_args();
        $selects = [];
        foreach($args as $arg) {
            if($arg instanceof ModelDB) {
                $selects[] = $arg->select;
            } else if($arg instanceof static::$db) {
                $selects[] = $arg;
            } else {
                throw new \Exception('UNION arguments must be instance of Evo\ModelDb or DB');
            }
        }
        array_unshift($selects, $this->select);

        foreach($selects as &$select) {
            $select = '('. $select .  ')';
        }

        $this->select = Evo::app()->db()->select()->union($selects, Zend_Db_Select::SQL_UNION_ALL);
    }

    public function distinct()
    {
        $this->select();
        $this->select->distinct();
        return $this;
    }

    public function limit($limit, $offset=0)
    {
        // закомментированный код понадобится для крупных бд, он значительно прибавляет в скорости выборки.
        // но неправильно работает с ордером связанных таблиц. возможно, позже найду решение, а пока так

        /*$limitSelect = DB::get_instance()->select()
            ->from($this->tableName, $this->primary)
            //->order($this->order)
            ->limitPage($offset, $limit);

        $this->select()->join(array('_limiter' => $limitSelect), '_limiter.' . $this->primary . ' = ' . $this->tableName . '.' . $this->primary);
        */
        $this->select();
        $this->select->limit($limit, $offset);

        return $this;
    }

    public function order($order)
    {
        $this->select();
        if($order instanceof Zend_Db_Expr) {
            $this->select->order($order);
        } else if($this->part('union')) {
            $this->select->order($order);
        } else if($field = $this->getField($order)){
            $this->select->order($field->alias);
        } else if(strpos($order, '.') === false && strpos($order, ' ') !== false) {
            $items = preg_split('/\s+/', $order);
            for($i=0;$i<count($items)-1;$i++) {
                if($items[$i] && ($field = $this->getField($items[$i]))) {
                    $items[$i] = $field->alias;
                }
            }
            $this->select->order($this->expr(implode(' ', $items)));
        } else {
            $this->select->order($order);
        }

        return $this;
    }
    /**
     *
     * selection - если нет, то выбираем все
     * если false || [] - ичего не выбираем
     *
     * @param string $type
     * @param mixed $tableName
     * @param mixed $condition
     * @param mixed $selection
     * @return \Evo\ModelDb
     *
     */


    public function orWhere($condition, $value= null)
    {
        return $this->where($condition, $value, true);
    }

    protected function getModelCondition($condition)
    {
        if(strpos($condition, '.') === false) {

            preg_match_all('/( AND | OR )/i', $condition, $matches);
            $lines = preg_split('/ AND | OR /i', $condition);

            $condition = '';

            foreach($lines as $n => $line) {
                $line = trim($line);

                if(strpos($line, '.') !== false) {
                    $condition .= $line;
                    continue;
                }
                else if(strpos($line, '(') !== false && strpos($line, 'IN') === false) {
                    $line = preg_replace_callback('/\((\w+)\)/', function($matches){
                        if($f = $this->getField($matches[1])) {
                            return '('.$f->alias.')';
                        } else {
                            return $matches[0];
                        }
                    }, $line);
                    $condition .= $line;
                } else {
                    $chars = explode(' ', $line);
                    if ($field = $this->getField($chars[0])) {
                        $chars[0] = $field->alias;
                    }
                    $condition .= implode(' ', $chars);
                }

                if(isset($matches[1][$n])) {
                    $condition .= ' '.$matches[1][$n].' ';
                }
            }
        }

        return $condition;
    }


    public function where($condition, $value = null, $or = false)
    {
        $method = $or ? 'orWhere' : 'where';

        if(!$condition instanceof Zend_Db_Expr) {
            $condition = $this->getModelCondition($condition);
            if (is_array($value) && preg_match('/\s*\=\s*\?/', $condition)) {
                $condition = preg_replace('/\s*\=\s*\?/', ' IN (?)', $condition);
            }
        }
        $this->select();
        $this->select->$method($condition, $value);

        return $this;
    }


    public function having($condition, $value = null)
    {
        $this->select();
        $this->select->having($condition, $value);
    }

    public function orHaving($condition, $value = null)
    {
        if(!$condition instanceof Zend_Db_Expr) {
            $condition = $this->getModelCondition($condition);
            if (is_array($value) && preg_match('/\s*\=\s*\?/', $condition)) {
                $condition = preg_replace('/\s*\=\s*\?/', ' IN (?)', $condition);
            }
        }

        $this->select();

        $this->select->orHaving($condition, $value);
    }

    public function group($item)
    {
        if(!$this->part('union') && strpos($item, '.') === false && ($field = $this->getField($item))) {
          $item = $field->alias;
        }

        $this->select();

        $this->select->group($item);

        return $this;
    }

    public function prepare()
    {
        $this->select();

        foreach($this->getFields() as $name => $field) {
            foreach($field->getFilter() as $filter) {
                $filter->prepare();
            }

            foreach($field->getFilter() as $filter) {
                $filter->execute();
            }
        }

        if($this->paginate) {
            $this->paginate->run();
        }
    }

    public function all(array $id=null)
    {
        $this->clearData();

        $this->unlock();

        $this->select();

        $this->prepare();

        if($id) {
            $this->select->where($this->tableName . '.' . $this->primary . ' IN (?)', $id);
        }

        $this->clearData();
        $this->executed = true;

        $this->assign(Evo::app()->db()->fetchAll($this->select), true);

        $this->clear();

        return $this;
    }

    public function one($id=null)
    {
        $this->unlock();
        $this->select();

        if($id) {
            $this->select->where($this->tableName . '.' . $this->primary . ' = ?', $id);
        }
        $this->clearData();
        $this->executed = true;

        $this->assign(Evo::app()->db()->fetchRow($this->select));

        $this->clear();

        return $this;
    }

    protected function getCacheParts()
    {
        return array_merge(['model', hash('md5', preg_replace('/\d{2}:\d{2}:\d{2}/', '00:00:00', (string)$this->select))], $this->usageTables);
    }

    public function part($part) {
        $this->select();
        return $this->select->getPart($part);
    }

    protected function beforeAll()
    {
        return;
    }

    public function clear()
    {
        $this->select = null;
        $this->tableName = $this->_tableName;
        $this->primary = $this->_primary;
        $this->usageTables = [];
        return $this;
    }

    public function clearData()
    {
        foreach($this->getFields() as $field) {
            $field->value(null);
        }
        $this->data = [];
        $this->usageFields = [];
        return $this;
    }

    public function reset($ref)
    {
        $this->select();
        $this->select->reset(constant('Zend_Db_Select::' . strtoupper($ref)));
    }

    // создание
    public function post($data = [], $keys = null)
    {
        if(!$this->_beforeSave($data, $keys)) {
            return false;
        }
        if(false === $this->beforePost($keys)) {
            return false;
        }
        if($data = $this->prepareInput()) {
            $this->lock();
            if($this->getField($this->primary) && $data[$this->primary]) {
                unset($data[$this->primary]);
            }
            Evo::app()->db()->insert($this->tableName, $data);
            if($this->getField($this->primary)) {
                $this->getField($this->primary)->value(Evo::app()->db()->lastInsertId());
            }

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
        if($input = $this->prepareInput()) {
            $this->lock();
            Evo::app()->db()->update($this->tableName, $input, Evo::app()->db()->quoteInto($this->primary . ' = ?', $this->id()));
            $this->unlock();
        }
        $this->afterPut($keys);
        return $this->_afterSave($keys);

    }

    public function delete($id=null, $cacheRefresh = true)
    {
        $this->_start();
        try {
            if ($id) {
                $this->id($id);
            }
            if ($this->id()) {

                $this->beforeDelete();
                $this->lock();
                Evo::app()->db()->delete(
                    $this->tableName,
                    Evo::app()->db()->quoteInto($this->primary . (is_array($this->id()) ? ' IN (?)' : ' = ?'), $this->id())
                );
                $this->unlock();

                $this->afterDelete();

            } else if ($this->part('where')) {

                $this->lock();

                Evo::app()->db()->delete(
                    $this->tableName,
                    implode(' ', $this->part('where'))
                );
                $this->unlock();
            } else {
                return false;
            }

            foreach ($this->relateArrayFields as $field) {
                if ($field->via) {
                    $this->lock($field->via);
                    Evo::app()->db()->delete(
                        $field->via,
                        Evo::app()->db()->quoteInto($this->primary . ' = ?', $this->id())
                    );
                    $this->unlock();
                }
            }
        } catch (\Exception $e) {
            Evo::app()->db()->rollBack();
            throw $e;
        }

        return true;
    }

    protected function _start() {
        if(!static::$transaction) {
            Evo::app()->db()->beginTransaction();
            static::$transaction = true;
            $this->transactionStarter = true;
        }
    }

    protected function _end()
    {
        if($this->transactionStarter) {
            Evo::app()->db()->commit();
            static::$transaction = false;
            $this->transactionStarter = false;
        }
    }

    protected function lock($tableName = null)
    {
        if(!$tableName) {
            $tableName = $this->tableName;
        }

        Evo::app()->db('LOCK TABLE `' . $tableName . '` WRITE');
        ModelDb::$locked = true;
        return $this;
    }

    protected function unlock()
    {
        if(ModelDb::$locked) {
            Evo::app()->db('UNLOCK TABLES');
            ModelDb::$locked = false;
        }
        return $this;
    }

    protected function _afterSave($keys)
    {
        if(false === $this->afterSave($keys)) {
            $this->unlock();
            return false;
        }

        try {
            $this->_end();
            $this->usageFields = [];
            $this->checked = false;

        } catch (\Exception $e) {
            Evo::app()->db()->rollBack();
            throw $e;
        }

        return !$this->getErrors();
    }

    protected function _beforeSave($data, &$keys)
    {
        if(!$keys = (array)$keys) {
            $keys = array_keys($this->getFields());
        }

        $keys = (array)$keys;
        $this->validate($data, $keys);
        if(!$this->valid || false === $this->beforeSave($keys)) {
            return false;
        }
        $this->_start();
        return true;
    }

}