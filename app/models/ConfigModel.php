<?php


namespace PitouFW\Model;


use PitouFW\Entity\Config;

class ConfigModel {
    private static array $config = [];

    public static function init(): void {
        $config = Config::fetchAll();
        self::$config = [];
        foreach ($config as $conf) {
            self::$config[$conf->getRef()] = $conf->getValue();
            settype(self::$config[$conf->getRef()], $conf->getType());
        }
    }

    public static function get(string $key): mixed {
        if (empty(self::$config)) {
            self::init();
        }

        return self::$config[$key] ?? null;
    }
}
