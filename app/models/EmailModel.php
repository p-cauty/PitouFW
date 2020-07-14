<?php


namespace PitouFW\Model;


class EmailModel {
    public static function hashId($id) {
        return sha1($id . EMAIL_RENDERING_KEY);
    }
}