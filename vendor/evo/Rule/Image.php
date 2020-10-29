<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 10.06.16
 * Time: 13:56
 */

namespace Evo\Rule;

use Evo;
use Evo\Image as EvoImage;

class Image extends File
{
    public $width = 0;
    public $height = 0;
    public $resize = EvoImage::RESIZE_TO_BEST_FIT;
    public $bgColor = [];
    public $from = '';
    public $extension = ['jpg', 'jpeg', 'png', 'gif'];


    public function check()
    {
        $value = $this->from ? $this->field->model->getField($this->from)->value() : $this->field->value();
        if($value) {
            $image = new EvoImage(Evo::getWebDir().'/'.$value);
            if($this->width || $this->height) {
                $image->resize($this->width, $this->height, $this->resize);
            }
            $value = $this->folder.'/'.basename($value);
            $image->save(Evo::getWebDir().'/'.$value);
            $this->field->value($value);
        }
        parent::check();
    }
}