<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Evo;

use Evo;

class File 
{
    const FILE_NOT_EXISTS = -0;
    const DIRECTORY_WRITE = -1;
    const PATH_NOT_FOUND  = -2;
    const SCRIPT_UPLOAD   = -3;
    const DIR_MODE = 0755;
    const FILE_MODE = 0664;

    public $errors = [];
    
    public static function absolute($filepath)
    {
        if(is_file($filepath) || is_dir($filepath)) {
            return $filepath;
        }
        
        $path =  preg_replace('/(\/)+/', '/', Evo::getWebDir() . '/' .$filepath);

        if(is_file($path) || is_dir($path)) {
            return $path;
        }

        return false;
    }
    
    public static function getFileData($filepath)
    {
        return [
            'type' => finfo_file(finfo_open(FILEINFO_MIME_TYPE), static::absolute($filepath)),
            'size' => filesize(static::absolute($filepath)),
        ];
    }
    
    public static function getNewFileName($name, $path) 
    {
        if ($handle = opendir(static::absolute($path))) {
            
            $fileNames = [];
            
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $fileNames[] = $entry;
                }
            }
            
            if(in_array($name, $fileNames)) {
                $newName = substr($name, 0, strripos($name, '.'));
                $ext = substr($name, strripos($name, '.') + 1);
                
                $counter = 0;
                while(in_array($newName.'_'.(string)$counter.'.'.$ext, $fileNames)) {
                    $counter++;
                }
                
                return $newName.'_'.(string)$counter.'.'.$ext;
            }
            
            closedir($handle);

            return $name;
        } else {
            return static::PATH_NOT_FOUND;
        }
    }
    
    public static function removeFile($pathToFile) 
    {    
        return @unlink(static::absolute($pathToFile));
    }

    public static function write($path, $content='', $mode='a+')
    {
        $path = preg_replace('/(\/)+/', '/', Evo::getWebDir() . '/'. basename(realpath(__DIR__.'/../../')) .  '/' .$path);

        $handler = fopen($path, $mode);
        if($content) {
            fwrite($handler, $content.PHP_EOL);
        }
        fclose($handler);
    }

    public static function read($path)
    {
        $path = static::absolute($path);
        if($path) {
            return file_get_contents($path);
        }
        return false;
    }

    public static function readDir($path)
    {
        $entries = [];
        if(!static::dirExists($path)) {
            return $entries;
        }
        $path = static::absolute($path);
        if($handle = opendir($path)) {
            while (false !==($entry = readdir($handle))) {
                if ($entry !== '.' && $entry !== '..') {
                    $entries[] = $entry;
                }
            }

        }
        return $entries;
    }

    public static function fileExists($path)
    {

        $path = static::absolute($path);


        if(is_file($path)) {
            return $path;
        }
        return false;
    }

    public static function dirExists($path)
    {
        if(strpos($path, Evo::getWebDir()) !== 0) {
            $path = static::absolute($path);
        }

        if(is_dir($path)) {
            return true;
        }
        return false;
    }

    public static function equals($a, $b)
    {
        // Check if filesize is different
        if(filesize($a) !== filesize($b))
            return false;

        // Check if content is different
        $ah = fopen($a, 'rb');
        $bh = fopen($b, 'rb');

        $result = true;
        while(!feof($ah))
        {
            if(fread($ah, 8192) != fread($bh, 8192))
            {
                $result = false;
                break;
            }
        }

        fclose($ah);
        fclose($bh);

        return $result;
    }

    public static function copy($from, $where, $isUploaded=false)
    {
        if(preg_match('/\.php$/', $where)) {
            return static::SCRIPT_UPLOAD;
        }

        if(!static::fileExists($from)) {
            return static::FILE_NOT_EXISTS;
        }

        $dir = static::absolute(dirname($where));

        if(!$dir) {
            return static::PATH_NOT_FOUND;
        } else if (!is_writable($dir)) {
            return static::DIRECTORY_WRITE;
        }

        $oldFile = $dir.'/'.basename($where);

        if(static::fileExists($oldFile) && static::equals($oldFile, $from)) {
            return dirname($where) .'/'.basename($oldFile);
        }

        $filename = static::getNewFileName(basename($where), dirname($where));

        $function = $isUploaded ? 'move_uploaded_file' : 'copy';


        if($function(static::absolute($from), $dir.'/'.$filename)) {
            chmod($dir.'/'.$filename, static::FILE_MODE);
            return $dir .'/'. $filename;
        }

        return false;
    }

    public static function delete($path) {
        $path = static::absolute($path);
        if(static::fileExists($path))
            unlink($path);
    }


}
