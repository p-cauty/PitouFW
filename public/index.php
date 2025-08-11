<?php

use PitouFW\Core\Alert;
use PitouFW\Core\Controller;
use PitouFW\Core\Request;
use PitouFW\Core\Router;
use PitouFW\Core\Session;
use PitouFW\Core\Translator;
use PitouFW\Model\ConfigModel;
use PitouFW\Model\UserModel;
use PitouFW\Model\UserSettingModel;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

Session::start();

$bool = PRODUCTION_ENV ? 0 : 1;
ini_set('display_errors', $bool);
ini_set('display_startup_errors', $bool);
error_reporting(E_ALL ^ E_DEPRECATED);
date_default_timezone_set('Europe/Paris');

if (Request::get()->getArg(0) == 'api' && empty($_POST)) {
    if ($json_data = json_decode(file_get_contents('php://input'), true)) {
        $_POST = $json_data;
    }
}

Translator::init();

if (UserModel::isLogged() && !UserModel::get()->isActive()) {
    UserModel::logout();
    Alert::error('Votre compte a été désactivé.');
    Router::redirect('account/login');
}

$path_to_require = Router::get()->getPathToRequire();
if (file_exists($path_to_require)) {
    require_once $path_to_require;
} else {
    Controller::http500InternalServerError();
}
