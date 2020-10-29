<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 11:03
 */

namespace Evo\Helper\View\Field\Form;

use Evo;
use Evo\Helper\View\Field\FormMultiDataField;
use Evo\Helper\View\Model as ViewModel;

class TextRange extends FormMultiDataField
{
    public $min = 0;
    public $max = 0;
    public $mask = 0;

    public function __construct(ViewModel $model, array $config)
    {
        parent::__construct($model, $config);
        $this->getHelper("Field/Form/Rule/Mask", $this, [$this->mask]);
    }

    protected function html()
    {
        $id = $this->inputAttributes['id'];
        unset($this->inputAttributes['name']);
        $this->inputAttributes['type'] = 'text';

        $mask = preg_replace('/9+/', '\\d+\\.?\\d*', $this->mask);

        $this->registerInlineScript("
            $('#$id').keyup(function(){
                var minLimit = $this->min;
                var maxLimit = $this->max;

                var value = /(\d*)\D+(\d*)/g.exec($(this).val());
                var less  = false;
                var great = false;
                if(value && value[1]) {
                    if(
                        (!minLimit || ((less  = !(parseFloat(value[1]) >= parseFloat(minLimit))) || true))
                                                &&
                        (!maxLimit || ((great = !(parseFloat(value[1]) <= parseFloat(maxLimit))) || true))
                    ) {
                        $('#{$id}_min').val(less ? minLimit : (great ? maxLimit : value[1]));
                    }
                } else {
                    $('#{$id}_min').val('');
                }
                less  = false;
                great = false;
                if(value && value[2]) {
                    if(
                        (!minLimit || ((less  = !(parseFloat(value[2]) >= parseFloat(minLimit))) || true))
                                                &&
                        (!maxLimit || ((great = !(parseFloat(value[2]) <= parseFloat(maxLimit))) || true))
                    ) {
                        $('#{$id}_max').val(less ? minLimit : (great ? maxLimit : value[2]));
                    }
                } else {
                    $('#{$id}_max').val('');
                }

            });
        ", 'jquery', 'text-range-'.$this->inputAttributes['id']);

        return  $this->options . PHP_EOL . $this->input($this->inputAttributes);
    }

    protected function data()
    {
        $id = $this->inputAttributes['id'];
        $name = $this->inputAttributes['name'];

        $value1 = count($this->data) > 0 && !empty($this->data[array_keys($this->data)[0]]) ?
            $this->data[array_keys($this->data)[0]] : '';
        $value2 = count($this->data) > 1 && !empty($this->data[array_keys($this->data)[1]]) ?
            $this->data[array_keys($this->data)[1]] : '';

        return $this->input(['type' => 'hidden', 'name' => $name.'[min]', 'id' => "{$id}_min", 'value' => $value1]) . PHP_EOL .
        $this->input(['type' => 'hidden', 'name' => $name.'[max]', 'id' => "{$id}_max", 'value' => $value2]);
    }
}