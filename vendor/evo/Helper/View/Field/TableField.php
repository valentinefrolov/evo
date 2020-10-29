<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 01.03.16
 * Time: 12:07
 */

namespace Evo\Helper\View\Field;

use \Closure;
use Evo\Debug;
use Evo\Helper\View\Filter;
use Evo\Helper\View\Model as ViewModel;

abstract class TableField extends ModelField
{
    protected $filters = [];
    protected $header = ''; // head template
    protected $href = '';

    protected $row = null;
    protected $column = [];


    public function __construct(ViewModel $model, array $config)
    {
        $config = parent::__construct($model, $config);

        if(isset($config['header'])) {
            $this->header = $this->setHandler($config['header']);
            unset($config['header']);
        }
        if(isset($config['href'])) {
            $this->href = $config['href'];
            unset($config['href']);
        }

        if(isset($config['filter'])) {
            if($this->field) {

                if (!is_array($config['filter']) || !isset($config['filter'][0]))
                    $config['filter'] = [$config['filter']];

                foreach ($config['filter'] as $filter) {

                    if(is_string($filter)) $filter = ['type' => $filter];

                    if(!is_object($filter) && is_array($filter)) {
                        $template = isset($filter['template']) ? $filter['template'] : null;
                        $data = isset($filter['data']) ? $filter['data'] : null;
                        $type = $filter['type'];

                        $field = !empty($filter['field']) ? $this->model->model->getField($filter['field']) : $this->field;
                        $filter = $this->getHelper('Filter/' . $type, $field, $template, $data);
                    }

                    $this->filters[] = $this->setFilter($filter);
                }

            }
            unset($config['filter']);
        }

        return $config;
    }

    // only for table
    public function setRow(TableRow $row)
    {
        $this->row = $row;
        $this->row->addField($this->name, $this);
    }

    public function setColumn(array $column)
    {
        $this->column = $column;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setFilter(Filter $filter)
    {
        //\Evo\Debug::dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        return $filter;
    }


    public function head()
    {
        $filterHtml = '';
        if($this->filters) {
            foreach($this->filters as $name => $filterData) {
                $this->filters[$name]->handle();
                $filterHtml.= $this->filters[$name]->html;
            }
        }
        if($this->header) {
            $handler = Closure::bind($this->header, $this, $this);
            return $handler();
        }
        $filterHtml = '';

        foreach($this->filters as $filter) {
            $filterHtml .= $filter->html;
        }

        return $this->th($this->div($this->span($this->title) . ($filterHtml ? $this->div($filterHtml, ['class' => 'filter-container']) : ''), ['class' => 'th-wrapper']), ['class' => $this->name]);
    }

    public function handle()
    {
        $result = parent::handle();

        $replacements = $this->row->getData();
        $result = $this->parser->parse(urldecode($result), $replacements);

        if($this->href) {
            $conf = ['href' => urldecode($this->parser->parse(urldecode($this->href), $replacements))];
            return $this->a($result ? $result : $this->lang->t('common.no_name'), $conf);
        }

        return $result;
    }

}