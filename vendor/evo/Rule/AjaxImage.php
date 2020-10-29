<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 15.06.16
 * Time: 12:25
 */

namespace Evo\Rule;

use Evo;
use Evo\Image as Image;
use Evo\File as FS;

class AjaxImage extends AjaxFile
{
    public $extension = ['jpg', 'jpeg', 'png', 'gif'];
    public $width = 0;
    public $height = 0;
    public $resize = Image::RESIZE_TO_BEST_FIT;
    public $bg = [];

    public function copy($from, $name='')
    {
        $name = $this->convertName($name ? $name : basename($from));

        $from = FS::absolute($from);
        if(FS::copy($from, $this->realPath.'/'.$name) && array_intersect($this->extension, array_keys(Image::$imageTypes))) {
            if($this->width || $this->height) {
                $image = new Image($from);
                $image->resize($this->resize, $this->width, $this->height);
                $image->save(FS::absolute($this->realPath.'/'.$name));
            }
            return $this->folder . '/'. $name;
        }

        return null;
    }


}