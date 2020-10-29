<?php

namespace Evo;


abstract class Html
{
    protected static $void = array('area','base','br','col','command','embed','hr','img','input','keygen','link','meta','param','source','track','wbr');

    protected static $tags = array(
        'a', 'head', 'style', 'title', 'address', 'article', 'footer', 'header', 'h1', 'h2',
        'h3', 'h4', 'h5', 'h6', 'hgroup', 'nav', 'section',
        'dd', 'div', 'dl', 'dt', 'figcaption', 'figure', 'li', 'main', 'ol', 'p', 'pre',
        'ul', 'abbr', 'b', 'bdi', 'bdo', 'cite', 'code', 'data', 'dfn', 'em', 'i', 'kbd',
        'mark', 'q', 'rp', 'rt', 'rtc', 'ruby', 's', 'samp', 'small', 'span', 'strong',
        'sub', 'sup', 'time', 'u', 'var', 'audio', 'map', 'video', 'object', 'canvas',
        'noscript', 'script', 'del', 'ins', 'caption', 'colgroup', 'table',
        'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'button', 'datalist', 'fieldset',
        'form', 'label', 'legend', 'meter', 'optgroup', 'option', 'output', 'progress',
        'select', 'details', 'dialog', 'menu', 'menuitem', 'summary', 'content', 'element',
        'shadow', 'template', 'acronym', 'applet', 'basefont', 'big', 'center',
        'content', 'dir', 'font', 'frame', 'frameset', 'isindex', 'listing', 'marquee',
        'plaintext', 'spacer', 'strike', 'tt', 'xmp', 'textarea'
    );
    
    /**
     * В случае, если нет вызываемого метода, будет вызван метод tag с параметром названия вызываемого метода
     * 
     * @param <type> $name 
     * @param <type> $args 
     * 
     * @return <type>
     */
    public function __call($name, $args) 
    {
        return self::__callStatic($name, $args);
    }
    
    public static function __callStatic($name, $args) 
    {
        if(!method_exists(get_class(), $name)) {
            if (in_array($name, self::$void)) {
                array_unshift($args, $name, '');
            } else if (in_array($name, self::$tags)){
                array_unshift($args, $name);
            }
            $name = 'tag';
        }
        return call_user_func_array('self::' . $name, $args);
    }

    public static function tag($tag, $innerHtml = '', $attributes = array())
    {

        $attributes = ($a = self::attributes($attributes)) ? ' '.$a : $a;
        $tag = strtolower($tag);

        if(is_array($innerHtml)) {
            echo 'Html:60';
            //\Evo\Debug::dump($innerHtml, false);
            \Evo\Debug::dump(debug_backtrace(), false, 10);
        }

        return '<'.$tag.$attributes.(in_array($tag, self::$void)?'/>':'>'.
            trim($innerHtml).
            '</'.$tag.'>');
    }

    public static function attributes($attributes)
    {
        $result = array();
        if(is_array($attributes)) {
            foreach($attributes as $key => $value) {
                if(gettype($key) === 'integer') {
                    $result[] = $value;
                } elseif(gettype($value) === 'array') {
                    $result[] = $key.'="'.self::drawStyle($value).'"';
                } else {
                    $result[] = $key.'="'.$value.'"';
                }
            }
        } else {
            return $attributes;
        }
        return implode(' ', $result);
    }
    
    public static function drawStyle($styles)
    {
        $result = '';

        foreach($styles as $style => $value) {
            $result .= $style . ':' . $value . ';';
            }
        return $result;
    }



}