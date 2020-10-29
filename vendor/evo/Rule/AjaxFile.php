<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 15.06.16
 * Time: 12:25
 */

namespace Evo\Rule;

use Evo;
use Evo\Helper\View\Json;


class AjaxFile extends File
{
    public function input($val)
    {
        if($this->checkIsUpload($val)) {

            $files = parent::input($val);

            $data = [];

            if ($this->multiple && is_array($files)) {
                foreach ($files as $index => $item) {
                    $data[] = [
                        'src' => $item,
                        'error' => $this->error ? $this->error : null
                    ];
                }
            } else {
                $data[] = [
                    'src' => $files,
                    'error' => $this->error ? $this->error : null
                ];
            }
            Evo::app()->view->returnAjax(Json::encode($data));
        }
        return $val;
    }
}