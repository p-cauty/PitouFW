<?php

use PitouFW\Core\Alert;
use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Entity\User;
use PitouFW\Model\UserModel;

if (UserModel::isLogged()) {
    header('location: ' . WEBROOT);
    die;
}

if (POST) {
    if (!empty($_POST['email']) && !empty($_POST['pass'])) {
        if (User::exists('email', $_POST['email'])) {
            $user = User::readBy('email', $_POST['email']);

            if (UserModel::checkPassword($_POST['pass'], $user->getPasswd())) {
                UserModel::login($user);

                Alert::success(L::login_success);
                header('location: ' . WEBROOT);
                die;
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
Controller::renderView('login/form');
