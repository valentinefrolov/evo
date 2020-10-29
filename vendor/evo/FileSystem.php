<?php


namespace Evo;

use Evo\Exception\FileNotFoundException;

class FileSystem
{
    const TYPE_DIRECTORY = 'DIR';
    const TYPE_FILE = 'FILE';

    /**
     * @param string $dir
     * @return array
     * @throws FileNotFoundException
     */
    public static function readDir(string $dir) : array
    {
        if ($handle = opendir($dir)) {
            $result = [];
            while (false !== ($entry = readdir($handle))) {
                if($entry != '.' && $entry !== '..') {
                    $result[] = $dir . DIRECTORY_SEPARATOR . $entry;
                }
            }
            closedir($handle);
            return $result;
        }
        throw new FileNotFoundException("Directory $dir not found");
    }

    /**
     * @param string $file
     * @return string|string[]
     * @throws FileNotFoundException
     */
    public static function getFileInfo(string $file)
    {
        if(is_dir($file)) {
            $info = pathinfo($file);
            $info['type'] = self::TYPE_DIRECTORY;
            return $info;
        } else if(is_file($file)) {
            $info = pathinfo($file);
            $info['type'] = self::TYPE_FILE;
            return $info;
        }
        throw new FileNotFoundException("Path $file not found");
    }

}