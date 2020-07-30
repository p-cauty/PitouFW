<?php

use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Core\Redis;
use PitouFW\Core\Request;
use PitouFW\Entity\EmailUpdate;
use PitouFW\Entity\User;
use PitouFW\Model\UserModel;

$token = Request::get()->getArg(1);

$redis = new Redis();
$cache_key = UserModel::ACCOUNT_VALIDATION_CACHE_PREFIX . $token;
$cached = $redis->get($cache_key);

$success = false;
if ($cached !== false) {
    if (User::exists('id', $cached)) {
        $user = User::read($cached);
        $user->setActivatedAt(date('Y-m-d H:i:s'))
            ->save();
        $redis->del($cache_key);
        $success = true;
    }
} elseif (EmailUpdate::exists('confirm_token', $token)) {
    $email_update = EmailUpdate::readBy('confirm_token', $token);
    if ($email_update->getConfirmedAt() === null) {
        $email_update->setConfirmedAt(date('Y-m-d H:i:s'))
            ->save();
    }
}

Data::get()->add('success', $success);
Controller::renderView('confirm/confirm');
