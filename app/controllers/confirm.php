<?php

use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Core\Redis;
use PitouFW\Core\Request;
use PitouFW\Entity\User;
use PitouFW\Model\UserModel;

$redis = new Redis();
$cache_key = UserModel::ACCOUNT_VALIDATION_CACHE_PREFIX . Request::get()->getArg(1);
$cached = $redis->get($cache_key);

$success = false;
if ($cached !== false) {
    if (User::exists('id', $cached)) {
        $user = User::readBy($cached);
        $user->setActivatedAt(date('Y-m-d H:i:s'))
            ->save();
        $success = true;
    }
}

Data::get()->add('success', $success);
Controller::renderView('confirm/confirm');
