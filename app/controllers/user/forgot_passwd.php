<?php

use PitouFW\Core\Alert;
use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Core\Mailer;
use PitouFW\Core\Utils;
use PitouFW\Entity\PasswdReset;
use PitouFW\Entity\User;
use PitouFW\Model\UserModel;
use function PitouFW\Core\t;

UserModel::rejectUsers();

if (POST) {
    if (!empty($_POST['email'])) {
        if (User::exists('email', $_POST['email'])) {
            $user = User::readBy('email', $_POST['email']);

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
        }

        Alert::success(L::forgot_passwd_success(NAME));
    }
}

Data::get()->add('TITLE', L::forgot_passwd_title);
Controller::renderView('user/passwd/forgot');