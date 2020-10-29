<?php

namespace Evo\Helper\View\Field\Form;

use Evo;
use Evo\Helper\View\Field\FormMultiDataField;

class AjaxImageGroup extends FormMultiDataField
{
    protected $template = '<div class="form-row image-ajax-group">{hint}{html}{error}</div>';
    public $route = '';

    protected function html()
    {
        return $this->div($this->label, ['id' => $this->inputAttributes['id'] . 'Holder']) . $this->a($this->lang->t('common.field.add'), ['class' => 'add-link', 'id' => $this->inputAttributes['id'] . 'AddLink']);
    }

    protected function ruleRequired()
    {
        return 'ajaxImage';
    }

    protected function data()
    {
        $ajax = $this->ajax();
        $ajax->files('$(this)');
        $ajax->block(false);
        $ajax->success('function(data){
                            $.each($.parseJSON(data), function(key, data) {
                                saveItem(data, key);
                            });
                        }');

        $this->registerInlineScript("
            window.ajaxImageGroupCreator = function(holder, inputId, inputName, limit, extensions){
    
                var currentIndex = 0;  
                var lastIndex = 0;

                function saveItem(data, number) {
                   
                    var item = holder.find('> .item[data-index]').eq(currentIndex+number);
                    var file = item.find(' > .file');
                    if(file.length) {
                        file.find('> .preview.image').append($('<img src=\"'+data.src+'\" name=\"'+inputName+'[]\" alt=\"loadedImage\"/>'));
                        var path = data.src.split('/');
                        file.find('> .name').html(path[path.length-1]);
                        if(!item.find('input[type=\"hidden\"]').length) {
                            item.append($('<input type=\"hidden\" name=\"'+inputName+'[]\" value=\"'+data.src+'\"/>'));
                        }
                        item.find('.add-button').remove();
                    } else {
                        createItem(data);
                    }
                
                }

                function removeItem(number) {
                    holder.find('> .item[data-index=\"'+number+'\"]').fadeOut(400, function(){
                        $(this).remove();
                        currentIndex = lastIndex = holder.find('> .item[data-index]').length;
                    });
                }

                function createItem(data) {
                
                    if(data && holder.find('img[src=\"'+data.src+'\"]').length) return;

                    var preview = $('<div class=\"preview image\"/>');
                    var filename = $('<span class=\"name\"/>');
                    var remove = $('<a class=\"remove-button\"/>');
                    remove.click(function(){
                        removeItem($(this).parent().attr('data-index'));
                    });
                    var action = null;

                    var item = $('<div class=\"item\" data-index=\"'+ lastIndex +'\"/>');

                    if(data && data.src) {
                        preview.append($('<img src=\"'+data.src+'\" alt=\"loadedImage\"/>'));
                        item.append($('<input type=\"hidden\" name=\"'+inputName+'[]\" value=\"'+data.src+'\"/>'));
                        var path = data.src.split('/');
                        filename.html(path[path.length-1]);
                    } else {
                        action = $('<div class=\"add-button\"/>');

                        var span = $('<span class=\"button\"/>').html('{$this->lang->t('common.field.choose')}');
                        var input = $('<input type=\"file\" name=\"'+inputName+'[]\" multiple/>');

                        action.append(span);
                        action.append(input);
                        input.change(function(){
                            currentIndex = parseInt($(this).parent().parent().attr('data-index'));
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
                    }

                    var file = $('<div class=\"file\"/>');
                    file.append(preview);
                    file.append(filename);

                    item.append(file);
                    item.append(action);
                    item.append(remove);

                    holder.append(item);

                    item.hide();
                    item.fadeIn(400);

                    lastIndex++;

                }

                return {
                    createItem:function(data) {
                        createItem(data);
                    }
                }
            }

        ", 'jquery', 'ajaxImageGroupCreator');

        $script = "var aIGC{$this->inputAttributes['id']} = new ajaxImageGroupCreator($('#{$this->inputAttributes['id']}Holder'), '{$this->inputAttributes['id']}', '{$this->inputAttributes['name']}', {$this->field->getRule('ajaxImage')->size}, '".implode('|', $this->field->getRule('ajaxImage')->extension)."'); " . PHP_EOL;

        foreach($this->value as $src) {
            $script.= "aIGC{$this->inputAttributes['id']}.createItem({src:'$src'});".PHP_EOL;
        }

        $script.= "$('#{$this->inputAttributes['id']}AddLink').click(function(){
            aIGC{$this->inputAttributes['id']}.createItem();
        });";

        Evo::app()->view->addScript($script, 'ajaxImageGroupCreator'.$this->inputAttributes['id'], 'ajaxImageGroupCreator');
    }
}