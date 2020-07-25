<?php

use PitouFW\Core\Alert;
use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Entity\User;
use PitouFW\Model\UserModel;

UserModel::rejectGuests();

$user = UserModel::get();

if (POST) {
    if (!empty($_POST['email'])) {
        if (filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
            if ($user->getEmail() === $_POST['email'] || !User::exists('email', $_POST['email'])) {
                $user->setEmail($_POST['email']);
                Alert::success(L::profile_success);

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
Controller::renderView('profile/form');
