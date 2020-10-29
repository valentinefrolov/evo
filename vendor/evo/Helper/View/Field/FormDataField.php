<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 14:39
 */

namespace Evo\Helper\View\Field;

use Closure;
use ArrayObject;
use Evo\Helper\View\Model as ViewModel;

abstract class FormDataField extends FormField
{
    public $options = '';
    public $data = [];
    public $dataHandler = null;

    public $selected = -1;

    public function __construct(ViewModel $model, array $config)
    {
        parent::__construct($model, $config);

        if(!empty($config['data'])) {
            $this->setData($config['data']);
            unset($config['data']);
        } else if($this->field && $this->field->data()) {
            $this->setData($this->field->data());
        }
    }

    public function setData($data)
    {
        if(!$data instanceof ArrayObject && !is_array($data)) {
            throw new \Exception('data of \''.get_class($this).'\' must be instance of ArrayObject or Array');
        }

        if($data instanceof ArrayObject) {
            $this->data = $data->data;
        } else {
            $this->data = $data;
        }

        $rules = is_array($r = $this->ruleRequired()) ? $r : [];

        foreach($rules as $rule) {
            $this->field->getRule($rule)->output($this);
        }


        if(!is_null($this->value) && (is_string($this->value) && strlen($this->value))) {
            foreach($this->data as $index => $value) {
                if($index == $this->value) {
                    $this->selected = $index;
                    break;
                }
            }
        }

    }

    public function handle()
    {
        if($this->dataHandler) {
            $handler = Closure::bind($this->setHandler($this->dataHandler), $this, $this);
            $this->options = $handler();
        } else {
            $this->options = $this->data();
        }

        return parent::handle();
    }

    abstract protected function data();
}