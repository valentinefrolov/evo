<?php
/**
 * TODO: Нелогичное размещение файла
 *
 */

namespace Evo\Helper\View\Field;

use Evo\View;
use Evo\Helper\View\TemplateParser;
use Evo\Helper\View\Field\TableField;

class TableRow extends View
{
    public $attributes = [];
    public $index = 0;

    protected $remove = false; // если надо не отображать
    protected $template = '';
    protected $fields = [];
    protected $parser = null;
    protected $data = [];

    public function __construct($data, $template, $index)
    {
        parent::__construct();
        $this->index = $index;
        $this->data = $data;
        $this->parser = new TemplateParser($this);
        if($template) {
            $this->template = $template;
        }
    }

    public function remove()
    {
        $this->remove = true;
    }

    public function addField($name, TableField $field)
    {
        $this->fields[$name] = $field;
    }

    public function getField($name=null)
    {
        if($name) {
            return $this->fields[$name];
        }

        return $this->fields;
    }

    public function getData($name=null)
    {
        if($name) {
            return $this->data[$name];
        }
        return $this->data;
    }

    public function handle()
    {
        if($this->template) {
            $data = [];
            foreach($this->fields as $name => $field)
                $data[$name] = $field->handle();

            return !$this->remove ? $this->parser->parse($this->template, $data) : '';
        } else {
            $return = '';
            foreach($this->fields as $field) {
                $return .= $this->td($field->handle(), $field->attributes).PHP_EOL;
            }
            return !$this->remove ? $this->tr($return, $this->attributes) : '';
        }
    }
}