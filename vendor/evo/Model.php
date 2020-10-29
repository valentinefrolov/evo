<?php

namespace Evo;

use Evo;

// TODO продумать миграцию addField и attachField!
// TODO сейчас при всех действиях проверяется наличие полей в модели, но они могут и е являься ее частью
//поэтому стоит заглушка soft  методе field, надо избавиться

// TODO сейчас пагинация реализована только в ModelDB путем ограничения ДО выборки. в основной модели
// надо сделать ограничения ПОСЛЕ выборки, и в ModelDB поставить флаг, как ограничивать ее : до ли после

// TODO предыдущий пункт также и для фильтров

// TODO избавиться от синтаксиса обращения к фильтрам "$model->filters('fieldName.filterName')"

abstract class Model extends Data
{
    public $fields = []; //TODO change to protected

    public $title = ''; // model field that identificate

    public $executed = false;

    protected $cloning = false;

    protected $mode = '';
    protected $relateFields = [];
    protected $relateArrayFields = [];

    protected $usageFields = [];

    protected $sort = false;

    protected $valid   = true;
    protected $checked = false;

    protected $multiple = false;

    protected $unsafe = []; // array of field names that must be confirm
    protected $safe = []; //  array of field names that must NOT be confirm

    protected $lang = null;

    abstract public function fields();
    abstract protected function rules();
    abstract public function all();
    abstract public function one();
    abstract public function post();
    abstract public function put();


    public function __construct(array $array = [])
    {
        $this->lang = Evo::app()->lang;
        parent::__construct($array);
        $this->createFields();
        $this->createRules();
    }

    public function isValid()
    {
        if($this->checked) {
            return $this->valid;
        }
        return false;
    }

    /**
     * TODO getting fields arguments
     *
     * @return bool
     */
    public function isConfirm()
    {
        $this->valid = true;
        foreach($this->getFields() as $field) {
            if(!$this->id()) {
                $field->addError(Evo::app()->lang->t('common.error.not_exists'));
                $this->valid = false;
            } else if(!$field->validate()) {
                $this->valid = false;
            }
        }
        return $this->valid;
    }

    protected function normalizeInput(array $data) : array
    {
        foreach(array_keys($this->getFields()) as $field) {
            $norm = $this->getField($field)->normalize(isset($data[$field])?$data[$field]:null);
            if($norm !== null)
                $data[$field] = $norm;
        }
        return $data;
    }

    public function validate($data = [], $keys = [])
    {
        if($data) {
            $this->assign($data);
        }

        if(!$keys = (array)$keys) {
            $keys = array_keys($this->getFields());
        }

        foreach($keys as $name) {
            if(isset($this->fields[$name])) {
                $this->usageFields[] = $name;
            }
        }

        if(false === $this->beforeValidate($keys)) {
            $this->valid = false;
        }

        foreach($keys as $name) {
            if (($field = $this->getField($name)) && !$field->validate()) {
                    $this->valid = false;
            }
        }

        $this->checked = true;

        if($this->valid) {
            $this->afterValidate($keys);
        }

        return $this->valid;
    }

    public function removeField($field)
    {
        if(is_string($field)) {
            $field = $this->getField($field);
        }


        foreach($this->relateArrayFields as $key => $_field) {
            if($field === $_field) {
                unset($this->relateArrayFields[$key]);
            }
        }

        foreach($this->relateFields as $key => $_field) {
            if($field === $_field) {
                unset($this->relateFields[$key]);
            }
        }

        foreach($this->fields as $key => $_field) {
            if($field === $_field) {
                unset($this->fields[$key]);
            }
        }

    }

    /**
     *
     * Encapsulation
     *
     * @param $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = ucfirst($mode);
        $params = [];
        $args = func_get_args();
        if(count($args) > 1) {
            for($i=1; $i<count($args);$i++) {
                $params[] = $args[$i];
            }
        }
        $method = 'mode'.$mode;
        if(method_exists($this, $method)) {
            $result = call_user_func_array(array($this, $method), $params);
            if($result !== null) {
                return $result;
            }
        } else if($mode != 'default'){
            throw new Evo\Exception\AmbiguousException("mode $mode not found");
        }

        return $this;
    }

    /**
     * Getter for mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param $field
     * @param string $alias
     * @param string $name
     * @param string $title
     * @return Evo\Field
     */
    public function addField($field, $alias='', $name='', $title='')
    {
        if($field instanceof FieldDb) {
            $field->model = $this;
            if($name) $field->name = $name; else $name = $field->name;
            $this->relateFields[$name] = $field;

        } else {
            if(!$name) $name = $field;
            $field = $this instanceof ModelDb ? 'Evo\FieldDb' : 'Evo\Field';
            $this->relateFields[$name] = new $field($this, $name, $title);
        }

        if($alias) $this->relateFields[$name]->alias = $alias;

        return $this->relateFields[$name];
    }

    public function name()
    {
        return strtolower(preg_replace_callback('/([a-z0-9]{1})([A-Z0-9]{1})/', function($matches){
            return $matches[1] . '_' . $matches[2];
        }, $this->className()));
    }

    public function className($full=false, $module='')
    {
        if(!$full)
            return substr(strrchr(get_class($this), '\\'), 1);

        $class = '/'.str_replace('\\', '/', get_class($this));
        $pos = strpos($class, '/Model/');
        $len = strlen('/Model/');
        return substr($class, $pos+$len);
    }

    public function assign($data, $is_multiple = false, $normalize = true)
    {

        $this->multiple = $is_multiple;

        if($is_multiple) {
            foreach($data as $index => $array) {
                $this->data[$index] = $array;
            }
        } else if(is_array($data)){

            $data = $normalize ? $this->normalizeInput($data) : $data;
            $fns = $this instanceof ModelDb ? 'Evo\FieldDb' : 'Evo\Field';

            foreach($data as $key => $value) {
                if($field = $this->getField($key)) {
                    if($field->value() && !isset($value)) {
                        continue;
                    }
                    $field->value($value);
                } else if(is_array($value)){
                    $this->relateArrayFields[$key] = new $fns($this, $key);
                    $this->relateArrayFields[$key]->value($value);
                } else {
                    $this->relateFields[$key] = new $fns($this, $key);
                    $this->relateFields[$key]->value($value);
                }
                $this->data[$key] = $value;
            }
        }



        $this->assigned = true;

        return $this;
    }

    public function __invoke()
    {
        return (bool)count($this->data);
    }

    // TODO after previous fix, rename to attachField
    protected function attachArrayField($name, FieldDb $field, $via=null)
    {
        if(is_string($via)) {
            $field->via = $via;
            $field->relatePrimary = $field->model->primary;
        }

        if(isset($this->relateArrayFields[$name])) {
            $field->value($this->relateArrayFields[$name]->value());
        }

        $field->name = $name;
        $field->model = $this;
        $this->relateArrayFields[$name] = $field;
    }

    // TODO move to addField
    protected function attachField($name, Field $field, $via=null)
    {
        if($via) {
            $field->via = $via;
            $field->relatePrimary = $field->model->primary;
        }

        if(isset($this->relateFields[$name])) {
            $field->value($this->relateFields[$name]->value());
        }

        $field->name = $name;
        $field->model = $this;
        $this->relateFields[$name] = $field;
    }

    public function getFields()
    {
        return array_merge($this->fields, $this->relateFields, $this->relateArrayFields);
    }


    public function getField($fieldName)
    {
        if(isset($this->fields[$fieldName])) {
            return $this->fields[$fieldName];
        } else if(isset($this->relateFields[$fieldName])){
            return $this->relateFields[$fieldName];
        } else if(isset($this->relateArrayFields[$fieldName])){
            return $this->relateArrayFields[$fieldName];
        }
        return null;
    }


    public function addError($fieldName, $errorText)
    {
        if($this->getField($fieldName)) {
            $this->getField($fieldName)->addError($errorText);
        } else {
            throw new \Exception("Field '$fieldName' not found in model '{$this->className()}'");
        }
    }

    
    public function getErrors($asHtmlArray = false)
    {
        $errors = [];

        foreach($this->getFields() as $field) {
            if($field->errors) {
                if($asHtmlArray) {
                    foreach ($field->errors as $error) {
                        $errors[] = $field->title() . ': ' . $error;
                    }
                } else {
                    $errors[$field->name] = $field->errors;
                }
            }
        }

        return $errors;
    }
    
    protected function beforeValidate($keys){}
    protected function beforeSave($keys){}
    protected function beforePost($keys){}
    protected function beforePut($keys){}
    protected function afterValidate($keys){}
    protected function afterSave($keys){}
    protected function afterPost($keys){}
    protected function afterPut($keys){}

    protected function beforeDelete(){}
    protected function afterDelete(){}


    protected function createFields()
    {
        foreach ($this->fields() as $name => $title){
            if(preg_match('/^\d+$/', $name)) {
                $name = $title;
                $title = null;
            }
            $field = $this instanceof ModelDb ? 'Evo\FieldDb' : 'Evo\Field';
            $this->fields[$name] = new $field($this, $name, $title);
        }
    }

    protected function createRules()
    {
        foreach($this->rules() as $rule) {
            $fields = is_array($rule[0]) ? $rule[0] : [$rule[0]];
            $name = $rule[1];
            unset($rule[0], $rule[1]);
            foreach($fields as $field){
                $this->getField($field)->addRule($name, $rule);
            }
        }
    }

    /**
     * data transformers:
     */


    public function unique()
    {
        $this->assign(parent::unique(), true);
        return $this->data;
    }

    /**
     * Создает массив {key => value}
     *
     * @param $value
     * @param null $key
     * @return array|bool
     */
    public function pairs($value, $key = null)
    {
        if(!$key) {
            $key = $this->primary;
        }
        $this->assign(parent::pairs($value, $key), true);
        return $this->data;
    }

    public function values($key = null)
    {
        if(!$key) {
            $key = $this->primary;
        }
        $this->assign(parent::values($key), true);
        return $this->data;
    }

    public function groupPairs($label, $value, $key = null)
    {
        if(!$key) {
            $key = $this->primary;
        }
        $this->assign(parent::groupPairs($label, $value, $key), true);
        return $this->data;
    }

    public function sortToArray($key)
    {
        $this->assign(parent::sortToArray($key), true);
        return $this->data;
    }

    public function origin()
    {
        $this->assign(parent::origin(), true);
        return $this->data;
    }

    public function offsetSet($name, $value)
    {
        if($field = $this->getField($name)) {
            $field->value($value);
        }

        if($this->sort && isset($this->sort[$name])) {
            $this->sort[$name] = $value;
        }
    }

    public function offsetExists($name)
    {
        return isset($this->data[$name]);
    }

    public function offsetUnset($name)
    {
        unset($this->fields[$name], $this->data[$name]);
    }

    public function offsetGet($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : (isset($this->relateArrayFields[$name]) ? [] : null);
    }






}