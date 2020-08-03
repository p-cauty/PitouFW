<?php

use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Core\Request;
use PitouFW\Entity\User;
use PitouFW\Model\UserModel;

$uid = Request::get()->getArg(2);
if (!User::exists('id', $uid)) {
    Controller::http404NotFound();
}

$success = false;
$user = User::read($uid);
if (!$user->isActive()) {
    UserModel::startAccountValidation($user);
    $success = true;
} elseif (UserModel::isAwaitingEmailConfirmation($user)) {
    UserModel::startNewMailValidation($user);
    $success = true;
}

Data::get()->add('TITLE', L::resend_title);
Data::get()->add('success', $success);
Controller::renderView('user/resend/resend');
