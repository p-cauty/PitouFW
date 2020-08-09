<?php
/**
 * Copy this file to "host.dist.php"
 */

const APP_URL = 'http://localhost/PitouFW/public/';
const PROD_HOST = 'localhost';
const JET_LAG = 0;

const LOGGING = true;

const INTERNAL_API_KEY = 'xxxxx';
const UNSUBSCRIBE_SALT = 'xxxxx';

const DB_HOST = 'localhost';
const DB_NAME = 'pitoufw';
const DB_USER = 'peter';
const DB_PASS = 'secret';

const REDIS_HOST = '127.0.0.1';
const REDIS_PORT = 6379;
const REDIS_PASS = 'secret';

const SMTP_HOST = 'mail.domain.com';
const SMTP_PORT = 587;
const SMTP_SECURE = true;
const SMTP_USER = 'phpmailer@domain.com';
const SMTP_PASS = '';

const EMAIL_SEND_AS_DEFAULT = NAME . ' <hello@' . PROD_HOST . '>';
const EMAIL_CONTACT = 'contact@' . PROD_HOST;
const EMAIL_RENDERING_KEY = 'xxxxx';

const JAM_APP_ID = 'xxxxx';
const JAM_SECRET = 'xxxxx';

const DEPLOYED_COMMIT = "NA";
const DEPLOYED_REF = "NA";
const ENV_NAME = "dev";
