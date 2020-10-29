<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 11:03
 */

namespace Evo\Helper\View\Field\Form;

use Evo;
use Evo\Helper\View\Field\FormField;

class EfFile extends EfImage
{
    public $url = '';
    protected $multiple = false;
    protected $ruleName = 'efFile';

    protected function callback()
    {
        $id = $this->inputAttributes['id'];

        return trim("
            function(file) {
                Popup.hide();
                var url = file.path.replace(/\\\/g, '/');
                var input = $('#{$id}InputWrapper input');
                if(input.length) {
                    input.attr('value', url);
                } else {
                    input = $('<input/>').attr('type', 'text').attr('readonly', 'readonly').attr('name', '{$this->inputAttributes['name']}');
                    $('#{$id}InputWrapper').append(input);
                }    
            }
        ");
    }

    protected function markup() : string
    {
        $input = '';
        $input = $this->input(['type' => 'text', 'readonly' => 'readonly', 'name' => $this->inputAttributes['name'], 'value' => $this->value]);

        $id = $this->inputAttributes['id'];

        return
            $this->div(
            $this->div(
                $input,
                ['id' => $id. 'InputWrapper', 'class' => 'elfinder-input-wrapper']
            ) .

            $this->div(
                $this->button($this->lang->t('common.manage.pick'), [
                    'id' => $id. 'Button',
                    'class' => 'elfinder-pick-button',
                    'data-popup' => 'Popup'.$id. 'ElFinder',
                    'type' => 'button'
                ]),
                ['class' => 'elfinder-button-wrapper'])
            , ['class' => 'elfinder-holder'])
            .

            $this->div(
                $this->div('', [
                    'id' => $id.'ElFinder',
                    'class' => 'elfinder-image-dialog',
                ])
                , [
                    'class' => 'elfinder-wrapper popup-data',
                    'id' => 'Popup'.$id.'ElFinder'
                ]
            )
            ;

    }
}