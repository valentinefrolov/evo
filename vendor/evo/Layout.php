<?php
/**
 * Created by Valentin Frolov valentinefrolov@gmail.com
 * For Aplex
 * Project Evo Engine Framework / Aplex Framework / Aplex CMS
 * Date: 02.03.2016, time: 22:49
 */

namespace Evo;

use Evo;
use Evo\Helper\View\Ajax;
use Evo\Exception\BehaviourException;

class Layout extends View
{
    const BUFFER_OFF = 0;
    const BUFFER_DELAY = 1;
    /* not show render results */
    const BUFFER_PRE = 2;

    protected $onBuffer = false;
    protected $preRendered = '';
    protected $asHtml = true;

    private $asset = [];
    private $libs = [];

    public function getBuffer() : bool
    {
        return $this->onBuffer < static::BUFFER_PRE;
    }

    public function __construct()
    {
        parent::__construct();

        $this->asset = [
            'js' => [],
            'html' => [],
            'css' => [],
        ];

        if(!$this->request->ajax) {
            $this->request->session('view.route', Evo::app()->request->route());
            $this->request->deleteSession('view.libs');
            $this->registerInlineScript("window.onpopstate = function(){window.location.reload();};", []);
        } else {
            $libs = $this->request->session('view.libs');
            $this->libs = is_array($libs) ? $libs : [];
        }

        $this->on('finish', function(){
            $libs = $this->libs;
            $this->request->session('view.libs', is_array($libs) ? $libs : []);
        });

        $this->onBuffer = static::BUFFER_OFF;
        $this->set('script-loader', $this->getSource('js/script-loader.js'));

    }

    /**
     * @param $asset
     * @param $id
     * @throws BehaviourException
     */
    public function addAsset($asset, $id)
    {
        if(isset($this->asset[$id])) {
            throw new BehaviourException("Asset '$id' already exists");
        }
        $this->asset[$id] = $asset;
    }


    public function registerScriptSrc(string $url, $d = [], $a = null)
    {

        if(!$a || !in_array($a, $this->libs)) {
            $key = $a ? $a : count($this->asset['js']);
            $this->asset['js'][$key] = [
                'type' => 'link',
                's' => "'$url'",
                'd' => (array)$d,
                'a' => $a
            ];
            if($a) $this->libs[] = $a;
        }
    }

    public function registerInlineScript(string $src, $d = [], $a = '', string $scope = null)
    {
        if(!$a || !in_array($a, $this->libs)) {
            $key = $a ? $a : ($scope ? $scope : count($this->asset['js']));
            if ($scope) {
                if (!empty($this->asset['js'][$scope])) {
                    if ($this->asset['js'][$scope]['type'] == 'function') {
                        $this->asset['js'][$scope]['s'] = $this->asset['js'][$scope]['s'] . PHP_EOL . $src;
                        if ($d) {
                            foreach ((array)$d as $_d) {
                                if ($_d !== $scope && !in_array($_d, $this->asset['js'][$scope]['d'])) {
                                    $this->asset['js'][$scope]['d'][] = $_d;
                                }
                            }
                        }
                    } else {
                        $d[] = $scope;
                        $this->asset['js'][$key] = [
                            'type' => 'function',
                            's' => $src,
                            'd' => (array)$d,
                            'a' => $a
                        ];
                    }
                } else {
                    $this->asset['js'][$scope] = [
                        'type' => 'function',
                        's' => $src,
                        'd' => (array)$d,
                        'a' => $a
                    ];
                }
            } else {
                $this->asset['js'][$key] = [
                    'type' => 'function',
                    's' => $src,
                    'd' => (array)$d,
                    'a' => $a
                ];
            }
            if($a) $this->libs[] = $a;
        }
    }



    public function addScript($script, $id=null, $d=[])
    {
        $d = $d ? implode(", ", (array)$d) : '*';

        $this->registerInlineScript($script, $d, $id);
    }

    public function addStyle($style, $id=null)
    {
        if($id) {
            if(!in_array($id, $this->libs)) {
                $this->asset['css'][$id] = $style;
                $this->libs[] = $id;
            }
        } else {
            $this->asset['css'][] = $style;
        }
    }

    public function addHtml($html, $id=null)
    {
        if($id) {
            if(!in_array($id, $this->libs)) {
                $this->asset['html'][$id] = $html;
                $this->libs[] = $id;
            }
        } else {
            $this->asset['html'][] = $html;
        }
    }

    public function render(array $__)
    {
        if($this->onBuffer < static::BUFFER_PRE) {
            ob_start();
            extract($__);
            unset($__);
            require_once $this->layout;
        }
        if($this->preRendered) {
            ob_end_clean();
            echo $this->preRendered;
            if($this->asHtml) {
                echo $this->drawAssets();
            }
        }
        $this->event('finish');
    }

    public function drawAssets($item=null)
    {
        $assets = [];
        if($item && isset($this->asset[$item])) {
            if($item == 'js') {
                $assets[] = '<script>'.implode(PHP_EOL, array_map(function($item){
                        $item['d'] = implode(', ', array_map(function($item){return "'$item'";}, $item['d']));
                        $return = 'window.scripts.push({s:';
                        $return .= $item['type'] == 'function' ? 'function(){'.$item['s'].'}' : $item['s'];

                        if($item['a']) $return .= ",a:'{$item['a']}'";
                        if($item['d']) $return .= ",d:[{$item['d']}]";
                        $return .= '});';
                        return $return;

                }, $this->asset[$item]))./*($this->request->ajax ? 'loadScript();' : '').*/'</script>';
            } else if(is_array($this->asset[$item])) {
                $assets[] =implode(PHP_EOL, $this->asset[$item]);
            } else {
                $assets[] =$this->asset[$item];
            }
        } else if(!$item){
            foreach ($this->asset as $item => $data) {
                $assets[] = $this->drawAssets($item);
            }
        }
        return implode(PHP_EOL, $assets);
    }

    public function ajaxBlock($id = '')
    {
        // начинаем
        if(!Ajax::$isInBlock) {
            $result = Ajax::begin($id);
            if(Ajax::$activeBlock) {
                ob_start();
            }
            return $result;
        } else {
            // закрывающий тег
            if(Ajax::$activeBlock) {

                $this->preRendered = ob_get_contents();
                ob_end_clean();
                $this->onBuffer = static::BUFFER_PRE;
            }
            return Ajax::end();
        }
    }

    public function returnAjax(string $preRendered = null, bool $asHtml = false)
    {
        if($preRendered) {
            if($asHtml) {
                $this->preRendered .= $preRendered;
            } else {
                $this->preRendered = $preRendered;
            }
        }
        $this->asHtml = $asHtml;
        $this->onBuffer = static::BUFFER_PRE;
    }


    protected function includeView($__, array $data = [])
    {
        extract($data);
        unset($data);

        if(strpos($__, '/') === 0) {
            $__ = 'view'.$__.'.php';
        } else {
            $__ = 'view/'.basename(dirname(debug_backtrace()[0]['file'])).'/'.$__.'.php';
        }
        require Evo::getSourcePath(Evo::app()->module->getPath().'/'.$__);
    }

}