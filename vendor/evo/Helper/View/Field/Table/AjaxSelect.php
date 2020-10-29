<?php


namespace Evo\Helper\View\Field\Table;

use Evo\Helper\View\Field\TableDataField;

class AjaxSelect extends TableDataField
{
    public $id = '';

    protected function html()
    {
        if(!$this->id) {
            $this->id = $this->model->model->primary;
        }

        if($this->model->model) {
            $name = $this->model->model->className().'['.$this->name.']['.$this->row->getData($this->id).']';
        } else {
            $name = $this->name.'['.$this->row->getData($this->id).']';
        }

        $options = [];

        foreach($this->options as $value => $title) {
            $attr = ['value' => $value];
            if($this->value == $value) {
                $attr = ['selected' => true];
            }
            $options[] = $this->option($title, $attr);
        }

        return $this->ajax()->data(["'$name'" => $this->js('$(this).val()')])
            ->block()
            ->onEvent('change')
            ->refresh()
            ->select(implode(PHP_EOL, $options));
    }
}