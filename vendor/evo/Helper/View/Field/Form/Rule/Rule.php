<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 16.06.16
 * Time: 14:03
 */

namespace Evo\Helper\View\Field\Form\Rule;

use Evo\Interfaces\ViewField;
use Evo\View;
use Evo\Helper\View\Ajax;
use Evo;

abstract class Rule extends View
{
    protected $_id = null;
    protected $_field = null;
    protected $_object = '';
    protected $_selector = '';

    protected $errorText = '';
    protected $param = null;
    protected $parent = '';
    protected $debug = false;

    protected static $objects = [];

    protected $ide = '';

    public function __construct(ViewField $field, array $config = null)
    {
        parent::__construct();

        $class = substr(strrchr(get_class($this), "\\"), 1);

        $this->_field = $field;
        $this->_id = ($id = $this->getId()) ? $id : '#'.$this->_field->id;
        $this->_object = str_replace(['[',']'], '_', $this->_field->model->name) . 'FormValidator';

        $this->ide = $this->_field->type == 'radio' || $this->_field->type == 'checkbox' ? 'length' : 'val()';

        $name = "[name=\"".$this->_field->inputAttributes['name']."\"]";
        $this->_selector = "$('{$this->_id}').length === 1 ? '$this->_id' : '$name:checked'";


        if(isset($config[0])) {
            $this->param = $config[0];
            unset($config[0]);
        }

        foreach($config as $name => $value) {
            if(property_exists($this, $name)) {
                $this->$name = $value;
            }
        }

        $this->parent = $this->parent ? "$('$this->parent')" : "$('$this->_id').parent()";


        if(!in_array($this->_field->model->name, static::$objects)) {

            Evo::app()->view->registerScriptSrc('/asset/js/aplexFormValidator.js', 'jquery', 'AplexFormValidator');

            $this->registerInlineScript(
                "window.{$this->_object} = new AplexFormValidator('{$this->_field->model->name}');",
                'AplexFormValidator',
                $this->_object,
                $this->_object
            );
            //Evo::app()->view->addScript("var {$this->_object} = new AplexFormValidator();", $this->_object, 'AplexFormValidator');

            static::$objects[] = $this->_field->model->name;
        }

        $action = $this->getAction();
        $listener = ($l = $this->listener()) ? $l : 'null';

        $errorText = $this->errorText ? $this->errorText : $this->lang->t('common.error.'.strtolower($class));

        //echo $this->_object . $this->_field->id. get_class($this);

        $this->registerInlineScript(
            "
                {$this->_object}.addChecker({
                    name: '{$this->_field->id}',
                    selector: {$this->_selector},
                    handle: {$this->handle()},
                    errorText: '$errorText',
                    parent: {$this->parent},
                    action: '$action',
                    listener: $listener,
                });
            ",
            $this->_object,
            $this->_object . $this->_field->id. get_class($this),
            $this->_object
        );

    }


    protected function getId(){
        return '';
    }

    protected function handle(){return '';}
    protected function listener(){return '';}
    abstract protected function getAction();
}