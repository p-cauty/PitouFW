<?php


namespace PitouFW\Core;


class Translator {
    public static ?\i18n $instance = null;

    /**
     * @return \i18n
     */
    public static function get(): \i18n {
        if (self::$instance === null) {
            self::init();
        }

        return self::$instance;
    }

    /**
     * @return bool
     */
    public static function init(): bool {
        self::$instance = new \i18n();
        self::$instance->setCachePath(ROOT . 'cache/');
        self::$instance->setFilePath(ROOT . 'lang/{LANGUAGE}.yml');

        if (Request::get()->getLang() !== null) {
            self::$instance->setForcedLang(Request::get()->getLang());
        } else {
            self::$instance->setFallbackLang(DEFAULT_LANGUAGE);
        }

        self::$instance->setMergeFallback(true);

        try {
            self::$instance->init();
            return true;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            Controller::http500InternalServerError();
        }

        return false;
    }
}

/**
 * @return \i18n
 */
function t(): \i18n {
    return Translator::get();
}
