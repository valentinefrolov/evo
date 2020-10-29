<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 11:03
 */

namespace Evo\Helper\View\Field\Form;

use Evo;

class EfImage extends EfImageGroup
{
    protected $multiple = false;
    protected $template = '<div class="form-row elfinder-image">{label} {html} {error}</div>';

    protected function getDeleteFunction() {
        return '';
    }

    protected function callback()
    {
        $id = $this->inputAttributes['id'];

        return trim("
            function(file) {
                Popup.hide();
                var url = file.path.replace(/\\\/g, '/');
                var image = $('#{$id}ImageWrapper .elfinder-picked-image');
                var input = $('#{$id}InputWrapper input');
                if(image.length) {
                    image.attr('src', url);
                    input.attr('value', url);
                } else {
                    image = $('<img/>').attr('class', 'elfinder-picked-image');
                    input = $('<input/>').attr('type', 'hidden').attr('name', '{$this->inputAttributes['name']}');
                    $('#{$id}ImageWrapper').append(image);
                    $('#{$id}InputWrapper').append(input);
                }    
            }
        ");
    }

    protected function markup() : string
    {
        $image = '';
        $input = '';

        if($this) {
            $image = $this->img(['src' => $this->value, 'class' => 'elfinder-picked-image']);
            $input = $this->input(['type' => 'hidden', 'name' => $this->inputAttributes['name'], 'value' => $this->value]);
        }

        $id = $this->inputAttributes['id'];

        return  $this->div(
                $image,
                ['id' => $id. 'ImageWrapper']
            ) .
            $this->div(
                $this->button($this->lang->t('common.manage.pick'), [
                    'id' => $id. 'Button',
                    'class' => 'elfinder-pick-button',
                    'data-popup' => 'Popup'.$id. 'ElFinder',
                    'type' => 'button'
                ]) .
                $this->div(
                    $input,
                    ['id' => $id. 'InputWrapper']
                ) .
                $this->div(
                    $this->div('', [
                        'id' => $id.'ElFinder',
                        'class' => 'elfinder-image-dialog',
                    ])
                    , [
                        'class' => 'elfinder-wrapper popup-data',
                        'id' => 'Popup'.$id.'ElFinder'
                    ]
                ),
                ['class' => 'elfinder-button-wrapper'])

            ;

    }

}