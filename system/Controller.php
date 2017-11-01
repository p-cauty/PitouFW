<?php
abstract class Controller {
    public static function renderView(string $path, bool $layout = true) {
        $file = VIEWS.$path.'.php';
        if (file_exists($file) ) {
            $appView = $file;
            $data = Utils::secure(Data::get()->getData());
            extract($data);
            if ($layout) {
                require_once VIEWS.'mainView.php';
            }
            else {
                require_once $appView;
            }
        }
        else {
            self::http500InternalServerError();
        }
    }

    public static function __callStatic($name, $arguments) {
        if (substr($name, 0, 4) == 'http') {
            $errCode = substr($name, 4, 3);
            $errMsg = preg_replace("#([A-Z])#", " $1", substr($name, 7));
            header("HTTP/1.1 $errCode$errMsg");
            if (Request::get()->getArg(0) != 'api' && file_exists(VIEWS . 'error/' . $errCode . '.php')) {
                self::renderView('error/' . $errCode);
            }
        }
    }
}