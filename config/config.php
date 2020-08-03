<?php

if (file_exists(__DIR__ . '/host.dist.php')) {
    require_once __DIR__ . '/host.dist.php';
} else {
    require_once __DIR__ . '/host.dev.php';
}

require_once __DIR__ . '/app.php';

const PROD_ENV = ENV_NAME === 'prod';

define('POST', $_SERVER['REQUEST_METHOD'] == 'POST');
define('WEBROOT', str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));

const JAM_CALLBACK_DEFAULT = APP_URL . 'user/jam';
const ENTITIES = ROOT . 'entities/';
const CORE = ROOT . 'core/';
const APP = ROOT . 'app/';
const MODELS = APP . 'models/';
const VIEWS = APP . 'views/';
const CONTROLLERS = APP . 'controllers/';
const ASSETS = WEBROOT . 'assets/';
const CSS = ASSETS . 'css/';
const JS = ASSETS . 'js/';
const FONTS = ASSETS . 'fonts/';
const IMG = ASSETS . 'img/';
const VENDORS = ASSETS . 'vendors/';

const RELEASE_NAME = DEPLOYED_REF . '-' . DEPLOYED_COMMIT;
