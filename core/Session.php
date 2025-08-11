<?php

namespace PitouFW\Core;

class Session
{
    private static ?array $flash = null;

    public static function start(): void
    {
        session_start();

        if (!empty($_POST)) {
            self::flush();
            foreach ($_POST as $key => $value) {
                if (preg_match("/pass(-|_)?(w(or)?d|key|code)?/i", $key)) {
                    continue;
                }

                $_SESSION['old'][$key] = $value;
            }
        }

        self::$flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);

        self::$flash = [...self::$flash, ...$_SESSION];
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['flash'][$key] = $value;
        self::$flash[$key] = $value;
    }

    public static function old(?string $key = null): mixed
    {
        if ($key === null) {
            return $_SESSION['old'] ?? [];
        }

        return $_SESSION['old'][$key] ?? null;
    }

    public static function flush(): void
    {
        $_SESSION['old'] = [];
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
        self::$flash[$key] = $value;
    }

    public static function get(string $key): mixed
    {
        return self::$flash[$key] ?? null;
    }

    public static function delete(string $key): void
    {
        unset($_SESSION[$key]);
        unset(self::$flash[$key]);
    }
}