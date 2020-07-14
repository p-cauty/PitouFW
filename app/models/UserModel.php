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
        $cache_key = self::SESSION_COOKIE_NAME . Utils::secure($_COOKIE[self::SESSION_COOKIE_NAME] ?? '');
        return $redis->get($cache_key);
    }

    public static function isLogged(): bool {
        if (isset($_COOKIE[self::SESSION_COOKIE_NAME])) {
            $cached = self::getCachedValue();
            return $cached !== false;
        }

        return false;
    }

    public static function get(): ?User {
        if (self::isLogged()) {
            if (self::$user === null) {
                $uid = self::getCachedValue();
                self::$user = User::read($uid);
            }

            return self::$user;
        }

        return null;
    }
}