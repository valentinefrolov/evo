<?php

namespace Evo\Rule;

use Evo;
use Evo\Rule;
use Evo\Html;
use Evo\Interfaces\ViewField;



/**
 * Description of Integer
 * TODO rewrite
 * @author frolov
 */
class ReCaptcha extends Rule{

    // TODO брать ключи из настроек модуля
    private $_googleKey = '6LdF0BUTAAAAAHPd51u_uSAH5tM9Oyle-H1rxCkS';
    private $_secretKey = '6LdF0BUTAAAAADCA3Rb1hq0Gd-cpUI2LkcQdPEmS';
    private $_googleUrl = 'https://www.google.com/recaptcha/api/siteverify';

    private static $_callbackScript = [];
    private static $_rendered = false;


    protected function check()
    {
        $success = false;
        $response = Evo::app()->request->get('g-recaptcha-response');

        if($response) {
            $remoteIp = $_SERVER['REMOTE_ADDR'];
            $secret = $this->_secretKey;
            $cUrl = curl_init($this->_googleUrl);
            curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($cUrl, CURLOPT_POST, 1);
            curl_setopt($cUrl, CURLOPT_POSTFIELDS, "secret=$secret&response=$response&remoteip=$remoteIp");
            $success = json_decode(curl_exec($cUrl), true)['success'];
            curl_close($cUrl);
        }

        if(!$success) {
            $this->makeError();
        }
    }

    // TODO old view fields
    public function output(ViewField $field)
    {
        $id = $field->inputAttr['id'];

        $field->inputAttr['class'] = 'g-recaptcha';
        $field->inputAttr['data-sitekey'] = $this->_googleKey;

        $googleKey = $this->_googleKey;
        self::$_callbackScript[] = "grecaptcha.render('$id', {'sitekey' : '$googleKey'})";

        Evo::app()->view->on('beforeDraw', function(){
            self::_drawCallbackScript();
        });
    }

    private static function _drawCallbackScript()
    {
        if(!static::$_rendered) {
            Evo::app()->view->setAsset(Html::script('', ['type' => 'text/javascript', 'src' => 'https://www.google.com/recaptcha/api.js?onload=reCaptchaCallback&amp;render=explicit', 'async', 'defer']));
            $script = "var reCaptchaCallback = function(){" . PHP_EOL;
            $script .= implode(";" . PHP_EOL, self::$_callbackScript);
            $script .= PHP_EOL . "}" . PHP_EOL;
            Evo::app()->view->setAsset(Html::script($script, ['type' => 'text/javascript']));
            static::$_rendered = true;
        }
    }

}

