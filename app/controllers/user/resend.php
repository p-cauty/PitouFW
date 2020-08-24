<?php

use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Core\Request;
use PitouFW\Core\Utils;
use PitouFW\Entity\User;
use PitouFW\Model\UserModel;
use PitouFW\Core\Redis;

$redis = new Redis();
$slugified_ip = Utils::parseIpForAntispam($_SERVER['REMOTE_ADDR']);
$ip_cache_key = UserModel::RESEND_EMAIL_IP_COOLDOWN_PREFIX . $slugified_ip;
$ip_attempts = (int) $redis->get($ip_cache_key);
$ttl = (int) $redis->ttl($ip_cache_key);
$ttl = $ttl > 0 ? $ttl : UserModel::RESEND_EMAIL_IP_COOLDOWN_TTL;

if ($ip_attempts >= UserModel::RESEND_EMAIL_IP_COOLDOWN_ATTEMPTS) {
    $redis->set($ip_cache_key, UserModel::RESEND_EMAIL_IP_COOLDOWN_ATTEMPTS, UserModel::RESEND_EMAIL_IP_COOLDOWN_BAN);
    Controller::http429TooManyRequests();
}

$ip_attempts++;
$redis->set($ip_cache_key, $ip_attempts, $ttl);

$uid = Request::get()->getArg(2);
if (!User::exists('id', $uid)) {
    Controller::http404NotFound();
}

$success = false;
$spam = false;
$user = User::read($uid);
$uid_cache_key = UserModel::RESEND_EMAIL_UID_COOLDOWN_PREFIX . $user->getId();
$must_wait = $redis->get($uid_cache_key) !== false;

if (!$must_wait) {
    if (!$user->isActive()) {
        $this->startAccountValidation();
        $success = true;
        $redis->set($uid_cache_key, 1, UserModel::RESEND_EMAIL_UID_COOLDOWN_TTL);
    } elseif ($this->isAwaitingEmailConfirmation()) {
        $user->startNewMailValidation();
        $success = true;
        $redis->set($uid_cache_key, 1, UserModel::RESEND_EMAIL_UID_COOLDOWN_TTL);
    }
} else {
    $spam = true;
}

Data::get()->add('TITLE', L::resend_title);
Data::get()->add('message', $spam ? L::resend_spam : ($success ? L::resend_success : L::resend_verified));
Controller::renderView('user/resend/resend');
