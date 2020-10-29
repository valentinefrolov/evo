<?php


namespace Evo;

use Evo\File;
use Gumlet\ImageResize;
use Gumlet\ImageResizeException;

class Image
{
    const RESIZE_TO_BEST_FIT = 0;
    const RESIZE_TO_LONG_SIDE = 1;
    const RESIZE_TO_SHORT_SIDE = 2;
    const RESIZE_TO_HEIGHT = 3;
    const RESIZE_TO_WIDTH = 4;
    const CROP = 5;

    /** @var ImageResize  */
    protected $resizer = null;
    /** @var string  */
    protected $fileName = '';

    public static $imageTypes = [
        'jpg' => IMAGETYPE_JPEG,
        'jpeg' => IMAGETYPE_JPEG,
        'JPG' => IMAGETYPE_JPEG,
        'JPEG' => IMAGETYPE_JPEG,
        'png' => IMAGETYPE_PNG,
        'PNG' => IMAGETYPE_PNG,
        'gif' => IMAGETYPE_GIF,
        'GIF' => IMAGETYPE_GIF,
    ];

    public static function getMimeTypeOfExtension($ext)
    {
        if(empty(static::$imageTypes[$ext])) {
            return false;
        }

        switch(static::$imageTypes[$ext]) {
            case IMAGETYPE_JPEG:
                return 'image/jpeg';
            case IMAGETYPE_GIF:
                return 'image/gif';
            case IMAGETYPE_PNG:
                return 'image/png';
            default:
                return false;
        }
    }

    public function __construct(string $fileName)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(100);
        $this->fileName = File::absolute($fileName);
        $this->resizer = new ImageResize($this->fileName);
    }

    public function resize(int $type, int $w=0, int $h=0)
    {
        switch($type) {
            case static::RESIZE_TO_LONG_SIDE:
                $this->resizer->resizeToLongSide(max($w, $h));
                break;
            case static::RESIZE_TO_SHORT_SIDE:
                if($w === 0) $w = PHP_INT_MAX;
                if($h === 0) $h = PHP_INT_MAX;
                $this->resizer->resizeToShortSide(min($w, $h));
                break;
            case static::RESIZE_TO_WIDTH:
                $this->resizer->resizeToWidth($w);
                break;
            case static::RESIZE_TO_HEIGHT:
                $this->resizer->resizeToHeight($h);
                break;
            case static::CROP:
                $this->resizer->crop($w, $h);
                break;
            case static::RESIZE_TO_BEST_FIT:
            default:
                $this->resizer->resizeToBestFit($w, $h);
        }
    }

    public function save(string $fileName = '') {

        $fileName = $fileName ? File::absolute($fileName) : $this->fileName;
        try {
            return $this->resizer->save($fileName);
        } catch(ImageResizeException $e) {
            return null;
        }
    }
}