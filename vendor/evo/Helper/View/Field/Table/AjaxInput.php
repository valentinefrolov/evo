<?php
/**
 * Created by Valentin Frolov valentinefrolov@gmail.com
 * For Aplex
 * Project Evo Engine Framework / Aplex Framework / Aplex CMS
 * Date: 27.03.2016, time: 18:51
 */

namespace Evo\Helper\View\Field\Table;

use Evo;
use Evo\Helper\View\Field\TableField;
use Evo\Helper\View\Model as ViewModel;

class AjaxInput extends TableField
{
    public $id = '';
    public $route = null;
    public $block = null;

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

        $ajax = $this->ajax()->data(["'$name'" => $this->js('$(this).val()')])
            ->block($this->block)
            ->onEvent('focusout keypress')
            ->condition("(e.type === 'keypress' && e.keyCode === 13) || e.type === 'focusout'")
            ->refresh();

        //echo $this->route;

        if($this->route) {
            $ajax->url($this->route);
        }

        return $ajax->input(['type' => 'text', 'value' => $this->value]);
    }
}