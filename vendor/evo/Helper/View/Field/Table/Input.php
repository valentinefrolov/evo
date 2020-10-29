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

class Input extends TableField
{
    public $id = '';
    public $route = null;
    public $block = null;

    protected function html()
    {
        if(!$this->id) {
            $this->id = $this->model->model->primary;
        }

        if($this->model->form) {
            $name = $this->model->form->setFieldName($this->name).'[]';
        } else {
            $name = $this->name.'[]';
        }


        return $this->input(['type' => 'text', 'value' => $this->value, 'name' => $name]);
    }
}