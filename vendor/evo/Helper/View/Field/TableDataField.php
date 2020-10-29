<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 14.12.16
 * Time: 11:12
 */

namespace Evo\Helper\View\Field;

use Closure;
use ArrayObject;
use Evo\Helper\View\Model as ViewModel;

abstract class TableDataField extends TableField
{
    public $data = [];
    public $dataHandler = null;
    public $options = [];

    public function __construct(ViewModel $model, array $config)
    {
        $config = parent::__construct($model, $config);

        if(!empty($config['data'])) {
            $this->setData($config['data']);
            unset($config['data']);
        } else if($this->field && $this->field->data()) {
            $this->setData($this->field->data());
        }

    }

    public function setData($data)
    {
        if (!$data instanceof ArrayObject && !is_array($data)) {
            throw new \Exception('data of \'' . get_class($this) . '\' must be instance of ArrayObject or Array');
        }

        if ($data instanceof ArrayObject) {
            $this->data = $data->data;
        } else {
            $this->data = $data;
        }
    }

    public function data(){
        return $this->data;
    }

    public function handle()
    {
        if($this->dataHandler) {
            $handler = Closure::bind($this->setHandler($this->dataHandler), $this, $this);
            $this->options = $handler();
        } else {
            $this->options = $this->data();
        }

        return parent::handle();
    }
}