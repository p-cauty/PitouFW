<?php

use PitouFW\Core\Controller;
use PitouFW\Core\Mailer;
use PitouFW\Core\Request;
use PitouFW\Core\Router;
use PitouFW\Core\Translator;

session_start();

define('ROOT', str_replace('public/index.php', '', $_SERVER['SCRIPT_FILENAME']));
require_once ROOT . 'vendor/autoload.php';
require_once ROOT . 'config/config.php';

$int = PROD_ENV ? 0 : 1;
ini_set('display_errors', $int);
ini_set('display_startup_errors', $int);
error_reporting(PROD_ENV ? E_ALL^E_DEPRECATED : E_ALL);
date_default_timezone_set(TIMEZONE);

if (Request::get()->getArg(0) == 'api' && empty($_POST)) {
    if ($json_data = json_decode(file_get_contents('php://input'), true)) {
        $_POST = $json_data;
    }
}

Translator::init();

require_once Router::get()->getPathToRequire();
