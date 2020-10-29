<?php


namespace Evo\Helper\Controller;

use Evo;

class Socket
{
    protected $route = '';

    public function __construct(int $port, string $route, bool $dual = false, array $data=[])
    {
        $config = Evo::getConfig('app', '');
        $protocol = !empty($config['protocol']) && strpos($config['protocol'], 'https') !== false ? 'wss://' : 'ws://';

        $this->route = $protocol.$config['host'].'/websocket/'.$port;
        $path = Evo::getRootPath('cli.php');
        $dual = $dual ? 'dual=1' : 'dual=0';
        $query = $data ? 'data='.$this->buildArrayQuery($data) : '';
        exec("php $path task=socket $route $dual port=808$port $query  > /dev/null &");
    }

    public function getUrl()
    {
        return $this->route;
    }

    protected function buildArrayQuery(array $array)
    {
        $string = '[';
        $keys = array_keys($array);
        for($i=0;$i<count($keys);$i++) {
            $string .= $keys[$i].'=';
            if(is_array($array[$keys[$i]])) {
                $string .= $this->buildArrayQuery($array[$keys[$i]]);
            } else {
                $string .= $array[$keys[$i]];
            }
            if(!empty($keys[$i+1])) {
                $string .= ',';
            }
        }
        $string .= ']';
        return $string;
    }

}