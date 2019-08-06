<?php
session_start();
require_once 'config.dist.php';

$bool = PROD_ENV ? 0 : 1;
$econst = PROD_ENV ? 0 : E_ALL;
ini_set('display_errors', $bool);
ini_set('display_startup_errors', $bool);
error_reporting($econst ^ E_DEPRECATED);
date_default_timezone_set('UTC');

define('NAME', 'PitouFW');
define('POST', $_SERVER['REQUEST_METHOD'] == 'POST');
define('ROOT', str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']), true);
define('WEBROOT', str_replace('index.php', '', $_SERVER['SCRIPT_NAME']), true);
define('ENTITIES', ROOT.'entities/');
define('CORE', ROOT.'core/');
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
        $last = count($split) - 1;
        $classname = $split[$last];
        unset($split[$last]);
        $namespace = implode('\\', $split);
    }

    $path = ROOT;
    if ($namespace == 'PitouFW\Model' && file_exists(MODELS.$classname.$ext)) {
        $path .= 'app/models/';
    } elseif ($namespace == 'PitouFW\Entity' && file_exists(ENTITIES.$classname.$ext)) {
        $path .= 'entities/';
    } elseif ($namespace == 'PitouFW\Core' && file_exists(CORE.$classname.$ext)) {
        $path .= 'core/';
    }

    if ($path != ROOT) {
        require_once $path . $classname . $ext;
    }
});

if (\PitouFW\Core\Request::get()->getArg(0) == 'api' && empty($_POST)) {
    if ($json_data = json_decode(file_get_contents('php://input'), true)) {
        $_POST = $json_data;
    }
}

require_once \PitouFW\Core\Router::get()->getPathToRequire();
if (\PitouFW\Core\Request::get()->getArg(0) == 'api') {
    \PitouFW\Core\Controller::renderView('json/json', false);
}