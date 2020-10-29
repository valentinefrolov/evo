<?php


namespace Evo\Helper\View\Field\Table;

use Evo\Helper\View\Field\TableField;

class AjaxCheckbox extends TableField
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

        $attr = ['type' => 'checkbox'];

        if($this->value) {
            $attr['checked'] = true;
        }

        return $this->ajax()->data(["'$name'" => $this->js('this.checked ? 1 : 0')])
            ->block()
            ->onEvent('change')
            ->refresh()
            ->input($attr) . $this->label();
    }
}