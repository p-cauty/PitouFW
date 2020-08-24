<?php

use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Core\Redis;
use PitouFW\Core\Request;
use PitouFW\Core\Utils;
use PitouFW\Entity\EmailUpdate;
use PitouFW\Entity\User;
use PitouFW\Model\UserModel;

$token = Request::get()->getArg(2);

$redis = new Redis();
$cache_key = UserModel::ACCOUNT_VALIDATION_CACHE_PREFIX . $token;
$cached = $redis->get($cache_key);
// TODO : pouvoir redemander un email de confirmation
$success = false;
if ($cached !== false) {
    if (User::exists('id', $cached)) {
        $user = User::read($cached);
        $user->setActivatedAt(Utils::datetime())
            ->save();
        $redis->del($cache_key);
        $success = true;
    }
} elseif (EmailUpdate::exists('confirm_token', $token)) {
    $email_update = EmailUpdate::readBy('confirm_token', $token);
    $user = $email_update->getUser();
    if ($user->isAwaitingEmailConfirmation()) {
        $real_email_update = $user->getLastEmailUpdate();
        if ($email_update->getConfirmToken() === $real_email_update->getConfirmToken()) {
            $email_update->setConfirmedAt(Utils::datetime())
                ->save();
            $success = true;
        }
    }
}

Data::get()->add('TITLE', L::confirm_title);
Data::get()->add('success', $success);
Controller::renderView('user/confirm/confirm');
