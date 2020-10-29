<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 10:39
 */

namespace Evo\Helper\View\Field;

use Evo\Helper\View\Model as ViewModel;
use Evo\Helper\View\Interfaces\IDifferentTemplate;

abstract class FormField extends ModelField implements IDifferentTemplate
{
    /** @var string */
    protected $template = '';
    protected $raw = false;

    public $inputAttributes = [];
    public $title = '';
    public $value = '';
    public $id = '';
    public $hint = '';
    public $required = false;
    public $placeholder = '';

    public $error = ''; // html
    public $errors = [];
    public $type = '';

    public function getTemplateGetter()
    {
        return $this->getHelper('Field/FormFieldTemplate', $this);
    }

    public function __construct(ViewModel $model, array $config)
    {
        $config = parent::__construct($model, $config);

        $this->type = strtolower(substr(get_class($this), strrpos(get_class($this), '\\')+1));

        if(isset($config['id'])) {
            $this->id = $config['id'];
        }

        if(isset($config['hint'])) {
            $this->hint = $this->p($config['hint'], ['class' => 'hint']);
        }

        if($this->field && $this->field->getErrors()) {
            $this->errors = $this->field->getErrors();
        }

        if(isset($config['error'])) {
            $error = !is_array($config['error']) ? [$config['error']] : $config['error'];
            $this->errors = array_merge($this->errors, $error);
        }

        $this->inputAttributes['name'] = $this->name ;

        $this->inputAttributes['id'] = isset($config['id']) ? $config['id'] : '';
        unset($config['id']);

        if($this->value !== null) {
            $this->inputAttributes['value'] = $this->value;
        }

        if(!empty($config['attributes'])) {
            foreach ($config['attributes'] as $key => $value) {
                if (!isset($this->inputAttributes[$key])) {
                    $this->inputAttributes[$key] = str_replace('"', '&quot;', $value);
                }
            }
            unset($config['attributes']);
        }

        if(!empty($config['required'])) {
            $this->required = $config['required'];
            unset($config['required']);
        } else if($this->field && $this->field->getRule('required')) {
            $this->required = true;
        }


        if($this->title) {
            $this->label = $this->getTemplateGetter()->getLabel();
        }

        if(isset($config['placeholder'])) {
            $this->inputAttributes['placeholder'] = is_string($config['placeholder']) ?
                $config['placeholder'] : ($this->field ? $this->field->title() : '');
            $this->placeholder = $this->inputAttributes['placeholder'];
        }




        if(isset($config['rules'])) {

            $rules = is_array($config['rules']) ? $config['rules'] : [$config['rules']];
            foreach($rules as $key => $rule) {
                $conf = [];
                if(!preg_match('/^\d+$/', $key)) {
                    $conf = is_array($rule) ? $rule : [$rule];
                    $rule = $key;
                }
                $this->getHelper("Field/Form/Rule/$rule", $this, $conf);
            }
            unset($config['rules']);
        }

        return $this->inputAttributes;
    }


    public function handle()
    {
        $this->error = $this->getTemplateGetter()->getError($this->errors);
        $result = parent::handle();

        return $result;
    }

    public function __toString()
    {
        return $this->handle();
    }


}