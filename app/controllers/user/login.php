<?php

use PitouFW\Core\Alert;
use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Entity\User;
use PitouFW\Model\UserModel;

UserModel::rejectUsers();

if (POST) {
    if (!empty($_POST['email']) && !empty($_POST['pass'])) {
        if (User::exists('email', $_POST['email'])) {
            $user = User::readBy('email', $_POST['email']);

            if (UserModel::checkPassword($_POST['pass'], $user->getPasswd())) {
                if ($user->isTrustable()) {
                    $ttl = !empty($_POST['remember']) && $_POST['remember'] === '1' ?
                        UserModel::SESSION_CACHE_TTL_LONG :
                        UserModel::SESSION_CACHE_TTL_DEFAULT;
                    $user->login($ttl);

                    Alert::success(L::login_success);
                    header('location: ' . WEBROOT);
                    die;
                } else {
                    Alert::error(L::login_inactive(WEBROOT . 'user/resend/' . $user->getId()));
                }
            } else {
                Alert::error(L::login_error);
            }
        } else {
            Alert::error(L::login_error);
        }
    } else {
        Alert::error(L::errors_form_empty);
    }
}

Data::get()->add('TITLE', L::login_title);
Controller::renderView('user/login/form');
