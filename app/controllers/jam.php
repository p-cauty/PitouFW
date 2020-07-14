<?php

use JustAuthMe\SDK\JamSdk;
use PitouFW\Entity\User;
use PitouFW\Model\UserModel;

$jamSdk = new JamSdk(
    JAM_APP_ID,
    JAM_CALLBACK_DEFAULT,
    JAM_SECRET
);

if (isset($_GET['access_token'])) {
    try {
        $response = $jamSdk->getUserInfos($_GET['access_token']);
        if (isset($response->jam_id)) {
            if (User::exists('jam_id', $response->jam_id)) {
                // Connexion
                $user = User::readBy('jam_id', $response->jam_id);
                UserModel::login($user);
            } else {
                if (isset($response->email)) {
                    if (User::exists('email', $response->email)) {
                        // Liaison de compte
                        $user = User::readBy('email', $response->email);
                        $user->setJamId($response->jam_id);
                        $user->save();
                    } else {
                        // Inscription
                        $user = new User();
                        $user->setEmail($response->email)
                            ->setJamId($response->jam_id);
                        $user->save();
                    }

                    UserModel::login($user);
                } else {
                    // L'utilisateur doit supprimer le service de son app et recommencer
                }
            }
        } else {
            // Erreur inconnue au niveau de JustAuthMe
        }
    } catch (\JustAuthMe\SDK\Exceptions\JamBadRequestException $e) {
    } catch (\JustAuthMe\SDK\Exceptions\JamInternalServerErrorException $e) {
    } catch (\JustAuthMe\SDK\Exceptions\JamNotFoundException $e) {
    } catch (\JustAuthMe\SDK\Exceptions\JamUnauthorizedException $e) {
    } catch (\JustAuthMe\SDK\Exceptions\JamUnknowErrorException $e) {
    }
}