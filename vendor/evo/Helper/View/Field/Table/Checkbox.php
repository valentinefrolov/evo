<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 01.03.16
 * Time: 17:37
 */

namespace Evo\Helper\View\Field\Table;

use Evo\Helper\View\Field\TableField;
use Closure;

class Checkbox extends TableField
{
    public $checked = false;
    public $showTitle = false;

    protected static $counter = 0;

    protected function html()
    {
        if($this->model->form) {
            $name = $this->model->form->setFieldName($this->name).'[]';
        } else {
            $name = $this->name.'[]';
        }

        $input = ['type' => 'checkbox','name' => $name, 'value' => $this->value, 'onchange' => "
            if($(this).is(':checked')) {
                $(this).parents('tr').addClass('selected');
            } else {
                $(this).parents('tr').removeClass('selected');
            }
        "];

        if($this->checked) {
            $input['checked'] = 'checked';
        }

        return $this->input($input) . $this->label();
    }

    public function head()
    {
        $filterHtml = '';
        if($this->filters) {
            foreach($this->filters as $name => $filterData) {
                $this->filters[$name]->handle();
                $filterHtml.= $this->filters[$name]->html;
            }
        }
        if($this->header) {
            $handler = Closure::bind($this->header, $this, $this);
            return $handler();
        }
        $filterHtml = '';
        foreach($this->filters as $filter) {
            $filterHtml .= $filter->html;
        }

        $counter = ++static::$counter;

        $id = str_replace(['[', ']'], '_', $this->name);

        \Evo::app()->view->addScript("
        
            var checkAll = $('#{$id}_{$counter}_TableAllSelector');
            var table = checkAll.parents('table');
                       
            checkAll.click(function(){
                var attr = $(this).is(':checked') ? true : false;
                table.find('input[type=\"checkbox\"]').each(function(){
                    $(this)[0].checked = attr;
                    if(attr) {
                        $(this).parents('tr').addClass('selected');
                    } else {
                        $(this).parents('tr').removeClass('selected');
                    }
                });
            });
           
            
        ");

        return $this->th($this->div($this->input(['type' => 'checkbox', 'id' => $id.'_'.$counter.'_TableAllSelector']) . $this->label($this->showTitle ? $this->title : '') . ($filterHtml ? $this->div($filterHtml, ['class' => 'filter-container']) : ''), ['class' => 'th-wrapper']), ['class' => $id]);
    }
}