<?php

namespace Evo\Rule;

use Evo;
use Evo\Rule;
use Evo\Interfaces\ViewField;
use Evo\Image;
use Evo\Helper\View\Ajax;
use Evo\File as FS;

class EfImage extends EfFile
{
    public $width = 0;
    public $height = 0;
    public $resize = Image::RESIZE_TO_BEST_FIT;
    public $bgColor = '';

    public $extension = ['jpg', 'png', 'gif', 'jpeg'];
    public $watermark = '';
    public $position = '';
    public $filters = [];


    public function output(ViewField $field)
    {
        return [
            'langId' => strtolower(Evo::app()->lang->getLocale()),
            'folder' => $this->folder,
            'url' => (Evo::app()->secure ? 'https://' : 'http://') . Evo::app()->host.$this->folder,
            'ext' => $this->extension,

            // resize
            'resize' => $this->resize,
            'width' => $this->width,
            'height' => $this->height,
            'bgColor' => $this->bgColor,

            'disabled' => ['rename', 'rm', 'cut'],
            Ajax::IS => true

            // watermark
            //'watermark' => $this->watermark,
            //'position' => $this->position,

            // perhaps will be needed
            //'filters' => $this->filters,

            // disable rm etc


            //'bind' => $this->bind
        ];

    }

    public function copy($from, $name='')
    {
        $name = $this->convertName($name ? $name : basename($from));

        $from = FS::absolute($from);

        if(FS::copy($from, $this->realPath.'/'.$name) && array_intersect($this->extension, array_keys(Image::$imageTypes))) {
            $image = new Image($from);
            if($this->width || $this->height) {
                $image->resize($this->resize, $this->width, $this->height);
            }
            $image->save(FS::absolute($this->realPath.'/'.$name));
            return $this->folder . '/'. $name;
        }

        return null;
    }
}