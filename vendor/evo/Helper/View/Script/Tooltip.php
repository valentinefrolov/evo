<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 04.03.16
 * Time: 11:22
 */

namespace Evo\Helper\View\Script;

use Evo;

class Tooltip
{
    public static function build(
        $data,
        $element,
        $callback=null,
        $jsParent=null,
        $attributes=[],
        $inject=null
    ) {
        if(!$jsParent) {
            $jsParent = $element.'.parent()';
        }
        $attr = '';
        $attributes = array_merge($attributes, ['class' => 'tooltip']);
        foreach($attributes as $at => $value) {
            $attr .= ".attr('$at', '$value')";
        }
        $object = [];
        if(is_array($data)) {
            foreach($data as $key => $value) {
                if($value) $object[] = "{index:'$key',value:'$value'}";
            }
            $rows = "var rows = [" . implode(','.PHP_EOL, $object) . '];';
        } else {
            $rows = "var rows = $data;";
        }


        $index = "var index = -1;";
        $click = $callback ? ".click(function(){
            callback($(this).data('row'));
            {$element}.trigger('change');
        })" : '';
        $callback = 'var callback = ' . ($callback ? "{$callback};" : 'false;');
        $inject = 'var inject = ' . ($inject ? "{$inject};" : 'false;');
        $list = "var list = $('<ul/>')$attr;
            $jsParent.append(list);
        ";
        $checker = "function check(inputText, all) {
            index = -1;
            list.empty();
            if(inputText) {
                inputText = new RegExp('^'+inputText, 'i');
                for(var i=0;i < rows.length;i++) {
                    if(rows[i].value.match(inputText)) {
                        var item = $('<li/>').data('row', rows[i]).html(rows[i].value)$click;
                        list.append(item);
                    }
                }
            } else if (all) {
                for(var i=0;i < rows.length;i++) {
                    var item = $('<li/>').data('row', rows[i]).html(rows[i].value)$click;
                    list.append(item);
                }
            }
            !list.children().length ? list.hide() : list.show();
        }";

        return "
        (function() {
            $rows
            $index
            $callback
            $list
            $checker
            $inject
            {$element}.on('keyup focus', function(e){
                if (e.keyCode == 40 || e.keyCode == 38) {
                    e.keyCode == 40 ? ++index : --index;
                    if (list.children().eq(index).length === 0) {
                        index = e.keyCode == 40 ? 0 : list.children().length - 1;
                    }
                    list.children().removeClass('active');
                    list.children().eq(index).addClass('active');
                } else if (e.keyCode == 13 && index != -1 && typeof callback === 'function') {
                    e.preventDefault();
                    if (list.children().eq(index).length !== 0) {
                        $(this).trigger('change');
                        if(callback) {
                            callback(list.children().eq(index).data('row'));
                        }
                        list.empty();
                        list.hide();
                    }
                } else
                    check(typeof inject === 'function' ? inject($(this).val()) : $(this).val());
            });
            $(document).click(function(e){
                if(!$(e.target).is(list) && !$(e.target).is($element)) {
                    list.empty();
                    list.hide();
                    index = -1;
                }
            });
            {$element}.click(function(){
                check('', true);
            });
            return {
                update: function(data) {
                    rows = data;
                    check('', true);
                }
            }
        })()";
    }
}
