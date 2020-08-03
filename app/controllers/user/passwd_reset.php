<?php

use PitouFW\Core\Alert;
use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Core\Request;
use PitouFW\Core\Utils;
use PitouFW\Entity\PasswdReset;
use PitouFW\Model\UserModel;

UserModel::rejectUsers();

$token = Request::get()->getArg(2);
if (!UserModel::isPasswdResetTokenValid($token)) {
    Controller::http404NotFound();
}

$passwd_reset = PasswdReset::readBy('token', $token);
$user = $passwd_reset->getUser();

if (POST) {
    if (!empty($_POST['pass1']) && !empty($_POST['pass2'])) {
        if (UserModel::validatePassword($_POST['pass1'])) {
            if ($_POST['pass1'] === $_POST['pass2']) {
                $user->setPasswd(UserModel::hashPassword($_POST['pass1']))
                    ->save();
                $passwd_reset->setUsedAt(Utils::datetime())
                    ->save();
                Alert::success(L::passwd_reset_success);
                header('location: ' . WEBROOT . 'user/login');
                die;
            } else {
                Alert::error(L::register_errors_identical);
            }
        } else {
            Alert::error(L::register_errors_invalid_passwd);
        }
    } else {
        Alert::error(L::errors_form_empty);
    }
}

Data::get()->add('TITLE', L::passwd_reset_title);
Controller::renderView('user/passwd/reset');
