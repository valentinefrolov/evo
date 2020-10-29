<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 01.03.16
 * Time: 12:29
 */

namespace Evo\Helper\View;

final class TemplateParser
{
    private $closure = null;

    public function __construct($object)
    {
        $closure = function($template, $data=null)
        {
            if(!$data) {
                $data = get_object_vars($this);
            }
            preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $template, $matches);

            list($replacement, $properties) = $matches;

            foreach($properties as $key => $property) {

                // TODO throw low priority exception when prop not exists
                if(isset($data[$property])) {
                    $properties[$key] = $data[$property];
                } else {
                    $properties[$key] = '';
                }
            }

            return str_replace($replacement, $properties, $template);
        };

        $this->closure = $closure->bindTo($object);
    }

    public function parse($template, $data=null)
    {
        $closure = $this->closure;
        return $closure($template, $data);
    }


}