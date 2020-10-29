<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 03.02.16
 * Time: 13:27
 */

namespace Evo\Helper\Controller;


use Evo;

/**
 * Class Permission
 *
 * Может принимать уже сформированные относительные маршруты
 * Выбрасывает исключение, если находит абсолютный маршрут
 *
 * @package Evo\Helper\Controller
 */

final class Permission
{
    private $accessMap = [];

    public function __construct(array $accessMap, $default = false)
    {
        if(!isset($accessMap['*'])) {
            $this->accessMap['*'] = [
                '*' => $default
            ];
        }
        foreach ($accessMap as $user => $config) {
            $this->accessMap[$user] = [];
            if(is_bool($config)) {
                $this->accessMap[$user] = [
                    '*' => $config
                ];
            } else {
                foreach($config as $ctrl => $conf) {
                    if(is_bool($conf)) {
                        $conf = ['*' => $conf];
                    }
                    foreach($conf as $action => $perm) {
                        if(preg_match('/\s*,\s*/', $action)) {
                            $actions = preg_split('/\s*,\s*/', $action);
                            unset($conf[$action]);
                            foreach($actions as $action) {
                                $conf[$action] = $perm;
                            }
                        }
                    }
                    if(preg_match('/\s*,\s*/', $ctrl)) {
                        $ctrls = preg_split('/\s*,\s*/', $ctrl);
                        foreach ($ctrls as $ctrl) {
                            $this->accessMap[$user][$ctrl] = $conf;
                        }
                    } else {
                        $this->accessMap[$user][$ctrl] = $conf;
                    }
                }
            }
        }
    }

    public function resolve(Evo\Interfaces\AuthModel $model = null, $route = '')
    {
        $role = $model ? $model->getUserRole() : '*';

        $route = preg_replace('/^(\/*)/', '', $route);

        if(strpos($route, Evo::app()->module->getUrl()) === 0) {
            $route = substr($route, strlen(Evo::app()->module->getUrl())+1);
        }
        if(strpos($route, '/')) {
            list($ctrl, $action) = explode('/', preg_replace('/[\?#]{1}.+$/', '', $route));
        } else {
            $ctrl = $route;
            $action = '*';
        }

        $default = $this->accessMap['*'];
        $scope = isset($this->accessMap[$role]) ? $this->accessMap[$role]: $this->accessMap['*'];

        if(is_bool($scope)) {
            return $scope;
        } else if(is_callable($scope)) {
            return $scope($model);
        } else if(is_array($scope)) {
            $default = isset($scope['*']) ? $scope['*'] : $default;
            $scope = isset($scope[$ctrl]) ? $scope[$ctrl] : $default;
            if(is_bool($scope)) {
                return $scope;
            } else if(is_callable($scope)) {
                return $scope($model);
            } else if(is_array($scope)) {
                $default = isset($scope['*']) ? $scope['*'] : $default;
                $scope = isset($scope[$action]) ? $scope[$action] : $default;
                if(is_bool($scope)) {
                    return $scope;
                } else if(is_callable($scope)) {
                    return $scope($model);
                } else {
                    return $default;
                }
            } else {
                return $default;
            }
        } else {
            return $default;
        }

    }
}