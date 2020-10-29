<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Evo;


use Evo;

class Response
{
    /**
     *
     * TODO: code list and contentType list
     * TODO: merge with locator
     * 
     */
    
    private $code = 200;
    private $contentType = 'text/html';


    public function response()
    {
        if(!headers_sent()) {
            header('Content-type: ' . Evo::app()->response->contentType);
            http_response_code(Evo::app()->response->code);
        }
    }

    public function __construct()
    {
        Evo::app()->on('beforeDraw', function() {
            static::response();
        });

    }

    public function code($value=null)
    {
        if($value) {
            $this->code = $value;
        }
        return $this->code;
    }
    
    public function contentType($value=null)
    {
        if($value) {
            $this->contentType = $value;
        }
        return $this->contentType;
    }

    /**
     * @param $file
     * @param null $title
     * @throws \Exception
     */
    public function attachment($file, $title = null)
    {
        if(!Evo::app()->request->ajax) {
            if (!file_exists($file))
                throw new \Exception("File '$file' doesn't exists");

            $title = $title ? $title : basename($file);


            header('Content-Type: ' . mime_content_type($file));

            header('Content-Length: ' . filesize($file));
            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename="' . $title . '"');
            header('Content-Transfer-Encoding: binary');

            header('Expires: 0');

            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            readfile($file);

            Evo::app()->request->saveSession();
            exit;
        }
    }
}
