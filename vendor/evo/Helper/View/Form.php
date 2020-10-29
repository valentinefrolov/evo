<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 10:33
 */

namespace Evo\Helper\View;

use Evo;
use Evo\Html;
use Evo\Model as EvoModel;

class Form extends Model
{
    protected static $forms = null;

    protected $namespace = 'Evo\Helper\View\Field\Form';
    protected $folder = 'Form';
    protected $defaultType = 'text';
    protected $defaultManageType = 'submit';

    protected $attributes = ['method' => 'POST'];

    public $name = '';
    public $title = true;
    public $placeholder = false;

    protected function getForms()
    {
        if (static::$forms === null) {

            if (Evo::app()->request->ajax) {
                if (Evo::app()->request->session('view.route') == Evo::app()->request->route()) {
                    static::$forms = [];
                } else {
                    static::$forms = Evo::app()->request->session('view.ajax.forms');
                }
            } else {
                Evo::app()->request->deleteSession('view.ajax.forms');
            }
        }
    }

    protected function setFormName($name)
    {
        if (!$name && $this->model) {
            $name = $this->model->className();
        }

        if ($name) {
            static::$forms[$name] = isset(static::$forms[$name]) ? static::$forms[$name] + 1 : 1;
            Evo::app()->request->session('view.ajax.forms', static::$forms);
            $this->name = static::$forms[$name] > 1 ? "{$name}[" . static::$forms[$name] . "]" : $name;
            $this->attributes['name'] = $this->name;
        }
    }

    public function callback($text, $callback = '', $immediate = false)
    {
        if (!$callback) {
            $callback = "alert('$text');";
        }

        $this->request->flash($this->name, $callback, $immediate);
    }


    public function __construct($model = null, array $config = [], EvoModel $_model = null)
    {
        parent::__construct($model, $config, $_model);

        static::getForms();

        $this->setFormName(!empty($this->config['name']) ? $this->config['name'] : '');

        $this->attributes = array_merge(static::$defaultAttributes, $this->attributes);
        $this->attributes = !empty($this->config['attributes']) ? array_merge($this->attributes, $this->config['attributes']) : $this->attributes;

        $this->namespace = !empty($this->config['namespace']) ? $this->config['namespace'] : $this->namespace;
    }

    public function attributes(array $data)
    {
        if (empty($this->attributes['name']) && !empty($data['name'])) {
            $this->setFormName($data['name']);
        }

        return parent::attributes($data);
    }

    protected function prepareFieldConfig($name, $data)
    {
        $config = parent::prepareFieldConfig($name, $data);

        if (isset($data['id'])) {
            $config['id'] = $data['id'];
        } else if ($this->name) {
            $config['id'] = 'Form' . str_replace(['[', ']'], '_', $this->name) . ucfirst($config['name']);
        } else {
            $config['id'] = ucfirst($config['name']);
        }

        if (isset($data['name'])) {
            $config['name'] = $data['name'];
        } else {
            $config['name'] = $this->setFieldName($config['name']);
        }

        if (!isset($config['title'])) {
            $config['title'] = $this->title;
        }

        if ($this->placeholder && !isset($config['placeholder'])) {
            $config['placeholder'] = $this->placeholder;
        }

        return $config;
    }


    public function build()
    {
        if (
            $this->request->flash('system.form') == $this->name
            &&
            ($callback = $this->request->flash($this->name))
            &&
            !$this->model->getErrors()
        ) {
            Evo::app()->view->addScript($callback);
        }

        /*if (empty($this->fields)) {
            trigger_error('No fields config for form');
        }*/

        //\Evo\Debug::log($this->attributes);

        $fields = [];

        foreach (array_keys($this->fields) as $name) {
            $fields[$name] = $this->constructField($this->fields[$name])->handle();
        }

        if ($this->template) {
            return $this->form($this->parser->parse($this->template, $fields), $this->attributes);
        }

        return $this->form(
            PHP_EOL . implode(PHP_EOL, $fields) . PHP_EOL,
            $this->attributes
        );
    }


    public function header()
    {
        $attr = Html::attributes($this->attributes);
        echo '<form' . ($attr ? ' ' . $attr : '') . '>' . PHP_EOL;
    }

    public function field($fieldName)
    {
        if (empty($this->fields[$fieldName])) {
            trigger_error('Field "' . $fieldName . '" does not exists. Perhaps you wanted to use "_' . $fieldName . '"?');
        }
        return $this->constructField($this->fields[$fieldName])->handle();
    }


    public function footer()
    {
        echo '</form>' . PHP_EOL;
    }

    public function getData($name=null)
    {
        if($name) {
            return isset($this->fields[$name]) ? $this->fields[$name]->value : null;
        }
        $data = [];
        foreach($this->fields as $name => $field)
            $data[$name] = $field->value;

        return $data;
    }

    /**
     *
     * [asasd][werwer]
     * sdfsdf[sdffd]
     * dfdf
     *
     * @param $name
     * @return string
     */
    public function setFieldName($name)
    {
        if($this->name) {
            $pos = strpos($name, '[');
            if($pos === 0) {
                return $this->name.$name;
            } else if($pos !== false) {
                return $this->name.'['.str_replace('[', '][', $name);
            } else {
                return $this->name.'['.$name.']';
            }
        }
        return $name;
    }

}