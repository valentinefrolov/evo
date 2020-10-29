<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 12.12.16
 * Time: 18:53
 */

namespace Evo\Helper\View\Filter\Ajax;

use Evo\Helper\View\Filter\Search as BaseSearch;


class Search extends BaseSearch
{
    protected function html()
    {
        $name = $this->field->name.$this->filter->getName();

        $ajax = $this->ajax()
                ->pushState(true)
                ->refresh()
                ->block()
                ->addSuccess("
                    var input = $('#$name');
                    input.focus();
                    input.val(input.val());
                ")
                ->url($this->locator->route($this->request->route(), [$this->filter->getName() => $this->quote("$('#$name').val()")], null, true));

        \Evo::app()->view->registerInlineScript("
        
        $(document).on('focus', '#$name', function(){
            $(this).data('focus', $(this).val());
        });
        $(document).on('keypress blur', '#$name', function(e){ 
            if((e.type === 'keypress' && e.keyCode == 13) || (e.type === 'blur')) {
                if($(this).val() != $(this).data('focus')) {
                    e.preventDefault();
                    e.stopPropagation();
                    $ajax
                }
                $(this).data('focus', false);
            }
        })", 'jquery', $name);


        return $this->div($this->input(['type' => 'text', 'value' => $this->request->get($this->filter->getName()), 'id' => $name, 'autocomplete' => 'off']), ['class' => 'filter-search']);
    }
}