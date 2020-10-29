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

class EfImageGroup extends FormField
{
    public $url = '';
    public $value = [];
    public $folder = '';
    protected $multiple = true;
    protected $ruleName = 'efImage';
    protected $template = '<div class="form-row elfinder-image-group">{label} {html} {error}</div>';

    protected function getDeleteFunction() {

        $deleteMessage = Evo::app()->lang->t('common.manage.delete');
        $id = $this->inputAttributes['id'];

        return "
            var {$id}DeleteFunction = function(obj) {
                if(confirm('$deleteMessage')) {
                    var index = -1;
                    $('#{$id}ImageWrapper .elfinder-picked-image').each(function(i){
                        if($(this).is(obj)) { index = i; return false; }
                    });
                    $('#{$id}InputWrapper input').eq(index).remove();
                    obj.remove();
                }
            };
            
            $('#{$id}ImageWrapper .elfinder-picked-image').click(function(){
                {$id}DeleteFunction($(this));
            });
            
        ";
    }

    protected  function callback() {

        $id = $this->inputAttributes['id'];

        return trim("
            function(files) {
                Popup.hide();
                for(var i = 0; i < files.length; i++) {
                    var path = files[i].path.replace(/\\\/g, '/');
                    
                    if($('#{$id}ImageWrapper img[src=\"'+path+'\"]').length) continue;
                    var image = $('<img/>').attr('src', path)
                        .attr('class', 'elfinder-picked-image');
                    var input = $('<input/>').attr('type', 'hidden')
                        .attr('name', '{$this->inputAttributes['name']}[]')
                        .attr('value', path);

                    $('#{$id}ImageWrapper').append(image);
                    $('#{$id}InputWrapper').append(input);

                    image.click(function(){{$id}DeleteFunction($(this));});
                }
                $('#{$id}Image').show();
            }
        ");
    }

    protected function markup() : string
    {

        $inputs = [];
        $images = [];

        foreach($this->value as $src) {
            $images[] = $this->img(['src' => $src, 'class' => 'elfinder-picked-image']);
            $inputs[] = $this->input(['type' => 'hidden', 'name' => $this->inputAttributes['name'].'[]', 'value' => $src]);
        }

        $id = $this->inputAttributes['id'];

        return  $this->div(
                implode(PHP_EOL, $images),
                ['id' => $id. 'ImageWrapper']
            ) .
            $this->div(
                implode(PHP_EOL, $inputs),
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
            ) .
            $this->div(
                $this->button($this->lang->t('common.manage.pick'), [
                    'id' => $id. 'Button',
                    'class' => 'elfinder-pick-button',
                    'data-popup' => 'Popup'.$id. 'ElFinder',
                    'type' => 'button'
                ]),
                ['class' => 'elfinder-button-wrapper'])

            ;

    }

    protected function html()
    {
        $langId = strtolower(Evo::app()->lang->getLocale());
        Evo::app()->view->addHtml(Evo::app()->view->getSource('/asset/elFinder/loader.html'), 'elFinder');
        Evo::app()->view->registerScriptSrc("/asset/elFinder/js/i18n/elfinder.$langId.js", 'elFinder', 'elFinder_lang');


        /** Rule output **/
        $rule = $this->getRule($this->ruleName);
        $url = $this->locator->buildQuery($rule->output($this));

        $id = $this->inputAttributes['id'];

        $multiple = $this->multiple ? 'true' : 'false';

        $this->registerInlineScript("

            {$this->getDeleteFunction()}
            
            $('#{$id}Button').click(function(){
                $('#{$id}ElFinder').elfinder({
                    url : '/asset/elFinder/connector.php?$url',
                    lang: '$langId',
                    getFileCallback : {$this->callback()},
                    useBrowserHistory: false,
                    commandsOptions:{
                        getfile:{
                            multiple: {$multiple},
                        }
                    } 
                }).elfinder('instance');
            });
        
        ", '*', $id.'ElFinder');

        return $this->markup();

    }
}