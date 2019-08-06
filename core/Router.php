<?php
/**
 * Created by PhpStorm.
 * User: peter_000
 * Date: 29/08/2016
 * Time: 11:15
 */

namespace PitouFW\Core;

require_once ROOT . 'routes.php';

class Router {
    private static $instance = null;
    public static $controllers = ROUTES;

    private $controller = null;

    private function __construct() {
        if (self::controllerExists()) {
            if (is_array(self::$controllers[Request::get()->getArg(0)])) {
                $this->controller = Request::get()->getArg(0).'/'.self::$controllers[Request::get()->getArg(0)][Request::get()->getArg(1)];
            }
            else {
                $this->controller = self::$controllers[Request::get()->getArg(0)];
            }
        }
        else {
            Controller::http404NotFound();
            exit();
        }
    }

    public static function get() {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function controllerExists() {
        if (array_key_exists(Request::get()->getArg(0), self::$controllers)) {
            if (is_array(self::$controllers[Request::get()->getArg(0)])) {
                return array_key_exists(Request::get()->getArg(1), self::$controllers[Request::get()->getArg(0)]);
            }
            else {
                return array_key_exists(Request::get()->getArg(0), self::$controllers);
            }
        }

        return false;
    }

    public function getPathToRequire() {
        return CONTROLLERS.$this->controller.'.php';
    }
}