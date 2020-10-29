<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 01.03.16
 * Time: 11:49
 */

namespace Evo\Helper\View;

use Evo;
use Evo\Html;
use Evo\Helper\View\Field\TableRow;
use Evo\Helper\View\Model;


/**
 *
 * получаем настройки manage
 * создаем на подобии manage в форме
 * при строительстве, проверяем, есть ли менедж, и есди да - рисуем tfoot с кнопками и оборачиваем таблицу в форму
 * к каждой row добавляем поле-хранилище ошибок
 * у каждой кнопки должен быть флаг - должен ли появляться чекбокс и если да - к каждой row добавляем чекбокс
 * а также быть helper-controller совершающий какие либо действия (к примеру перенос из одного раздла в другой)
 *
 *
 * Class Table
 * @package Evo\Helper\View
 *
 */

class Table extends Model
{
    protected $headTemplate = '';
    protected $footTemplate = '';


    public $form = null;

    protected $namespace = 'Evo\Helper\View\Field\Table';
    protected $folder = 'Table';
    protected $defaultType = 'text';

    protected $columns = [];
    protected $rows = [];


    protected $manage = [];

    public function template($template, $context=null)
    {
        switch($context) {
            case 'head' :
                $this->headTemplate = $template;
                break;
            case 'foot' :
                $this->footTemplate = $template;
                break;
            case null:
            default:
                $this->template = $template;
                break;
        }
    }

    protected function prepareTemplate()
    {
        $headFields = [];

        foreach(array_keys($this->fields) as $name) {
            $headFields[] = $this->constructField($this->fields[$name])->head();
        }

        $head = '';
        foreach($headFields as $field) {
            $head .= $field.PHP_EOL;
        }
        if(!$this->headTemplate) {
            $this->headTemplate = $this->tr($head);
        } else {
            $this->headTemplate = $this->parser->parse($this->headTemplate, array_merge($headFields, ['header' => $head]));
        }

        if($this->footTemplate) {
            $this->footTemplate = $this->parser->parse($this->footTemplate);
        }
    }


    public function __toString()
    {
        return $this->build();
    }

    public function build()
    {
        if(empty($this->fields)) {
            trigger_error('No fields config for table');
        }

        $this->namespace = !empty($this->config['namespace']) ? $this->config['namespace'] : $this->namespace;

        // build columns
        foreach($this->data as $index => $row) {
            foreach($row as $name => $value) {
                if(empty($this->columns[$name]))
                    $this->columns[$name] = [];

                $this->columns[$name][$index] = $value;
            }
        }


        // build rows
        foreach($this->data as $index => $row) {

            $this->rows[$index] = new TableRow($row, $this->template, $index);

            foreach(array_keys($this->fields) as $name) {
                $field = $this->constructField($this->fields[$name]);
                $field->setRow($this->rows[$index]);
                $field->setColumn(empty($this->columns[$name]) ? [] : $this->columns[$name]);

                if(empty($this->fields[$name]['value'])) {
                    $field->setValue(isset($row[$name]) ? $row[$name] : null);
                }
            }

        }

        $body = '';
        foreach($this->rows as $row) {
            $body .= $row->handle() . PHP_EOL;
        }

        $this->prepareTemplate();

        $table = (string)$this->table(
            ($this->headTemplate ?  $this->thead($this->headTemplate) : '').PHP_EOL.
            ($body ?  $this->tbody($body) : '').PHP_EOL.
            ($this->footTemplate ?  $this->tfoot($this->footTemplate) : '').PHP_EOL,
            $this->attributes
        );

        if($this->form) {
            $this->form->template(
                $this->div(
                    implode(PHP_EOL, array_map(function($item){
                        return "{{$item}}";
                    }, array_keys($this->form->fields)))
                , ['class' => $this->manageClass]) .
                $table
            );
            return $this->form->build();
        }

        return $table;
    }


    public function manage(array $fieldsConfig = [])
    {
        $this->form = $this->getHelper('Form', $this->model);
        $this->form->manage($fieldsConfig);
    }

    public function getRows() {
        return $this->rows;
    }

}


