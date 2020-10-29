<?php

namespace Evo;

use Evo;
use Evo\Helper\Controller\ArrayFileCreator;


class Lang
{
    const TODO = '!TODO!';
    /** @var string  */
    protected $language = '';
    /** @var Storage  */
    protected $storage = null;
    /** @var Storage  */
    protected $todo = null;
    /** @var array  */
    protected $langs = [];
    /** @var array  */
    protected $dictionaries = [];

    public function __construct(string $lang, string $path)
    {
        $this->language = $lang;

        $this->langs = Evo::getConfig('lang', '');

        $this->storage = new Storage();
        $this->todo = new Storage();

        $this->dictionaries[] = $path;
        $this->storage->set('', is_file($this->dictionaries[0]) ? require $this->dictionaries[0] : []);

        if(defined('DEBUG') && DEBUG) {
            Evo::app()->on('finish', function(){
                if($data = $this->todo->get()) {
                    /*\Util::sendEmail('valentinefrolov@gmail.com', 'dict rewrite', print_r(\Evo::app()->request->get(), true).print_r($data, true), 'text/plain', 'valentinefrolov@gmail.com');*/
                    $start = count($this->dictionaries) > 1 ? 1 : 0;
                    // rewrite all but root dict
                    for($i = $start; $i < count($this->dictionaries); $i++) {
                        $storage = new Storage();
                        $storage->set('', require $this->dictionaries[$i]);
                        $this->setTodoRecursive($data, '', $storage);
                        ArrayFileCreator::write($this->dictionaries[$i], $storage->get());
                    }
                }
            });
        }
    }

    public function getLangs() {
        return $this->langs;
    }

    /**
     * @param $name
     * @throws Exception\BehaviourException
     */
    public function changeLang($name)
    {
        if(!in_array($name, $this->langs)) {
            throw new Evo\Exception\BehaviourException("Language $name is not registered");
        }
        $this->language = $name;

        $path = ($p = Evo::app()->module->getPath()) ? $p.'/' : $p;

        $this->dictionaries = [Evo::getSourceDir().'/' . $path . 'dict/'.strtolower($this->language).'.php'];

        $this->storage->delete();
        $this->storage->set('', require $this->dictionaries[0]);

    }

    protected function setTodoRecursive($data, $parent, Storage $storage) {
        foreach($data as $key => $value) {
            if(is_array($value)) {
                $this->setTodoRecursive($value, $parent ? $parent . '.'. $key: $key, $storage);
            } else {
                $_key = $parent ? $parent . '.'. $key : $key;
                $_val = $storage->get($_key);
                if(!$_val) {
                    $storage->set($_key, $value);
                }
            }
        }
    }

    protected function setDictRecursive($data, $parent = '') {
        $this->storage;
        foreach($data as $key => $value) {
            if(is_array($value)) {
                $this->setDictRecursive($value, $parent ? $parent . '.'. $key: $key);
            } else if($value && $value != static::TODO) {
                $this->storage->set($parent ? $parent . '.'. $key : $key, $value);
            }
        }
    }

    /**
     * @param string $path
     * @param string $entry
     */
    public function setDict(string $path, string $entry = '')
    {
        $this->dictionaries[] = $path;
        $this->setDictRecursive(require $path, $entry);
    }

    public function convert($string, $input = 'cyrillic', $output = 'latin')
    {
        $from = $this->storage->get($input);
        $to = $this->storage->get($output);

        return str_replace($from, $to, $string);
    }

    public function convertToLatin($string, $entry = 'cyrillic')
    {    
        return $this->convert($string, $entry, 'latin');
    }

    /**
     * @param $entry
     * @return array|mixed|null|string
     */
    public function t($entry)
    {
        $found = $this->storage->get($entry);

        if(is_string($found) && $count = preg_match_all('/{([^?]*)\?([^}]*)}/', $found)) {
            $args = func_get_args();
            for($i=1; $i<=$count; $i++) {
                $found = preg_replace('/{([^?]*)\?([^}]*)}/', !empty($args[$i]) ? '${1}'.$args[$i].'${2}' : '', $found,1);
            }
        }
        if((defined('DEBUG') && DEBUG) && (!$found || $found == static::TODO) && !$this->todo->get($entry)) {
            $this->todo->set($entry, static::TODO);
        }
        return !$found || $found == static::TODO ? $entry : $found;
    }

    public function exists($entry) {
        $found = $this->storage->get($entry);
        return !!$found && $found != static::TODO;
    }

    public function getId()
    {
        return ($id = array_search(strtolower($this->language), $this->langs, true)) !== null? $id : 1;
    }

    // TODO: locale may look as 'ru' | 'RU' | 'ru_RU' | 'ru_KZ' etc
    public function getLocale($template = null)
    {
        if($template == 'f' || $template == 'full') {
            return strtolower($this->language).'_'.strtoupper($this->language);
        }

        return $this->language;
    }

}