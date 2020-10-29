<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 11:03
 */

namespace Evo\Helper\View\Field\Form;

use Evo;
use Evo\Helper\View\Json;
use Evo\Helper\View\Field\FormDataField;
use Evo\Helper\View\Script\Tooltip;

class TextSelect extends FormDataField
{
    protected function html()
    {
        $attr = $this->inputAttributes;
        $attr['type'] = 'text';
        $attr['autocomplete'] = 'off';
        unset($attr['name']);

        return $this->div(
            $this->input(array_merge($this->inputAttributes, ['type' => 'hidden', 'id' => $this->inputAttributes['id'].'Input'])).
            $this->input($attr),
        ['class' => 'text-data-holder']);
    }

    protected function data()
    {
        $data = [];

        //\Evo\Debug::dump($this->data, false);

        foreach($this->data as $key => $value) {
            $value = str_replace("'", "\'", $value);
            $data[] = "{index:'$key',value:'$value'}";
        }

        //\Evo\Debug::log($data);

        $jsonData = '['.implode(', ', $data).']';

        Evo::app()->view->registerScriptSrc('/asset/js/textSelectHandler.js', 'jquery', 'TextSelectHandler');

        $id = $this->inputAttributes['id'];
        
        $this->registerInlineScript("
            
            var {$id}Handler = new TextSelectHandler(
                $('#{$id}'),
                $('#{$id}Input'),
                $jsonData
            );
            ".
            Tooltip::build(
                "{$id}Handler.getData()",
                '$(\'#'.$id.'\')',
                "{$id}Handler.setter"
            ), 'TextSelectHandler', 'TextSelect_'.$id
        );
    }
}