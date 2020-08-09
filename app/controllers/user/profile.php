<?php

use PitouFW\Core\Alert;
use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Core\Redis;
use PitouFW\Entity\User;
use PitouFW\Model\UserModel;

UserModel::rejectGuests();

$user = UserModel::get();

if (POST) {
    if (!empty($_POST['email'])) {
        if (filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
            if ($user->getEmail() === $_POST['email'] || !User::exists('email', $_POST['email'])) {
                Alert::success(L::profile_success_default);

                $redis = new Redis();
                $cache_key = UserModel::UPDATE_EMAIL_COOLDOWN_PREFIX . $user->getId();
                $update_attempts = (int) $redis->get($cache_key);
                $must_wait = $update_attempts >= UserModel::UPDATE_EMAIL_COOLDOWN_ATTEMPTS;
                $ttl = (int) $redis->ttl($cache_key);
                $ttl = $ttl > 0 ? $ttl : UserModel::UPDATE_EMAIL_COOLDOWN_TTL;

                if (!$must_wait) {
                    if (TRUST_NEEDED && $user->getEmail() !== $_POST['email']) {
                            UserModel::startNewMailValidation($user);
                            Alert::success(L::profile_success_with_email);
                    }

                    $user->setEmail($_POST['email']);
                    $update_attempts++;
                    $redis->set($cache_key, $update_attempts, $ttl);
                } else {
                    Alert::warning(L::profile_warning);
                }

                if (!empty($_POST['pass1'])) {
                    if (UserModel::validatePassword($_POST['pass1'])) {
                        if ($_POST['pass1'] === $_POST['pass2']) {
                            $user->setPasswd(UserModel::hashPassword($_POST['pass1']));
                        } else {
                            Alert::warning(L::register_errors_identical);
                        }
                    } else {
                        Alert::warning(L::register_errors_invalid_passwd);
                    }
                }
                $user->save();
            } else {
                Alert::error(L::register_errors_email_exists);
            }
        } else {
            Alert::error(L::register_errors_invalid_email);
        }
    } else {
        Alert::error(L::errors_form_empty);
    }
}

Data::get()->add('TITLE', L::profile_title);
Data::get()->add('user', $user);
Controller::renderView('user/profile/form');
