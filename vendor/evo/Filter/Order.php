<?php

namespace Evo\Filter;

use Evo\Filter;


class Order extends Filter
{
    protected $default = 'DESC';
    // if true this order becomes first to select
    protected $top = true;

    public function id() {
        return 'o';
    }

    public function prepare()
    {
        parent::prepare();

        if($this->value && !in_array(strtoupper($this->value), ['ASC', 'DESC']) && $this->default) {
            $this->value = $this->default;
        }

        if($this->value && !in_array(strtoupper($this->value), ['ASC', 'DESC'])) {
            throw new \Exception('Wrong value of filter \'Order\': '.$this->value);
        }
    }

    public function sql() 
    {
        if($this->top) {
            $orders = $this->field->model->part('order');
            $this->field->model->reset('order');
        }

        $this->field->model->order($this->field->name . ' ' . strtoupper($this->value));

        if($this->top) {
            foreach ($orders as $order) {
                if($order instanceof \Zend_Db_Expr) {
                    $this->field->model->order($this->field->model->expr($order->__toString()));
                } else {
                    $this->field->model->order($order[0] . ' ' . $order[1]);
                }
            }
        }

    }

}
