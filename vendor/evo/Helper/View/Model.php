<?php
/**
 * Created by Valentin Frolov valentinefrolov@gmail.com
 * For Aplex
 * Project Evo Engine Framework / Aplex Framework / Aplex CMS
 * Date: 01.03.2016, time: 23:08
 */

namespace Evo\Helper\View;

use Evo\Model as EvoModel;
use Evo\View;

abstract class Model extends View
{
    public static $defaultAttributes = [];
    public $model = null;
    public $manageClass = 'manage-box';
    public $config = [];

    protected $parser = null;

    protected $fields = [];
    protected $_fields = [];
    protected $data = [];
    protected $attributes = [];

    protected $template = '';
    protected $namespace = '';

    protected $defaultType = '';
    protected $defaultManageType = '';

    public function __construct($model=null, array $config = [], EvoModel $_model = null)
    {
        parent::__construct();

        if($model instanceof EvoModel) {
            $this->model = $model;
            $this->data = $model->data;
        } else if(is_array($model)) {
            $this->data = $model;
        }

        if($_model && $_model instanceof EvoModel) {
            $this->model = $_model;
        }


        $this->config = $config;
        $this->parser = new TemplateParser($this);

        foreach($config as $name => $data) {
            if(method_exists($this, $name)) {
                $this->$name($data);
            }
        }
    }

    public function fields(array $fieldsConfig)
    {
        foreach($fieldsConfig as $name => $config) {

            if(preg_match('/^\d+$/', $name)) {
                $name = $config;
                $config = [];
            }

            $fieldData = $this->prepareFieldConfig($name, $config);
            $this->fields[$name] = $fieldData;
        }

        return $this;
    }

    public function ns($ns)
    {
        $this->config['namespace'] = $ns;
        return $this;
    }

    public function attributes(array $data)
    {
        $this->attributes = array_merge(static::$defaultAttributes, $this->attributes, $data);
        return $this;
    }

    public function template($template)
    {
        $this->template = $template;
    }

    public function manage(array $fieldsConfig = [])
    {
        foreach($fieldsConfig as $name => $config) {
            if(preg_match('/^\d+$/', $name)) {
                $name = $config;
                $config = [];
            }

            $fieldData = $this->prepareFieldConfig($name, $config);
            $fieldData['type'] = $fieldData['type'] == $this->defaultType ?
                $this->defaultManageType :
                $fieldData['type'] ;
            $this->fields['_' . $name] = $fieldData;
        }
    }

    protected function prepareFieldConfig($name, $data)
    {
        $config = [];
        if(preg_match('/^\d+$/', $name) && is_string($data)) {
            $config['name'] = $data;
            $config['type'] = $this->defaultType;
        } else if(is_string($data)) {
            $config['name'] = $name;
            $config['type'] = $data;
        } else {
            $config = array_merge($data, ['name' => $name]);
        }
        $config['type'] = empty($config['type']) ? $this->defaultType : $config['type'];
        $config['field'] = $this->model && ($f = $this->model->getField($config['name'])) ? $f : null;

        return $config;
    }

    protected function constructField($config)
    {

        $type = ucfirst($config['type']);
        $namespace = !empty($config['namespace']) ? $config['namespace'] : $this->namespace;

        if(empty($config['namespace'])) {
            $field = $this->getHelper("Field/{$this->folder}/{$type}", $this, $config);
        } else {
            try {
                $class = "$namespace\\$type";
                $field = new $class($this, $config);
            } catch (\Exception $e) {
                $class = "$this->namespace\\$type";
                $field = new $class($this, $config);
            }
        }

        return $field;
    }

    /**
     * @param $name
     * @return array
     */
    public function getField($name)
    {
        return isset($this->fields[$name]) ? $this->fields[$name] : false;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getTemplate() {
        return $this->template;
    }

    abstract public function build();


}