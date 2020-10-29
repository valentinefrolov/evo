<?php

namespace Evo\Rule;

use Evo;
use Evo\Interfaces\FieldEntity;
use Evo\Rule;
use Evo\Interfaces\ViewField;
use Evo\Helper\View\Ajax;
use Evo\File as FS;

class efFile extends File
{
    public $bind = null;

    public function output(ViewField $field)
    {
        return [
            // base
            'folder' => $this->folder,
            'url' => (Evo::app()->secure ? 'https://' : 'http://') . Evo::app()->host.$this->folder,
            'ext' => $this->extension,

            'bind' => $this->bind,
            Ajax::IS => true
        ];

    }
    
}