<?php
/**
 * Created by PhpStorm.
 * User: peter_000
 * Date: 29/08/2016
 * Time: 11:15
 */

namespace PitouFW\Core;

use JetBrains\PhpStorm\NoReturn;

require_once ROOT . 'routes.php';

class Router {
    private static ?Router $instance = null;
    public static array $controllers = ROUTES;

    private string $controller;

    private function __construct() {
       $this->controller = $this->getControllerName(0, '', self::$controllers);
    }

    /**
     * @param int $depth
     * @param string $path
     * @param array|null $sub_controllers
     * @return string
     */
    private function getControllerName(int $depth = 0, string $path = '', ?array $sub_controllers = null): string {
        if ($sub_controllers === null || $depth >= 20) {
            return false;
        }

        $current_path = Request::get()->getArg($depth);
        $current_path = $current_path !== '' ? $current_path : 'home';
        if ($current_path !== '' && array_key_exists($current_path, $sub_controllers)) {
            if (is_array($sub_controllers[$current_path])) {
                return $this->getControllerName($depth + 1, $path . $current_path . '/', $sub_controllers[$current_path]);
            }

            return $path . $sub_controllers[$current_path];
        }

        Controller::http404NotFound();
        die;
    }

    /**
     * @return Router
     */
    public static function get(): Router {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return string
     */
    public function getPathToRequire(): string {
        return CONTROLLERS.$this->controller.'.php';
    }

    /**
     * @param string $relative_path
     */
    #[NoReturn]
    public static function redirect(string $relative_path = ''): void {
        header('location: ' . webroot() . $relative_path);
        die;
    }
}
