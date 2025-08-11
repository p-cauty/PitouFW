<?php

define('ROOT', str_replace('config', '', __DIR__));

require_once __DIR__ . '/app.php';

$env_file = __DIR__ . '/../.env';
if (!file_exists($env_file)) {
    echo 'Please create a .env file in the root directory. You can use the .env.example file as a template.';
    die;
}

$envs = parse_ini_file($env_file);

foreach ($envs as $key => $value) {
    if (str_starts_with($key, '#')) {
        continue;
    }

    if (!defined($key)) {
        if (json_decode($value) !== null) {
            define($key, json_decode($value, true));
        } else {
            define($key, $value);
        }
    }
}

const PRODUCTION_ENV = ENV_NAME === 'prod' || ENV_NAME === 'production';
const STAGING_ENV = ENV_NAME === 'preprod' || ENV_NAME === 'staging';
const DEV_ENV = ENV_NAME === 'dev' || ENV_NAME === 'local';

define('POST', isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST');
define('WEBROOT', isset($_SERVER['SCRIPT_NAME']) ? str_replace('index.php', '', $_SERVER['SCRIPT_NAME']) : '');

const APP = ROOT . 'app/';
const ENTITIES = APP . 'entities/';
const FLOWS = APP . 'flows/';
const MODELS = APP . 'models/';
const VIEWS = APP . 'views/';
const CONTROLLERS = APP . 'controllers/';
const CORE = ROOT . 'core/';
const STORAGE = ROOT . 'storage/';
const ASSETS = WEBROOT . 'assets/';
const CSS = ASSETS . 'css/';
const JS = ASSETS . 'js/';
const FONTS = ASSETS . 'fonts/';
const IMG = ASSETS . 'img/';
const VENDORS = ASSETS . 'vendors/';
