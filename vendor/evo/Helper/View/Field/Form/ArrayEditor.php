<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 11:03
 */

namespace Evo\Helper\View\Field\Form;

use Evo;
use Evo\Helper\View\Field\FormDataField;
use Evo\Helper\Controller\ArrayFileCreator;

class ArrayEditor extends FormDataField
{
    protected $template = '<div class="form-row checkbox">{html}{label}{error}</div>';

    protected function html()
    {
        $this->registerInlineScript("

            $('#{$this->inputAttributes['id']}').keydown(function(e){
                if(e.keyCode == 13) {
                    e.preventDefault();
                    var editor = this;
                    var doc = editor.ownerDocument.defaultView;
                    var sel = doc.getSelection();
                    var range = sel.getRangeAt(0);
                    var tabNode = document.createTextNode('\u000a');
                    range.insertNode(tabNode);
                    range.setStartAfter(tabNode);
                    range.setEndAfter(tabNode);
                    sel.removeAllRanges();
                    sel.addRange(range);
                } else if(e.keyCode == 9) {
                    e.preventDefault();
                    var editor = this;
                    var doc = editor.ownerDocument.defaultView;
                    var sel = doc.getSelection();
                    var range = sel.getRangeAt(0);
                    var tabNode = document.createTextNode('\u0020\u0020\u0020\u0020');
                    range.insertNode(tabNode);
                    range.setStartAfter(tabNode);
                    range.setEndAfter(tabNode);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            });
            $(document).on('keyup', '#{$this->inputAttributes['id']}', function(e){
                $('#{$this->inputAttributes['id']}Helper').val($(this).html());
            });
            $(document).on('paste', '#{$this->inputAttributes['id']}', function(e){
                var pastedText = undefined;

                if (window.clipboardData && window.clipboardData.getData) {
                    pastedText = window.clipboardData.getData('Text');
                } else {
                    var clipboardData = (e.originalEvent || e).clipboardData;
                    if (clipboardData && clipboardData.getData) {
                        pastedText = clipboardData.getData('text/plain');
                    }
                }
                e.preventDefault();
                var editor = this;
                var doc = editor.ownerDocument.defaultView;
                var sel = doc.getSelection();
                var range = sel.getRangeAt(0);
                range.deleteContents();
                var tabNode = document.createTextNode(pastedText);
                range.insertNode(tabNode);
                range.setStartAfter(tabNode);
                range.setEndAfter(tabNode);
                sel.removeAllRanges();
                sel.addRange(range);

                $('#{$this->inputAttributes['id']}Helper').val($(this).html());
            });
        ", 'jquery', 'ArrayEditor'.$this->inputAttributes['id']);

        $name = $this->inputAttributes['name'];
        unset($this->inputAttributes['name']);
        $this->inputAttributes['contenteditable'] = 'true';
        return $this->pre($this->options, $this->inputAttributes).PHP_EOL.$this->input(['type' => 'hidden', 'name' => $name, 'value' => $this->options, 'id' => $this->inputAttributes['id'].'Helper']);
    }


    protected function data()
    {
        return ArrayFileCreator::encode($this->data);
    }
}