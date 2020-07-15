<?php


namespace PitouFW\Model;


use JustAuthMe\SDK\JamSdk;

class JustAuthMeFactory {
    public static function getSdk($callback = JAM_CALLBACK_DEFAULT): JamSdk {
        return new JamSdk(
            JAM_APP_ID,
            $callback,
            JAM_SECRET
        );
    }
}