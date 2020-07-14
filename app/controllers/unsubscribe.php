<?php

use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Entity\NewsletterEmail;
use PitouFW\Model\UserModel;

if (!isset($_GET['email'], $_GET['key'])) {
    Controller::http404NotFound();
}

if (UserModel::hashInfo(strtolower($_GET['email']) . UNSUBSCRIBE_SALT) !== $_GET['key']) {
    Controller::http403Forbidden();
}

if (NewsletterEmail::exists('email', $_GET['email'])) {
    NewsletterEmail::deleteBy('email', $_GET['email']);
}

Data::get()->add('TITLE', L::unsubscribe_title);
Data::get()->add('email', $_GET['email']);
Controller::renderView('unsubscribe/unsubscribe');