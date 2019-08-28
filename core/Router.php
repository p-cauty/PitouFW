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
       $this->controller = $this->getControllerName(0, '', self::$controllers);
    }

    private function getControllerName($depth = 0, $path = '', $sub_controllers = null) {
        if ($sub_controllers === null || $depth >= 20) {
            return false;
        }

        $current_path = Request::get()->getArg($depth);
        if ($current_path !== '' && array_key_exists($current_path, $sub_controllers)) {
            if (is_array($sub_controllers[$current_path])) {
                return $this->getControllerName($depth + 1, $path . $current_path . '/', $sub_controllers[$current_path]);
            }

            return $path . $sub_controllers[$current_path];
        }

        Controller::http404NotFound();
        die;
    }

    public static function get() {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getPathToRequire() {
        return CONTROLLERS.$this->controller.'.php';
    }
}