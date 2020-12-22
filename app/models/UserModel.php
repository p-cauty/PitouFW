<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 07/08/2019
 * Time: 00:11
 */

namespace PitouFW\Model;


use PitouFW\Core\DB;
use PitouFW\Core\Mailer;
use PitouFW\Core\Redis;
use PitouFW\Core\Router;
use PitouFW\Core\Utils;
use PitouFW\Entity\EmailUpdate;
use PitouFW\Entity\User;
use function PitouFW\Core\t;

class UserModel {
    const SESSION_COOKIE_NAME = 'PTFW_SESSID';
    const SESSION_CACHE_PREFIX = 'session_';
    const SESSION_CACHE_TTL_DEFAULT = 86400; // 1 day
    const SESSION_CACHE_TTL_LONG = 86400 * 366; // 1 year

    const ACCOUNT_VALIDATION_CACHE_PREFIX = 'valid_';
    const ACCOUNT_VALIDATION_CACHE_TTL = 86400; // 1 day

    const FORGOT_PASSWD_EMAIL_COOLDOWN_PREFIX = 'forgot_passwd_email_';
    const FORGOT_PASSWD_EMAIL_COOLDOWN_TTL = 300; // 5 min

    const FORGOT_PASSWD_IP_COOLDOWN_PREFIX = 'forgot_passwd_ip_';
    const FORGOT_PASSWD_IP_COOLDOWN_ATTEMPTS = 5;
    const FORGOT_PASSWD_IP_COOLDOWN_TTL = 60; // 1 min
    const FORGOT_PASSWD_IP_COOLDOWN_BAN = 600; // 10 min

    const RESEND_EMAIL_UID_COOLDOWN_PREFIX = 'resend_uid_';
    const RESEND_EMAIL_UID_COOLDOWN_TTL = 300; // 5 min;

    const RESEND_EMAIL_IP_COOLDOWN_PREFIX = 'resend_ip_';
    const RESEND_EMAIL_IP_COOLDOWN_ATTEMPTS = 5;
    const RESEND_EMAIL_IP_COOLDOWN_TTL = 60; // 1 min;
    const RESEND_EMAIL_IP_COOLDOWN_BAN = 600; // 10 min;

    const UPDATE_EMAIL_COOLDOWN_PREFIX = 'update_';
    const UPDATE_EMAIL_COOLDOWN_ATTEMPTS = 2;
    const UPDATE_EMAIL_COOLDOWN_TTL = 86400; // 1 day

    private static ?User $user = null;

    public static function hashInfo(string $info): string {
        return hash('sha512', $info);
    }

    public static function generateSessionToken(): string {
        return sha1(uniqid());
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

    public static function rejectGuests(): void {
        if (!self::isLogged()) {
            Router::redirect('user/login');
        }
    }

    public static function rejectUsers(): void {
        if (self::isLogged()) {
            Router::redirect();
        }
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

    public static function isPasswdResetTokenValid(string $token): bool {
        $minus_24h = Utils::time() - 86400;
        $datetime_to_compare_with = Utils::datetime($minus_24h);

        $req = DB::get()->prepare("
            SELECT COUNT(*) AS cnt
            FROM passwd_reset
            WHERE token = ?
            AND used_at IS NULL
            AND requested_at > ?
        ");
        $req->execute([$token, $datetime_to_compare_with]);
        $res = $req->fetch();

        return $res['cnt'] > 0;
    }
}
