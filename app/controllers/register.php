<?php

use PitouFW\Core\Alert;
use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Entity\User;
use PitouFW\Model\UserModel;

UserModel::rejectUsers();

if (POST) {
    if (!empty($_POST['email']) && !empty($_POST['pass1']) && !empty($_POST['pass2'])) {
        if (filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
            if (!User::exists('email', $_POST['email'])) {
                if (UserModel::validatePassword($_POST['pass1'])) {
                    if ($_POST['pass1'] === $_POST['pass2']) {
                        $user = new User();
                        $user->setEmail($_POST['email'])
                            ->setPasswd(UserModel::hashPassword($_POST['pass1']));
                        $uid = $user->save();
                        $user->setId($uid);

                        UserModel::login($user);

                        Alert::success(L::register_success(NAME));
                        header('location: ' . WEBROOT);
                        die;
                    } else {
                        Alert::error(L::register_errors_identical);
                    }
                } else {
                    Alert::error(L::register_errors_invalid_passwd);
                }
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

Data::get()->add('TITLE', L::register_title);
Controller::renderView('register/form');
