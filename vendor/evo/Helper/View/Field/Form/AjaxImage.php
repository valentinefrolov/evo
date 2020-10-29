<?php

namespace Evo\Helper\View\Field\Form;

use Evo;
use Evo\Helper\View\Field\FormField;

class AjaxImage extends FormField
{
    protected $template = '<div class="form-row image-ajax">{hint}{html}{error}</div>';
    public $route = '';

    protected function html()
    {

        $ajax = $this->ajax();
        $ajax->files('$(this)');
        $ajax->success('function(data){
                            $.each($.parseJSON(data), function(key, data) {
                                saveItem(data, key);
                            });
                        }');

        $this->registerInlineScript("
            window.ajaxImageCreator = function(holder, input, inputName, limit, extensions){

                function saveItem(data, number) {
                    if(!holder.find('img[src=\"'+data.src+'\"]').length) {
                        var item = holder.find('> .item');
                        var file = item.find(' > .file');

                        file.find('> .preview.image').empty().append($('<img src=\"'+data.src+'\" name=\"'+inputName+'\" alt=\"loadedImage\"/>'));
                        var path = data.src.split('/');
                        file.find('> .name').html(path[path.length-1]);
                        if(!item.find('input[type=\"hidden\"]').length) {
                            item.append($('<input type=\"hidden\" name=\"'+inputName+'\" value=\"'+data.src+'\"/>'));
                        } else {
                            item.find('input[type=\"hidden\"]').val(data.src).attr('name', inputName);
                        }
                    }
                }

                function createItem(data) {

                    var preview = $('<div class=\"preview image\"/>');
                    var filename = $('<span class=\"name\"/>');

                    var action = null;

                    var item = $('<div class=\"item\"/>');

                    preview.append($('<img src=\"'+data.src+'\" alt=\"loadedImage\"/>'));

                    var path = data.src.split('/');
                    filename.html(path[path.length-1]);

                    var input = $('<input type=\"file\" name=\"'+inputName+'\"/>');

                    input.change(function(){
                        item.find('input[type=\"hidden\"]').attr('name', '');
                        var current = 0;
                        var extension = true;
                        $.each(this.files, function(k, f) {
                            current += f.size;
                            if(!f.name.match(new RegExp(extensions + '$', 'i'))) {
                                alert('{$this->lang->t('common.error.file.extension')}');
                                extension = false;
                                f = [];
                                return false;
                            }
                        });
                        if(current <= limit && extension) {
                            ".$ajax."
                        } else {
                            alert('{$this->lang->t('common.error.file.max_file_size')}' + maxFileSize);
                        }
                    });


                    action = $('<div class=\"add-button\"/>');

                    var span = $('<span class=\"button\"/>').html('{$this->lang->t('common.field.choose')}');


                    action.append(span);
                    action.append(input);

                    var file = $('<div class=\"file\"/>');
                    file.append(preview);
                    file.append(filename);

                    item.append(file);
                    item.append(action);

                    if(data && data.src) {
                        item.append($('<input type=\"hidden\" name=\"'+inputName+'\" value=\"'+data.src+'\"/>'));
                    }

                    holder.append(item);

                    item.hide();
                    item.fadeIn(400);

                }

                return {
                    createItem:function(data) {
                        createItem(data);
                    }
                }
            }

        ", 'jquery', 'ajaxImageCreator');


        $script = "var aIC{$this->inputAttributes['id']} = new ajaxImageCreator($('#{$this->inputAttributes['id']}Holder'), '{$this->inputAttributes['id']}', '{$this->inputAttributes['name']}', {$this->field->getRule('ajaxImage')->size}, '".implode('|', $this->field->getRule('ajaxImage')->extension)."'); " . PHP_EOL;


        $script.= "aIC{$this->inputAttributes['id']}.createItem({src:'$this->value'});".PHP_EOL;

        Evo::app()->view->addScript($script, 'ajaxImageCreator'.$this->inputAttributes['id'], 'ajaxImageCreator');

        return $this->div($this->label, ['id' => $this->inputAttributes['id'] . 'Holder']);
    }

    protected function ruleRequired()
    {
        return 'ajaxImage';
    }

    protected function data()
    {


    }
}