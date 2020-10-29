<?php

namespace Evo\Helper\View\Filter;

use Evo\Field;
use Evo\Helper\View\Filter;
use Evo;
use ArrayObject;

abstract class DataFilter extends Filter
{
    protected $data = [];
    protected $options = [];

    public function __construct(Field $field, $template='', $data=[])
    {
        parent::__construct($field, $template);

        if($data) {
            $this->setData($data);
        } else if($this->field->data) {
            $this->setData($this->field->data);
        }
    }

    public function setData($data)
    {
        if($data instanceof ArrayObject) {
            $this->data = $data->data;
        } else {
            $this->data = $data;
        }
        $this->options = $this->getOptions();
        return $this;
    }

    public function handle()
    {
        if(!$this->data) {
            $class = get_class($this);
            throw new \Exception("No Data defined for filter '$class'");
        }
        return parent::handle();
    }

    protected function getOptions()
    {
        $options = [];
        foreach($this->data as $value => $name) {
            $opt = ['value' => $value];

            if ((string)$this->filter->value == (string)$value) {
                $opt['selected'] = 'selected';
            }
            $options[] = $this->option($name, $opt);
        }
        return $options;
    }

    protected function getFilter($field)
    {
        return $field->getFilter('where');
    }

}