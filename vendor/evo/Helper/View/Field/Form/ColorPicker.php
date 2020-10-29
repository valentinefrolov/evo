<?php

namespace Evo\Helper\View\Field\Form;

use Evo\Helper\View\Field\FormMultiDataField;

class ColorPicker extends FormMultiDataField
{
    public $limit = 0;
    public $size = 32;

    protected function data()
    {
        $cols = $rows = ceil(sqrt(count($this->data)));

        $divs = '';
        $counter = 0;
        $dataCount = count($this->data);
        for($i = 0; $i < $rows; $i++) {
            for($j =0 ; $j < $cols && $counter++ < $dataCount; $j++) {
                $top = $i*$this->size; $left = $j*$this->size;
                $current = current($this->data);
                $selected = in_array($current, $this->value) ? ' selected' : '';
                $divs .= $this->div('',['style' => "background:#{$current}; top: {$top}px; left: {$left}px;", 'class' => "color__item$selected", 'data-color' => $current]).PHP_EOL;
                next($this->data);
            }
        }

        return $divs;
    }

    protected function html()
    {
        $size = ceil(sqrt(count($this->data))) * $this->size;

        \Evo::app()->view->addStyle("
            <style>
                #{$this->inputAttributes['id']}_holder {
                    box-sizing: border-box;
                    display: inline-block; 
                    position: relative; 
                    width: {$size}px; 
                    height: {$size}px
                }
            
                #{$this->inputAttributes['id']}_holder .color__item {
                    box-sizing: border-box;
                    position: absolute; 
                    width:{$this->size}px; 
                    height:{$this->size}px;
                }
                
                #{$this->inputAttributes['id']}_holder .color__item::before {
                    background: white;
                    display: block;
                    left: 0;
                    height: 100%;
                    opacity: .5;
                    position: absolute;
                    top: 0;
                    width: 100%;
                }
                
                #{$this->inputAttributes['id']}_holder .color__item::after {
                    border-left: 4px solid red;
                    border-bottom: 4px solid red;
                    display: block;
                    height: 25%;
                    left: 25%;
                    position: absolute;
                    top: 30%;
                    transform: rotate(-45deg);
                    width: 50%;
                }
                
                
                #{$this->inputAttributes['id']}_holder .color__item.selected::before, 
                #{$this->inputAttributes['id']}_holder .color__item.selected::after {
                    content: \"\";
                }
                
                #{$this->inputAttributes['id']}_inputs {
                    visibility: hidden;
                }
            
            </style>
        
        ");

        $this->registerInlineScript("
            var holder = $('#{$this->inputAttributes['id']}_holder');
            var inputs = $('#{$this->inputAttributes['id']}_inputs');
            var limit = {$this->limit};
            var colors = holder.find('[data-color]');
            
            function selectColor(div) {
                var input = $('<input type=\"hidden\" value=\"'+div.data('color')+'\" name=\"{$this->inputAttributes['name']}[]\"/>');
                inputs.append(input);
                div.data('input', input);
                div.addClass('selected');
            }
            
            function deselectColor(div) {
                div.data('input').remove();
                div.data('input', null);
                div.removeClass('selected');
            }
         
            colors.each(function(){
                var div = $(this);
                if(div.hasClass('selected')){
                    selectColor(div);
                }
            });
            
            colors.click(function(){
                var div = $(this);
                if(!div.hasClass('selected')) {
                    if(!limit || inputs.children().length < limit) {
                        selectColor(div);
                    }
                } else {
                    deselectColor(div);
                }
            })",
        'jquery', $this->inputAttributes['id'].'_colorPicker');

        $holder = $this->div($this->options, ['id' => $this->inputAttributes['id'].'_holder']);
        $inputs = $this->div('', ['id' => $this->inputAttributes['id'].'_inputs']);

        return  $holder . $inputs;
    }


}
