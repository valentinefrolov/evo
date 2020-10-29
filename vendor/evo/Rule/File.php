<?php

namespace Evo\Rule;

use Evo;
use Evo\Rule;
use Evo\Interfaces\FieldEntity;
use Evo\Interfaces\ViewField;
use Evo\File as FS;

/**
 * Description of regexp
 *
 * @author frolov
 */
class File extends Rule
{
    public $folder = '';
    public $path = '';
    public $size = 100000; // in KB
    public $extension = [];
    public $types = [];
    public $realPath = '';

    public $multiple = false;


    public function __construct(FieldEntity $field, array $params=null)
    {
        parent::__construct($field, $params);

        $this->size = $this->size * 1000;

        if(!$this->folder) {
            $class = get_class($this);
            $model = $this->field->model->className();
            throw new \Exception("Folder for rule '$class' doesn't declared at field '{$this->field->name}' on model '$model'");
        }

        if(strpos($this->folder, '/') !== 0) {
            $this->folder = '/'.$this->folder;
        }

        if($this->extension && !is_array($this->extension)) {
            $this->extension = [$this->extension];
        }
        if($this->types && !is_array($this->types)) {
            $this->types = [$this->types];
        }
        $this->realPath = $this->path ? (strpos($this->path,'/') === 0 ? '':'/').$this->path.$this->folder : $this->folder;
    }

    public function output(ViewField $field)
    {
        if($value = $field->data) {
            if(is_array($value)) {
                $foundImages = [];
                foreach($value as $index => $src) {
                    if(!FS::fileExists($src) && FS::fileExists($this->realPath.'/'.$src)) {
                        $foundImages[$index] = $this->folder.'/'.$src;
                    } else {
                        $foundImages[$index] = $src;
                    }
                }
                if($foundImages) {
                    $field->data = $foundImages;
                }
            } else {
                if(!FS::fileExists($value) && FS::fileExists($this->realPath.'/'.$value)) {
                    $field->data = $this->folder.'/'.$value;
                }
            }
        }
    }

    protected function checkIsUpload($val) {
        return (
            (is_array($val) && (isset($val['tmp_name']) || (isset($val[0]) && isset($val[0]['tmp_name']))))
            ||
            ($val = Evo::app()->request->files("{$this->field->model->className()}.{$this->field->name}"))// legacy support
        );
    }

    public function input($val)
    {
        $result = $val;

        //upload
        if($this->checkIsUpload($val)) {

            $result = [];

            if(isset($val[0]) && !isset($val['tmp_name'])) {

                $result = [];

                foreach($val as $index => $item) {
                    $this->checkFileUpload($item);
                    if($this->error) {
                        $result = '0';
                    } else if($res = FS::copy($item['tmp_name'], $this->realPath.'/'.$item['name'])) {
                        $result[] = str_replace('\\', '/', $this->folder.'/'.basename($res));
                    }
                }

            } else {
                $this->checkFileUpload($val);

                if($this->error) {
                    $result = '0';
                } else if($res = FS::copy($val['tmp_name'], $this->realPath.'/'.$this->convertName($val['name']))) {
                    $result = str_replace('\\', '/', $this->folder.'/'.basename($res));
                }
            }

        }

        return $result;
    }

    public function check()
    {
        /*if(is_array($this->field->value())) {
            foreach($this->field->value() as $index => $value) {
                if(!$value) continue;
                $this->checkFile($value);
            }

        } else if($this->field->value()){
            $this->checkFile($this->field->value());
        }*/
        //$this->field->value($this->field->value());
        if(is_array($this->field->value())) {
            $this->field->value($this->checkFile($this->field->value()));
        }

    }

    public function checkFile($item)
    {
        $_item = $item;

        if($this->path && strpos($item['tmp_name'], $this->folder) === 0) {
            $_item['tmp_name'] = str_replace($this->folder, $this->realPath, $item['tmp_name']);
        }

        if(!FS::fileExists($_item['tmp_name'])) {
            $this->makeError($item['name'], 'file.no_file');
        }

        return $this->copy($_item['tmp_name'], $item['name']);
    }

    public function checkFileUpload(array $item)
    {
        if($item['error'] !== UPLOAD_ERR_OK) {
            switch($item['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $this->makeError([$item['name']], 'file.upload_max_filesize');
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $this->makeError([$item['name']], 'file.max_file_size');
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $this->makeError([$item['name']], 'file.partial');
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $this->makeError([$item['name']], 'file.no_tmp_dir');
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $this->makeError([$item['name']], 'file.cant_write');
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $this->makeError([$item['name']], 'file.system_extension');
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $this->makeError([$item['name']], 'file.no_file');
                    break;
            }
        }

        if($item['size'] > $this->size) {
            $this->makeError([$item['name']], 'file.file_size');
        }

        if($this->extension) {
            $ext = substr(strrchr($item['name'], '.'), 1);
            if(!in_array(strtolower($ext), $this->extension)) {
                $this->makeError([$item['name']], 'file.extension');
            }
        }

        if($this->types) {
            if(!in_array($item['type'], $this->types)) {
                $this->makeError([$item['name']], 'file.type');
            }
        }
    }

    public function convertName($name)
    {
        return preg_replace('/\s+/', '_', Evo::app()->lang->convertToLatin($name));
    }

    public function copy($from, $name='')
    {
        $name = $this->convertName($name ? $name : basename($from));

        if(FS::copy($from, $this->realPath.'/'.$name)) {
            return $this->folder . '/'. $name;
        }
        return null;
    }

    public function delete() {
        if($this->field->value()) {
            FS::delete($this->field->value());
        }
    }


}
