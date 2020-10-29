<?php
/**
 * Created by Valentin Frolov valentinefrolov@gmail.com
 * For Aplex
 * Project Evo Engine Framework / Aplex Framework / Aplex CMS
 * Date: 01.03.2016, time: 23:19
 */

namespace Evo\Helper\View\Field;

use Closure;
use Evo\Debug;
use Evo\Field;
use Evo\Helper\View\Ajax;
use Evo\Helper\View\Interfaces\IDifferentTemplate;
use Evo\View;
use Evo\Helper\View\TemplateParser;
use Evo\Interfaces\ViewField;
use Evo\Helper\View\Model as ViewModel;
use Evo\Helper\View\Interfaces\IModelField;


abstract class ModelField extends View implements ViewField
{
    public $name = '';
    public $attributes = [];
    public $model = null;

    public $html = null;

    protected $class = '';

    public $title = '';
    protected $field = null;
    protected $value = null;
    protected $template = '';
    protected $handler = null;
    protected $parser = null;
    protected $raw = false;

    /** @var IModelField  */
    protected $templateGetter = null;


    public function __construct(ViewModel $model, array $config)
    {
        parent::__construct();

        $this->model = $model;
        $this->parser = new TemplateParser($this);

        if(isset($config['name'])) {
            $this->name = $config['name'];
            unset($config['name']);
        }

        if(isset($config['handler'])) {
            $this->handler = $this->setHandler($config['handler']);
            unset($config['handler']);
        }

        if(isset($config['field'])) {
            $this->field = $this->setField($config['field']);
            unset($config['field']);
        }

        if(isset($config['title']) && $config['title'] !== false) {
            $this->title = is_string($config['title']) ? $config['title'] :
                ($this->field ? $this->field->title() : '');
            $config['title'] = $this->title;
        } else if(isset($config['title']) && $config['title'] === false) {
            $this->title = '';
        } else if($this->title !== false && $this->field) {
            $this->title = $this->field->title();
        }

        if(isset($config['value'])) {
            $this->value = $config['value'];
            unset($config['value']);
        } else if ($this->field && $this->field->value() !== null) {
            $this->value = $this->field->value();
        }

        if(isset($config['template'])) {
            $this->template = $config['template'];
            unset($config['template']);
        } else if(!$this->template && !$this->raw && $this instanceof IDifferentTemplate){
            $this->template = $this->getTemplateGetter()->getWrapper();
        }

        foreach($config as $prop => $value) {
            if(property_exists($this, $prop)) {
                $reflection = new \ReflectionProperty($this, $prop);
                if($reflection->isPublic()) {
                    $this->$prop = $value;
                }
            }
        }

        if($rules = $this->ruleRequired()) {
            $class = get_class($this);

            if(!$this->field) {
                throw new \Exception("Required rules declared on ViewField '$class', but FormField doesn't exists");
            }
            if(!is_array($rules)) {
                $rules = [$rules];
            }
            foreach($rules as $rule) {
                if(!$this->field->getRule($rule)) {
                    $classField = get_class($this->field);
                    throw new \Exception("Rule '$rule' doesn't exists on FormField '$classField', but declared as required on ViewField '$class'");
                }
            }
        }

        return $config;
    }


    public function handle()
    {
        $this->event('handle');

        if($this->handler) {
            $handler = Closure::bind($this->handler, $this, $this);
            if(false !== ($result = $handler())) {
                return $result;
            }
        }

        $this->html = $this->html();

        if($this->template) {
            return $this->parser->parse($this->template);
        }

        return $this->html;
    }

    protected function setField(Field $field)
    {
        return $field;
    }

    protected function setHandler(callable $handler)
    {
        return $handler;
    }

    protected function getRule($name)
    {
        if($this->field && ($rule = $this->field->getRule($name))) {
            return $rule;
        } else {
            throw new \Exception('No Rule:'.$name.' for form field "'.get_class($this).'"');
        }
    }

    protected function ruleRequired()
    {
        return [];
    }

    public function registerInlineScript(string $src, $d = [], $a = '', string $scope = null)
    {
        if(Ajax::$activeBlock) $a = '';
        \Evo::app()->view->registerInlineScript($src, $d, $a, $scope);
    }

    abstract protected function html();
}