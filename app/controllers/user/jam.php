<?php

use JustAuthMe\SDK\JamSdk;
use PitouFW\Core\Alert;
use PitouFW\Core\Utils;
use PitouFW\Entity\User;
use PitouFW\Model\JustAuthMeFactory;
use PitouFW\Model\UserModel;

$jamSdk = JustAuthMeFactory::getSdk();

if (isset($_GET['access_token'])) {
    try {
        $response = $jamSdk->getUserInfos($_GET['access_token']);
    } catch (Exception $e) {
        Alert::error(L::jam_errors_unknown($e->getMessage()));
        header('location: ' . WEBROOT);
        die;
    }

    if (User::exists('jam_id', $response->jam_id)) {
        // Login
        $user = User::readBy('jam_id', $response->jam_id);

        Alert::success(L::login_success);
        $user->login(UserModel::SESSION_CACHE_TTL_LONG);
    } else {
        if (isset($response->email)) {
            if (User::exists('email', $response->email)) {
                // Account linking
                $user = User::readBy('email', $response->email);
                $user->setJamId($response->jam_id);
                if (!$user->isActive()) {
                    $user->setActivatedAt(Utils::datetime());
                }
                $user->save();

                if ($this->isAwaitingEmailConfirmation()) {
                    $email_update = $this->getLastEmailUpdate();
                    $email_update->setConfirmedAt(Utils::datetime())
                        ->save();
                }
            } else {
                // Registration
                $user = new User();
                $user->setEmail($response->email)
                    ->setJamId($response->jam_id)
                    ->setActivatedAt(Utils::datetime());
                $uid = $user->save();
                $user->setId($uid);
            }

            Alert::success(L::login_success);
            $user->login(UserModel::SESSION_CACHE_TTL_LONG);
        } else {
            // The user need to remove the service from their JustAuthMe app and try again
            Alert::error(L::jam_errors_no_email(NAME));
        }
    }

    header('location: ' . WEBROOT);
    die;
}
