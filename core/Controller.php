<?php

namespace PitouFW\Core;

abstract class Controller {
    public static function __callStatic(string $name, string $arguments): void {
        if (substr($name, 0, 4) == 'http') {
            $errCode = substr($name, 4, 3);
            $errMsg = preg_replace("#([A-Z])#", " $1", substr($name, 7));
            header("HTTP/1.1 $errCode$errMsg");
            if (Request::get()->getArg(0) != 'api' && file_exists(VIEWS . 'error/' . $errCode . '.php')) {
                self::renderView('error/' . $errCode);
                die;
            }
        }
    }

    public static function renderView(string $path, bool $layout = true): void {
        $file = VIEWS.$path.'.php';
        if (file_exists($file) ) {
            $appView = $file;
            $data = Request::get()->getArg(0) !== 'api' ? Utils::secure(Data::get()->getData()) : Data::get()->getData();
            extract($data);
            if ($layout) {
                require_once VIEWS . 'mainView.php';
            }
            else {
                require_once $appView;
            }
        }
        else {
            self::http500InternalServerError();
        }
    }

    public static function renderApiError(string $message): void {
        Logger::logError($message);
        Data::get()->add('status', 'error');
        Data::get()->add('message', $message);
        Controller::renderView('json/json', false);
        die;
    }

    public static function renderApiSuccess(): void {
        Data::get()->setData(array_merge(['status' => 'success'], Data::get()->getData()));
    }

    public static function sendNoCacheHeaders(): void {
        header('Cache-Control: no-store');
        header('Pragma: no-cache');
    }
}