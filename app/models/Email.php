<?php


namespace PitouFW\Model;


class Email {
    public static function hashId($id) {
        return sha1($id . EMAIL_RENDERING_KEY);
    }
}