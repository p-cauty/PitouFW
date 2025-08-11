<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 18/11/2018
 * Time: 15:12
 */

const ROUTES = [
    'home' => 'home',
    'cron' => 'cron',
    'user' => [
        'register' => 'register',
        'login' => 'login',
        'logout' => 'logout',
        'forgot-passwd' => 'forgot_passwd',
        'passwd-reset' => 'passwd_reset',
        'unsubscribe' => 'unsubscribe',
        'profile' => 'profile',
        'confirm' => 'confirm',
        'resend' => 'resend'
    ],
    'api' => [
        'mailer' => 'mailer',
        'version' => 'version'
    ]
];