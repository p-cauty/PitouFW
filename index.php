<?php
session_start();
require_once 'config.dist.php';

$bool = PROD_ENV ? 0 : 1;
$econst = PROD_ENV ? 0 : E_ALL;
ini_set('display_errors', $bool);
ini_set('display_startup_errors', $bool);
error_reporting($econst ^ E_DEPRECATED);
date_default_timezone_set('UTC');

define('NAME', 'PHPeter');
define('POST', $_SERVER['REQUEST_METHOD'] == 'POST');
define('ROOT', str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']), true);
define('WEBROOT', str_replace('index.php', '', $_SERVER['SCRIPT_NAME']), true);
define('BEANS', ROOT.'beans/');
define('SYSTEM', ROOT.'system/');
define('APP', ROOT.'app/');
define('MODELS', APP.'models/');
define('VIEWS', APP.'views/');
define('CONTROLLERS', APP.'controllers/');
define('ASSETS', WEBROOT.'assets/');
define('CSS', ASSETS.'css/');
define('JS', ASSETS.'js/');
define('FONTS', ASSETS.'fonts/');
define('IMG', ASSETS.'img/');
define('VENDORS', ASSETS.'vendors/');

spl_autoload_register(function ($classname) {
    $ext = '.php';
    $split = explode('\\', $classname);
    $namespace = '';
    if (count($split) > 1) {
        $namespace = $split[0];
        $classname = $split[1];
    }

    $path = ROOT;
    if ($namespace == 'Model' && file_exists(ROOT.'app/models/'.$classname.$ext)) {
        $path .= 'app/models/';
    } elseif ($namespace == 'Bean' & file_exists(ROOT.'beans/'.$classname.$ext)) {
        $path .= 'beans/';
    } elseif ($namespace == '' && file_exists(ROOT.'system/'.$classname.$ext)) {
        $path .= 'system/';
    }

    if ($path != ROOT) {
        require_once $path . $classname . $ext;
    }
});

if (Request::get()->getArg(0) == 'api') {
    if ($json_data = json_decode(file_get_contents('php://input'), true)) {
        $_POST = $json_data;
    }
}

require_once Router::get()->getPathToRequire();
if (Request::get()->getArg(0) == 'api') {
    Controller::renderView('json/json', false);
}