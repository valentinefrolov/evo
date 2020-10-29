<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 02.03.16
 * Time: 17:05
 */

namespace Evo\Helper\View;

use Evo\Field;
use Evo\View;
use Evo;

abstract class Filter extends View
{
    public $html = '';

    protected $filter = null;
    protected $field = null;
    protected $template = '';

    public function __construct(Field $field, $template='')
    {
        parent::__construct();
        $this->field = $field;
        $this->filter = $this->getFilter($field);
        $this->template = $template;
    }

    public function handle()
    {
        $this->html = $this->template ? str_replace('{html}', $this->html(), $this->template) : $this->html();
        return $this->html;
    }

    abstract protected function html();
    abstract protected function getFilter($field);

}