<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 07/08/2019
 * Time: 00:11
 */

namespace PitouFW\Model;


use PitouFW\Core\Redis;
use PitouFW\Core\Utils;
use PitouFW\Entity\User;

class UserModel {
    const SESSION_COOKIE_NAME = 'PTFW_SESSID';
    const SESSION_CACHE_PREFIX = 'session_';
    const SESSION_CACHE_TTL = 86400; // 1 day

    private static ?User $user = null;

    public static function hashInfo(string $info): string {
        return hash('sha512', $info);
    }

    public static function generateSessionToken(): string {
        return sha1(uniqid());
    }

    public static function login(User $user, int $ttl = self::SESSION_CACHE_TTL): bool {
        if (!User::exists('id', $user->getId())) {
            var_dump('user do not exists');
            return false;
        }

        $session_token = self::generateSessionToken();
        $redis = new Redis();
        $cache_key = self::SESSION_CACHE_PREFIX . $session_token;
        $cookie_set = setcookie(self::SESSION_COOKIE_NAME, $session_token, Utils::time() + $ttl, WEBROOT, PROD_HOST);
        $redis_set = $redis->set($cache_key, $user->getId(), $ttl);

        return $cookie_set && $redis_set;
    }

    private static function getCachedValue() {
        $redis = new Redis();
        $cache_key = self::SESSION_CACHE_PREFIX . Utils::secure($_COOKIE[self::SESSION_COOKIE_NAME] ?? '');
        return $redis->get($cache_key);
    }

    public static function isLogged(): bool {
        if (self::$user !== null) {
            return true;
        }

        if (isset($_COOKIE[self::SESSION_COOKIE_NAME])) {
            $uid = self::getCachedValue();
            return User::exists('id', $uid) && $uid !== false;
        }

        return false;
    }

    public static function get(): ?User {
        if (self::isLogged()) {
            if (self::$user === null) {
                $uid = self::getCachedValue();
                if (User::exists('id', $uid)) {
                    self::$user = User::read($uid);
                }
            }

            return self::$user;
        }

        return null;
    }

    public static function validatePassword(string $passwd): bool {
        $lower = preg_match("#[a-z]+#", $passwd) === 1;
        $upper = preg_match("#[A-Z]+#", $passwd) === 1;
        $digit = preg_match("#[0-9]+#", $passwd) === 1;
        $length = strlen($passwd) >= PASSWD_MINIMAL_LENGTH;

        return $lower && $upper && $digit && $length;
    }

    public static function hashPassword(string $passwd): string {
        return password_hash($passwd, PASSWORD_DEFAULT);
    }

    public static function checkPassword(string $passwd, string $hash): bool {
        return password_verify($passwd, $hash);
    }

    public static function logout() {
        $session_token = $_COOKIE[self::SESSION_COOKIE_NAME] ?? null;

        if ($session_token !== null) {
            setcookie(self::SESSION_COOKIE_NAME, '', -1, WEBROOT, PROD_HOST);
            $redis = new Redis();
            $cache_key = self::SESSION_CACHE_PREFIX . $session_token;
            $res = $redis->del($cache_key);

            return $res > 0;
        }

        return true;
    }
}