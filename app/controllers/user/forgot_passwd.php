<?php

use PitouFW\Core\Alert;
use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Core\Mailer;
use PitouFW\Core\Redis;
use PitouFW\Core\Utils;
use PitouFW\Entity\PasswdReset;
use PitouFW\Entity\User;
use PitouFW\Model\UserModel;
use function PitouFW\Core\t;

UserModel::rejectUsers();

$redis = new Redis();
$slugified_ip = Utils::parseIpForAntispam($_SERVER['REMOTE_ADDR']);
$ip_cache_key = UserModel::FORGOT_PASSWD_IP_COOLDOWN_PREFIX . $slugified_ip;
$ip_attempts = (int) $redis->get($ip_cache_key);
$ttl = (int) $redis->ttl($ip_cache_key);
$ttl = $ttl > 0 ? $ttl : UserModel::FORGOT_PASSWD_IP_COOLDOWN_TTL;

if ($ip_attempts >= UserModel::FORGOT_PASSWD_IP_COOLDOWN_ATTEMPTS) {
    $redis->set($ip_cache_key, UserModel::FORGOT_PASSWD_IP_COOLDOWN_ATTEMPTS, UserModel::FORGOT_PASSWD_IP_COOLDOWN_BAN);
    Controller::http429TooManyRequests();
}

if (POST) {
    if (!empty($_POST['email'])) {
        $ip_attempts++;
        $redis->set($ip_cache_key, $ip_attempts, $ttl);
        Alert::success(L::forgot_passwd_success(NAME));

        if (User::exists('email', $_POST['email'])) {
            $user = User::readBy('email', $_POST['email']);
            $email_cache_key = UserModel::FORGOT_PASSWD_EMAIL_COOLDOWN_PREFIX . Utils::slugify($_POST['email'], '_');
            $must_wait = $redis->get($email_cache_key) !== false;

            if (!$must_wait) {
                do {
                    $token = Utils::generateToken();
                } while (PasswdReset::exists('token', $token));

                $passwd_reset = new PasswdReset();
                $passwd_reset->setToken($token)
                    ->setUserId($user->getId())
                    ->save();

                $mailer = new Mailer();
                $mailer->queueMail(
                    $user->getEmail(),
                    L::passwd_reset_email_subject,
                    'mail/' . t()->getAppliedLang() . '/passwd_reset',
                    ['token' => $token]
                );

                $redis->set($email_cache_key, 1, UserModel::FORGOT_PASSWD_EMAIL_COOLDOWN_TTL);
            } else {
                Alert::error(L::forgot_passwd_error);
            }
        }

    }
}

Data::get()->add('TITLE', L::forgot_passwd_title);
Controller::renderView('user/passwd/forgot');